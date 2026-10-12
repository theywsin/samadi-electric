<?php
/**
 * Contact details list (only fields filled in the Customizer are shown).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_class   = isset( $args['class'] ) ? $args['class'] : '';
$samadi_phone   = (string) samadi_option( 'contact_phone' );
$samadi_mobile  = (string) samadi_option( 'contact_mobile' );
$samadi_email   = (string) samadi_option( 'contact_email' );
$samadi_address = (string) samadi_option( 'contact_address' );
$samadi_hours   = (string) samadi_option( 'contact_hours' );

if ( ! $samadi_phone && ! $samadi_mobile && ! $samadi_email && ! $samadi_address && ! $samadi_hours ) {
	return;
}
?>
<ul class="se-contact <?php echo esc_attr( $samadi_class ); ?>">
	<?php foreach ( array_filter( array( $samadi_phone, $samadi_mobile ) ) as $samadi_number ) : ?>
		<li class="se-contact__item">
			<?php samadi_icon( 'phone', array( 'size' => 18 ) ); ?>
			<a href="tel:<?php echo esc_attr( samadi_tel( $samadi_number ) ); ?>" dir="ltr"><?php echo esc_html( $samadi_number ); ?></a>
		</li>
	<?php endforeach; ?>
	<?php if ( $samadi_email ) : ?>
		<li class="se-contact__item">
			<?php samadi_icon( 'mail', array( 'size' => 18 ) ); ?>
			<a href="mailto:<?php echo esc_attr( antispambot( $samadi_email ) ); ?>"><?php echo esc_html( antispambot( $samadi_email ) ); ?></a>
		</li>
	<?php endif; ?>
	<?php if ( $samadi_address ) : ?>
		<li class="se-contact__item">
			<?php samadi_icon( 'map-pin', array( 'size' => 18 ) ); ?>
			<address><?php echo nl2br( esc_html( $samadi_address ) ); ?></address>
		</li>
	<?php endif; ?>
	<?php if ( $samadi_hours ) : ?>
		<li class="se-contact__item">
			<?php samadi_icon( 'clock', array( 'size' => 18 ) ); ?>
			<span><?php echo esc_html( $samadi_hours ); ?></span>
		</li>
	<?php endif; ?>
</ul>
