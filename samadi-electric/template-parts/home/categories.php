<?php
/**
 * Homepage popular categories (real WooCommerce product categories).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_ids   = array_filter( array_map( 'absint', explode( ',', (string) samadi_option( 'home_categories' ) ) ) );
$samadi_limit = max( 1, min( 24, absint( samadi_option( 'home_categories_limit' ) ) ) );
$samadi_args  = array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'number'     => $samadi_limit,
	'orderby'    => 'menu_order',
);

if ( $samadi_ids ) {
	$samadi_args['include'] = $samadi_ids;
} else {
	$samadi_args['parent'] = 0;
	$samadi_default        = (int) get_option( 'default_product_cat' );
	if ( $samadi_default ) {
		$samadi_args['exclude'] = array( $samadi_default );
	}
}

$samadi_terms = get_terms( $samadi_args );

if ( is_wp_error( $samadi_terms ) || ! $samadi_terms ) {
	return;
}
?>
<section class="se-section se-home-cats" aria-labelledby="se-home-cats-title">
	<div class="se-section-head">
		<h2 class="se-section-head__title" id="se-home-cats-title"><?php esc_html_e( 'دسته‌بندی‌های محبوب', 'samadi-electric' ); ?></h2>
		<a class="se-section-head__link" href="<?php echo esc_url( samadi_shop_url() ); ?>"><?php esc_html_e( 'همه محصولات', 'samadi-electric' ); ?><?php samadi_icon( 'chevron-left', array( 'size' => 16 ) ); ?></a>
	</div>
	<ul class="se-cat-grid">
		<?php foreach ( $samadi_terms as $samadi_term ) : ?>
			<li class="se-cat-card">
				<a class="se-cat-card__link" href="<?php echo esc_url( get_term_link( $samadi_term ) ); ?>">
					<span class="se-cat-card__media">
						<?php
						$samadi_img = samadi_term_image( $samadi_term, 'samadi-category' );
						echo $samadi_img ? $samadi_img : samadi_get_icon( 'zap', array( 'size' => 36 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="se-cat-card__name"><?php echo esc_html( $samadi_term->name ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
