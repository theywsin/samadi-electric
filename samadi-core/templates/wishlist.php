<?php
/**
 * Wishlist page ([samadi_wishlist]).
 *
 * Copy to yourtheme/samadi-core/wishlist.php to override.
 *
 * @package Samadi_Core
 *
 * @var array $args { @type WC_Product[] $products }
 */

defined( 'ABSPATH' ) || exit;

$samadi_core_products = isset( $args['products'] ) ? (array) $args['products'] : array();
$samadi_core_shop     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<div class="se-core-wishlist woocommerce" data-se-wishlist-page>
	<?php if ( $samadi_core_products ) : ?>
		<p class="se-core-wishlist__count">
			<?php
			printf(
				/* translators: %s: number of products */
				esc_html__( '%s کالا در فهرست علاقه‌مندی‌های شما', 'samadi-core' ),
				'<strong data-se-wishlist-total>' . esc_html( samadi_core_number( count( $samadi_core_products ) ) ) . '</strong>'
			);
			?>
		</p>
		<?php
		$samadi_core_columns = (int) apply_filters( 'loop_shop_columns', 4 ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook.
		wc_set_loop_prop( 'name', 'samadi_wishlist' );
		wc_set_loop_prop( 'columns', $samadi_core_columns );
		wc_set_loop_prop( 'is_shortcode', true );

		woocommerce_product_loop_start();

		foreach ( $samadi_core_products as $samadi_core_product ) {
			$GLOBALS['post'] = get_post( $samadi_core_product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $GLOBALS['post'] );
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();
		wp_reset_postdata();
		wc_reset_loop();
		?>
	<?php endif; ?>

	<div class="se-core-empty" data-se-wishlist-empty<?php echo $samadi_core_products ? ' hidden' : ''; ?>>
		<span class="se-core-empty__icon"><?php echo samadi_core_icon( 'heart', 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<h2 class="se-core-empty__title"><?php esc_html_e( 'فهرست علاقه‌مندی‌های شما خالی است', 'samadi-core' ); ?></h2>
		<p class="se-core-empty__text"><?php esc_html_e( 'با زدن آیکون قلب روی هر محصول، آن را برای خرید بعدی اینجا نگه دارید.', 'samadi-core' ); ?></p>
		<a class="se-btn se-btn--primary" href="<?php echo esc_url( $samadi_core_shop ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'samadi-core' ); ?></a>
	</div>
</div>
