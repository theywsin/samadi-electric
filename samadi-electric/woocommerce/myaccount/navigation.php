<?php
/**
 * My Account navigation: customer card + icon menu.
 *
 * Based on WooCommerce's myaccount/navigation.php; menu items, classes and hooks are unchanged.
 * Follows the Figma design (v2): initial-letter avatar, flat menu on the page fill.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$samadi_user = wp_get_current_user();

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation se-account-nav" aria-label="<?php esc_attr_e( 'بخش‌های حساب کاربری', 'samadi-electric' ); ?>">
	<?php if ( $samadi_user->exists() ) : ?>
		<div class="se-account-nav__user">
			<span class="se-account-nav__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $samadi_user->display_name ? $samadi_user->display_name : $samadi_user->user_login, 0, 1 ) ); ?></span>
			<span class="se-account-nav__who">
				<strong class="se-account-nav__name"><?php echo esc_html( $samadi_user->display_name ); ?></strong>
				<?php if ( $samadi_user->user_email ) : ?>
					<span class="se-account-nav__email" dir="ltr"><?php echo esc_html( $samadi_user->user_email ); ?></span>
				<?php endif; ?>
			</span>
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
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
