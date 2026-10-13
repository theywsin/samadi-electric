<?php
/**
 * Newsletter (خبرنامه): mobile-number sign-up form, subscriber table and admin list.
 *
 * The theme's homepage newsletter strip renders [samadi_newsletter] when no other newsletter
 * shortcode is configured. Each new subscriber fires `samadi_core_newsletter_subscribed`, which an
 * SMS integration can use to send a welcome message.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Newsletter module.
 */
final class Samadi_Core_Newsletter {

	/**
	 * Admin page slug.
	 */
	const PAGE = 'samadi-core-subscribers';

	/**
	 * Sign-ups allowed per visitor IP in RATE_WINDOW seconds.
	 */
	const RATE_LIMIT = 5;

	/**
	 * Rate-limit window in seconds.
	 */
	const RATE_WINDOW = 600;

	/**
	 * Subscriber table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;

		return $wpdb->prefix . 'samadi_subscribers';
	}

	/**
	 * Create / upgrade the subscriber table.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mobile varchar(15) NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'subscribed',
				source varchar(50) NOT NULL DEFAULT '',
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY mobile (mobile),
				KEY status (status)
			) {$charset};"
		);

		update_option( 'samadi_core_db_version', SAMADI_CORE_DB_VERSION, false );
	}

	/**
	 * Install the table on sites updated without re-activation.
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'samadi_core_db_version' ) !== SAMADI_CORE_DB_VERSION ) {
			self::install();
		}
	}

	/**
	 * Admin hooks (the subscriber list stays reachable when the form is switched off).
	 */
	public static function admin_init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_samadi_core_export_subscribers', array( __CLASS__, 'export' ) );
	}

	/**
	 * Front-end hooks.
	 */
	public static function init() {
		add_shortcode( 'samadi_newsletter', array( __CLASS__, 'shortcode' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_post_samadi_newsletter', array( __CLASS__, 'handle_post' ) );
		add_action( 'admin_post_nopriv_samadi_newsletter', array( __CLASS__, 'handle_post' ) );
		add_filter( 'samadi_core_script_data', array( __CLASS__, 'script_data' ) );
	}

	/**
	 * Setting with its default when left empty.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function text( $key ) {
		$value    = trim( (string) samadi_core_setting( $key ) );
		$defaults = samadi_core_setting_defaults();

		return '' !== $value ? $value : (string) $defaults[ $key ];
	}

	/**
	 * Messages by result code.
	 *
	 * @param string $code Result code.
	 * @return string
	 */
	public static function message( $code ) {
		switch ( $code ) {
			case 'ok':
				return self::text( 'newsletter_success' );
			case 'exists':
				return __( 'این شماره قبلاً در خبرنامه عضو شده است.', 'samadi-core' );
			case 'invalid':
				return __( 'لطفاً شماره موبایل معتبر وارد کنید؛ مثل ۰۹۱۲۳۴۵۶۷۸۹.', 'samadi-core' );
			case 'limit':
				return __( 'تعداد تلاش‌ها زیاد بود. چند دقیقه دیگر دوباره امتحان کنید.', 'samadi-core' );
			default:
				return __( 'ثبت عضویت انجام نشد. لطفاً دوباره تلاش کنید.', 'samadi-core' );
		}
	}

	/**
	 * Subscribe a mobile number.
	 *
	 * @param string $mobile Raw mobile input.
	 * @param string $source Where the form was shown.
	 * @return string Result code: ok|exists|invalid|limit|error.
	 */
	public static function subscribe( $mobile, $source = '' ) {
		global $wpdb;

		$mobile = samadi_core_normalize_mobile( $mobile );

		if ( ! samadi_core_is_mobile( $mobile ) ) {
			return 'invalid';
		}

		if ( ! self::within_rate_limit() ) {
			return 'limit';
		}

		$table  = self::table();
		$source = substr( sanitize_key( $source ), 0, 50 );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$table} WHERE mobile = %s", $mobile ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $row && 'subscribed' === $row->status ) {
			return 'exists';
		}

		if ( $row ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$saved = $wpdb->update( $table, array( 'status' => 'subscribed' ), array( 'id' => (int) $row->id ), array( '%s' ), array( '%d' ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$saved = $wpdb->insert(
				$table,
				array(
					'mobile'     => $mobile,
					'status'     => 'subscribed',
					'source'     => $source,
					'user_id'    => get_current_user_id(),
					'created_at' => current_time( 'mysql', true ),
				),
				array( '%s', '%s', '%s', '%d', '%s' )
			);
		}

		if ( false === $saved ) {
			return 'error';
		}

		self::notify( $mobile, $source );

		/**
		 * A mobile number joined the newsletter.
		 *
		 * @param string $mobile Normalized mobile (09XXXXXXXXX).
		 * @param string $source Form source.
		 */
		do_action( 'samadi_core_newsletter_subscribed', $mobile, $source );

		return 'ok';
	}

	/**
	 * Count this attempt against the visitor's IP; false when over the limit.
	 *
	 * @return bool
	 */
	private static function within_rate_limit() {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'samadi_nl_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 32 );
		$hit = (int) get_transient( $key );

		if ( $hit >= self::RATE_LIMIT ) {
			return false;
		}

		set_transient( $key, $hit + 1, self::RATE_WINDOW );

		return true;
	}

	/**
	 * Email the store about a new subscriber, when an address is set.
	 *
	 * @param string $mobile Mobile.
	 * @param string $source Source.
	 */
	private static function notify( $mobile, $source ) {
		$to = sanitize_email( (string) samadi_core_setting( 'newsletter_notify' ) );

		if ( ! $to ) {
			return;
		}

		/* translators: %s: site name */
		$subject = sprintf( __( '[%s] عضو جدید خبرنامه', 'samadi-core' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
		/* translators: 1: mobile number, 2: form source */
		$body = sprintf( __( "شماره موبایل: %1\$s\nمحل فرم: %2\$s", 'samadi-core' ), $mobile, $source ? $source : '-' );

		wp_mail( $to, $subject, $body );
	}

	/**
	 * REST route.
	 */
	public static function routes() {
		register_rest_route(
			'samadi-core/v1',
			'/newsletter',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'rest_subscribe' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'mobile'  => array(
						'type'     => 'string',
						'required' => true,
					),
					'source'  => array(
						'type'    => 'string',
						'default' => '',
					),
					'website' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * POST /newsletter.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_subscribe( $request ) {
		// Honeypot: bots fill every field. Pretend success.
		$code = '' !== trim( (string) $request['website'] ) ? 'ok' : self::subscribe( (string) $request['mobile'], (string) $request['source'] );

		return new WP_REST_Response(
			array(
				'code'    => $code,
				'success' => in_array( $code, array( 'ok', 'exists' ), true ),
				'message' => self::message( $code ),
			),
			in_array( $code, array( 'ok', 'exists' ), true ) ? 200 : ( 'limit' === $code ? 429 : 400 )
		);
	}

	/**
	 * No-JavaScript form submission (admin-post.php).
	 */
	public static function handle_post() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Public sign-up form on cacheable pages; protected by honeypot and rate limit.
		$honeypot = isset( $_POST['website'] ) ? sanitize_text_field( wp_unslash( $_POST['website'] ) ) : '';
		$mobile   = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		$source   = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
		// phpcs:enable

		$code     = '' !== $honeypot ? 'ok' : self::subscribe( $mobile, $source );
		$redirect = wp_get_referer();
		$redirect = $redirect ? $redirect : home_url( '/' );

		wp_safe_redirect( add_query_arg( 'se_newsletter', $code, remove_query_arg( 'se_newsletter', $redirect ) ) . '#se-newsletter' );
		exit;
	}

	/**
	 * Front-end data.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function script_data( $data ) {
		$data['newsletter'] = array(
			'invalid' => self::message( 'invalid' ),
			'sending' => __( 'در حال ثبت…', 'samadi-core' ),
		);

		return $data;
	}

	/**
	 * [samadi_newsletter source="footer" button="عضویت" placeholder="…"].
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		static $n = 0;
		++$n;

		$atts = shortcode_atts(
			array(
				'source'      => is_front_page() ? 'home' : 'shortcode',
				'button'      => self::text( 'newsletter_button' ),
				'placeholder' => self::text( 'newsletter_placeholder' ),
			),
			$atts,
			'samadi_newsletter'
		);

		// Result of a no-JavaScript submission.
		$code    = isset( $_GET['se_newsletter'] ) ? sanitize_key( wp_unslash( $_GET['se_newsletter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$message = $code && 1 === $n ? self::message( $code ) : '';
		$ok      = in_array( $code, array( 'ok', 'exists' ), true );
		$id      = 'se-newsletter-mobile-' . $n;

		ob_start();
		?>
		<form class="se-core-newsletter" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-se-newsletter novalidate<?php echo 1 === $n ? ' id="se-newsletter"' : ''; ?>>
			<input type="hidden" name="action" value="samadi_newsletter">
			<input type="hidden" name="source" value="<?php echo esc_attr( sanitize_key( $atts['source'] ) ); ?>">
			<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'شماره موبایل', 'samadi-core' ); ?></label>
			<div class="se-core-newsletter__row">
				<input class="se-core-newsletter__input" type="tel" id="<?php echo esc_attr( $id ); ?>" name="mobile" inputmode="numeric" autocomplete="tel" dir="ltr" maxlength="20" required placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>" aria-describedby="<?php echo esc_attr( $id ); ?>-msg">
				<button type="submit" class="se-btn se-btn--primary se-core-newsletter__submit"><?php echo esc_html( $atts['button'] ); ?></button>
			</div>
			<div class="se-core-hp" aria-hidden="true">
				<label><?php esc_html_e( 'وب‌سایت', 'samadi-core' ); ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
			</div>
			<p class="se-core-newsletter__msg<?php echo $message ? ( $ok ? ' is-success' : ' is-error' ) : ''; ?>" id="<?php echo esc_attr( $id ); ?>-msg" role="status" aria-live="polite"><?php echo esc_html( $message ); ?></p>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Admin submenu.
	 */
	public static function menu() {
		$hook = add_submenu_page(
			Samadi_Core_Settings::PAGE,
			__( 'اعضای خبرنامه', 'samadi-core' ),
			__( 'اعضای خبرنامه', 'samadi-core' ),
			Samadi_Core_Settings::capability(),
			self::PAGE,
			array( __CLASS__, 'render_admin' )
		);

		add_action( 'load-' . $hook, array( __CLASS__, 'handle_admin_actions' ) );
	}

	/**
	 * Delete subscribers (row and bulk actions).
	 */
	public static function handle_admin_actions() {
		global $wpdb;

		if ( ! current_user_can( Samadi_Core_Settings::capability() ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified below.
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( '-1' === $action || '' === $action ) {
			$action = isset( $_REQUEST['action2'] ) ? sanitize_key( wp_unslash( $_REQUEST['action2'] ) ) : '';
		}
		// phpcs:enable

		if ( 'delete' !== $action ) {
			return;
		}

		if ( isset( $_REQUEST['subscriber'] ) && ! is_array( $_REQUEST['subscriber'] ) ) {
			check_admin_referer( 'samadi_core_delete_subscriber_' . absint( $_REQUEST['subscriber'] ) );
			$ids = array( absint( $_REQUEST['subscriber'] ) );
		} else {
			check_admin_referer( 'bulk-subscribers' );
			$ids = isset( $_REQUEST['subscriber'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['subscriber'] ) ) : array();
		}

		$ids = array_filter( $ids );
		if ( $ids ) {
			$table        = self::table();
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE, 'deleted' => count( $ids ) ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Admin list page.
	 */
	public static function render_admin() {
		if ( ! current_user_can( Samadi_Core_Settings::capability() ) ) {
			return;
		}

		require_once SAMADI_CORE_DIR . 'includes/modules/class-newsletter-table.php';

		$table = new Samadi_Core_Newsletter_Table();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'اعضای خبرنامه', 'samadi-core' ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=samadi_core_export_subscribers' ), 'samadi_core_export_subscribers' ) ); ?>"><?php esc_html_e( 'خروجی CSV', 'samadi-core' ); ?></a>
			<hr class="wp-header-end">
			<?php if ( ! samadi_core_module_enabled( 'newsletter' ) ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'ماژول خبرنامه خاموش است؛ فرم عضویت در سایت نمایش داده نمی‌شود.', 'samadi-core' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					/* translators: %s: number of deleted subscribers */
					printf( esc_html__( '%s عضو حذف شد.', 'samadi-core' ), esc_html( samadi_core_number( absint( $_GET['deleted'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					?>
				</p></div>
			<?php endif; ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE ); ?>">
				<?php
				$table->search_box( __( 'جستجوی شماره', 'samadi-core' ), 'samadi-subscriber' );
				$table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * CSV export of all subscribed numbers.
	 */
	public static function export() {
		global $wpdb;

		if ( ! current_user_can( Samadi_Core_Settings::capability() ) ) {
			wp_die( esc_html__( 'دسترسی ندارید.', 'samadi-core' ), 403 );
		}

		check_admin_referer( 'samadi_core_export_subscribers' );

		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT mobile, status, source, created_at FROM {$table} ORDER BY id DESC", ARRAY_A );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=samadi-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- BOM so Excel reads UTF-8.
		fputcsv( $out, array( 'mobile', 'status', 'source', 'created_at_utc' ) );
		foreach ( (array) $rows as $row ) {
			fputcsv( $out, array( $row['mobile'], $row['status'], $row['source'], $row['created_at'] ) );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
}
