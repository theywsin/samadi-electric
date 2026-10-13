<?php
/**
 * Dashboard: welcome banner, stats, latest orders, addresses, wishlist and account info.
 *
 * Replaces woocommerce/templates/myaccount/dashboard.php; the dashboard hooks are kept.
 *
 * @package Samadi_Account
 * @version 4.4.0
 *
 * @var WP_User $current_user Current user.
 */

defined( 'ABSPATH' ) || exit;

$sad_user_id = get_current_user_id();
$sad_stats   = Samadi_Account::stats( $sad_user_id );
$sad_orders  = Samadi_Account::recent_orders( $sad_user_id, 4 );
$sad_book    = Samadi_Account_Addresses::all( $sad_user_id );
$sad_default = Samadi_Account_Addresses::default_id( $sad_user_id );
$sad_wish    = array_slice( Samadi_Account_Wishlist::items( $sad_user_id ), 0, 4 );
$sad_hero_id = (int) sad_option( 'hero_image' );
$sad_hero    = $sad_hero_id ? wp_get_attachment_image_url( $sad_hero_id, 'full' ) : '';
$sad_hero    = $sad_hero ? $sad_hero : SAD_URL . 'assets/images/hero.jpg';
$sad_phone   = (string) get_user_meta( $sad_user_id, 'billing_phone', true );
$sad_wish_on = Samadi_Account_Wishlist::enabled() || Samadi_Account_Wishlist::third_party();
?>

<section class="sad-hero" style="background-image:url('<?php echo esc_url( $sad_hero ); ?>')">
	<div class="sad-hero__text">
		<h1 class="sad-hero__title"><?php echo esc_html( sprintf( (string) sad_option( 'hero_title' ), sad_first_name( $current_user ) ) ); ?></h1>
		<p class="sad-hero__desc"><?php echo nl2br( esc_html( sprintf( (string) sad_option( 'hero_text' ), get_bloginfo( 'name' ) ) ) ); ?></p>
	</div>
</section>

<ul class="sad-stats">
	<li class="sad-stat">
		<div class="sad-stat__top"><span class="sad-stat__icon"><?php echo sad_icon( 'shopping-bag', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="sad-stat__label">تعداد سفارش‌ها</span></div>
		<p class="sad-stat__value"><?php echo esc_html( sad_fa( number_format_i18n( $sad_stats['orders'] ) ) ); ?></p>
		<a class="sad-stat__link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">مشاهده سفارش‌ها</a>
	</li>
	<li class="sad-stat">
		<div class="sad-stat__top"><span class="sad-stat__icon"><?php echo sad_icon( 'wallet', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="sad-stat__label">مجموع خریدها</span></div>
		<p class="sad-stat__value sad-stat__value--price"><?php echo wp_kses_post( sad_price( $sad_stats['spent'] ) ); ?></p>
		<a class="sad-stat__link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">جزئیات مالی <?php echo sad_icon( 'chevron-left', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	</li>
	<?php if ( $sad_wish_on ) : ?>
		<li class="sad-stat sad-stat--heart">
			<div class="sad-stat__top"><span class="sad-stat__icon"><?php echo sad_icon( 'heart', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="sad-stat__label">علاقه‌مندی‌ها</span></div>
			<p class="sad-stat__value"><?php echo esc_html( sad_fa( number_format_i18n( $sad_stats['wishlist'] ) ) ); ?></p>
			<a class="sad-stat__link" href="<?php echo esc_url( wc_get_account_endpoint_url( Samadi_Account_Wishlist::ENDPOINT ) ); ?>">مشاهده موارد محبوب</a>
		</li>
	<?php endif; ?>
	<li class="sad-stat">
		<div class="sad-stat__top"><span class="sad-stat__icon"><?php echo sad_icon( 'map-pin', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="sad-stat__label">آدرس‌های ثبت شده</span></div>
		<p class="sad-stat__value"><?php echo esc_html( sad_fa( number_format_i18n( $sad_stats['addresses'] ) ) ); ?></p>
		<a class="sad-stat__link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>">مدیریت آدرس‌ها <?php echo sad_icon( 'chevron-left', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	</li>
</ul>

<section class="sad-card">
	<?php sad_template( 'parts/head', array( 'title' => 'آخرین سفارش‌های من', 'link' => $sad_orders ? wc_get_account_endpoint_url( 'orders' ) : '', 'label' => 'مشاهده همه سفارش‌ها' ) ); ?>
	<?php if ( $sad_orders ) : ?>
		<ul class="sad-orders">
			<?php
			foreach ( $sad_orders as $sad_order ) {
				sad_template( 'parts/order-row', array( 'order' => $sad_order, 'actions' => false ) );
			}
			?>
		</ul>
	<?php else : ?>
		<div class="sad-empty">
			<?php echo sad_icon( 'shopping-bag', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>هنوز سفارشی ثبت نکرده‌اید.</p>
			<a class="sad-btn sad-btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده محصولات</a>
		</div>
	<?php endif; ?>
</section>

<section class="sad-panel">
	<?php sad_template( 'parts/head', array( 'title' => 'آدرس‌های من', 'link' => wc_get_account_endpoint_url( 'edit-address' ), 'label' => 'مدیریت آدرس‌ها' ) ); ?>
	<div class="sad-addresses">
		<?php
		$sad_shown = 0;
		// Default first.
		uksort(
			$sad_book,
			static function ( $a, $b ) use ( $sad_default ) {
				return ( $b === $sad_default ) - ( $a === $sad_default );
			}
		);
		foreach ( $sad_book as $sad_id => $sad_address ) {
			if ( $sad_shown >= ( sad_option( 'address_book' ) ? 2 : 3 ) ) {
				break;
			}
			sad_template( 'parts/address-card', array( 'id' => $sad_id, 'address' => $sad_address, 'default' => $sad_id === $sad_default, 'manage' => false ) );
			++$sad_shown;
		}
		if ( sad_option( 'address_book' ) ) {
			sad_template( 'parts/address-new' );
		}
		?>
	</div>
</section>

<?php if ( $sad_wish_on && Samadi_Account_Wishlist::enabled() ) : ?>
	<section class="sad-panel">
		<?php sad_template( 'parts/head', array( 'title' => 'علاقه‌مندی‌های من', 'link' => $sad_wish ? wc_get_account_endpoint_url( Samadi_Account_Wishlist::ENDPOINT ) : '', 'label' => 'مشاهده همه' ) ); ?>
		<?php if ( $sad_wish ) : ?>
			<div class="sad-products">
				<?php
				foreach ( $sad_wish as $sad_pid ) {
					$sad_product = wc_get_product( $sad_pid );
					if ( $sad_product ) {
						sad_template( 'parts/product-card', array( 'product' => $sad_product ) );
					}
				}
				?>
			</div>
		<?php else : ?>
			<div class="sad-empty sad-empty--flat">
				<?php echo sad_icon( 'heart', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<p>با زدن آیکون قلب روی محصولات، آن‌ها را این‌جا ذخیره کنید.</p>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>

<section class="sad-card">
	<?php sad_template( 'parts/head', array( 'title' => 'اطلاعات حساب کاربری', 'link' => wc_get_account_endpoint_url( 'edit-account' ), 'label' => 'ویرایش اطلاعات' ) ); ?>
	<ul class="sad-info">
		<li class="sad-info__item">
			<span class="sad-info__icon"><?php echo sad_icon( 'key-round', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="sad-info__text"><span class="sad-info__label">رمز عبور</span><span class="sad-info__value" dir="ltr">••••••••</span></span>
		</li>
		<li class="sad-info__item">
			<span class="sad-info__icon"><?php echo sad_icon( 'user', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="sad-info__text"><span class="sad-info__label">نام و نام خانوادگی</span><span class="sad-info__value"><?php echo esc_html( sad_user_name( $current_user ) ); ?></span></span>
		</li>
		<li class="sad-info__item">
			<span class="sad-info__icon"><?php echo sad_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="sad-info__text"><span class="sad-info__label">شماره موبایل</span><span class="sad-info__value" dir="ltr"><?php echo esc_html( $sad_phone ? sad_phone( $sad_phone ) : '—' ); ?></span></span>
		</li>
		<li class="sad-info__item">
			<span class="sad-info__icon"><?php echo sad_icon( 'mail', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="sad-info__text"><span class="sad-info__label">ایمیل</span><span class="sad-info__value" dir="ltr"><?php echo esc_html( $current_user->user_email ); ?></span></span>
		</li>
	</ul>
</section>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
