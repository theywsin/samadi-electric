<?php
/**
 * Post card (blog grids and homepage articles section).
 *
 * Args: heading (h2|h3), style ('' | 'journal' — homepage magazine card without a box).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_heading = isset( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h2';
$samadi_journal = isset( $args['style'] ) && 'journal' === $args['style'];
?>
<article <?php post_class( $samadi_journal ? 'se-post-card se-post-card--journal' : 'se-post-card se-card' ); ?>>
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
		<?php if ( $samadi_journal ) : ?>
			<p class="se-post-card__meta">
				<?php
				$samadi_cats  = get_the_category();
				$samadi_parts = array();
				if ( $samadi_cats ) {
					$samadi_parts[] = $samadi_cats[0]->name;
				}
				$samadi_parts[] = samadi_reading_time_label();
				// Each part is bidi-isolated so Latin category names don't reorder the line.
				echo '<bdi>' . implode( '</bdi>  •  <bdi>', array_map( 'esc_html', $samadi_parts ) ) . '</bdi>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Parts escaped above.
				?>
			</p>
		<?php endif; ?>
		<<?php echo esc_attr( $samadi_heading ); ?> class="se-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( $samadi_heading ); ?>>
		<?php if ( $samadi_journal ) : ?>
			<a class="se-post-card__more" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php esc_html_e( 'ادامه مطلب', 'samadi-electric' ); ?><?php samadi_icon( 'arrow-left', array( 'size' => 12 ) ); ?></a>
		<?php else : ?>
			<p class="se-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
			<time class="se-post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<?php endif; ?>
	</div>
</article>
