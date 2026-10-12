<?php
/**
 * Page content.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_is_front    = ! empty( $args['is_front'] );
$samadi_is_wc_page  = samadi_is_wc() && ( is_cart() || is_checkout() || is_account_page() );

while ( have_posts() ) :
	the_post();
	$samadi_elementor = samadi_is_built_with_elementor();
	?>
	<main id="se-main" class="se-main<?php echo $samadi_elementor ? ' se-main--elementor' : ''; ?>">
		<?php if ( $samadi_elementor && ! $samadi_is_wc_page ) : ?>
			<?php the_content(); ?>
		<?php else : ?>
			<div class="se-container">
				<?php
				if ( ! $samadi_is_front ) {
					samadi_breadcrumb();
				}
				if ( samadi_is_wc() && ( is_cart() || is_checkout() ) ) {
					samadi_wc_checkout_steps();
				}
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( $samadi_is_wc_page ? 'se-wc-page' : 'se-entry se-card' ); ?>>
					<?php if ( ! $samadi_is_front && ! $samadi_elementor ) : ?>
						<header class="se-entry__header">
							<?php the_title( '<h1 class="entry-title se-entry__title">', '</h1>' ); ?>
						</header>
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
				</article>
				<?php
				if ( ! $samadi_is_wc_page && ( comments_open() || get_comments_number() ) ) {
					comments_template();
				}
				?>
			</div>
		<?php endif; ?>
	</main>
	<?php
endwhile;
