<?php
/**
 * Product archive: shop, product category/tag/brand/attribute archives and product search.
 *
 * Based on WooCommerce's archive-product.php — all hooks are preserved. With Elementor Pro, a
 * "Products Archive" Theme Builder template replaces this file automatically.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

$samadi_term        = is_product_taxonomy() ? get_queried_object() : null;
$samadi_has_filters = samadi_wc_has_filters();
$samadi_thumb       = ( $samadi_term instanceof WP_Term ) ? samadi_term_image( $samadi_term, 'samadi-category' ) : '';
?>
<header class="se-archive-header<?php echo $samadi_thumb ? ' has-image' : ''; ?>">
	<div class="se-archive-header__text">
		<?php samadi_breadcrumb(); ?>
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="se-archive-header__title woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>
		<?php do_action( 'woocommerce_archive_description' ); ?>
	</div>
	<?php if ( $samadi_thumb ) : ?>
		<div class="se-archive-header__media"><?php echo $samadi_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
</header>

<?php do_action( 'woocommerce_shop_loop_header' ); ?>

<div class="se-shop<?php echo $samadi_has_filters ? ' se-shop--sidebar' : ''; ?>">
	<?php if ( $samadi_has_filters ) : ?>
		<aside class="se-filters" id="se-filters" data-se-filters aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'samadi-electric' ); ?>">
			<div class="se-filters__head">
				<p class="se-filters__title"><?php esc_html_e( 'فیلترها', 'samadi-electric' ); ?></p>
				<button type="button" class="se-icon-btn se-filters__close" data-se-filters-close>
					<?php samadi_icon( 'x' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'بستن فیلترها', 'samadi-electric' ); ?></span>
				</button>
			</div>
			<div class="se-filters__body">
				<?php dynamic_sidebar( 'sidebar-shop' ); ?>
			</div>
		</aside>
	<?php endif; ?>

	<div class="se-shop__results" id="se-shop-results" data-se-shop-results>
		<div class="se-toolbar">
			<div class="se-toolbar__main">
				<?php if ( woocommerce_product_loop() ) : ?>
					<?php samadi_wc_sort_control(); ?>
				<?php endif; ?>
				<?php if ( $samadi_has_filters ) : ?>
					<button type="button" class="se-pill se-toolbar__filters" data-se-filters-open aria-controls="se-filters" aria-expanded="false">
						<?php samadi_icon( 'sliders-horizontal', array( 'size' => 18 ) ); ?>
						<span><?php esc_html_e( 'فیلترها', 'samadi-electric' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
			<?php
			if ( $samadi_has_filters ) {
				/**
				 * Quick filter chips (brand, colour, model, price…). Rendered by the companion plugin.
				 */
				do_action( 'samadi_shop_quick_filters' );
			}
			?>
			<?php if ( woocommerce_product_loop() ) : ?>
				<div class="se-toolbar__meta">
					<?php samadi_wc_result_count(); ?>
					<?php samadi_wc_view_toggle(); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( woocommerce_product_loop() ) : ?>
			<?php
			do_action( 'woocommerce_before_shop_loop' );

			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
			}

			woocommerce_product_loop_end();

			do_action( 'woocommerce_after_shop_loop' );
			?>
		<?php else : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php endif; ?>
	</div>
</div>
<?php
do_action( 'woocommerce_after_main_content' );

do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
