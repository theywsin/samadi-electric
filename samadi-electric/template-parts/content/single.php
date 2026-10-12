<?php
/**
 * Single post content — magazine layout from the Samadi Electric design
 * (category pill, large title, meta row, wide cover, readable column, tags/share, related posts).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_has_sidebar = is_active_sidebar( 'sidebar-blog' );

while ( have_posts() ) :
	the_post();
	$samadi_cats      = get_the_category();
	$samadi_elementor = samadi_is_built_with_elementor();
	?>
	<main id="se-main" class="se-main se-article-page">
		<div class="se-container">
			<?php samadi_breadcrumb(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'se-article' ); ?>>
				<header class="se-article__header">
					<?php if ( $samadi_cats ) : ?>
						<a class="se-pill" href="<?php echo esc_url( get_category_link( $samadi_cats[0] ) ); ?>"><?php echo esc_html( $samadi_cats[0]->name ); ?></a>
					<?php endif; ?>
					<?php the_title( '<h1 class="entry-title se-article__title">', '</h1>' ); ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="se-article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<ul class="se-article__meta">
						<li><?php samadi_icon( 'user', array( 'size' => 16 ) ); ?><?php the_author(); ?></li>
						<li><?php samadi_icon( 'calendar', array( 'size' => 16 ) ); ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></li>
						<li><?php samadi_icon( 'clock', array( 'size' => 16 ) ); ?><?php echo esc_html( samadi_reading_time_label() ); ?></li>
					</ul>
				</header>

				<?php if ( has_post_thumbnail() && ! $samadi_elementor ) : ?>
					<figure class="se-article__cover">
						<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 1340px) 100vw, 1312px' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="se-article__layout<?php echo $samadi_has_sidebar ? ' has-sidebar' : ''; ?>">
					<div class="se-article__main">
						<div class="se-entry__content entry-content se-article__content">
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

						<footer class="se-article__footer">
							<?php the_tags( '<div class="se-tags">', '', '</div>' ); ?>
							<button type="button" class="se-btn se-btn--outline se-article__share" data-se-share data-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title() ) ); ?>" data-url="<?php echo esc_url( get_permalink() ); ?>">
								<?php samadi_icon( 'share-2', array( 'size' => 16 ) ); ?>
								<?php esc_html_e( 'اشتراک‌گذاری', 'samadi-electric' ); ?>
							</button>
						</footer>

						<?php
						the_post_navigation(
							array(
								'prev_text' => '<span class="se-post-nav__label">' . esc_html__( 'مطلب قبلی', 'samadi-electric' ) . '</span><span class="se-post-nav__title">%title</span>',
								'next_text' => '<span class="se-post-nav__label">' . esc_html__( 'مطلب بعدی', 'samadi-electric' ) . '</span><span class="se-post-nav__title">%title</span>',
							)
						);

						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>
					</div>

					<?php if ( $samadi_has_sidebar ) : ?>
						<aside class="se-sidebar se-article__sidebar" aria-label="<?php esc_attr_e( 'سایدبار', 'samadi-electric' ); ?>">
							<?php dynamic_sidebar( 'sidebar-blog' ); ?>
						</aside>
					<?php endif; ?>
				</div>
			</article>

			<?php
			$samadi_related = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 3,
					'post__not_in'        => array( get_the_ID() ),
					'category__in'        => wp_list_pluck( $samadi_cats, 'term_id' ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);

			if ( $samadi_related->have_posts() ) :
				?>
				<section class="se-section se-related-posts">
					<?php samadi_section_head( __( 'مطالب مرتبط', 'samadi-electric' ), $samadi_cats ? get_category_link( $samadi_cats[0] ) : '' ); ?>
					<div class="se-post-grid se-post-grid--journal">
						<?php
						while ( $samadi_related->have_posts() ) :
							$samadi_related->the_post();
							get_template_part( 'template-parts/archive/post-card', null, array( 'heading' => 'h3', 'style' => 'journal' ) );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;
