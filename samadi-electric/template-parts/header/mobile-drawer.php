<?php
/**
 * Off-canvas mobile navigation drawer.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_location = has_nav_menu( 'mobile' ) ? 'mobile' : 'primary';
$samadi_cats     = samadi_is_wc() ? samadi_mega_menu_categories() : array();
?>
<div class="se-drawer" id="se-drawer" data-se-drawer hidden>
	<div class="se-drawer__overlay" data-se-drawer-close></div>
	<div class="se-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="se-drawer-title" tabindex="-1">
		<div class="se-drawer__head">
			<p class="se-drawer__title" id="se-drawer-title"><?php esc_html_e( 'منو', 'samadi-electric' ); ?></p>
			<button type="button" class="se-icon-btn" data-se-drawer-close>
				<?php samadi_icon( 'x' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'بستن منو', 'samadi-electric' ); ?></span>
			</button>
		</div>

		<div class="se-drawer__body">
			<?php if ( $samadi_cats ) : ?>
				<p class="se-drawer__label"><?php echo esc_html( samadi_option( 'header_categories_label' ) ); ?></p>
				<ul class="se-drawer__cats se-menu se-menu--vertical">
					<?php foreach ( $samadi_cats as $samadi_cat ) : ?>
						<li class="menu-item<?php echo $samadi_cat['children'] ? ' menu-item-has-children' : ''; ?>">
							<a href="<?php echo esc_url( get_term_link( $samadi_cat['term'] ) ); ?>"><?php echo esc_html( $samadi_cat['term']->name ); ?></a>
							<?php if ( $samadi_cat['children'] ) : ?>
								<button type="button" class="se-submenu-toggle" aria-expanded="false">
									<?php samadi_icon( 'chevron-down', array( 'size' => 16 ) ); ?>
									<span class="screen-reader-text">
										<?php
										/* translators: %s: category name. */
										echo esc_html( sprintf( __( 'زیرمجموعه‌های %s', 'samadi-electric' ), $samadi_cat['term']->name ) );
										?>
									</span>
								</button>
								<ul class="sub-menu">
									<?php foreach ( $samadi_cat['children'] as $samadi_child ) : ?>
										<li class="menu-item"><a href="<?php echo esc_url( get_term_link( $samadi_child ) ); ?>"><?php echo esc_html( $samadi_child->name ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( has_nav_menu( $samadi_location ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'منوی موبایل', 'samadi-electric' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $samadi_location,
							'container'      => false,
							'menu_class'     => 'se-menu se-menu--vertical',
							'depth'          => 3,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php
			$samadi_drawer_wholesale = samadi_wholesale_url();
			$samadi_drawer_label     = trim( (string) samadi_option( 'header_wholesale_label' ) );
			if ( $samadi_drawer_wholesale && $samadi_drawer_label ) :
				?>
				<a class="se-btn se-btn--primary se-btn--block se-drawer__wholesale" href="<?php echo esc_url( $samadi_drawer_wholesale ); ?>"><?php echo esc_html( $samadi_drawer_label ); ?></a>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/components/contact-list', null, array( 'class' => 'se-drawer__contact' ) ); ?>
		</div>
	</div>
</div>
