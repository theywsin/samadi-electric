<?php
/**
 * 404 page. With Elementor Pro a "404" Theme Builder template takes over.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! samadi_elementor_location( 'single' ) ) :
	?>
	<main id="se-main" class="se-main">
		<div class="se-container">
			<section class="se-empty se-empty--404">
				<span class="se-empty__icon"><?php samadi_icon( 'zap', array( 'size' => 32 ) ); ?></span>
				<p class="se-empty__code" aria-hidden="true"><?php echo esc_html( samadi_option( 'persian_digits' ) ? samadi_fa_digits( '404' ) : '404' ); ?></p>
				<h1 class="se-empty__title"><?php esc_html_e( 'صفحه موردنظر پیدا نشد', 'samadi-electric' ); ?></h1>
				<p class="se-empty__text"><?php esc_html_e( 'ممکن است آدرس تغییر کرده باشد. از جستجو استفاده کنید یا به فروشگاه بروید.', 'samadi-electric' ); ?></p>
				<div class="se-empty__search"><?php get_search_form(); ?></div>
				<div class="se-empty__actions">
					<a class="se-btn se-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'samadi-electric' ); ?></a>
					<?php if ( samadi_is_wc() ) : ?>
						<a class="se-btn se-btn--outline" href="<?php echo esc_url( samadi_shop_url() ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'samadi-electric' ); ?></a>
					<?php endif; ?>
				</div>
				<?php
				if ( samadi_is_wc() ) {
					$samadi_cats = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'parent'     => 0,
							'hide_empty' => true,
							'number'     => 5,
							'orderby'    => 'menu_order',
							'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
						)
					);
					if ( ! is_wp_error( $samadi_cats ) && $samadi_cats ) :
						?>
						<div class="se-empty__cats">
							<ul class="se-cat-tiles">
								<?php foreach ( $samadi_cats as $samadi_cat ) : ?>
									<li class="se-cat-tile">
										<a class="se-cat-tile__link" href="<?php echo esc_url( get_term_link( $samadi_cat ) ); ?>">
											<span class="se-cat-tile__media">
												<?php
												$samadi_img = samadi_term_image( $samadi_cat, 'woocommerce_thumbnail' );
												echo $samadi_img ? $samadi_img : samadi_get_icon( 'zap', array( 'size' => 32 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												?>
											</span>
											<span class="se-cat-tile__name"><?php echo esc_html( $samadi_cat->name ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php
					endif;
				}
				?>
			</section>
		</div>
	</main>
	<?php
endif;

get_footer();
