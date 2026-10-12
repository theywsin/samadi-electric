<?php
/**
 * Theme footer (used when no Elementor footer template is assigned).
 *
 * Columns (right → left): store information, quick links, customer service, trust seals.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_about       = (string) samadi_option( 'footer_about' );
$samadi_social      = samadi_social_links();
$samadi_copyright   = (string) samadi_option( 'footer_copyright' );
$samadi_phone       = (string) samadi_option( 'contact_phone' );
$samadi_hours       = (string) samadi_option( 'contact_hours' );
$samadi_address     = (string) samadi_option( 'contact_address' );
$samadi_email       = (string) samadi_option( 'contact_email' );
$samadi_trust_title = (string) samadi_option( 'footer_trust_title' );
$samadi_trust_text  = (string) samadi_option( 'footer_trust_text' );
?>
<footer class="se-footer" id="se-footer">
	<div class="se-footer__main">
		<div class="se-container se-footer__grid">
			<div class="se-footer__col se-footer__col--about">
				<?php samadi_site_logo( 'footer' ); ?>
				<?php if ( $samadi_about ) : ?>
					<div class="se-footer__about"><?php echo wp_kses_post( wpautop( $samadi_about ) ); ?></div>
				<?php endif; ?>
				<?php if ( $samadi_address ) : ?>
					<address class="se-footer__address"><?php echo nl2br( esc_html( $samadi_address ) ); ?></address>
				<?php endif; ?>
				<?php if ( $samadi_phone || $samadi_hours ) : ?>
					<p class="se-footer__hotline">
						<?php if ( $samadi_phone ) : ?>
							<a href="tel:<?php echo esc_attr( samadi_tel( $samadi_phone ) ); ?>" dir="ltr"><?php echo esc_html( $samadi_phone ); ?></a>
						<?php endif; ?>
						<?php if ( $samadi_phone && $samadi_hours ) : ?>
							<span class="se-footer__sep" aria-hidden="true">|</span>
						<?php endif; ?>
						<?php if ( $samadi_hours ) : ?>
							<span><?php echo esc_html( $samadi_hours ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
				<?php if ( $samadi_email ) : ?>
					<p class="se-footer__email"><a href="mailto:<?php echo esc_attr( antispambot( $samadi_email ) ); ?>"><?php echo esc_html( antispambot( $samadi_email ) ); ?></a></p>
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
				<h2 class="se-footer__title"><?php echo esc_html( has_nav_menu( 'footer_shop' ) ? wp_get_nav_menu_name( 'footer_shop' ) : __( 'دسترسی سریع', 'samadi-electric' ) ); ?></h2>
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
					foreach ( array_slice( samadi_mega_menu_categories(), 0, 5 ) as $samadi_cat ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $samadi_cat['term'] ) ), esc_html( $samadi_cat['term']->name ) );
					}
					echo '</ul>';
				}
				?>
			</div>

			<div class="se-footer__col">
				<h2 class="se-footer__title"><?php echo esc_html( has_nav_menu( 'footer_help' ) ? wp_get_nav_menu_name( 'footer_help' ) : __( 'خدمات مشتریان', 'samadi-electric' ) ); ?></h2>
				<?php
				if ( has_nav_menu( 'footer_help' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer_help',
							'container'      => false,
							'menu_class'     => 'se-footer__links',
							'depth'          => 1,
						)
					);
				} elseif ( samadi_is_wc() ) {
					echo '<ul class="se-footer__links">';
					printf( '<li><a href="%s">%s</a></li>', esc_url( wc_get_page_permalink( 'myaccount' ) ), esc_html__( 'پیگیری سفارش', 'samadi-electric' ) );
					printf( '<li><a href="%s">%s</a></li>', esc_url( wc_get_cart_url() ), esc_html__( 'سبد خرید', 'samadi-electric' ) );
					$samadi_terms_page = wc_terms_and_conditions_page_id();
					if ( $samadi_terms_page ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $samadi_terms_page ) ), esc_html( get_the_title( $samadi_terms_page ) ) );
					}
					$samadi_privacy = (int) get_option( 'wp_page_for_privacy_policy' );
					if ( $samadi_privacy && 'publish' === get_post_status( $samadi_privacy ) ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $samadi_privacy ) ), esc_html( get_the_title( $samadi_privacy ) ) );
					}
					echo '</ul>';
				}
				?>
			</div>

			<div class="se-footer__col se-footer__col--trust">
				<?php if ( $samadi_trust_title ) : ?>
					<h2 class="se-footer__title"><?php echo esc_html( $samadi_trust_title ); ?></h2>
				<?php endif; ?>
				<div class="se-trust">
					<?php if ( is_active_sidebar( 'footer-trust' ) ) : ?>
						<?php dynamic_sidebar( 'footer-trust' ); ?>
					<?php else : ?>
						<span class="se-trust__seal"><?php samadi_icon( 'shield-check', array( 'size' => 38 ) ); ?></span>
						<span class="se-trust__seal"><?php samadi_icon( 'badge-check', array( 'size' => 38 ) ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $samadi_trust_text ) : ?>
					<p class="se-footer__trust-text"><?php echo esc_html( $samadi_trust_text ); ?></p>
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
					/* translators: 1: site name, 2: year. */
					echo esc_html( sprintf( __( 'تمامی حقوق این وب‌سایت متعلق به %1$s است. © %2$s', 'samadi-electric' ), get_bloginfo( 'name' ), ( samadi_option( 'persian_digits' ) ? samadi_fa_digits( samadi_local_year() ) : samadi_local_year() ) ) );
				}
				?>
			</p>
		</div>
	</div>
</footer>
