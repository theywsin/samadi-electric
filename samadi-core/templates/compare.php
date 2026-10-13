<?php
/**
 * Compare page ([samadi_compare]).
 *
 * Copy to yourtheme/samadi-core/compare.php to override.
 *
 * @package Samadi_Core
 *
 * @var array $args {
 *     @type WC_Product[] $products Products.
 *     @type array        $rows     Rows from Samadi_Core_Compare::rows().
 * }
 */

defined( 'ABSPATH' ) || exit;

$samadi_core_products = isset( $args['products'] ) ? (array) $args['products'] : array();
$samadi_core_rows     = isset( $args['rows'] ) ? (array) $args['rows'] : array();
$samadi_core_shop     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$samadi_core_global   = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
?>
<div class="se-core-compare-page woocommerce" data-se-compare-page>
	<?php if ( $samadi_core_products ) : ?>
		<div class="se-core-compare-page__bar">
			<p class="se-core-compare-page__count">
				<?php
				printf(
					/* translators: %s: number of products */
					esc_html__( 'مقایسه %s کالا', 'samadi-core' ),
					'<strong>' . esc_html( samadi_core_number( count( $samadi_core_products ) ) ) . '</strong>'
				);
				?>
			</p>
			<?php if ( count( $samadi_core_products ) > 1 ) : ?>
				<label class="se-core-switch">
					<input type="checkbox" data-se-compare-diff>
					<span class="se-core-switch__track" aria-hidden="true"></span>
					<span><?php esc_html_e( 'فقط تفاوت‌ها', 'samadi-core' ); ?></span>
				</label>
			<?php endif; ?>
			<button type="button" class="se-core-link" data-se-compare-clear><?php echo samadi_core_icon( 'trash-2', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'پاک کردن همه', 'samadi-core' ); ?></button>
		</div>

		<div class="se-core-compare-scroll" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'جدول مقایسه محصولات', 'samadi-core' ); ?>">
			<table class="se-core-compare-table" style="--se-compare-cols: <?php echo (int) count( $samadi_core_products ); ?>">
				<caption class="screen-reader-text"><?php esc_html_e( 'مقایسه مشخصات محصولات', 'samadi-core' ); ?></caption>
				<thead>
					<tr>
						<th scope="col" class="se-core-compare__label"><span class="screen-reader-text"><?php esc_html_e( 'ویژگی', 'samadi-core' ); ?></span></th>
						<?php foreach ( $samadi_core_products as $samadi_core_product ) : ?>
							<th scope="col" class="se-core-compare__product">
								<button type="button" class="se-core-compare__remove" data-se-compare="<?php echo esc_attr( $samadi_core_product->get_id() ); ?>" data-se-compare-remove aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'حذف %s از مقایسه', 'samadi-core' ), $samadi_core_product->get_name() ) ); ?>"><?php echo samadi_core_icon( 'x', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
								<a class="se-core-compare__image" href="<?php echo esc_url( $samadi_core_product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
									<?php echo $samadi_core_product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
								<a class="se-core-compare__name" href="<?php echo esc_url( $samadi_core_product->get_permalink() ); ?>"><?php echo esc_html( $samadi_core_product->get_name() ); ?></a>
								<div class="se-core-compare__cart">
									<?php
									$GLOBALS['product'] = $samadi_core_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
									woocommerce_template_loop_add_to_cart();
									?>
								</div>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $samadi_core_rows as $samadi_core_key => $samadi_core_row ) : ?>
						<tr class="se-core-compare__row se-core-compare__row--<?php echo esc_attr( sanitize_html_class( $samadi_core_key ) ); ?>"<?php echo ! empty( $samadi_core_row['same'] ) ? ' data-same' : ''; ?>>
							<th scope="row" class="se-core-compare__label"><?php echo esc_html( $samadi_core_row['label'] ); ?></th>
							<?php foreach ( $samadi_core_row['values'] as $samadi_core_value ) : ?>
								<td><?php echo '' !== trim( wp_strip_all_tags( (string) $samadi_core_value ) ) || false !== strpos( (string) $samadi_core_value, 'star-rating' ) ? wp_kses_post( $samadi_core_value ) : '<span class="se-core-compare__none" aria-label="' . esc_attr__( 'ندارد', 'samadi-core' ) . '">—</span>'; ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="se-core-empty">
			<span class="se-core-empty__icon"><?php echo samadi_core_icon( 'git-compare-arrows', 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h2 class="se-core-empty__title"><?php esc_html_e( 'هنوز محصولی برای مقایسه انتخاب نکرده‌اید', 'samadi-core' ); ?></h2>
			<p class="se-core-empty__text"><?php esc_html_e( 'روی کارت هر محصول، دکمه مقایسه را بزنید تا مشخصات آن‌ها را کنار هم ببینید.', 'samadi-core' ); ?></p>
			<a class="se-btn se-btn--primary" href="<?php echo esc_url( $samadi_core_shop ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'samadi-core' ); ?></a>
		</div>
	<?php endif; ?>
</div>
<?php
$GLOBALS['product'] = $samadi_core_global; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
