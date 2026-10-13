<?php
/**
 * Admin page: design gallery with previews, install / activate / edit / reset.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin.
 */
class SHF_Admin {

	const PAGE = 'samadi-header-footer';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_shf', array( __CLASS__, 'handle' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SHF_FILE ), array( __CLASS__, 'action_link' ) );
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		add_menu_page( 'هدر و فوتر', 'هدر و فوتر', 'manage_options', self::PAGE, array( __CLASS__, 'render' ), 'dashicons-align-wide', 59 );
	}

	/**
	 * Plugins screen link.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_link( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ) . '">انتخاب طرح</a>' );
		return $links;
	}

	/**
	 * Assets.
	 *
	 * @param string $hook Hook.
	 */
	public static function assets( $hook ) {
		if ( 'toplevel_page_' . self::PAGE === $hook ) {
			wp_enqueue_style( 'shf-admin', SHF_URL . 'assets/css/admin.css', array(), SHF_VERSION );
		}
	}

	/**
	 * Action URL.
	 *
	 * @param string $do     Action.
	 * @param string $design Design key or part.
	 * @return string
	 */
	public static function url( $do, $design ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=shf&do=' . $do . '&design=' . $design ), 'shf_' . $do . '_' . $design );
	}

	/**
	 * Handle actions.
	 */
	public static function handle() {
		$do     = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';
		$design = isset( $_GET['design'] ) ? sanitize_key( wp_unslash( $_GET['design'] ) ) : '';
		check_admin_referer( 'shf_' . $do . '_' . $design );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی ندارید.' );
		}

		$message = '';
		switch ( $do ) {
			case 'activate':
			case 'reset':
				$id = SHF_Library::install( $design, 'reset' === $do );
				if ( is_wp_error( $id ) ) {
					$message = 'error';
					break;
				}
				SHF_Library::activate( SHF_Library::part( $design ), $id );
				$message = 'reset' === $do ? 'reset' : 'activated';
				break;
			case 'install':
				$id      = SHF_Library::install( $design );
				$message = is_wp_error( $id ) ? 'error' : 'installed';
				break;
			case 'deactivate':
				if ( in_array( $design, array( 'header', 'footer' ), true ) ) {
					SHF_Library::deactivate( $design );
					$message = 'deactivated';
				}
				break;
			case 'sticky':
				update_option( 'shf_sticky', get_option( 'shf_sticky', '1' ) ? '' : '1' );
				$message = 'saved';
				break;
		}

		wp_safe_redirect( add_query_arg( 'shf_msg', $message, admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * Page.
	 */
	public static function render() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.
		$msg      = isset( $_GET['shf_msg'] ) ? sanitize_key( wp_unslash( $_GET['shf_msg'] ) ) : '';
		$messages = array(
			'activated'   => array( 'success', 'طرح نصب و فعال شد. برای تغییر طراحی روی «ویرایش با المنتور» کلیک کنید.' ),
			'installed'   => array( 'success', 'طرح در کتابخانه المنتور نصب شد.' ),
			'reset'       => array( 'success', 'نسخه تازه طرح نصب و فعال شد (نسخه قبلی در «قالب‌ها» باقی ماند).' ),
			'deactivated' => array( 'success', 'هدر/فوتر پیش‌فرض قالب دوباره فعال شد.' ),
			'saved'       => array( 'success', 'تنظیمات ذخیره شد.' ),
			'error'       => array( 'error', 'عملیات انجام نشد.' ),
		);
		$catalogue = SHF_Designs::catalogue();
		?>
		<div class="wrap shf-admin" dir="rtl">
			<h1>هدر و فوتر</h1>
			<p class="shf-admin__intro">یکی از طرح‌ها را پیش‌نمایش کنید و با یک کلیک نصب و فعال کنید. طرح به‌صورت یک قالب در کتابخانه المنتور ساخته می‌شود و همه بخش‌هایش — رنگ، متن، فاصله، ستون‌ها و ویجت‌ها — با المنتور قابل ویرایش است.</p>

			<?php if ( isset( $messages[ $msg ] ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $messages[ $msg ][0] ); ?> is-dismissible"><p><?php echo esc_html( $messages[ $msg ][1] ); ?></p></div>
			<?php endif; ?>

			<?php if ( wp_is_block_theme() && ! SHF_Library::has_pro() ) : ?>
				<div class="notice notice-warning inline"><p>قالب فعلی یک «قالب بلوکی» است و فایل header.php ندارد؛ هدر و فوتر فعال‌شده روی آن نمایش داده نمی‌شوند. از قالب صمدی الکتریک، Hello Elementor یا یک قالب کلاسیک استفاده کنید (یا Elementor Pro).</p></div>
			<?php endif; ?>

			<?php foreach ( array( 'header' => 'طرح‌های هدر', 'footer' => 'طرح‌های فوتر' ) as $part => $title ) : ?>
				<?php $active = SHF_Library::active( $part ); ?>
				<section class="shf-admin__section">
					<div class="shf-admin__head">
						<h2><?php echo esc_html( $title ); ?></h2>
						<?php if ( $active ) : ?>
							<a class="button" href="<?php echo esc_url( self::url( 'deactivate', $part ) ); ?>">بازگشت به <?php echo 'header' === $part ? 'هدر' : 'فوتر'; ?> پیش‌فرض قالب</a>
						<?php endif; ?>
					</div>
					<div class="shf-admin__grid">
						<?php foreach ( $catalogue[ $part ] as $key => $design ) : ?>
							<?php
							$installed = SHF_Library::installed( $key );
							$is_active = $installed && $installed === $active;
							?>
							<article class="shf-card<?php echo $is_active ? ' is-active' : ''; ?>">
								<a class="shf-card__shot" href="<?php echo esc_url( add_query_arg( 'shf_preview', $key, home_url( '/' ) ) ); ?>" target="_blank" rel="noopener">
									<img src="<?php echo esc_url( SHF_URL . 'assets/previews/' . $key . '.png' ); ?>" alt="<?php echo esc_attr( $design['name'] ); ?>" loading="lazy">
									<span class="shf-card__zoom">پیش‌نمایش زنده روی سایت</span>
								</a>
								<div class="shf-card__body">
									<h3><?php echo esc_html( $design['name'] ); ?> <?php echo $is_active ? '<span class="shf-badge">فعال</span>' : ( $installed ? '<span class="shf-badge shf-badge--muted">نصب‌شده</span>' : '' ); ?></h3>
									<p><?php echo esc_html( $design['desc'] ); ?></p>
									<div class="shf-card__actions">
										<?php if ( $is_active ) : ?>
											<a class="button button-primary" href="<?php echo esc_url( SHF_Library::edit_url( $installed ) ); ?>">ویرایش با المنتور</a>
											<a class="button" href="<?php echo esc_url( self::url( 'reset', $key ) ); ?>" onclick="return confirm('یک نسخه تازه از این طرح نصب و فعال شود؟ تغییرات شما در نسخه فعلی حفظ می‌شود ولی دیگر فعال نیست.');">نسخه تازه</a>
										<?php else : ?>
											<a class="button button-primary" href="<?php echo esc_url( self::url( 'activate', $key ) ); ?>">نصب و فعال‌سازی</a>
											<?php if ( $installed ) : ?>
												<a class="button" href="<?php echo esc_url( SHF_Library::edit_url( $installed ) ); ?>">ویرایش با المنتور</a>
											<?php endif; ?>
										<?php endif; ?>
										<a class="button-link" href="<?php echo esc_url( add_query_arg( 'shf_preview', $key, home_url( '/' ) ) ); ?>" target="_blank" rel="noopener">پیش‌نمایش</a>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>

			<section class="shf-admin__section">
				<h2>تنظیمات</h2>
				<p>
					هدر چسبان (هنگام اسکرول بالای صفحه می‌ماند):
					<strong><?php echo get_option( 'shf_sticky', '1' ) ? 'روشن' : 'خاموش'; ?></strong>
					<a class="button" href="<?php echo esc_url( self::url( 'sticky', 'all' ) ); ?>"><?php echo get_option( 'shf_sticky', '1' ) ? 'خاموش کردن' : 'روشن کردن'; ?></a>
				</p>
				<p class="description">
					منوها از «نمایش ← فهرست‌ها» می‌آیند (جایگاه «منوی اصلی» برای هدر و جایگاه‌های فوتر برای ستون‌ها). اطلاعات تماس و شبکه‌های اجتماعی هنگام نصب از تنظیمات قالب صمدی خوانده می‌شوند و بعداً در المنتور قابل تغییرند.
					<?php if ( SHF_Library::has_pro() ) : ?>
						Elementor Pro فعال است: طرح‌ها به‌صورت قالب «هدر/فوتر» Theme Builder با شرط «کل سایت» نصب می‌شوند.
					<?php endif; ?>
				</p>
			</section>
		</div>
		<?php
	}
}
