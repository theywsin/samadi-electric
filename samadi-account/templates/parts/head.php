<?php
/**
 * Section header: title + optional link.
 *
 * @package Samadi_Account
 *
 * @var string $title Title.
 * @var string $link  URL.
 * @var string $label Link text.
 * @var string $tag   Heading tag.
 */

defined( 'ABSPATH' ) || exit;

$tag = isset( $tag ) ? $tag : 'h2';
?>
<div class="sad-head">
	<<?php echo esc_attr( $tag ); ?> class="sad-head__title"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $tag ); ?>>
	<?php if ( ! empty( $link ) ) : ?>
		<a class="sad-head__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $label ); ?> <?php echo sad_icon( 'chevron-left', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<?php endif; ?>
</div>
