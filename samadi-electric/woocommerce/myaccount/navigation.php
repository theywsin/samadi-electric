<?php
/**
 * My Account navigation — user card + icon menu (Samadi Electric design).
 *
 * Based on WooCommerce's myaccount/navigation.php; the menu items, classes and hooks are unchanged.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$samadi_user  = wp_get_current_user();
$samadi_icons = array(
	'dashboard'       => 'layout-grid',
	'orders'          => 'package',
	'downloads'       => 'file-text',
	'edit-address'    => 'map-pin',
	'payment-methods' => 'credit-card',
	'edit-account'    => 'user',
	'customer-logout' => 'log-out',
);

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation se-account-nav" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<div class="se-account-user">
		<span class="se-account-user__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $samadi_user->display_name ? $samadi_user->display_name : $samadi_user->user_login, 0, 1 ) ); ?></span>
		<span class="se-account-user__text">
			<strong class="se-account-user__name"><?php echo esc_html( $samadi_user->display_name ); ?></strong>
			<?php if ( $samadi_user->user_email ) : ?>
				<span class="se-account-user__email" dir="ltr"><?php echo esc_html( $samadi_user->user_email ); ?></span>
			<?php endif; ?>
		</span>
	</div>
	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WooCommerce. ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<?php samadi_icon( isset( $samadi_icons[ $endpoint ] ) ? $samadi_icons[ $endpoint ] : 'chevron-left', array( 'size' => 20 ) ); ?>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
