<?php
/**
 * Single post content.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_has_sidebar = is_active_sidebar( 'sidebar-blog' );

while ( have_posts() ) :
	the_post();
	?>
	<main id="se-main" class="se-main">
		<div class="se-container">
			<?php samadi_breadcrumb(); ?>
			<div class="se-layout<?php echo $samadi_has_sidebar ? ' se-layout--sidebar' : ''; ?>">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'se-entry se-card' ); ?>>
					<header class="se-entry__header">
						<?php the_title( '<h1 class="entry-title se-entry__title">', '</h1>' ); ?>
						<?php samadi_post_meta(); ?>
					</header>
					<?php if ( has_post_thumbnail() && ! samadi_is_built_with_elementor() ) : ?>
						<figure class="se-entry__thumb">
							<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
						</figure>
					<?php endif; ?>
					<div class="se-entry__content entry-content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="se-page-links">' . esc_html__( 'صفحات:', 'samadi-electric' ),
								'after'  => '</nav>',
							)
						);
						?>
					</div>
					<footer class="se-entry__footer">
						<?php the_tags( '<div class="se-tags">', '', '</div>' ); ?>
					</footer>
				</article>
				<?php if ( $samadi_has_sidebar ) : ?>
					<aside class="se-sidebar" aria-label="<?php esc_attr_e( 'سایدبار', 'samadi-electric' ); ?>">
						<?php dynamic_sidebar( 'sidebar-blog' ); ?>
					</aside>
				<?php endif; ?>
			</div>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="se-post-nav__label">' . esc_html__( 'نوشته قبلی', 'samadi-electric' ) . '</span><span class="se-post-nav__title">%title</span>',
					'next_text' => '<span class="se-post-nav__label">' . esc_html__( 'نوشته بعدی', 'samadi-electric' ) . '</span><span class="se-post-nav__title">%title</span>',
				)
			);
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</main>
	<?php
endwhile;
