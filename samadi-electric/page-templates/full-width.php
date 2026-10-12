<?php
/**
 * Template Name: تمام‌عرض (بدون عنوان)
 * Template Post Type: page, post
 *
 * Full-width content between the site header and footer, without the page title or card frame.
 * Ideal for Elementor-built pages that should keep the site header/footer.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="se-main" class="se-main se-main--full">
		<?php the_content(); ?>
	</main>
	<?php
endwhile;

get_footer();
