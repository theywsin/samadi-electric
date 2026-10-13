<?php
/**
 * My Addresses: address cards (see template-parts/account/addresses.php).
 *
 * Based on WooCommerce's myaccount/my-address.php; the address filters and hook are kept.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="se-panel">
	<p class="se-panel__intro">
		<?php echo apply_filters( 'woocommerce_my_account_my_address_description', esc_html__( 'این آدرس‌ها به‌صورت پیش‌فرض در صفحه تسویه‌حساب استفاده می‌شوند.', 'samadi-electric' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WooCommerce.Commenting.CommentHooks.MissingHookComment ?>
	</p>
	<?php get_template_part( 'template-parts/account/addresses' ); ?>
</section>
