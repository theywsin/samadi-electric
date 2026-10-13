<?php
/**
 * Homepage project consultation call-to-action.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_title = (string) samadi_option( 'consult_title' );
$samadi_btn   = (string) samadi_option( 'consult_btn_text' );
$samadi_url   = samadi_contact_url( (string) samadi_option( 'consult_btn_url' ) );

if ( '' === $samadi_title ) {
	return;
}

$samadi_text   = (string) samadi_option( 'consult_text' );
$samadi_badge  = (string) samadi_option( 'consult_badge' );
$samadi_badge2 = (string) samadi_option( 'consult_badge_text' );
$samadi_phone  = (string) samadi_option( 'contact_phone' );
?>
<section class="se-section se-consult">
	<?php if ( $samadi_badge ) : ?>
		<div class="se-consult__badge" aria-hidden="true">
			<strong><?php echo esc_html( $samadi_badge ); ?></strong>
			<?php if ( $samadi_badge2 ) : ?>
				<span><?php echo esc_html( $samadi_badge2 ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="se-consult__copy">
		<h2 class="se-consult__title"><?php echo esc_html( $samadi_title ); ?></h2>
		<?php if ( $samadi_text ) : ?>
			<p class="se-consult__text"><?php echo esc_html( $samadi_text ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $samadi_btn && $samadi_url ) : ?>
		<div class="se-consult__action">
			<a class="se-btn se-btn--primary se-btn--lg se-btn--block" href="<?php echo esc_url( $samadi_url ); ?>">
				<?php samadi_icon( 'headset', array( 'size' => 18 ) ); ?>
				<?php echo esc_html( $samadi_btn ); ?>
			</a>
			<?php if ( $samadi_phone ) : ?>
				<a class="se-consult__phone" href="tel:<?php echo esc_attr( samadi_tel( $samadi_phone ) ); ?>" dir="ltr"><?php echo esc_html( $samadi_phone ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
