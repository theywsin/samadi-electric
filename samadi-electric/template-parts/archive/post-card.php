<?php
/**
 * Post card (blog grids and homepage articles section).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_heading = isset( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h2';
?>
<article <?php post_class( 'se-post-card se-card' ); ?>>
	<a class="se-post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) );
		} else {
			echo '<span class="se-post-card__placeholder">' . samadi_get_icon( 'lightbulb', array( 'size' => 40 ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</a>
	<div class="se-post-card__body">
		<<?php echo esc_attr( $samadi_heading ); ?> class="se-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( $samadi_heading ); ?>>
		<p class="se-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<time class="se-post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	</div>
</article>
