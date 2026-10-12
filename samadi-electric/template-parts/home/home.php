<?php
/**
 * Theme homepage (used when no static front page is configured).
 * Every section reads real store data and can be toggled in the Customizer.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_count = max( 1, min( 24, absint( samadi_option( 'home_products_count' ) ) ) );
?>
<main id="se-main" class="se-main se-home">
	<div class="se-container">
		<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
		<?php
		if ( samadi_option( 'home_show_hero' ) ) {
			get_template_part( 'template-parts/home/hero' );
		}

		if ( samadi_option( 'home_show_benefits' ) && samadi_option( 'benefits_show' ) ) {
			get_template_part( 'template-parts/components/benefits' );
		}

		if ( samadi_is_wc() ) {
			if ( samadi_option( 'home_show_categories' ) ) {
				get_template_part( 'template-parts/home/categories' );
			}

			$samadi_sections = array(
				'home_show_sale'        => array( __( 'پیشنهادهای ویژه', 'samadi-electric' ), 'on_sale="true" orderby="popularity"' ),
				'home_show_bestsellers' => array( __( 'پرفروش‌ترین‌ها', 'samadi-electric' ), 'best_selling="true"' ),
				'home_show_new'         => array( __( 'جدیدترین محصولات', 'samadi-electric' ), 'orderby="date" order="DESC"' ),
			);

			foreach ( $samadi_sections as $samadi_option_key => $samadi_section ) {
				if ( ! samadi_option( $samadi_option_key ) ) {
					continue;
				}
				get_template_part(
					'template-parts/home/products',
					null,
					array(
						'title'     => $samadi_section[0],
						'shortcode' => sprintf( '[products limit="%d" columns="4" %s]', $samadi_count, $samadi_section[1] ),
					)
				);
			}

			if ( samadi_option( 'home_show_brands' ) ) {
				get_template_part( 'template-parts/home/brands' );
			}
		}

		if ( samadi_option( 'home_show_blog' ) ) {
			get_template_part( 'template-parts/home/posts' );
		}
		?>
	</div>
</main>
