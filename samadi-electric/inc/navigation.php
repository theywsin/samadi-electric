<?php
/**
 * Menus: submenu toggles, product category mega menu, mobile drawer.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add an accessible toggle button to menu items that have children.
 *
 * @param string   $item_output Item HTML.
 * @param WP_Post  $item        Menu item.
 * @param int      $depth       Depth.
 * @param stdClass $args        Menu args.
 * @return string
 */
function samadi_menu_toggle_button( $item_output, $item, $depth, $args ) {
	if ( empty( $args->theme_location ) || ! in_array( $args->theme_location, array( 'primary', 'mobile' ), true ) ) {
		return $item_output;
	}

	if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$item_output .= sprintf(
			'<button type="button" class="se-submenu-toggle" aria-expanded="false" aria-label="%s">%s</button>',
			/* translators: %s: menu item title. */
			esc_attr( sprintf( __( 'زیرمنوی %s', 'samadi-electric' ), wp_strip_all_tags( $item->title ) ) ),
			samadi_get_icon( 'chevron-down', array( 'size' => 16 ) )
		);
	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'samadi_menu_toggle_button', 10, 4 );

/**
 * Product categories used by the mega menu (top level + children).
 *
 * @return array<int, array{term:WP_Term, children:WP_Term[]}>
 */
function samadi_mega_menu_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$exclude = (int) get_option( 'default_product_cat' );
	$parents = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => $exclude ? array( $exclude ) : array(),
			'number'     => (int) apply_filters( 'samadi_mega_menu_limit', 12 ),
			'orderby'    => 'menu_order',
		)
	);

	if ( is_wp_error( $parents ) || ! $parents ) {
		return array();
	}

	$out = array();
	foreach ( $parents as $parent ) {
		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $parent->term_id,
				'hide_empty' => true,
				'number'     => 8,
				'orderby'    => 'menu_order',
			)
		);
		$out[] = array(
			'term'     => $parent,
			'children' => is_wp_error( $children ) ? array() : $children,
		);
	}

	return $out;
}

/**
 * Category thumbnail image tag (WooCommerce term meta), or empty string.
 *
 * @param WP_Term $term Term.
 * @param string  $size Image size.
 * @return string
 */
function samadi_term_image( $term, $size = 'thumbnail' ) {
	$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	if ( ! $thumb_id ) {
		return '';
	}

	return wp_get_attachment_image(
		$thumb_id,
		$size,
		false,
		array(
			'alt'     => $term->name,
			'loading' => 'lazy',
		)
	);
}

/**
 * Render the product categories mega menu.
 */
function samadi_mega_menu() {
	$cats = samadi_mega_menu_categories();

	if ( ! $cats ) {
		return;
	}
	?>
	<div class="se-mega" data-se-mega>
		<button type="button" class="se-mega__toggle" aria-expanded="false" aria-controls="se-mega-panel">
			<?php samadi_icon( 'menu', array( 'size' => 20 ) ); ?>
			<span><?php echo esc_html( samadi_option( 'header_categories_label' ) ); ?></span>
		</button>
		<div class="se-mega__panel" id="se-mega-panel" hidden>
			<ul class="se-mega__list">
				<?php foreach ( $cats as $index => $cat ) : ?>
					<li class="se-mega__item">
						<a class="se-mega__parent" href="<?php echo esc_url( get_term_link( $cat['term'] ) ); ?>">
							<span class="se-mega__thumb"><?php echo samadi_term_image( $cat['term'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="se-mega__name"><?php echo esc_html( $cat['term']->name ); ?></span>
						</a>
						<?php if ( $cat['children'] ) : ?>
							<ul class="se-mega__children">
								<?php foreach ( $cat['children'] as $child ) : ?>
									<li><a href="<?php echo esc_url( get_term_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( samadi_is_wc() ) : ?>
				<a class="se-mega__all" href="<?php echo esc_url( samadi_shop_url() ); ?>"><?php esc_html_e( 'مشاهده همه محصولات', 'samadi-electric' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Fallback for the primary menu when none is assigned: link to shop + top categories.
 */
function samadi_primary_menu_fallback() {
	if ( ! current_user_can( 'edit_theme_options' ) && ! samadi_is_wc() ) {
		return;
	}

	echo '<ul class="se-menu">';
	if ( samadi_is_wc() ) {
		foreach ( array_slice( samadi_mega_menu_categories(), 0, 6 ) as $cat ) {
			printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( get_term_link( $cat['term'] ) ), esc_html( $cat['term']->name ) );
		}
	}
	if ( current_user_can( 'edit_theme_options' ) ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( admin_url( 'nav-menus.php' ) ), esc_html__( 'ساخت منوی اصلی', 'samadi-electric' ) );
	}
	echo '</ul>';
}
