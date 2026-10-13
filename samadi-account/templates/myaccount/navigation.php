<?php
/**
 * Account sidebar: profile, menu and support box.
 *
 * Replaces woocommerce/templates/myaccount/navigation.php; hooks and menu source are kept.
 *
 * @package Samadi_Account
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$sad_user = wp_get_current_user();

do_action( 'woocommerce_before_account_navigation' );
?>
<aside class="sad-side">
	<div class="sad-side__card">
		<div class="sad-profile">
			<span class="sad-avatar sad-avatar--lg"><?php echo sad_avatar( $sad_user->ID, 96 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in sad_avatar(). ?></span>
			<p class="sad-profile__name"><?php echo esc_html( sad_user_name( $sad_user ) ); ?></p>
			<p class="sad-profile__email">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="sad-profile__edit" aria-label="ویرایش اطلاعات حساب"><?php echo sad_icon( 'pencil', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<span dir="ltr"><?php echo esc_html( $sad_user->user_email ); ?></span>
			</p>
		</div>

		<nav class="woocommerce-MyAccount-navigation sad-nav" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
			<ul>
				<?php foreach ( wc_get_account_menu_items() as $sad_endpoint => $sad_label ) : ?>
					<li class="<?php echo wc_get_account_menu_item_classes( $sad_endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WooCommerce. ?>">
						<a href="<?php echo esc_url( wc_get_account_endpoint_url( $sad_endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $sad_endpoint ) ? 'aria-current="page"' : ''; ?>>
							<?php echo sad_icon( Samadi_Account::menu_icon( $sad_endpoint ), 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $sad_label ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<?php if ( sad_option( 'support_show' ) ) : ?>
			<div class="sad-support">
				<span class="sad-support__icon"><?php echo sad_icon( 'headset', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<p class="sad-support__title"><?php echo esc_html( (string) sad_option( 'support_title' ) ); ?></p>
				<p class="sad-support__text"><?php echo esc_html( (string) sad_option( 'support_text' ) ); ?></p>
				<a class="sad-btn sad-btn--outline sad-btn--block" href="<?php echo esc_url( sad_support_url() ); ?>"><?php echo esc_html( (string) sad_option( 'support_label' ) ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</aside>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
