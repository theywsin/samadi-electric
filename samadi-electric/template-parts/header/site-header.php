<?php
/**
 * Theme header (used when no Elementor header template is assigned).
 *
 * Desktop: row 1 = logo, search, actions; row 2 = categories mega menu + primary menu.
 * Mobile: compact row with menu toggle, logo, search toggle, cart — plus full-width search.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_wholesale_url   = samadi_wholesale_url();
$samadi_wholesale_label = trim( (string) samadi_option( 'header_wholesale_label' ) );
$samadi_wishlist_url    = samadi_wishlist_url();
?>
<header id="se-header" class="se-header" data-se-header>
	<div class="se-header__main">
		<div class="se-container se-header__row">
			<button type="button" class="se-icon-btn se-header__burger" data-se-drawer-open aria-controls="se-drawer" aria-expanded="false">
				<?php samadi_icon( 'menu' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'باز کردن منو', 'samadi-electric' ); ?></span>
			</button>

			<?php samadi_site_logo( 'header' ); ?>

			<?php if ( samadi_option( 'header_show_search' ) ) : ?>
				<div class="se-header__search" data-se-live-search>
					<?php get_search_form(); ?>
				</div>
			<?php endif; ?>

			<div class="se-header__actions">
				<?php if ( $samadi_wholesale_url && $samadi_wholesale_label ) : ?>
					<a class="se-btn se-btn--primary se-header__wholesale" href="<?php echo esc_url( $samadi_wholesale_url ); ?>"><?php echo esc_html( $samadi_wholesale_label ); ?></a>
				<?php endif; ?>

				<?php if ( $samadi_wishlist_url ) : ?>
					<a class="se-icon-btn se-header__wishlist" href="<?php echo esc_url( $samadi_wishlist_url ); ?>">
						<?php samadi_icon( 'heart' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'علاقه‌مندی‌ها', 'samadi-electric' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( samadi_is_wc() && samadi_option( 'header_show_account' ) ) : ?>
					<a class="se-icon-btn se-header__account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<?php samadi_icon( 'user' ); ?>
						<span class="screen-reader-text"><?php echo is_user_logged_in() ? esc_html__( 'حساب کاربری', 'samadi-electric' ) : esc_html__( 'ورود / ثبت‌نام', 'samadi-electric' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( samadi_is_wc() ) : ?>
					<a class="se-icon-btn se-header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<?php samadi_icon( 'shopping-cart' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'سبد خرید', 'samadi-electric' ); ?></span>
						<?php echo samadi_wc_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in function. ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="se-header__nav">
		<div class="se-container se-header__nav-row">
			<?php
			if ( samadi_is_wc() && samadi_option( 'header_show_categories' ) ) {
				samadi_mega_menu();
			}
			?>
			<nav class="se-primary-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'samadi-electric' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'se-menu',
						'depth'          => 3,
						'fallback_cb'    => 'samadi_primary_menu_fallback',
					)
				);
				?>
			</nav>
			<?php
			$samadi_phone = (string) samadi_option( 'contact_phone' );
			if ( $samadi_phone ) :
				?>
				<a class="se-header__phone" href="tel:<?php echo esc_attr( samadi_tel( $samadi_phone ) ); ?>">
					<?php samadi_icon( 'phone', array( 'size' => 18 ) ); ?>
					<span dir="ltr"><?php echo esc_html( $samadi_phone ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>
