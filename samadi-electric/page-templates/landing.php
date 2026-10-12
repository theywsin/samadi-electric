<?php
/**
 * Template Name: صفحه فرود (Landing)
 * Template Post Type: page
 *
 * Campaign/landing page: minimal header (logo only), no menus, no bottom navigation.
 * Build the content with Elementor.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'samadi_show_bottom_nav', '__return_false' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'se-landing' ); ?>>
<?php wp_body_open(); ?>
<div id="se-page" class="se-site">
	<header class="se-landing__header">
		<div class="se-container"><?php samadi_site_logo( 'header' ); ?></div>
	</header>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<main id="se-main" class="se-main se-main--full">
			<?php the_content(); ?>
		</main>
		<?php
	endwhile;
	?>
	<footer class="se-landing__footer">
		<div class="se-container">
			<?php get_template_part( 'template-parts/components/contact-list' ); ?>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
