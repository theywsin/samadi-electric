<?php
/**
 * Post archive / blog index / search results.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_is_search = ! empty( $args['search'] );

if ( $samadi_is_search ) {
	/* translators: %s: search query. */
	$samadi_title = sprintf( __( 'نتایج جستجو برای «%s»', 'samadi-electric' ), get_search_query() );
} elseif ( is_home() && ! is_front_page() ) {
	$samadi_title = single_post_title( '', false );
} elseif ( is_archive() ) {
	$samadi_title = get_the_archive_title();
} else {
	$samadi_title = __( 'آخرین مطالب', 'samadi-electric' );
}
?>
<main id="se-main" class="se-main">
	<div class="se-container">
		<?php samadi_breadcrumb(); ?>
		<header class="se-page-header">
			<h1 class="se-page-header__title"><?php echo wp_kses_post( $samadi_title ); ?></h1>
			<?php
			$samadi_desc = get_the_archive_description();
			if ( $samadi_desc && ! $samadi_is_search ) {
				echo '<div class="se-page-header__desc">' . wp_kses_post( $samadi_desc ) . '</div>';
			}
			?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="se-post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/archive/post-card' );
				endwhile;
				?>
			</div>
			<?php samadi_pagination(); ?>
		<?php else : ?>
			<section class="se-empty">
				<h2 class="se-empty__title"><?php esc_html_e( 'مطلبی پیدا نشد', 'samadi-electric' ); ?></h2>
				<p class="se-empty__text"><?php esc_html_e( 'عبارت دیگری را جستجو کنید.', 'samadi-electric' ); ?></p>
				<div class="se-empty__search"><?php get_search_form(); ?></div>
			</section>
		<?php endif; ?>
	</div>
</main>
