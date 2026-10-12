<?php
/**
 * WooCommerce integration: wrappers, loop, product card, single product, cart/checkout.
 *
 * Everything relies on WooCommerce's own templates, hooks and data. The theme only re-arranges
 * output and adds presentation; prices, stock, cart totals and validation stay with WooCommerce.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Wrappers & breadcrumb
 * ---------------------------------------------------------------------- */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Open the theme content wrapper.
 */
function samadi_wc_wrapper_start() {
	$classes = array( 'se-main', 'se-shop-main' );
	if ( is_product() ) {
		$classes[] = 'se-single-product';
	}
	echo '<main id="se-main" class="' . esc_attr( implode( ' ', $classes ) ) . '"><div class="se-container">';

	// Product archives print the breadcrumb inside their header card (archive-product.php).
	if ( ! is_shop() && ! is_product_taxonomy() && ! is_search() ) {
		samadi_breadcrumb();
	}
}
add_action( 'woocommerce_before_main_content', 'samadi_wc_wrapper_start', 10 );

/**
 * Close the theme content wrapper.
 */
function samadi_wc_wrapper_end() {
	echo '</div></main>';
}
add_action( 'woocommerce_after_main_content', 'samadi_wc_wrapper_end', 10 );

/**
 * Use the theme breadcrumb markup for WooCommerce's breadcrumb (keeps WC's BreadcrumbList schema).
 *
 * @param array $defaults Defaults.
 * @return array
 */
function samadi_wc_breadcrumb_defaults( $defaults ) {
	return array_merge( $defaults, samadi_wc_breadcrumb_args() );
}
add_filter( 'woocommerce_breadcrumb_defaults', 'samadi_wc_breadcrumb_defaults' );

/* -------------------------------------------------------------------------
 * Shop loop settings
 * ---------------------------------------------------------------------- */

/**
 * Products per page from theme settings.
 *
 * @return int
 */
function samadi_wc_per_page() {
	return max( 4, absint( samadi_option( 'shop_per_page' ) ) );
}
add_filter( 'loop_shop_per_page', 'samadi_wc_per_page', 20 );

/**
 * Loop columns from theme settings.
 *
 * @return int
 */
function samadi_wc_columns() {
	return max( 2, min( 5, absint( samadi_option( 'shop_columns' ) ) ) );
}
add_filter( 'loop_shop_columns', 'samadi_wc_columns' );

/**
 * Related products: 4 items in 4 columns.
 *
 * @param array $args Args.
 * @return array
 */
function samadi_wc_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'samadi_wc_related_args' );
add_filter( 'woocommerce_upsell_display_args', 'samadi_wc_related_args' );

/**
 * Gallery thumbnails per row.
 *
 * @return int
 */
function samadi_wc_thumbnail_columns() {
	return 5;
}
add_filter( 'woocommerce_product_thumbnails_columns', 'samadi_wc_thumbnail_columns' );

/* -------------------------------------------------------------------------
 * Product card (see woocommerce/content-product.php)
 *
 * WooCommerce's default loop callbacks are removed from the loop hooks because the theme card
 * renders image, title, price and the add-to-cart control itself. The hooks themselves still
 * fire inside the card so third-party extensions keep working.
 * ---------------------------------------------------------------------- */

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

/**
 * Discount percentage for a product on sale (max across variations), or 0.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function samadi_wc_discount_percent( $product ) {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}

	$max = 0;

	if ( $product->is_type( 'variable' ) ) {
		$prices = $product->get_variation_prices( true );
		foreach ( $prices['regular_price'] as $id => $regular ) {
			$sale = isset( $prices['sale_price'][ $id ] ) ? (float) $prices['sale_price'][ $id ] : 0;
			if ( (float) $regular > 0 && $sale < (float) $regular ) {
				$max = max( $max, (int) round( 100 - ( $sale / (float) $regular * 100 ) ) );
			}
		}
	} elseif ( $product->is_type( array( 'simple', 'external' ) ) ) {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $regular > 0 && $sale < $regular ) {
			$max = (int) round( 100 - ( $sale / $regular * 100 ) );
		}
	}

	return max( 0, $max );
}

/**
 * Sale badge markup.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function samadi_wc_sale_badge( $product ) {
	if ( ! $product->is_on_sale() ) {
		return '';
	}

	$percent = samadi_wc_discount_percent( $product );
	$label   = $percent
		/* translators: %s: discount percent. */
		? sprintf( __( '%s٪ تخفیف', 'samadi-electric' ), samadi_number( $percent ) )
		: __( 'فروش ویژه', 'samadi-electric' );

	return '<span class="se-badge se-badge--sale onsale">' . esc_html( $label ) . '</span>';
}

/**
 * Single product sale flash uses the same badge.
 *
 * @param string     $html    Default HTML.
 * @param WP_Post    $post    Post.
 * @param WC_Product $product Product.
 * @return string
 */
function samadi_wc_sale_flash( $html, $post, $product ) {
	return samadi_wc_sale_badge( $product );
}
add_filter( 'woocommerce_sale_flash', 'samadi_wc_sale_flash', 10, 3 );

/**
 * Loop add-to-cart button: icon button with accessible text, behavior unchanged (WooCommerce decides
 * between AJAX add, "select options", "view products" and external links per product type/stock).
 *
 * @param string     $html    Link HTML.
 * @param WC_Product $product Product.
 * @param array      $args    Args.
 * @return string
 */
function samadi_wc_loop_add_to_cart_link( $html, $product, $args = array() ) {
	$text  = $product->add_to_cart_text();
	$icon  = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ? 'shopping-cart' : 'chevron-left';
	$label = isset( $args['attributes']['aria-label'] ) ? $args['attributes']['aria-label'] : $text;

	$attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();
	$attributes['aria-label'] = $label;

	return sprintf(
		'<a href="%1$s" data-quantity="%2$s" class="%3$s se-pcard__cart" %4$s>%5$s<span class="se-pcard__cart-text">%6$s</span></a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
		wc_implode_html_attributes( $attributes ),
		samadi_get_icon( $icon, array( 'size' => 20 ) ),
		esc_html( $text )
	);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'samadi_wc_loop_add_to_cart_link', 10, 3 );

/**
 * Card stock label.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function samadi_wc_card_stock( $product ) {
	if ( ! samadi_option( 'card_show_stock' ) ) {
		return '';
	}

	if ( ! $product->is_in_stock() ) {
		return '<span class="se-stock se-stock--out">' . esc_html__( 'ناموجود', 'samadi-electric' ) . '</span>';
	}
	if ( $product->is_on_backorder() ) {
		return '<span class="se-stock se-stock--backorder">' . esc_html__( 'پیش‌سفارش', 'samadi-electric' ) . '</span>';
	}
	if ( $product->managing_stock() ) {
		$qty = (int) $product->get_stock_quantity();
		$low = (int) wc_get_low_stock_amount( $product );
		if ( $qty > 0 && $qty <= $low ) {
			/* translators: %s: stock quantity. */
			return '<span class="se-stock se-stock--low">' . esc_html( sprintf( __( 'تنها %s عدد باقی مانده', 'samadi-electric' ), samadi_number( $qty ) ) ) . '</span>';
		}
	}

	// Plain "in stock" is the normal state and is not labelled on cards (keeps cards compact, as in the design).
	return '';
}

/* -------------------------------------------------------------------------
 * Prices
 * ---------------------------------------------------------------------- */

/**
 * Persian digits in formatted prices (text nodes only).
 *
 * @param string $html Price HTML.
 * @return string
 */
function samadi_wc_price_digits( $html ) {
	return samadi_option( 'persian_digits' ) ? samadi_fa_digits( $html ) : $html;
}
add_filter( 'wc_price', 'samadi_wc_price_digits', 20 );

/* -------------------------------------------------------------------------
 * Cart count fragment
 * ---------------------------------------------------------------------- */

/**
 * Header cart count markup.
 *
 * @return string
 */
function samadi_wc_cart_count_html() {
	$count = ( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

	return sprintf(
		'<span class="se-cart-count%1$s" data-count="%2$d">%3$s</span>',
		$count ? '' : ' is-empty',
		absint( $count ),
		esc_html( samadi_number( $count ) )
	);
}

/**
 * Refresh header/bottom-nav cart counts after AJAX cart changes.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function samadi_wc_cart_fragments( $fragments ) {
	$fragments['span.se-cart-count'] = samadi_wc_cart_count_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'samadi_wc_cart_fragments' );

/* -------------------------------------------------------------------------
 * Single product
 * ---------------------------------------------------------------------- */

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/**
 * Brand + SKU line under the product title.
 */
function samadi_wc_single_meta_line() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$items = array();

	if ( samadi_option( 'product_show_brand' ) ) {
		$brand = samadi_product_brand( $product->get_id() );
		if ( $brand ) {
			$items[] = sprintf(
				'<span class="se-meta-line__item">%s <a href="%s">%s</a></span>',
				esc_html__( 'برند:', 'samadi-electric' ),
				esc_url( get_term_link( $brand ) ),
				esc_html( $brand->name )
			);
		}
	}

	if ( samadi_option( 'product_show_sku' ) && wc_product_sku_enabled() && $product->get_sku() ) {
		$items[] = sprintf(
			'<span class="se-meta-line__item">%s <span class="sku" dir="ltr">%s</span></span>',
			esc_html__( 'کد کالا:', 'samadi-electric' ),
			esc_html( $product->get_sku() )
		);
	}

	if ( $items ) {
		echo '<div class="se-meta-line product_meta">' . implode( '', $items ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
	}
}
add_action( 'woocommerce_single_product_summary', 'samadi_wc_single_meta_line', 6 );

/**
 * Store benefits under the add-to-cart form.
 */
function samadi_wc_single_benefits() {
	if ( ! samadi_option( 'product_benefits' ) ) {
		return;
	}
	$items = array_slice( samadi_benefit_items(), 0, 4 );
	if ( ! $items ) {
		return;
	}
	echo '<ul class="se-mini-benefits">';
	foreach ( $items as $item ) {
		printf(
			'<li class="se-mini-benefits__item">%s<span>%s</span></li>',
			samadi_get_icon( $item['icon'], array( 'size' => 22 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item['title'] )
		);
	}
	echo '</ul>';
}
add_action( 'woocommerce_single_product_summary', 'samadi_wc_single_benefits', 45 );

/**
 * Product tabs: "Additional information" becomes "Technical specifications" and comes first.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function samadi_wc_product_tabs( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title']    = __( 'مشخصات فنی', 'samadi-electric' );
		$tabs['additional_information']['priority'] = 5;
	}
	if ( isset( $tabs['description'] ) ) {
		$tabs['description']['title'] = __( 'توضیحات', 'samadi-electric' );
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'samadi_wc_product_tabs', 20 );

/**
 * Remove the duplicate H2 inside the specifications/description tab panels (the tab is the heading).
 */
add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string' );
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );

/**
 * Add brand and SKU rows to the technical specifications table (from real product data).
 *
 * @param array      $attributes Display attributes.
 * @param WC_Product $product    Product.
 * @return array
 */
function samadi_wc_display_attributes( $attributes, $product ) {
	$extra = array();

	$brand = samadi_product_brand( $product->get_id() );
	if ( $brand ) {
		$extra['se_brand'] = array(
			'label' => __( 'برند', 'samadi-electric' ),
			'value' => '<a href="' . esc_url( get_term_link( $brand ) ) . '">' . esc_html( $brand->name ) . '</a>',
		);
	}

	if ( wc_product_sku_enabled() && $product->get_sku() ) {
		$extra['se_sku'] = array(
			'label' => __( 'کد کالا', 'samadi-electric' ),
			'value' => '<span dir="ltr">' . esc_html( $product->get_sku() ) . '</span>',
		);
	}

	return $extra + $attributes;
}
add_filter( 'woocommerce_display_product_attributes', 'samadi_wc_display_attributes', 10, 2 );

/**
 * Show the "additional information" tab when brand/SKU exist even without attributes.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function samadi_wc_force_specs_tab( $tabs ) {
	global $product;

	if ( ! isset( $tabs['additional_information'] ) && $product instanceof WC_Product && ( samadi_product_brand( $product->get_id() ) || ( wc_product_sku_enabled() && $product->get_sku() ) ) ) {
		$tabs['additional_information'] = array(
			'title'    => __( 'مشخصات فنی', 'samadi-electric' ),
			'priority' => 5,
			'callback' => 'woocommerce_product_additional_information_tab',
		);
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'samadi_wc_force_specs_tab', 15 );

/**
 * Expose swatch colors (attribute term meta `se_color`, editable with the companion plugin) to the
 * swatch script. The <select> itself stays the source of truth for WooCommerce's variation form.
 *
 * @param string $html Select HTML.
 * @param array  $args Args.
 * @return string
 */
function samadi_wc_variation_select_colors( $html, $args ) {
	$taxonomy = isset( $args['attribute'] ) ? $args['attribute'] : '';

	if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) || empty( $args['product'] ) ) {
		return $html;
	}

	$terms  = wc_get_product_terms( $args['product']->get_id(), $taxonomy, array( 'fields' => 'all' ) );
	$colors = array();

	foreach ( $terms as $term ) {
		$hex = sanitize_hex_color( (string) get_term_meta( $term->term_id, 'se_color', true ) );
		if ( $hex ) {
			$colors[ $term->slug ] = $hex;
		}
	}

	if ( ! $colors ) {
		return $html;
	}

	return preg_replace( '/<select /', '<select data-se-colors="' . esc_attr( wp_json_encode( $colors ) ) . '" ', $html, 1 );
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'samadi_wc_variation_select_colors', 10, 2 );

/**
 * Mobile sticky buy bar on single product pages (drives the real add-to-cart form).
 */
function samadi_wc_sticky_buy_bar() {
	if ( ! is_product() || ! samadi_option( 'mobile_sticky_cart' ) ) {
		return;
	}

	$product = wc_get_product( get_queried_object_id() );

	if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() || $product->is_type( 'external' ) ) {
		return;
	}
	?>
	<div class="se-buybar" data-se-buybar hidden>
		<div class="se-buybar__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<button type="button" class="se-btn se-btn--primary se-buybar__btn" data-se-buybar-btn>
			<?php samadi_icon( 'shopping-cart', array( 'size' => 20 ) ); ?>
			<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
		</button>
	</div>
	<?php
}
add_action( 'wp_footer', 'samadi_wc_sticky_buy_bar', 6 );

/* -------------------------------------------------------------------------
 * Cart & checkout
 * ---------------------------------------------------------------------- */

/**
 * Purchase progress indicator (cart → details → payment/confirmation).
 */
function samadi_wc_checkout_steps() {
	if ( is_wc_endpoint_url( 'order-pay' ) ) {
		$current = 2;
	} elseif ( is_wc_endpoint_url( 'order-received' ) ) {
		$current = 3;
	} elseif ( is_checkout() ) {
		$current = 2;
	} elseif ( is_cart() ) {
		$current = 1;
	} else {
		return;
	}

	$steps = array(
		1 => array( __( 'سبد خرید', 'samadi-electric' ), 'shopping-cart' ),
		2 => array( __( 'اطلاعات و پرداخت', 'samadi-electric' ), 'file-text' ),
		3 => array( __( 'ثبت سفارش', 'samadi-electric' ), 'check' ),
	);

	echo '<ol class="se-steps" aria-label="' . esc_attr__( 'مراحل خرید', 'samadi-electric' ) . '">';
	foreach ( $steps as $number => $step ) {
		$state = $number < $current ? 'done' : ( $number === $current ? 'current' : 'todo' );
		printf(
			'<li class="se-steps__item is-%1$s"%2$s><span class="se-steps__icon">%3$s</span><span class="se-steps__label">%4$s</span></li>',
			esc_attr( $state ),
			'current' === $state ? ' aria-current="step"' : '',
			samadi_get_icon( 'done' === $state ? 'check' : $step[1], array( 'size' => 18 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $step[0] )
		);
	}
	echo '</ol>';
}

/* -------------------------------------------------------------------------
 * Archive header helpers
 * ---------------------------------------------------------------------- */

/**
 * The product archive header is rendered by woocommerce/archive-product.php; remove WC's default
 * header callback to avoid a duplicate title.
 */
remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );

/**
 * Remove result count + ordering from the generic hook: the archive toolbar prints them.
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Whether the shop filter panel has content.
 *
 * @return bool
 */
function samadi_wc_has_filters() {
	return samadi_option( 'shop_sidebar' ) && is_active_sidebar( 'sidebar-shop' );
}

/**
 * Catalog sorting options (same rules as WooCommerce's ordering dropdown, filterable by the same hook).
 *
 * @return array<string, string>
 */
function samadi_wc_sort_options() {
	$default_orderby = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );

	$options = apply_filters(
		'woocommerce_catalog_orderby',
		array(
			'menu_order' => __( 'پیش‌فرض', 'samadi-electric' ),
			'popularity' => __( 'محبوب‌ترین (پرفروش)', 'samadi-electric' ),
			'rating'     => __( 'بیشترین امتیاز', 'samadi-electric' ),
			'date'       => __( 'جدیدترین', 'samadi-electric' ),
			'price'      => __( 'ارزان‌ترین', 'samadi-electric' ),
			'price-desc' => __( 'گران‌ترین', 'samadi-electric' ),
		)
	);

	if ( 'menu_order' !== $default_orderby ) {
		unset( $options['menu_order'] );
	}
	if ( ! wc_review_ratings_enabled() ) {
		unset( $options['rating'] );
	}

	return $options;
}

/**
 * Sort control: pill button + list of real sorting links (crawlable, works without JS, AJAX-enhanced).
 */
function samadi_wc_sort_control() {
	$options = samadi_wc_sort_options();

	if ( ! $options ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( (string) $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
	$base    = remove_query_arg( array( 'orderby', 'product-page', 'paged' ), get_pagenum_link( 1, false ) );
	?>
	<div class="se-sort" data-se-sort>
		<button type="button" class="se-pill se-sort__toggle" aria-expanded="false" aria-controls="se-sort-list">
			<?php samadi_icon( 'arrow-up-down', array( 'size' => 18 ) ); ?>
			<span><?php esc_html_e( 'مرتب‌سازی', 'samadi-electric' ); ?></span>
			<span class="screen-reader-text">: <?php echo esc_html( isset( $options[ $current ] ) ? $options[ $current ] : '' ); ?></span>
		</button>
		<ul class="se-sort__list" id="se-sort-list" hidden>
			<?php foreach ( $options as $key => $label ) : ?>
				<li>
					<a class="se-sort__link<?php echo $key === $current ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'orderby', $key, $base ) ); ?>" data-se-filter-link rel="nofollow"<?php echo $key === $current ? ' aria-current="true"' : ''; ?>>
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Product count line ("۲۳۶ محصول"), keeps WooCommerce's result-count class for scripts.
 */
function samadi_wc_result_count() {
	$total = (int) wc_get_loop_prop( 'total' );
	/* translators: %s: number of products. */
	printf( '<p class="woocommerce-result-count se-result-count" role="status">%s</p>', esc_html( sprintf( __( '%s محصول', 'samadi-electric' ), samadi_number( $total ) ) ) );
}

/**
 * Single product: gallery carousel arrows (flexslider) with Persian labels.
 *
 * @param array $options Options.
 * @return array
 */
function samadi_wc_gallery_options( $options ) {
	$options['directionNav'] = true;
	$options['prevText']     = __( 'تصویر قبلی', 'samadi-electric' );
	$options['nextText']     = __( 'تصویر بعدی', 'samadi-electric' );

	return $options;
}
add_filter( 'woocommerce_single_product_carousel_options', 'samadi_wc_gallery_options' );

/**
 * Brand tile shown at the top of the gallery thumbnail column (moved into the gallery by shop.js).
 */
function samadi_wc_gallery_brand() {
	global $product;

	if ( ! $product instanceof WC_Product || ! samadi_option( 'product_show_brand' ) ) {
		return;
	}

	$brand = samadi_product_brand( $product->get_id() );

	if ( ! $brand ) {
		return;
	}

	$logo = samadi_term_image( $brand, 'thumbnail' );

	printf(
		'<a class="se-gallery-brand" href="%1$s" title="%2$s">%3$s</a>',
		esc_url( get_term_link( $brand ) ),
		esc_attr( $brand->name ),
		$logo ? $logo : '<span>' . esc_html( $brand->name ) . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped image markup.
	);
}
add_action( 'woocommerce_before_single_product_summary', 'samadi_wc_gallery_brand', 21 );

/**
 * List/grid view preference (purely presentational; stored client side).
 */
function samadi_wc_view_toggle() {
	?>
	<div class="se-view-toggle" role="group" aria-label="<?php esc_attr_e( 'نحوه نمایش محصولات', 'samadi-electric' ); ?>">
		<button type="button" class="se-view-toggle__btn is-active" data-se-view="grid" aria-pressed="true">
			<?php samadi_icon( 'layout-grid', array( 'size' => 20 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'نمایش شبکه‌ای', 'samadi-electric' ); ?></span>
		</button>
		<button type="button" class="se-view-toggle__btn" data-se-view="list" aria-pressed="false">
			<?php samadi_icon( 'list', array( 'size' => 20 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'نمایش فهرستی', 'samadi-electric' ); ?></span>
		</button>
	</div>
	<?php
}

/**
 * End time (Unix timestamp) for the homepage offers countdown.
 *
 * Uses the Customizer value when set; otherwise the earliest real "sale ends" date among products on
 * sale. Returns 0 when there is no future end date, so no invented deadline is ever shown.
 *
 * @return int
 */
function samadi_sale_countdown_end() {
	$option = (string) samadi_option( 'home_sale_ends' );

	if ( $option ) {
		$date = date_create_immutable( $option, wp_timezone() );
		return $date ? $date->getTimestamp() : 0;
	}

	if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
		return 0;
	}

	$cached = get_transient( 'samadi_sale_countdown_end' );
	if ( false !== $cached ) {
		return (int) $cached > time() ? (int) $cached : 0;
	}

	$end = 0;
	foreach ( array_slice( wc_get_product_ids_on_sale(), 0, 100 ) as $id ) {
		$product = wc_get_product( $id );
		$to      = $product ? $product->get_date_on_sale_to() : null;
		if ( $to && $to->getTimestamp() > time() && ( ! $end || $to->getTimestamp() < $end ) ) {
			$end = $to->getTimestamp();
		}
	}

	set_transient( 'samadi_sale_countdown_end', $end, 10 * MINUTE_IN_SECONDS );

	return $end;
}

/**
 * Countdown text "HH : MM : SS" (hours may exceed 24) for server-side rendering before JS ticks.
 *
 * @param int $end End timestamp.
 * @return string
 */
function samadi_countdown_text( $end ) {
	$left = max( 0, (int) $end - time() );
	$text = sprintf( '%02d : %02d : %02d', floor( $left / 3600 ), floor( $left / 60 ) % 60, $left % 60 );

	return samadi_option( 'persian_digits' ) ? samadi_fa_digits( $text ) : $text;
}

/**
 * Recalculate the offers countdown when products change.
 */
function samadi_flush_sale_countdown() {
	delete_transient( 'samadi_sale_countdown_end' );
}
add_action( 'woocommerce_update_product', 'samadi_flush_sale_countdown' );
add_action( 'woocommerce_new_product', 'samadi_flush_sale_countdown' );
add_action( 'woocommerce_scheduled_sales', 'samadi_flush_sale_countdown' );
