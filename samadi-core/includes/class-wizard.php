<?php
/**
 * Setup wizard (admin page + AJAX endpoints).
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wizard.
 */
class Samadi_Core_Wizard {

	const PAGE  = 'samadi-setup';
	const NONCE = 'samadi_core_wizard';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_ajax_samadi_core_plugin', array( __CLASS__, 'ajax_plugin' ) );
		add_action( 'wp_ajax_samadi_core_step', array( __CLASS__, 'ajax_step' ) );
		add_action( 'wp_ajax_samadi_core_remove', array( __CLASS__, 'ajax_remove' ) );
	}

	/**
	 * Admin menu.
	 */
	public static function menu() {
		add_menu_page(
			__( 'راه‌اندازی صمدی الکتریک', 'samadi-core' ),
			__( 'صمدی الکتریک', 'samadi-core' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' ),
			'dashicons-lightbulb',
			3
		);
	}

	/**
	 * Redirect to the wizard once after activation.
	 */
	public static function maybe_redirect() {
		if ( ! get_transient( 'samadi_core_redirect' ) ) {
			return;
		}
		delete_transient( 'samadi_core_redirect' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		if ( wp_doing_ajax() || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE ) );
		exit;
	}

	/**
	 * Reminder notice until the demo has been imported.
	 */
	public static function notice() {
		$screen = get_current_screen();
		if ( ! current_user_can( 'manage_options' ) || get_option( Samadi_Core_Demo_Importer::DONE ) || ( $screen && 'toplevel_page_' . self::PAGE === $screen->id ) ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p><strong>%s</strong> %s <a class="button button-primary" style="margin-inline-start:8px" href="%s">%s</a></p></div>',
			esc_html__( 'صمدی الکتریک:', 'samadi-core' ),
			esc_html__( 'برای نصب افزونه‌های لازم و درون‌ریزی کامل دمو (همه صفحات، محصولات و منوها) راه‌انداز را اجرا کنید.', 'samadi-core' ),
			esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ),
			esc_html__( 'شروع راه‌اندازی', 'samadi-core' )
		);
	}

	/**
	 * Wizard assets.
	 *
	 * @param string $hook Hook suffix.
	 */
	public static function assets( $hook ) {
		if ( 'toplevel_page_' . self::PAGE !== $hook ) {
			return;
		}
		wp_enqueue_style( 'samadi-core-wizard', SAMADI_CORE_URL . 'assets/wizard.css', array(), SAMADI_CORE_VERSION );
		wp_enqueue_script( 'samadi-core-wizard', SAMADI_CORE_URL . 'assets/wizard.js', array(), SAMADI_CORE_VERSION, true );

		$plugins = array();
		foreach ( Samadi_Core_Plugins::required() as $slug => $plugin ) {
			$plugins[] = array( 'slug' => $slug, 'name' => $plugin['name'], 'status' => Samadi_Core_Plugins::status( $slug ) );
		}
		$steps = array();
		foreach ( Samadi_Core_Demo_Importer::steps() as $key => $label ) {
			$steps[] = array( 'key' => $key, 'label' => $label );
		}

		wp_localize_script(
			'samadi-core-wizard',
			'samadiCore',
			array(
				'ajax'    => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE ),
				'plugins' => $plugins,
				'steps'   => $steps,
				'i18n'    => array(
					'installing' => __( 'در حال نصب و فعال‌سازی…', 'samadi-core' ),
					'active'     => __( 'فعال', 'samadi-core' ),
					'running'    => __( 'در حال انجام…', 'samadi-core' ),
					'done'       => __( 'انجام شد', 'samadi-core' ),
					'failed'     => __( 'خطا', 'samadi-core' ),
					'confirm'    => __( 'همه محتوای دموی درون‌ریزی‌شده (محصولات، برگه‌ها، تصاویر، منوها) حذف شود؟', 'samadi-core' ),
					'removed'    => __( 'محتوای دمو حذف شد.', 'samadi-core' ),
				),
			)
		);
	}

	/**
	 * Wizard page.
	 */
	public static function render() {
		$imported = get_option( Samadi_Core_Demo_Importer::DONE );
		$theme    = wp_get_theme();
		$is_theme = 'samadi-electric' === $theme->get_template();
		?>
		<div class="wrap sc-wizard" dir="rtl">
			<div class="sc-hero">
				<span class="sc-hero__icon dashicons dashicons-lightbulb" aria-hidden="true"></span>
				<div>
					<h1><?php esc_html_e( 'راه‌اندازی فروشگاه صمدی الکتریک', 'samadi-core' ); ?></h1>
					<p><?php esc_html_e( 'با یک کلیک، افزونه‌های لازم نصب می‌شوند و دموی کامل فروشگاه درون‌ریزی می‌شود: صفحه اصلی Elementor، فروشگاه، محصولات با تصویر و ویژگی، برندها، وبلاگ، درباره ما، تماس، خرید عمده، قوانین، منوها و تنظیمات قالب.', 'samadi-core' ); ?></p>
				</div>
			</div>

			<?php if ( ! $is_theme ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'قالب «Samadi Electric» فعال نیست. ابتدا قالب را از «نمایش ← پوسته‌ها» فعال کنید.', 'samadi-core' ); ?></p></div>
			<?php endif; ?>

			<div class="sc-grid">
				<section class="sc-card">
					<h2><span class="sc-num">۱</span><?php esc_html_e( 'افزونه‌های لازم', 'samadi-core' ); ?></h2>
					<ul class="sc-list" id="sc-plugins"></ul>
				</section>
				<section class="sc-card">
					<h2><span class="sc-num">۲</span><?php esc_html_e( 'درون‌ریزی دمو', 'samadi-core' ); ?></h2>
					<ul class="sc-list" id="sc-steps"></ul>
				</section>
			</div>

			<div class="sc-actions">
				<button type="button" class="button button-primary button-hero" id="sc-start"<?php disabled( ! $is_theme ); ?>>
					<?php echo $imported ? esc_html__( 'اجرای دوباره (فقط موارد جاافتاده)', 'samadi-core' ) : esc_html__( 'شروع راه‌اندازی کامل', 'samadi-core' ); ?>
				</button>
				<?php if ( $imported ) : ?>
					<button type="button" class="button button-link-delete" id="sc-remove"><?php esc_html_e( 'حذف محتوای دمو', 'samadi-core' ); ?></button>
				<?php endif; ?>
			</div>
			<p class="sc-status" id="sc-status" role="status" aria-live="polite"></p>

			<div class="sc-done"<?php echo $imported ? '' : ' hidden'; ?> id="sc-done">
				<h2><?php esc_html_e( 'فروشگاه آماده است', 'samadi-core' ); ?></h2>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'مشاهده سایت', 'samadi-core' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'سفارشی‌سازی قالب', 'samadi-core' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"><?php esc_html_e( 'محصولات', 'samadi-core' ); ?></a>
				</p>
				<p class="description"><?php esc_html_e( 'اطلاعات تماس، شبکه‌های اجتماعی و نام برندها نمونه هستند؛ آن‌ها را از «سفارشی‌سازی ← صمدی الکتریک» و «محصولات ← برندها» با اطلاعات واقعی جایگزین کنید.', 'samadi-core' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Verify an AJAX request.
	 */
	private static function verify() {
		check_ajax_referer( self::NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'دسترسی ندارید.', 'samadi-core' ) ), 403 );
		}
	}

	/**
	 * Install/activate a plugin.
	 */
	public static function ajax_plugin() {
		self::verify();
		$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$result = Samadi_Core_Plugins::install_and_activate( $slug );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'status' => 'active' ) );
	}

	/**
	 * Run an import step.
	 */
	public static function ajax_step() {
		self::verify();
		$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		try {
			wp_send_json_success( array( 'message' => Samadi_Core_Demo_Importer::run_step( $step ) ) );
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Remove the demo.
	 */
	public static function ajax_remove() {
		self::verify();
		/* translators: %d: number of removed objects. */
		wp_send_json_success( array( 'message' => sprintf( __( '%d مورد حذف شد.', 'samadi-core' ), Samadi_Core_Demo_Importer::remove() ) ) );
	}
}
