<?php
/**
 * My Account navigation — profile card + icon menu.
 *
 * Overrides woocommerce/templates/myaccount/navigation.php; all hooks and the
 * menu item source (wc_get_account_menu_items) are kept.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$samadi_user = wp_get_current_user();

do_action( 'woocommerce_before_account_navigation' );
?>

<aside class="se-account-side">
	<div class="se-account-profile">
		<span class="se-account-profile__avatar"><?php echo get_avatar( $samadi_user->ID, 64, '', '' ); ?></span>
		<div class="se-account-profile__info">
			<p class="se-account-profile__name"><?php echo esc_html( $samadi_user->display_name ); ?></p>
			<?php if ( $samadi_user->user_email ) : ?>
				<p class="se-account-profile__email" dir="ltr"><?php echo esc_html( $samadi_user->user_email ); ?></p>
			<?php endif; ?>
		</div>
		<a class="se-account-profile__logout" href="<?php echo esc_url( wc_logout_url() ); ?>">
			<?php samadi_icon( 'log-out', array( 'size' => 18 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'خروج از حساب', 'samadi-electric' ); ?></span>
		</a>
	</div>

	<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
		<ul>
			<?php foreach ( wc_get_account_menu_items() as $samadi_endpoint => $samadi_label ) : ?>
				<?php $samadi_meta = samadi_wc_account_item_meta( $samadi_endpoint ); ?>
				<li class="<?php echo wc_get_account_menu_item_classes( $samadi_endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WooCommerce. ?>">
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( $samadi_endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $samadi_endpoint ) ? 'aria-current="page"' : ''; ?>>
						<span class="se-account-nav__icon"><?php samadi_icon( $samadi_meta['icon'], array( 'size' => 20 ) ); ?></span>
						<span class="se-account-nav__label"><?php echo esc_html( $samadi_label ); ?></span>
						<?php samadi_icon( 'chevron-left', array( 'size' => 16, 'class' => 'se-account-nav__arrow' ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</aside>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
