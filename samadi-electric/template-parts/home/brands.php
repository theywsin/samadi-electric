<?php
/**
 * Homepage brands strip (requires a brand taxonomy, e.g. WooCommerce Brands).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_tax = samadi_brand_taxonomy();

if ( ! $samadi_tax ) {
	return;
}

$samadi_brands = get_terms(
	array(
		'taxonomy'   => $samadi_tax,
		'hide_empty' => true,
		'number'     => 12,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);

if ( is_wp_error( $samadi_brands ) || ! $samadi_brands ) {
	return;
}
?>
<section class="se-section se-home-brands">
	<?php samadi_section_head( __( 'برندهای معتبر', 'samadi-electric' ) ); ?>
	<ul class="se-brand-strip">
		<?php foreach ( $samadi_brands as $samadi_brand ) : ?>
			<li class="se-brand-strip__item">
				<a href="<?php echo esc_url( get_term_link( $samadi_brand ) ); ?>" class="se-brand-strip__link">
					<?php
					$samadi_logo = samadi_term_image( $samadi_brand, 'medium' );
					echo $samadi_logo ? $samadi_logo : '<span class="se-brand-strip__name">' . esc_html( $samadi_brand->name ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
