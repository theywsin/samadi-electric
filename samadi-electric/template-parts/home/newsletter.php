<?php
/**
 * Homepage newsletter strip. Uses a newsletter plugin shortcode when configured, otherwise the
 * companion plugin's mobile-number sign-up form. Hidden when neither is available.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_shortcode = (string) samadi_option( 'newsletter_shortcode' );

if ( $samadi_shortcode ) {
	$samadi_form = do_shortcode( $samadi_shortcode );
} elseif ( shortcode_exists( 'samadi_newsletter' ) ) {
	$samadi_form = do_shortcode( '[samadi_newsletter]' );
} else {
	$samadi_form = '';
}

if ( '' === trim( $samadi_form ) ) {
	return;
}

$samadi_title = (string) samadi_option( 'newsletter_title' );
$samadi_text  = (string) samadi_option( 'newsletter_text' );
?>
<section class="se-section se-newsletter">
	<span class="se-newsletter__icon"><?php samadi_icon( 'mail', array( 'size' => 36 ) ); ?></span>
	<div class="se-newsletter__copy">
		<?php if ( $samadi_title ) : ?>
			<h2 class="se-newsletter__title"><?php echo esc_html( $samadi_title ); ?></h2>
		<?php endif; ?>
		<?php if ( $samadi_text ) : ?>
			<p class="se-newsletter__text"><?php echo esc_html( $samadi_text ); ?></p>
		<?php endif; ?>
	</div>
	<div class="se-newsletter__form"><?php echo $samadi_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output. ?></div>
</section>
