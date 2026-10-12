<?php
/**
 * Theme footer (used when no Elementor footer template is assigned).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_about     = (string) samadi_option( 'footer_about' );
$samadi_social    = samadi_social_links();
$samadi_copyright = (string) samadi_option( 'footer_copyright' );
?>
<footer class="se-footer" id="se-footer">
	<?php if ( samadi_option( 'benefits_show' ) && ! is_front_page() ) : ?>
		<div class="se-container">
			<?php get_template_part( 'template-parts/components/benefits' ); ?>
		</div>
	<?php endif; ?>

	<div class="se-footer__main">
		<div class="se-container se-footer__grid">
			<div class="se-footer__col se-footer__col--about">
				<?php samadi_site_logo( 'footer' ); ?>
				<?php if ( $samadi_about ) : ?>
					<div class="se-footer__about"><?php echo wp_kses_post( wpautop( $samadi_about ) ); ?></div>
				<?php endif; ?>
				<?php if ( $samadi_social ) : ?>
					<ul class="se-social">
						<?php foreach ( $samadi_social as $samadi_network ) : ?>
							<li>
								<a class="se-social__link" href="<?php echo esc_url( $samadi_network['url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php samadi_icon( $samadi_network['icon'], array( 'size' => 20 ) ); ?>
									<span class="screen-reader-text"><?php echo esc_html( $samadi_network['label'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="se-footer__col">
				<h2 class="se-footer__title"><?php esc_html_e( 'دسته‌بندی‌های اصلی', 'samadi-electric' ); ?></h2>
				<?php
				if ( has_nav_menu( 'footer_shop' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer_shop',
							'container'      => false,
							'menu_class'     => 'se-footer__links',
							'depth'          => 1,
						)
					);
				} elseif ( samadi_is_wc() ) {
					echo '<ul class="se-footer__links">';
					foreach ( array_slice( samadi_mega_menu_categories(), 0, 6 ) as $samadi_cat ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $samadi_cat['term'] ) ), esc_html( $samadi_cat['term']->name ) );
					}
					echo '</ul>';
				}
				?>
			</div>

			<?php if ( has_nav_menu( 'footer_help' ) ) : ?>
				<div class="se-footer__col">
					<h2 class="se-footer__title"><?php echo esc_html( wp_get_nav_menu_name( 'footer_help' ) ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_help',
							'container'      => false,
							'menu_class'     => 'se-footer__links',
							'depth'          => 1,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="se-footer__col">
				<h2 class="se-footer__title"><?php esc_html_e( 'ارتباط با ما', 'samadi-electric' ); ?></h2>
				<?php get_template_part( 'template-parts/components/contact-list' ); ?>
				<?php if ( is_active_sidebar( 'footer-trust' ) ) : ?>
					<div class="se-trust"><?php dynamic_sidebar( 'footer-trust' ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="se-footer__bottom">
		<div class="se-container">
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
		</div>
	</div>
</footer>
