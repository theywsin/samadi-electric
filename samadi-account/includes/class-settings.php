<?php
/**
 * Settings page (WooCommerce → حساب کاربری).
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings.
 */
class Samadi_Account_Settings {

	const PAGE = 'sad-settings';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 60 );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SAD_FILE ), array( __CLASS__, 'action_link' ) );
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		add_submenu_page(
			class_exists( 'WooCommerce' ) ? 'woocommerce' : 'options-general.php',
			'طراحی حساب کاربری',
			'طراحی حساب کاربری',
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Plugins screen link.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_link( $links ) {
		$url = admin_url( ( class_exists( 'WooCommerce' ) ? 'admin.php' : 'options-general.php' ) . '?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">تنظیمات</a>' );
		return $links;
	}

	/**
	 * Fields: key => [ label, type, description ].
	 *
	 * @return array
	 */
	private static function fields() {
		return array(
			'hero_image'      => array( 'تصویر بنر خوشامد', 'image', 'تصویر پس‌زمینه بنر پیشخوان (پیشنهاد: ۱۶۰۰×۴۰۰، سمت راست ساده برای متن). خالی = تصویر پیش‌فرض افزونه.' ),
			'hero_title'      => array( 'عنوان بنر', 'text', '%s = نام کوچک مشتری' ),
			'hero_text'       => array( 'متن بنر', 'textarea', '%s = نام فروشگاه' ),
			'support_show'    => array( 'کادر پشتیبانی', 'checkbox', 'نمایش کادر «نیاز به کمک دارید؟» زیر منو' ),
			'support_title'   => array( 'عنوان پشتیبانی', 'text', '' ),
			'support_text'    => array( 'متن پشتیبانی', 'text', '' ),
			'support_label'   => array( 'متن دکمه پشتیبانی', 'text', '' ),
			'support_url'     => array( 'لینک دکمه پشتیبانی', 'url', 'خالی = برگه «تماس با ما» (در صورت وجود)' ),
			'wishlist'        => array( 'علاقه‌مندی‌ها', 'checkbox', 'فعال‌سازی علاقه‌مندی داخلی (اگر YITH یا TI Wishlist فعال باشد، از همان استفاده می‌شود)' ),
			'address_book'    => array( 'دفترچه آدرس', 'checkbox', 'چند آدرس برای هر مشتری (خانه، محل کار، …)؛ آدرس پیش‌فرض با صورتحساب/ارسال ووکامرس همگام می‌شود' ),
			'checkout_picker' => array( 'انتخاب آدرس در تسویه‌حساب', 'checkbox', 'نمایش آدرس‌های ذخیره‌شده بالای فرم تسویه‌حساب' ),
			'hide_title'      => array( 'عنوان برگه', 'checkbox', 'پنهان کردن عنوان برگه «حساب کاربری» (مطابق طرح)' ),
			'persian_digits'  => array( 'ارقام فارسی', 'checkbox', '' ),
			'jalali'          => array( 'تاریخ شمسی', 'checkbox', 'تاریخ سفارش‌ها به شمسی (اگر افزونه تاریخ شمسی دارید، غیرفعال کنید)' ),
			'font'            => array( 'فونت وزیرمتن', 'checkbox', 'استفاده از فونت وزیرمتن همراه افزونه (محلی، بدون CDN)' ),
			'color_primary'   => array( 'رنگ اصلی', 'color', '' ),
			'color_accent'    => array( 'رنگ آیکون‌ها', 'color', '' ),
		);
	}

	/**
	 * Register setting.
	 */
	public static function register() {
		register_setting(
			'sad_settings',
			'sad_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => sad_defaults(),
			)
		);
	}

	/**
	 * Sanitize.
	 *
	 * @param mixed $input Input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$out   = array();
		foreach ( self::fields() as $key => $field ) {
			$value = isset( $input[ $key ] ) ? $input[ $key ] : '';
			switch ( $field[1] ) {
				case 'checkbox':
					$out[ $key ] = ! empty( $value );
					break;
				case 'image':
					$out[ $key ] = absint( $value );
					break;
				case 'url':
					$out[ $key ] = esc_url_raw( (string) $value );
					break;
				case 'color':
					$color       = sanitize_hex_color( (string) $value );
					$out[ $key ] = $color ? $color : sad_defaults()[ $key ];
					break;
				case 'textarea':
					$out[ $key ] = sanitize_textarea_field( (string) $value );
					break;
				default:
					$out[ $key ] = sanitize_text_field( (string) $value );
			}
		}
		flush_rewrite_rules( false );
		return $out;
	}

	/**
	 * Page.
	 */
	public static function render() {
		wp_enqueue_media();
		?>
		<div class="wrap" dir="rtl">
			<h1>طراحی حساب کاربری</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'sad_settings' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( self::fields() as $key => $field ) : ?>
						<?php
						$name  = 'sad_settings[' . $key . ']';
						$id    = 'sad-' . $key;
						$value = sad_option( $key );
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
							<td>
								<?php if ( 'checkbox' === $field[1] ) : ?>
									<label><input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( (bool) $value ); ?>> <?php echo esc_html( $field[2] ); ?></label>
								<?php elseif ( 'image' === $field[1] ) : ?>
									<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
									<div id="<?php echo esc_attr( $id ); ?>-preview" style="margin-bottom:8px">
										<img src="<?php echo esc_url( $value ? (string) wp_get_attachment_image_url( (int) $value, 'medium' ) : SAD_URL . 'assets/images/hero.jpg' ); ?>" style="max-width:420px;height:auto;border-radius:12px" alt="">
									</div>
									<button type="button" class="button" data-sad-media="<?php echo esc_attr( $id ); ?>">انتخاب تصویر</button>
									<button type="button" class="button-link" data-sad-media-clear="<?php echo esc_attr( $id ); ?>">پیش‌فرض</button>
									<p class="description"><?php echo esc_html( $field[2] ); ?></p>
								<?php elseif ( 'textarea' === $field[1] ) : ?>
									<textarea class="large-text" rows="3" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( (string) $value ); ?></textarea>
									<p class="description"><?php echo esc_html( $field[2] ); ?></p>
								<?php else : ?>
									<input type="<?php echo 'color' === $field[1] ? 'color' : ( 'url' === $field[1] ? 'url' : 'text' ); ?>" class="<?php echo 'color' === $field[1] ? '' : 'regular-text'; ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
									<?php if ( $field[2] ) : ?>
										<p class="description"><?php echo esc_html( $field[2] ); ?></p>
									<?php endif; ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<script>
		( function () {
			document.addEventListener( 'click', function ( e ) {
				var pick = e.target.closest( '[data-sad-media]' );
				var clear = e.target.closest( '[data-sad-media-clear]' );
				if ( clear ) {
					var cid = clear.getAttribute( 'data-sad-media-clear' );
					document.getElementById( cid ).value = '';
					document.querySelector( '#' + cid + '-preview img' ).src = <?php echo wp_json_encode( SAD_URL . 'assets/images/hero.jpg' ); ?>;
				}
				if ( ! pick || ! window.wp || ! wp.media ) {
					return;
				}
				var id = pick.getAttribute( 'data-sad-media' );
				var frame = wp.media( { library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					document.getElementById( id ).value = a.id;
					document.querySelector( '#' + id + '-preview img' ).src = ( a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url );
				} );
				frame.open();
			} );
		}() );
		</script>
		<?php
	}
}
