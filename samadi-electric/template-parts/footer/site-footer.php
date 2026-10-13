<?php
/**
 * Theme footer (used when no Elementor footer template is assigned).
 *
 * Layout: consultation/contact band → brand + link columns + contact → bottom bar.
 * On mobile the link columns become accordions (progressive enhancement in theme.js).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_about     = (string) samadi_option( 'footer_about' );
$samadi_social    = samadi_social_links();
$samadi_copyright = (string) samadi_option( 'footer_copyright' );
$samadi_style     = 'light' === samadi_option( 'footer_style' ) ? 'light' : 'dark';
$samadi_columns   = samadi_footer_link_columns();
$samadi_phone     = trim( (string) samadi_option( 'contact_phone' ) );
$samadi_phone     = $samadi_phone ? $samadi_phone : trim( (string) samadi_option( 'contact_mobile' ) );
$samadi_wholesale = samadi_wholesale_url();
$samadi_cta       = samadi_option( 'footer_cta_show' ) && ( $samadi_phone || $samadi_wholesale );
$samadi_contact   = is_active_sidebar( 'footer-trust' );
foreach ( array( 'contact_phone', 'contact_mobile', 'contact_email', 'contact_address', 'contact_hours' ) as $samadi_field ) {
	$samadi_contact = $samadi_contact || '' !== trim( (string) samadi_option( $samadi_field ) );
}
?>
<footer class="se-footer se-footer--<?php echo esc_attr( $samadi_style ); ?>" id="se-footer" data-se-footer>
	<?php if ( samadi_option( 'benefits_show' ) && ! is_front_page() ) : ?>
		<div class="se-footer__benefits">
			<div class="se-container">
				<?php get_template_part( 'template-parts/components/benefits' ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="se-footer__body">
		<?php if ( $samadi_cta ) : ?>
			<div class="se-container">
				<div class="se-footer__cta">
					<span class="se-footer__cta-icon"><?php samadi_icon( 'headset', array( 'size' => 28 ) ); ?></span>
					<div class="se-footer__cta-text">
						<p class="se-footer__cta-title"><?php echo esc_html( (string) samadi_option( 'footer_cta_title' ) ); ?></p>
						<?php if ( samadi_option( 'footer_cta_text' ) ) : ?>
							<p class="se-footer__cta-desc"><?php echo esc_html( (string) samadi_option( 'footer_cta_text' ) ); ?></p>
						<?php endif; ?>
					</div>
					<div class="se-footer__cta-actions">
						<?php if ( $samadi_phone ) : ?>
							<a class="se-btn se-btn--accent se-footer__cta-call" href="tel:<?php echo esc_attr( samadi_tel( $samadi_phone ) ); ?>">
								<?php samadi_icon( 'phone', array( 'size' => 18 ) ); ?>
								<span dir="ltr"><?php echo esc_html( $samadi_phone ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( $samadi_wholesale ) : ?>
							<a class="se-btn se-footer__cta-wholesale" href="<?php echo esc_url( $samadi_wholesale ); ?>">
								<?php samadi_icon( 'building-2', array( 'size' => 18 ) ); ?>
								<span><?php echo esc_html( (string) samadi_option( 'header_wholesale_label' ) ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="se-footer__main">
			<div class="se-container se-footer__grid" style="--se-footer-cols: <?php echo max( 1, count( $samadi_columns ) ); ?>">
				<div class="se-footer__col se-footer__col--about">
					<?php samadi_site_logo( 'footer' ); ?>
					<?php if ( $samadi_about ) : ?>
						<div class="se-footer__about"><?php echo wp_kses_post( wpautop( $samadi_about ) ); ?></div>
					<?php endif; ?>
					<?php if ( $samadi_social ) : ?>
						<p class="se-footer__social-title"><?php esc_html_e( 'ما را دنبال کنید', 'samadi-electric' ); ?></p>
						<ul class="se-social">
							<?php foreach ( $samadi_social as $samadi_key => $samadi_network ) : ?>
								<li>
									<a class="se-social__link se-social__link--<?php echo esc_attr( $samadi_key ); ?>" href="<?php echo esc_url( $samadi_network['url'] ); ?>" target="_blank" rel="noopener noreferrer">
										<?php samadi_icon( $samadi_network['icon'], array( 'size' => 20 ) ); ?>
										<span class="screen-reader-text"><?php echo esc_html( $samadi_network['label'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<?php foreach ( $samadi_columns as $samadi_key => $samadi_column ) : ?>
					<div class="se-footer__col se-footer__col--links" data-se-footer-col>
						<h2 class="se-footer__title"><?php echo esc_html( $samadi_column['title'] ); ?></h2>
						<div class="se-footer__panel" id="<?php echo esc_attr( 'se-footer-panel-' . sanitize_html_class( $samadi_key ) ); ?>">
							<?php echo $samadi_column['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in samadi_footer_link_columns(). ?>
						</div>
					</div>
				<?php endforeach; ?>

				<?php if ( $samadi_contact ) : ?>
					<div class="se-footer__col se-footer__col--contact">
						<h2 class="se-footer__title se-footer__title--static"><?php esc_html_e( 'ارتباط با ما', 'samadi-electric' ); ?></h2>
						<?php get_template_part( 'template-parts/components/contact-list', null, array( 'class' => 'se-contact--footer' ) ); ?>
						<?php if ( is_active_sidebar( 'footer-trust' ) ) : ?>
							<div class="se-trust"><?php dynamic_sidebar( 'footer-trust' ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="se-footer__bottom">
			<div class="se-container se-footer__bottom-row">
				<p class="se-footer__copy">
					<?php
					if ( $samadi_copyright ) {
						echo wp_kses_post( $samadi_copyright );
					} else {
						/* translators: 1: year, 2: site name. */
						echo esc_html( sprintf( __( '© %1$s %2$s. تمامی حقوق محفوظ است.', 'samadi-electric' ), samadi_number( gmdate( 'Y' ) ), get_bloginfo( 'name' ) ) );
					}
					?>
				</p>
				<a class="se-footer__top" href="#se-page">
					<span><?php esc_html_e( 'بازگشت به بالا', 'samadi-electric' ); ?></span>
					<?php samadi_icon( 'chevron-up', array( 'size' => 18 ) ); ?>
				</a>
			</div>
		</div>
	</div>
</footer>
