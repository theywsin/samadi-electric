<?php
/**
 * My Account navigation: profile card, icon menu and a support box.
 *
 * Based on WooCommerce's myaccount/navigation.php; menu items, classes and hooks are unchanged.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$samadi_user    = wp_get_current_user();
$samadi_support = samadi_account_support_url();

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation se-account-nav" aria-label="<?php esc_attr_e( 'بخش‌های حساب کاربری', 'samadi-electric' ); ?>">
	<?php if ( $samadi_user->exists() ) : ?>
		<div class="se-account-nav__user">
			<span class="se-account-nav__avatar">
				<?php echo get_avatar( $samadi_user->ID, 160, '', '', array( 'class' => 'se-account-nav__avatar-img' ) ); ?>
			</span>
			<strong class="se-account-nav__name"><?php echo esc_html( $samadi_user->display_name ); ?></strong>
			<a class="se-account-nav__email" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" title="<?php esc_attr_e( 'ویرایش اطلاعات حساب', 'samadi-electric' ); ?>">
				<span dir="ltr"><?php echo esc_html( $samadi_user->user_email ); ?></span>
				<?php samadi_icon( 'pencil', array( 'size' => 15 ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<ul class="se-account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WooCommerce. ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<span class="se-account-nav__icon"><?php samadi_icon( samadi_account_menu_icon( $endpoint ), array( 'size' => 20 ) ); ?></span>
					<span class="se-account-nav__label"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( $samadi_support ) : ?>
		<div class="se-account-help">
			<span class="se-account-help__icon"><?php samadi_icon( 'headset', array( 'size' => 28 ) ); ?></span>
			<strong class="se-account-help__title"><?php esc_html_e( 'نیاز به کمک دارید؟', 'samadi-electric' ); ?></strong>
			<span class="se-account-help__text"><?php esc_html_e( 'کارشناسان ما آماده پاسخگویی هستند.', 'samadi-electric' ); ?></span>
			<a class="se-btn se-btn--outline se-account-help__btn" href="<?php echo esc_url( $samadi_support ); ?>"><?php esc_html_e( 'تماس با پشتیبانی', 'samadi-electric' ); ?></a>
		</div>
	<?php endif; ?>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
