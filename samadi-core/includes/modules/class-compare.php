<?php
/**
 * Product comparison (مقایسه محصولات).
 *
 * The list lives in a cookie managed by the front-end script, so it works on cached pages and
 * needs no server round-trip; the [samadi_compare] page reads the same cookie.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Compare module.
 */
final class Samadi_Core_Compare {

	/**
	 * Cookie name.
	 */
	const COOKIE = 'samadi_compare';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'samadi_wishlist_button', array( __CLASS__, 'append_to_card_slot' ), 20, 2 );
		add_filter( 'samadi_wishlist_provider', array( __CLASS__, 'keep_card_slot' ), 30 );
		add_filter( 'samadi_core_card_buttons', array( __CLASS__, 'card_button' ), 20, 2 );
		add_filter( 'samadi_core_single_buttons', array( __CLASS__, 'single_button' ), 20, 2 );
		add_filter( 'samadi_core_script_data', array( __CLASS__, 'script_data' ) );

		add_shortcode( 'samadi_compare', array( __CLASS__, 'shortcode' ) );

		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
		add_action( 'wp_footer', array( __CLASS__, 'bar' ), 5 );
	}

	/**
	 * Maximum number of products.
	 *
	 * @return int
	 */
	public static function max() {
		return min( 6, max( 2, (int) samadi_core_setting( 'compare_max' ) ) );
	}

	/**
	 * Product IDs chosen by the visitor.
	 *
	 * @return int[]
	 */
	public static function ids() {
		return samadi_core_cookie_ids( self::COOKIE, self::max() );
	}

	/**
	 * Compare page URL.
	 *
	 * @return string
	 */
	public static function url() {
		$page = (int) samadi_core_setting( 'compare_page' );

		return $page && 'publish' === get_post_status( $page ) ? (string) get_permalink( $page ) : '';
	}

	/**
	 * Whether the current request is the compare page.
	 *
	 * @return bool
	 */
	private static function is_compare_page() {
		$page = (int) samadi_core_setting( 'compare_page' );

		return $page && is_page( $page );
	}

	/**
	 * The theme renders its card action slot only when a wishlist provider exists. With the wishlist
	 * module off, compare still needs that slot.
	 *
	 * @param string $provider Provider.
	 * @return string
	 */
	public static function keep_card_slot( $provider ) {
		return $provider ? $provider : ( samadi_core_setting( 'compare_card' ) ? 'samadi-compare' : '' );
	}

	/**
	 * Theme hook: add the compare button after the heart in the card's action slot.
	 *
	 * @param string $html       Markup so far.
	 * @param int    $product_id Product ID.
	 * @return string
	 */
	public static function append_to_card_slot( $html, $product_id ) {
		if ( ! samadi_core_setting( 'compare_card' ) || ! self::url() ) {
			return $html;
		}

		return '<span class="se-core-card-actions">' . $html . self::button( $product_id ) . '</span>';
	}

	/**
	 * Card buttons for themes without the Samadi card slot.
	 *
	 * @param string[] $buttons    Buttons.
	 * @param int      $product_id Product ID.
	 * @return string[]
	 */
	public static function card_button( $buttons, $product_id ) {
		if ( samadi_core_setting( 'compare_card' ) && self::url() ) {
			$buttons['compare'] = self::button( $product_id );
		}

		return $buttons;
	}

	/**
	 * Icon button (product cards).
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	public static function button( $product_id ) {
		$product_id = absint( $product_id );
		$active     = in_array( $product_id, self::ids(), true );

		return sprintf(
			'<button type="button" class="se-core-compare%1$s" data-se-compare="%2$d" aria-pressed="%3$s" aria-label="%4$s" title="%4$s" data-label-add="%5$s" data-label-remove="%6$s">%7$s</button>',
			$active ? ' is-active' : '',
			$product_id,
			$active ? 'true' : 'false',
			esc_attr( $active ? __( 'حذف از مقایسه', 'samadi-core' ) : __( 'افزودن به مقایسه', 'samadi-core' ) ),
			esc_attr__( 'افزودن به مقایسه', 'samadi-core' ),
			esc_attr__( 'حذف از مقایسه', 'samadi-core' ),
			samadi_core_icon( 'git-compare-arrows', 18 )
		);
	}

	/**
	 * Wide button on the single product page.
	 *
	 * @param string[]   $buttons Buttons.
	 * @param WC_Product $product Product.
	 * @return string[]
	 */
	public static function single_button( $buttons, $product ) {
		if ( ! samadi_core_setting( 'compare_single' ) || ! self::url() ) {
			return $buttons;
		}

		$id     = $product->get_id();
		$active = in_array( $id, self::ids(), true );

		$buttons['compare'] = sprintf(
			'<button type="button" class="se-core-action se-core-compare%1$s" data-se-compare="%2$d" aria-pressed="%3$s" data-label-add="%4$s" data-label-remove="%5$s">%6$s<span class="se-core-action__label">%7$s</span></button>',
			$active ? ' is-active' : '',
			$id,
			$active ? 'true' : 'false',
			esc_attr__( 'افزودن به مقایسه', 'samadi-core' ),
			esc_attr__( 'در فهرست مقایسه', 'samadi-core' ),
			samadi_core_icon( 'git-compare-arrows', 20 ),
			esc_html( $active ? __( 'در فهرست مقایسه', 'samadi-core' ) : __( 'افزودن به مقایسه', 'samadi-core' ) )
		);

		return $buttons;
	}

	/**
	 * Front-end data.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function script_data( $data ) {
		$data['compare'] = array(
			'cookie' => self::COOKIE,
			'url'    => self::url(),
			'max'    => self::max(),
			'isPage' => self::is_compare_page(),
			'i18n'   => array(
				'added'   => __( 'به فهرست مقایسه اضافه شد.', 'samadi-core' ),
				'removed' => __( 'از فهرست مقایسه حذف شد.', 'samadi-core' ),
				/* translators: %s: maximum number of products */
				'full'    => __( 'حداکثر %s محصول را می‌توانید با هم مقایسه کنید.', 'samadi-core' ),
				'view'    => __( 'مقایسه', 'samadi-core' ),
			),
		);

		return $data;
	}

	/**
	 * The compare page differs per visitor.
	 */
	public static function template_redirect() {
		if ( self::is_compare_page() ) {
			samadi_core_no_page_cache();
		}
	}

	/**
	 * Floating bar with the number of products picked for comparison (filled in by the script).
	 */
	public static function bar() {
		if ( ! self::url() || self::is_compare_page() || is_admin() ) {
			return;
		}
		?>
		<div class="se-compare-bar" data-se-compare-bar hidden>
			<div class="se-compare-bar__inner">
				<span class="se-compare-bar__icon"><?php echo samadi_core_icon( 'git-compare-arrows', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="se-compare-bar__text">
					<?php
					printf(
						/* translators: %s: number of products */
						esc_html__( '%s کالا برای مقایسه', 'samadi-core' ),
						'<strong data-se-compare-count>0</strong>'
					);
					?>
				</span>
				<a class="se-btn se-btn--accent se-compare-bar__go" href="<?php echo esc_url( self::url() ); ?>"><?php esc_html_e( 'مقایسه کنید', 'samadi-core' ); ?></a>
				<button type="button" class="se-compare-bar__clear" data-se-compare-clear aria-label="<?php esc_attr_e( 'پاک کردن فهرست مقایسه', 'samadi-core' ); ?>"><?php echo samadi_core_icon( 'x', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Brand name of a product (theme helper, or WooCommerce Brands).
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private static function brand( $product ) {
		$id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

		if ( function_exists( 'samadi_product_brand' ) ) {
			$term = samadi_product_brand( $id );
			return $term ? $term->name : '';
		}

		if ( taxonomy_exists( 'product_brand' ) ) {
			$terms = get_the_terms( $id, 'product_brand' );
			return $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
		}

		return '';
	}

	/**
	 * Table rows: key => [ label, values[] ] — values are HTML, one per product.
	 *
	 * @param WC_Product[] $products Products.
	 * @return array<string, array{label: string, values: string[]}>
	 */
	public static function rows( $products ) {
		$rows = array();
		$add  = static function ( $key, $label, $callback ) use ( &$rows, $products ) {
			$values = array_map( $callback, $products );
			if ( array_filter( array_map( 'trim', array_map( 'wp_strip_all_tags', $values ) ), 'strlen' ) ) {
				$rows[ $key ] = array(
					'label'  => $label,
					'values' => $values,
				);
			}
		};

		$add(
			'price',
			__( 'قیمت', 'samadi-core' ),
			static function ( $product ) {
				return '<span class="price">' . $product->get_price_html() . '</span>';
			}
		);

		if ( wc_review_ratings_enabled() ) {
			$add(
				'rating',
				__( 'امتیاز', 'samadi-core' ),
				static function ( $product ) {
					return $product->get_rating_count() ? wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) : '';
				}
			);
		}

		$add(
			'stock',
			__( 'موجودی', 'samadi-core' ),
			static function ( $product ) {
				if ( ! $product->is_in_stock() ) {
					return '<span class="se-core-stock is-out">' . esc_html__( 'ناموجود', 'samadi-core' ) . '</span>';
				}
				return '<span class="se-core-stock">' . esc_html( $product->is_on_backorder() ? __( 'قابل پیش‌سفارش', 'samadi-core' ) : __( 'موجود', 'samadi-core' ) ) . '</span>';
			}
		);

		$add(
			'brand',
			__( 'برند', 'samadi-core' ),
			static function ( $product ) {
				return esc_html( self::brand( $product ) );
			}
		);

		if ( wc_product_sku_enabled() ) {
			$add(
				'sku',
				__( 'کد کالا', 'samadi-core' ),
				static function ( $product ) {
					return $product->get_sku() ? '<span dir="ltr">' . esc_html( $product->get_sku() ) . '</span>' : '';
				}
			);
		}

		if ( wc_product_weight_enabled() ) {
			$add(
				'weight',
				__( 'وزن', 'samadi-core' ),
				static function ( $product ) {
					return $product->has_weight() ? esc_html( wc_format_weight( $product->get_weight() ) ) : '';
				}
			);
		}

		if ( wc_product_dimensions_enabled() ) {
			$add(
				'dimensions',
				__( 'ابعاد', 'samadi-core' ),
				static function ( $product ) {
					return $product->has_dimensions() ? esc_html( wc_format_dimensions( $product->get_dimensions( false ) ) ) : '';
				}
			);
		}

		// Visible attributes, in the order they first appear.
		$labels = array();
		foreach ( $products as $product ) {
			foreach ( $product->get_attributes() as $name => $attribute ) {
				if ( $attribute instanceof WC_Product_Attribute && $attribute->get_visible() && ! isset( $labels[ $name ] ) ) {
					$labels[ $name ] = wc_attribute_label( $attribute->get_name(), $product );
				}
			}
		}

		foreach ( $labels as $name => $label ) {
			$add(
				'attribute-' . sanitize_title( $name ),
				$label,
				static function ( $product ) use ( $name ) {
					return esc_html( $product->get_attribute( $name ) );
				}
			);
		}

		/**
		 * Comparison table rows. Each row: [ 'label' => string, 'values' => string[] (HTML, one per product) ].
		 *
		 * @param array        $rows     Rows.
		 * @param WC_Product[] $products Products.
		 */
		$rows = apply_filters( 'samadi_core_compare_rows', $rows, $products );

		foreach ( $rows as $key => $row ) {
			$plain               = array_map( 'trim', array_map( 'wp_strip_all_tags', $row['values'] ) );
			$rows[ $key ]['same'] = count( $plain ) > 1 && 1 === count( array_unique( $plain ) );
		}

		return $rows;
	}

	/**
	 * [samadi_compare] — comparison table.
	 *
	 * @return string
	 */
	public static function shortcode() {
		$products = array_values( array_filter( array_map( 'samadi_core_visible_product', self::ids() ) ) );

		return samadi_core_template(
			'compare',
			array(
				'products' => $products,
				'rows'     => $products ? self::rows( $products ) : array(),
			)
		);
	}
}
