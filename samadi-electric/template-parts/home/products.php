<?php
/**
 * Homepage product section rendered with WooCommerce's [products] shortcode (real data, cached by WC).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_html = do_shortcode( $args['shortcode'] );

// The shortcode returns an empty wrapper when there are no products: hide the section then.
if ( false === strpos( $samadi_html, '<li' ) ) {
	return;
}
?>
<section class="se-section se-home-products">
	<?php samadi_section_head( $args['title'], samadi_shop_url() ); ?>
	<div class="se-products se-products--row se-products-scroll">
		<?php echo $samadi_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce shortcode output. ?>
	</div>
</section>
