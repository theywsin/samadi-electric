<?php
/**
 * Comments.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="se-comments se-card">
	<?php if ( have_comments() ) : ?>
		<h2 class="se-comments__title">
			<?php
			$samadi_count = get_comments_number();
			/* translators: %s: number of comments. */
			echo esc_html( sprintf( _n( '%s دیدگاه', '%s دیدگاه', $samadi_count, 'samadi-electric' ), samadi_number( $samadi_count ) ) );
			?>
		</h2>
		<ol class="se-comments__list comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="se-comments__closed"><?php esc_html_e( 'امکان ثبت دیدگاه بسته شده است.', 'samadi-electric' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit' => 'se-btn se-btn--primary',
		)
	);
	?>
</section>
