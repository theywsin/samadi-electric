<?php
/**
 * Homepage hero banner (Customizer driven).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_image   = absint( samadi_option( 'hero_image' ) );
$samadi_title   = (string) samadi_option( 'hero_title' );
$samadi_text    = (string) samadi_option( 'hero_text' );
$samadi_btn1    = (string) samadi_option( 'hero_btn1_text' );
$samadi_btn1_u  = (string) samadi_option( 'hero_btn1_url' );
$samadi_btn1_u  = $samadi_btn1_u ? $samadi_btn1_u : samadi_shop_url();
$samadi_btn2    = (string) samadi_option( 'hero_btn2_text' );
$samadi_btn2_u  = samadi_wholesale_url();
$samadi_align   = in_array( samadi_option( 'hero_align' ), array( 'start', 'center', 'end' ), true ) ? samadi_option( 'hero_align' ) : 'start';
?>
<section class="se-hero se-hero--<?php echo esc_attr( $samadi_align ); ?><?php echo $samadi_image ? ' has-image' : ''; ?>">
	<?php
	if ( $samadi_image ) {
		echo wp_get_attachment_image(
			$samadi_image,
			'samadi-banner',
			false,
			array(
				'class'         => 'se-hero__image',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'sizes'         => '(max-width: 1320px) 100vw, 1280px',
				'alt'           => '',
			)
		);
	}
	?>
	<div class="se-hero__content">
		<?php if ( $samadi_title ) : ?>
			<h2 class="se-hero__title"><?php echo esc_html( $samadi_title ); ?></h2>
		<?php endif; ?>
		<?php if ( $samadi_text ) : ?>
			<p class="se-hero__text"><?php echo esc_html( $samadi_text ); ?></p>
		<?php endif; ?>
		<div class="se-hero__actions">
			<?php if ( $samadi_btn1 ) : ?>
				<a class="se-btn se-btn--primary se-btn--lg" href="<?php echo esc_url( $samadi_btn1_u ); ?>">
					<?php echo esc_html( $samadi_btn1 ); ?>
					<?php samadi_icon( 'arrow-left', array( 'size' => 18 ) ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $samadi_btn2 && $samadi_btn2_u ) : ?>
				<a class="se-btn se-btn--light se-btn--lg" href="<?php echo esc_url( $samadi_btn2_u ); ?>"><?php echo esc_html( $samadi_btn2 ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
