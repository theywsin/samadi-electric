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
				<p class="se-empty__code" aria-hidden="true"><?php echo esc_html( samadi_number( 404 ) ); ?></p>
				<h1 class="se-empty__title"><?php esc_html_e( 'صفحه موردنظر پیدا نشد', 'samadi-electric' ); ?></h1>
				<p class="se-empty__text"><?php esc_html_e( 'ممکن است آدرس تغییر کرده باشد. از جستجو استفاده کنید یا به فروشگاه بروید.', 'samadi-electric' ); ?></p>
				<div class="se-empty__search"><?php get_search_form(); ?></div>
				<div class="se-empty__actions">
					<a class="se-btn se-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'samadi-electric' ); ?></a>
					<?php if ( samadi_is_wc() ) : ?>
						<a class="se-btn se-btn--outline" href="<?php echo esc_url( samadi_shop_url() ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'samadi-electric' ); ?></a>
					<?php endif; ?>
				</div>
			</section>
		</div>
	</main>
	<?php
endif;

get_footer();
