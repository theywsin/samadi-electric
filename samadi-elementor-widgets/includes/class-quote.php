<?php
/**
 * Wholesale purchase / price inquiry requests.
 *
 * - Front-end form ([samadi_quote_form] or the Elementor widget) posts to admin-post.php.
 * - Protected by nonce, honeypot, minimum fill time and per-IP rate limiting; all input sanitized.
 * - Requests are stored as a private post type (WooCommerce → درخواست‌های استعلام), admin is emailed.
 * - Errors and old input travel through a short-lived server-side transient, never in the URL.
 * - "Price on request" products: per-product checkbox, not purchasable, price replaced by a quote link.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quote requests.
 */
class Samadi_EW_Quote {

	const POST_TYPE  = 'se_quote';
	const ACTION     = 'samadi_quote';
	const META_POR   = '_se_price_on_request';
	const MAX_ITEMS  = 30;
	const RATE_LIMIT = 5;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( __CLASS__, 'handle' ) );

		// Admin.
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_admin' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );

		// Price on request.
		add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'product_field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_field' ) );
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'is_purchasable' ), 20, 2 );
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'price_html' ), 20, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( __CLASS__, 'loop_button_text' ), 20, 2 );

		// Product page button.
		add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'single_button' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_button_not_purchasable' ), 31 );
	}

	/* ---------------------------------------------------------------------
	 * Storage
	 * ------------------------------------------------------------------ */

	/**
	 * Private post type for requests (only shop managers can see them).
	 */
	public static function register_post_type() {
		$cap = 'manage_woocommerce';

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'درخواست‌های استعلام', 'samadi-elementor-widgets' ),
					'singular_name' => __( 'درخواست استعلام', 'samadi-elementor-widgets' ),
					'menu_name'     => __( 'استعلام و خرید عمده', 'samadi-elementor-widgets' ),
					'all_items'     => __( 'درخواست‌های استعلام', 'samadi-elementor-widgets' ),
					'edit_item'     => __( 'مشاهده درخواست', 'samadi-elementor-widgets' ),
					'search_items'  => __( 'جستجوی درخواست‌ها', 'samadi-elementor-widgets' ),
					'not_found'     => __( 'درخواستی ثبت نشده است.', 'samadi-elementor-widgets' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => 'woocommerce',
				'show_in_rest'        => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => false,
				'capabilities'        => array(
					'edit_post'          => $cap,
					'read_post'          => $cap,
					'delete_post'        => $cap,
					'edit_posts'         => $cap,
					'edit_others_posts'  => $cap,
					'delete_posts'       => $cap,
					'publish_posts'      => $cap,
					'read_private_posts' => $cap,
					'create_posts'       => 'do_not_allow',
				),
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Front-end form
	 * ------------------------------------------------------------------ */

	/**
	 * Render the request form.
	 *
	 * @param array $args { title, intro, button }.
	 * @return string
	 */
	public static function render_form( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'  => __( 'فرم درخواست خرید عمده و استعلام قیمت', 'samadi-elementor-widgets' ),
				'intro'  => __( 'فهرست کالاهای موردنیاز را وارد کنید؛ کارشناسان فروش در اولین فرصت با شما تماس می‌گیرند.', 'samadi-elementor-widgets' ),
				'button' => __( 'ثبت درخواست', 'samadi-elementor-widgets' ),
			)
		);

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Display-only flags.
		$status = isset( $_GET['se_quote'] ) ? sanitize_key( wp_unslash( $_GET['se_quote'] ) ) : '';
		$token  = isset( $_GET['se_quote_token'] ) ? sanitize_key( wp_unslash( $_GET['se_quote_token'] ) ) : '';
		$pid    = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
		// phpcs:enable

		$state  = $token ? get_transient( 'se_quote_' . $token ) : false;
		$errors = is_array( $state ) && isset( $state['errors'] ) ? (array) $state['errors'] : array();
		$old    = is_array( $state ) && isset( $state['old'] ) ? (array) $state['old'] : array();

		if ( 'sent' === $status ) {
			return '<div id="se-quote-form" class="se-quote se-quote--sent" role="status" tabindex="-1"><div class="se-quote__success">' . samadi_ew_icon( 'check', 40 ) . '<h2>' . esc_html__( 'درخواست شما ثبت شد', 'samadi-elementor-widgets' ) . '</h2><p>' . esc_html__( 'از اعتماد شما سپاسگزاریم. کارشناسان فروش به‌زودی با شما تماس می‌گیرند.', 'samadi-elementor-widgets' ) . '</p><a class="se-btn se-btn--primary" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'بازگشت به فروشگاه', 'samadi-elementor-widgets' ) . '</a></div></div>';
		}

		$items = isset( $old['items'] ) && is_array( $old['items'] ) ? $old['items'] : array();

		if ( ! $items && $pid ) {
			$product = wc_get_product( $pid );
			if ( $product && 'publish' === $product->get_status() && $product->is_visible() ) {
				$items[] = array(
					'name' => $product->get_name() . ( $product->get_sku() ? ' (' . $product->get_sku() . ')' : '' ),
					'qty'  => '',
				);
			}
		}

		while ( count( $items ) < 3 ) {
			$items[] = array(
				'name' => '',
				'qty'  => '',
			);
		}

		$val = static function ( $key ) use ( $old ) {
			return isset( $old[ $key ] ) ? (string) $old[ $key ] : '';
		};

		$field_error = static function ( $key ) use ( $errors ) {
			return isset( $errors[ $key ] ) ? '<span class="se-field__error" id="se-q-' . esc_attr( $key ) . '-error">' . esc_html( $errors[ $key ] ) . '</span>' : '';
		};

		$described = static function ( $key ) use ( $errors ) {
			return isset( $errors[ $key ] ) ? ' aria-invalid="true" aria-describedby="se-q-' . esc_attr( $key ) . '-error"' : '';
		};

		ob_start();
		?>
		<div id="se-quote-form" class="se-quote" tabindex="-1">
			<?php if ( $args['title'] ) : ?>
				<h2 class="se-quote__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $args['intro'] ) : ?>
				<p class="se-quote__intro"><?php echo esc_html( $args['intro'] ); ?></p>
			<?php endif; ?>

			<?php if ( $errors ) : ?>
				<div class="se-quote__errors" role="alert">
					<p><?php esc_html_e( 'لطفاً موارد زیر را بررسی کنید:', 'samadi-elementor-widgets' ); ?></p>
					<ul>
						<?php foreach ( $errors as $message ) : ?>
							<li><?php echo esc_html( $message ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<form class="se-quote__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<input type="hidden" name="se_return" value="<?php echo esc_url( self::current_url() ); ?>">
				<input type="hidden" name="se_ts" value="<?php echo esc_attr( (string) time() ); ?>">
				<?php wp_nonce_field( self::ACTION, 'se_quote_nonce' ); ?>
				<div class="se-hp" aria-hidden="true">
					<label for="se-q-website"><?php esc_html_e( 'این فیلد را خالی بگذارید', 'samadi-elementor-widgets' ); ?></label>
					<input type="text" id="se-q-website" name="se_website" value="" tabindex="-1" autocomplete="off">
				</div>

				<div class="se-quote__grid">
					<p class="se-field">
						<label for="se-q-name"><?php esc_html_e( 'نام و نام خانوادگی', 'samadi-elementor-widgets' ); ?> <abbr class="required" title="<?php esc_attr_e( 'الزامی', 'samadi-elementor-widgets' ); ?>">*</abbr></label>
						<input type="text" id="se-q-name" name="se_name" required maxlength="100" autocomplete="name" value="<?php echo esc_attr( $val( 'name' ) ); ?>"<?php echo $described( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo $field_error( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
					<p class="se-field">
						<label for="se-q-phone"><?php esc_html_e( 'شماره تماس', 'samadi-elementor-widgets' ); ?> <abbr class="required" title="<?php esc_attr_e( 'الزامی', 'samadi-elementor-widgets' ); ?>">*</abbr></label>
						<input type="tel" id="se-q-phone" name="se_phone" required maxlength="20" inputmode="tel" autocomplete="tel" dir="ltr" value="<?php echo esc_attr( $val( 'phone' ) ); ?>"<?php echo $described( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo $field_error( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
					<p class="se-field">
						<label for="se-q-company"><?php esc_html_e( 'نام شرکت / فروشگاه', 'samadi-elementor-widgets' ); ?></label>
						<input type="text" id="se-q-company" name="se_company" maxlength="120" autocomplete="organization" value="<?php echo esc_attr( $val( 'company' ) ); ?>">
					</p>
					<p class="se-field">
						<label for="se-q-email"><?php esc_html_e( 'ایمیل (اختیاری)', 'samadi-elementor-widgets' ); ?></label>
						<input type="email" id="se-q-email" name="se_email" maxlength="120" autocomplete="email" dir="ltr" value="<?php echo esc_attr( $val( 'email' ) ); ?>"<?php echo $described( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo $field_error( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
				</div>

				<fieldset class="se-quote__items">
					<legend><?php esc_html_e( 'فهرست محصولات', 'samadi-elementor-widgets' ); ?> <abbr class="required" title="<?php esc_attr_e( 'الزامی', 'samadi-elementor-widgets' ); ?>">*</abbr></legend>
					<?php echo $field_error( 'items' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div class="se-quote__rows" data-se-quote-rows data-max="<?php echo esc_attr( (string) self::MAX_ITEMS ); ?>">
						<?php foreach ( $items as $i => $item ) : ?>
							<div class="se-quote__row">
								<label class="screen-reader-text" for="se-q-item-<?php echo esc_attr( (string) $i ); ?>"><?php esc_html_e( 'نام یا کد کالا', 'samadi-elementor-widgets' ); ?></label>
								<input type="text" id="se-q-item-<?php echo esc_attr( (string) $i ); ?>" name="se_items[<?php echo esc_attr( (string) $i ); ?>][name]" maxlength="200" placeholder="<?php esc_attr_e( 'نام یا کد کالا', 'samadi-elementor-widgets' ); ?>" value="<?php echo esc_attr( isset( $item['name'] ) ? (string) $item['name'] : '' ); ?>">
								<label class="screen-reader-text" for="se-q-qty-<?php echo esc_attr( (string) $i ); ?>"><?php esc_html_e( 'تعداد', 'samadi-elementor-widgets' ); ?></label>
								<input type="number" id="se-q-qty-<?php echo esc_attr( (string) $i ); ?>" name="se_items[<?php echo esc_attr( (string) $i ); ?>][qty]" min="1" max="1000000" inputmode="numeric" placeholder="<?php esc_attr_e( 'تعداد', 'samadi-elementor-widgets' ); ?>" value="<?php echo esc_attr( isset( $item['qty'] ) ? (string) $item['qty'] : '' ); ?>">
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="se-btn se-btn--outline se-quote__add" data-se-quote-add><?php echo samadi_ew_icon( 'plus', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'افزودن ردیف', 'samadi-elementor-widgets' ); ?></button>
				</fieldset>

				<p class="se-field">
					<label for="se-q-notes"><?php esc_html_e( 'توضیحات', 'samadi-elementor-widgets' ); ?></label>
					<textarea id="se-q-notes" name="se_notes" rows="4" maxlength="2000"><?php echo esc_textarea( $val( 'notes' ) ); ?></textarea>
				</p>

				<button type="submit" class="se-btn se-btn--primary se-btn--lg"><?php echo esc_html( $args['button'] ); ?></button>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Current URL (for redirect back).
	 *
	 * @return string
	 */
	private static function current_url() {
		global $wp;

		$url = home_url( add_query_arg( array(), isset( $wp->request ) ? $wp->request : '' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['product_id'] ) ) {
			$url = add_query_arg( 'product_id', absint( $_GET['product_id'] ), $url ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $url;
	}

	/**
	 * Handle submission.
	 */
	public static function handle() {
		$return = isset( $_POST['se_return'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['se_return'] ) ), home_url( '/' ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$return = remove_query_arg( array( 'se_quote', 'se_quote_token' ), $return );

		$fail = static function ( $errors, $old ) use ( $return ) {
			$token = strtolower( wp_generate_password( 20, false, false ) );
			set_transient( 'se_quote_' . $token, array( 'errors' => $errors, 'old' => $old ), 10 * MINUTE_IN_SECONDS );
			wp_safe_redirect( add_query_arg( array( 'se_quote' => 'error', 'se_quote_token' => $token ), $return ) . '#se-quote-form' );
			exit;
		};

		if ( ! isset( $_POST['se_quote_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['se_quote_nonce'] ) ), self::ACTION ) ) {
			$fail( array( 'form' => __( 'نشست شما منقضی شده است. لطفاً فرم را دوباره ارسال کنید.', 'samadi-elementor-widgets' ) ), array() );
		}

		$old = array(
			'name'    => isset( $_POST['se_name'] ) ? sanitize_text_field( wp_unslash( $_POST['se_name'] ) ) : '',
			'phone'   => isset( $_POST['se_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['se_phone'] ) ) : '',
			'company' => isset( $_POST['se_company'] ) ? sanitize_text_field( wp_unslash( $_POST['se_company'] ) ) : '',
			'email'   => isset( $_POST['se_email'] ) ? sanitize_email( wp_unslash( $_POST['se_email'] ) ) : '',
			'notes'   => isset( $_POST['se_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['se_notes'] ) ) : '',
			'items'   => array(),
		);

		$raw_items = isset( $_POST['se_items'] ) && is_array( $_POST['se_items'] ) ? wp_unslash( $_POST['se_items'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized per field below.
		foreach ( array_slice( $raw_items, 0, self::MAX_ITEMS ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = isset( $row['name'] ) ? sanitize_text_field( (string) $row['name'] ) : '';
			$qty  = isset( $row['qty'] ) ? absint( Samadi_EW_Search::sanitize_term( (string) $row['qty'] ) ) : 0;
			if ( '' !== $name ) {
				$old['items'][] = array(
					'name' => mb_substr( $name, 0, 200 ),
					'qty'  => $qty ? min( $qty, 1000000 ) : '',
				);
			}
		}

		// Honeypot or submitted faster than a human could: silently pretend success.
		$ts = isset( $_POST['se_ts'] ) ? absint( $_POST['se_ts'] ) : 0;
		if ( ! empty( $_POST['se_website'] ) || ( $ts && ( time() - $ts ) < 3 ) ) {
			wp_safe_redirect( add_query_arg( 'se_quote', 'sent', $return ) . '#se-quote-form' );
			exit;
		}

		$errors = array();
		if ( '' === $old['name'] ) {
			$errors['name'] = __( 'نام و نام خانوادگی را وارد کنید.', 'samadi-elementor-widgets' );
		}
		$digits = preg_replace( '/\D/', '', Samadi_EW_Search::sanitize_term( $old['phone'] ) );
		if ( strlen( $digits ) < 8 || strlen( $digits ) > 15 ) {
			$errors['phone'] = __( 'شماره تماس معتبر وارد کنید.', 'samadi-elementor-widgets' );
		}
		if ( '' !== $old['email'] && ! is_email( $old['email'] ) ) {
			$errors['email'] = __( 'ایمیل واردشده معتبر نیست.', 'samadi-elementor-widgets' );
		}
		if ( ! $old['items'] ) {
			$errors['items'] = __( 'دست‌کم یک کالا وارد کنید.', 'samadi-elementor-widgets' );
		}

		if ( $errors ) {
			$fail( $errors, $old );
		}

		// Rate limit per IP (hashed, no raw IP stored).
		$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$rate_key = 'se_quote_rl_' . substr( wp_hash( $ip ), 0, 20 );
		$count    = (int) get_transient( $rate_key );
		if ( $count >= self::RATE_LIMIT ) {
			$fail( array( 'form' => __( 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً کمی بعد دوباره تلاش کنید یا تماس بگیرید.', 'samadi-elementor-widgets' ) ), $old );
		}
		set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'private',
				/* translators: 1: name, 2: company. */
				'post_title'  => trim( sprintf( __( '%1$s %2$s', 'samadi-elementor-widgets' ), $old['name'], $old['company'] ? '— ' . $old['company'] : '' ) ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$fail( array( 'form' => __( 'ثبت درخواست با خطا مواجه شد. لطفاً دوباره تلاش کنید.', 'samadi-elementor-widgets' ) ), $old );
		}

		update_post_meta( $post_id, '_se_name', $old['name'] );
		update_post_meta( $post_id, '_se_phone', $old['phone'] );
		update_post_meta( $post_id, '_se_company', $old['company'] );
		update_post_meta( $post_id, '_se_email', $old['email'] );
		update_post_meta( $post_id, '_se_notes', $old['notes'] );
		update_post_meta( $post_id, '_se_items', $old['items'] );
		update_post_meta( $post_id, '_se_status', 'new' );
		update_post_meta( $post_id, '_se_customer_id', get_current_user_id() );

		self::notify( $post_id, $old );

		/**
		 * Fires after a quote request is stored.
		 *
		 * @param int   $post_id Request ID.
		 * @param array $data    Sanitized data.
		 */
		do_action( 'samadi_quote_submitted', $post_id, $old );

		wp_safe_redirect( add_query_arg( 'se_quote', 'sent', $return ) . '#se-quote-form' );
		exit;
	}

	/**
	 * Email the store.
	 *
	 * @param int   $post_id Request ID.
	 * @param array $data    Data.
	 */
	private static function notify( $post_id, $data ) {
		$to = samadi_ew_setting( 'quote_email' );
		$to = $to ? $to : get_option( 'admin_email' );

		$lines   = array();
		$lines[] = __( 'درخواست استعلام قیمت / خرید عمده جدید', 'samadi-elementor-widgets' );
		$lines[] = '';
		$lines[] = __( 'نام:', 'samadi-elementor-widgets' ) . ' ' . $data['name'];
		$lines[] = __( 'تلفن:', 'samadi-elementor-widgets' ) . ' ' . $data['phone'];
		if ( $data['company'] ) {
			$lines[] = __( 'شرکت:', 'samadi-elementor-widgets' ) . ' ' . $data['company'];
		}
		if ( $data['email'] ) {
			$lines[] = __( 'ایمیل:', 'samadi-elementor-widgets' ) . ' ' . $data['email'];
		}
		$lines[] = '';
		$lines[] = __( 'اقلام:', 'samadi-elementor-widgets' );
		foreach ( $data['items'] as $item ) {
			$lines[] = '- ' . $item['name'] . ( $item['qty'] ? ' × ' . $item['qty'] : '' );
		}
		if ( $data['notes'] ) {
			$lines[] = '';
			$lines[] = __( 'توضیحات:', 'samadi-elementor-widgets' ) . ' ' . $data['notes'];
		}
		$lines[] = '';
		$lines[] = admin_url( 'post.php?post=' . absint( $post_id ) . '&action=edit' );

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		if ( $data['email'] && is_email( $data['email'] ) ) {
			$headers[] = 'Reply-To: ' . $data['email'];
		}

		/* translators: %d: request ID. */
		wp_mail( $to, sprintf( __( 'درخواست استعلام جدید #%d', 'samadi-elementor-widgets' ), $post_id ), implode( "\n", $lines ), $headers );
	}

	/* ---------------------------------------------------------------------
	 * Admin screens
	 * ------------------------------------------------------------------ */

	/**
	 * Status labels.
	 *
	 * @return array<string, string>
	 */
	public static function statuses() {
		return array(
			'new'        => __( 'جدید', 'samadi-elementor-widgets' ),
			'processing' => __( 'در حال پیگیری', 'samadi-elementor-widgets' ),
			'quoted'     => __( 'پیش‌فاکتور ارسال شد', 'samadi-elementor-widgets' ),
			'closed'     => __( 'بسته شد', 'samadi-elementor-widgets' ),
		);
	}

	/**
	 * Meta boxes.
	 */
	public static function meta_boxes() {
		add_meta_box( 'se-quote-details', __( 'جزئیات درخواست', 'samadi-elementor-widgets' ), array( __CLASS__, 'details_box' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'se-quote-status', __( 'وضعیت پیگیری', 'samadi-elementor-widgets' ), array( __CLASS__, 'status_box' ), self::POST_TYPE, 'side', 'high' );
	}

	/**
	 * Details (read-only).
	 *
	 * @param WP_Post $post Post.
	 */
	public static function details_box( $post ) {
		$rows = array(
			__( 'نام', 'samadi-elementor-widgets' )     => get_post_meta( $post->ID, '_se_name', true ),
			__( 'تلفن', 'samadi-elementor-widgets' )    => get_post_meta( $post->ID, '_se_phone', true ),
			__( 'شرکت', 'samadi-elementor-widgets' )    => get_post_meta( $post->ID, '_se_company', true ),
			__( 'ایمیل', 'samadi-elementor-widgets' )   => get_post_meta( $post->ID, '_se_email', true ),
			__( 'توضیحات', 'samadi-elementor-widgets' ) => get_post_meta( $post->ID, '_se_notes', true ),
		);
		echo '<table class="widefat striped"><tbody>';
		foreach ( $rows as $label => $value ) {
			printf( '<tr><th style="width:140px">%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( (string) $value ) ) );
		}
		echo '</tbody></table><h4>' . esc_html__( 'اقلام درخواستی', 'samadi-elementor-widgets' ) . '</h4><table class="widefat striped"><thead><tr><th>' . esc_html__( 'کالا', 'samadi-elementor-widgets' ) . '</th><th>' . esc_html__( 'تعداد', 'samadi-elementor-widgets' ) . '</th></tr></thead><tbody>';
		foreach ( (array) get_post_meta( $post->ID, '_se_items', true ) as $item ) {
			printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( isset( $item['name'] ) ? $item['name'] : '' ), esc_html( isset( $item['qty'] ) ? (string) $item['qty'] : '' ) );
		}
		echo '</tbody></table>';
	}

	/**
	 * Status + internal note.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function status_box( $post ) {
		wp_nonce_field( 'se_quote_admin', 'se_quote_admin_nonce' );
		$current = get_post_meta( $post->ID, '_se_status', true );
		echo '<p><label for="se-quote-status">' . esc_html__( 'وضعیت', 'samadi-elementor-widgets' ) . '</label><br><select id="se-quote-status" name="se_status" class="widefat">';
		foreach ( self::statuses() as $key => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
		}
		echo '</select></p><p><label for="se-quote-note">' . esc_html__( 'یادداشت داخلی', 'samadi-elementor-widgets' ) . '</label><textarea id="se-quote-note" name="se_admin_note" class="widefat" rows="4">' . esc_textarea( (string) get_post_meta( $post->ID, '_se_admin_note', true ) ) . '</textarea></p>';
	}

	/**
	 * Save status/note.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function save_admin( $post_id, $post ) {
		if ( ! isset( $_POST['se_quote_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['se_quote_admin_nonce'] ) ), 'se_quote_admin' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$status = isset( $_POST['se_status'] ) ? sanitize_key( wp_unslash( $_POST['se_status'] ) ) : 'new';
		if ( array_key_exists( $status, self::statuses() ) ) {
			update_post_meta( $post_id, '_se_status', $status );
		}
		if ( isset( $_POST['se_admin_note'] ) ) {
			update_post_meta( $post_id, '_se_admin_note', sanitize_textarea_field( wp_unslash( $_POST['se_admin_note'] ) ) );
		}
		// Requests stay private regardless of the publish box.
		if ( 'private' !== $post->post_status ) {
			remove_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_admin' ), 10 );
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'private' ) );
			add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_admin' ), 10, 2 );
		}
	}

	/**
	 * List columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'         => isset( $columns['cb'] ) ? $columns['cb'] : '',
			'title'      => __( 'درخواست‌کننده', 'samadi-elementor-widgets' ),
			'se_phone'   => __( 'تلفن', 'samadi-elementor-widgets' ),
			'se_items'   => __( 'تعداد اقلام', 'samadi-elementor-widgets' ),
			'se_status'  => __( 'وضعیت', 'samadi-elementor-widgets' ),
			'date'       => __( 'تاریخ', 'samadi-elementor-widgets' ),
		);
	}

	/**
	 * Column content.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'se_phone':
				echo '<span dir="ltr">' . esc_html( (string) get_post_meta( $post_id, '_se_phone', true ) ) . '</span>';
				break;
			case 'se_items':
				echo esc_html( (string) count( (array) get_post_meta( $post_id, '_se_items', true ) ) );
				break;
			case 'se_status':
				$statuses = self::statuses();
				$status   = (string) get_post_meta( $post_id, '_se_status', true );
				echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status );
				break;
		}
	}

	/* ---------------------------------------------------------------------
	 * Price on request
	 * ------------------------------------------------------------------ */

	/**
	 * Product edit field (General tab → pricing).
	 */
	public static function product_field() {
		woocommerce_wp_checkbox(
			array(
				'id'          => self::META_POR,
				'label'       => __( 'قیمت با استعلام', 'samadi-elementor-widgets' ),
				'description' => __( 'به‌جای قیمت، «استعلام قیمت» نمایش داده شود و محصول قابل افزودن به سبد نباشد.', 'samadi-elementor-widgets' ),
			)
		);
	}

	/**
	 * Save field (WooCommerce verifies the product edit nonce before this hook).
	 *
	 * @param WC_Product $product Product.
	 */
	public static function save_product_field( $product ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked by WooCommerce (woocommerce_meta_nonce).
		$product->update_meta_data( self::META_POR, isset( $_POST[ self::META_POR ] ) ? 'yes' : 'no' );
	}

	/**
	 * Whether a product's price is "on request".
	 *
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	public static function is_price_on_request( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$parent_id = $product->get_parent_id();
		$source    = $parent_id ? wc_get_product( $parent_id ) : $product;

		if ( $source && 'yes' === $source->get_meta( self::META_POR ) ) {
			return true;
		}

		return samadi_ew_setting( 'price_on_request_empty' ) && $product->is_type( array( 'simple', 'variation' ) ) && '' === $product->get_price();
	}

	/**
	 * Not purchasable when price is on request.
	 *
	 * @param bool       $purchasable Purchasable.
	 * @param WC_Product $product     Product.
	 * @return bool
	 */
	public static function is_purchasable( $purchasable, $product ) {
		return self::is_price_on_request( $product ) ? false : $purchasable;
	}

	/**
	 * Price HTML → "استعلام قیمت".
	 *
	 * @param string     $html    HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function price_html( $html, $product ) {
		if ( ! self::is_price_on_request( $product ) ) {
			return $html;
		}

		return '<span class="se-price-request">' . esc_html( samadi_ew_setting( 'quote_button_label' ) ) . '</span>';
	}

	/**
	 * Loop button text for price-on-request products (link goes to the product page).
	 *
	 * @param string     $text    Text.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function loop_button_text( $text, $product ) {
		return self::is_price_on_request( $product ) ? (string) samadi_ew_setting( 'quote_button_label' ) : $text;
	}

	/**
	 * Quote button after the add-to-cart form.
	 */
	public static function single_button() {
		global $product;

		if ( ! $product instanceof WC_Product || 'none' === samadi_ew_setting( 'quote_button' ) ) {
			return;
		}
		if ( 'request' === samadi_ew_setting( 'quote_button' ) && ! self::is_price_on_request( $product ) ) {
			return;
		}

		echo Samadi_EW_Components::quote_button(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Quote button for products without an add-to-cart form (not purchasable).
	 */
	public static function single_button_not_purchasable() {
		global $product;

		if ( ! $product instanceof WC_Product || $product->is_purchasable() || $product->is_type( array( 'external', 'grouped', 'variable' ) ) || 'none' === samadi_ew_setting( 'quote_button' ) ) {
			return;
		}

		echo Samadi_EW_Components::quote_button(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
