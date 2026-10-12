<?php
/**
 * Site footer and document end.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

samadi_render_footer();

if ( samadi_show_bottom_nav() ) {
	get_template_part( 'template-parts/components/bottom-nav' );
}
?>
</div><!-- #se-page -->
<?php get_template_part( 'template-parts/header/mobile-drawer' ); ?>
<?php wp_footer(); ?>
</body>
</html>
