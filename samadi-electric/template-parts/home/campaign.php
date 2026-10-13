<?php
/**
 * Homepage campaign banner (dark green with a lifestyle photo).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_title = (string) samadi_option( 'campaign_title' );

if ( '' === $samadi_title ) {
	return;
}

$samadi_image = absint( samadi_option( 'campaign_image' ) );
$samadi_text  = (string) samadi_option( 'campaign_text' );
$samadi_btn   = (string) samadi_option( 'campaign_btn_text' );
$samadi_url   = (string) samadi_option( 'campaign_btn_url' );
$samadi_url   = $samadi_url ? $samadi_url : samadi_shop_url();
?>
<section class="se-section se-campaign">
	<div class="se-campaign__content">
		<h2 class="se-campaign__title"><?php echo esc_html( $samadi_title ); ?></h2>
		<?php if ( $samadi_text ) : ?>
			<p class="se-campaign__text"><?php echo esc_html( $samadi_text ); ?></p>
		<?php endif; ?>
		<?php if ( $samadi_btn ) : ?>
			<a class="se-btn se-btn--light" href="<?php echo esc_url( $samadi_url ); ?>">
				<?php echo esc_html( $samadi_btn ); ?>
				<?php samadi_icon( 'arrow-left', array( 'size' => 18 ) ); ?>
			</a>
		<?php endif; ?>
	</div>
	<div class="se-campaign__media">
		<?php
		if ( $samadi_image ) {
			echo wp_get_attachment_image( $samadi_image, 'large', false, array( 'class' => 'se-campaign__image', 'loading' => 'lazy', 'alt' => '' ) );
		} else {
			printf( '<img class="se-campaign__image" src="%s" width="463" height="244" alt="" loading="lazy">', esc_url( SAMADI_URI . '/assets/images/campaign.jpg' ) );
		}
		?>
	</div>
</section>
