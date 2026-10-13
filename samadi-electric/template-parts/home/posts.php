<?php
/**
 * Homepage educational articles.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, min( 12, absint( samadi_option( 'home_posts_count' ) ) ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $samadi_posts->have_posts() ) {
	return;
}

$samadi_blog = (int) get_option( 'page_for_posts' );
?>
<section class="se-section se-home-posts">
	<?php samadi_section_head( __( 'مجله برق و روشنایی', 'samadi-electric' ), $samadi_blog ? get_permalink( $samadi_blog ) : '' ); ?>
	<div class="se-post-grid se-post-grid--journal">
		<?php
		while ( $samadi_posts->have_posts() ) :
			$samadi_posts->the_post();
			get_template_part( 'template-parts/archive/post-card', null, array( 'heading' => 'h3', 'style' => 'journal' ) );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
