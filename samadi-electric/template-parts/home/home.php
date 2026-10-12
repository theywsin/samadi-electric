<?php
/**
 * Theme homepage (used when no static front page is configured).
 * Every section reads real store data and can be toggled in the Customizer.
 *
 * Order follows the approved Figma design: hero → benefits → categories → special offers → campaign →
 * best sellers → brands → journal → consultation → newsletter (on phones categories come before benefits).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_count = max( 1, min( 24, absint( samadi_option( 'home_products_count' ) ) ) );
?>
<main id="se-main" class="se-main se-home">
	<div class="se-container se-home__sections">
		<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
		<?php
		if ( samadi_option( 'home_show_hero' ) ) {
			get_template_part( 'template-parts/home/hero' );
		}

		if ( samadi_option( 'home_show_benefits' ) && samadi_option( 'benefits_show' ) ) {
			get_template_part( 'template-parts/components/benefits' );
		}

		if ( samadi_is_wc() && samadi_option( 'home_show_categories' ) ) {
			get_template_part( 'template-parts/home/categories' );
		}

		if ( samadi_is_wc() ) {
			if ( samadi_option( 'home_show_sale' ) ) {
				get_template_part(
					'template-parts/home/products',
					null,
					array(
						'title'     => (string) samadi_option( 'home_sale_title' ),
						'shortcode' => sprintf( '[products limit="%d" columns="4" on_sale="true" orderby="popularity"]', $samadi_count ),
						'countdown' => samadi_sale_countdown_end(),
						'modifier'  => 'sale',
					)
				);
			}

			if ( samadi_option( 'home_show_campaign' ) ) {
				get_template_part( 'template-parts/home/campaign' );
			}

			if ( samadi_option( 'home_show_bestsellers' ) ) {
				get_template_part(
					'template-parts/home/products',
					null,
					array(
						'title'     => (string) samadi_option( 'home_bestsellers_title' ),
						'shortcode' => sprintf( '[products limit="%d" columns="4" best_selling="true"]', $samadi_count ),
					)
				);
			}

			if ( samadi_option( 'home_show_new' ) ) {
				get_template_part(
					'template-parts/home/products',
					null,
					array(
						'title'     => __( 'جدیدترین محصولات', 'samadi-electric' ),
						'shortcode' => sprintf( '[products limit="%d" columns="4" orderby="date" order="DESC"]', $samadi_count ),
					)
				);
			}

			if ( samadi_option( 'home_show_brands' ) ) {
				get_template_part( 'template-parts/home/brands' );
			}
		} elseif ( samadi_option( 'home_show_campaign' ) ) {
			get_template_part( 'template-parts/home/campaign' );
		}

		if ( samadi_option( 'home_show_blog' ) ) {
			get_template_part( 'template-parts/home/posts' );
		}

		if ( samadi_option( 'home_show_consult' ) ) {
			get_template_part( 'template-parts/home/consult' );
		}

		if ( samadi_option( 'home_show_newsletter' ) ) {
			get_template_part( 'template-parts/home/newsletter' );
		}
		?>
	</div>
</main>
