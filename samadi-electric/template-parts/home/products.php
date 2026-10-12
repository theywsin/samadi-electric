<?php
/**
 * Homepage product section rendered with WooCommerce's [products] shortcode (real data, cached by WC).
 *
 * Args: title, shortcode, link (optional), countdown (optional end timestamp for the offers timer).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_html = do_shortcode( $args['shortcode'] );

// The shortcode returns an empty wrapper when there are no products: hide the section then.
if ( false === strpos( $samadi_html, '<li' ) ) {
	return;
}

$samadi_end = isset( $args['countdown'] ) ? (int) $args['countdown'] : 0;
$samadi_mod = isset( $args['modifier'] ) ? sanitize_html_class( $args['modifier'] ) : '';
?>
<section class="se-section se-home-products<?php echo $samadi_mod ? ' se-home-products--' . esc_attr( $samadi_mod ) : ''; ?>">
	<div class="se-section-head">
		<h2 class="se-section-head__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php if ( $samadi_end > time() ) : ?>
			<p class="se-countdown" data-se-countdown="<?php echo esc_attr( $samadi_end ); ?>">
				<span class="se-countdown__label"><?php esc_html_e( 'پایان پیشنهادها:', 'samadi-electric' ); ?></span>
				<span class="se-countdown__time" dir="ltr"><?php echo esc_html( samadi_countdown_text( $samadi_end ) ); ?></span>
			</p>
		<?php else : ?>
			<a class="se-section-head__link" href="<?php echo esc_url( ! empty( $args['link'] ) ? $args['link'] : samadi_shop_url() ); ?>"><?php esc_html_e( 'مشاهده همه', 'samadi-electric' ); ?><?php samadi_icon( 'arrow-left', array( 'size' => 14 ) ); ?></a>
		<?php endif; ?>
	</div>
	<div class="se-products se-products--row se-products-scroll">
		<?php echo $samadi_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce shortcode output. ?>
	</div>
</section>
