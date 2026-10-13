<?php
/**
 * Homepage hero banner (Customizer driven): copy on the start side, photo on the end side.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_image  = absint( samadi_option( 'hero_image' ) );
$samadi_badge  = (string) samadi_option( 'hero_badge' );
$samadi_title  = (string) samadi_option( 'hero_title' );
$samadi_text   = (string) samadi_option( 'hero_text' );
$samadi_note   = (string) samadi_option( 'hero_note' );
$samadi_btn1   = (string) samadi_option( 'hero_btn1_text' );
$samadi_btn1_u = (string) samadi_option( 'hero_btn1_url' );
$samadi_btn1_u = $samadi_btn1_u ? $samadi_btn1_u : samadi_shop_url();
$samadi_btn2   = (string) samadi_option( 'hero_btn2_text' );
$samadi_btn2_u = samadi_contact_url( (string) samadi_option( 'hero_btn2_url' ) );
?>
<section class="se-home-hero" aria-labelledby="se-home-hero-title">
	<div class="se-home-hero__media">
		<?php
		if ( $samadi_image ) {
			echo wp_get_attachment_image(
				$samadi_image,
				'samadi-banner',
				false,
				array(
					'class'         => 'se-home-hero__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '(max-width: 767px) 100vw, 780px',
					'alt'           => '',
				)
			);
		} else {
			printf(
				'<img class="se-home-hero__image" src="%s" width="780" height="440" alt="" fetchpriority="high">',
				esc_url( SAMADI_URI . '/assets/images/hero.jpg' )
			);
		}
		?>
	</div>
	<div class="se-home-hero__content">
		<?php if ( $samadi_badge ) : ?>
			<span class="se-kicker"><?php echo esc_html( $samadi_badge ); ?></span>
		<?php endif; ?>
		<?php if ( $samadi_title ) : ?>
			<h2 class="se-home-hero__title" id="se-home-hero-title"><?php echo esc_html( $samadi_title ); ?></h2>
		<?php endif; ?>
		<?php if ( $samadi_text ) : ?>
			<p class="se-home-hero__text"><?php echo esc_html( $samadi_text ); ?></p>
		<?php endif; ?>
		<?php if ( $samadi_btn1 || ( $samadi_btn2 && $samadi_btn2_u ) ) : ?>
			<div class="se-home-hero__actions">
				<?php if ( $samadi_btn1 ) : ?>
					<a class="se-btn se-btn--primary" href="<?php echo esc_url( $samadi_btn1_u ); ?>">
						<?php echo esc_html( $samadi_btn1 ); ?>
						<?php samadi_icon( 'arrow-left', array( 'size' => 18 ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $samadi_btn2 && $samadi_btn2_u ) : ?>
					<a class="se-btn se-btn--outline" href="<?php echo esc_url( $samadi_btn2_u ); ?>"><?php echo esc_html( $samadi_btn2 ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $samadi_note ) : ?>
			<p class="se-home-hero__note"><?php echo esc_html( $samadi_note ); ?></p>
		<?php endif; ?>
	</div>
</section>
