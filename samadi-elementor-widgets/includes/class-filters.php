<?php
/**
 * Product filters.
 *
 * Uses WooCommerce's native query parameters so filtering is done by WooCommerce itself:
 *  - filter_{attribute}=slug1,slug2 & query_type_{attribute}=or   (product attributes, e.g. pa_color)
 *  - filter_product_brand=ID1,ID2                                  (WooCommerce Brands)
 *  - min_price / max_price                                         (price)
 * plus one parameter handled here:
 *  - se_stock=instock                                              (only in-stock products)
 *
 * Only filters that apply to products in the current archive (category, tag, brand…) are shown.
 * URLs are shareable; the front-end script enhances the GET form with AJAX + history.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters.
 */
class Samadi_EW_Filters {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'normalize_request' ), 1 );
		add_filter( 'woocommerce_product_query_tax_query', array( __CLASS__, 'stock_tax_query' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_widget' ) );
		add_action( 'samadi_shop_quick_filters', array( __CLASS__, 'quick_chips' ) );

		add_filter( 'wp_robots', array( __CLASS__, 'wp_robots' ) );
		add_filter( 'wpseo_robots', array( __CLASS__, 'yoast_robots' ) );
		add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'rank_math_robots' ) );
	}

	/**
	 * Register the classic widget (for the theme's "Shop filters" sidebar).
	 */
	public static function register_widget() {
		require_once SAMADI_EW_DIR . 'includes/class-filter-widget.php';
		register_widget( 'Samadi_EW_Filter_Widget' );
	}

	/**
	 * Checkbox arrays (no-JS submissions) → WooCommerce's comma-separated format.
	 */
	public static function normalize_request() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only catalog filtering.
		foreach ( $_GET as $key => $value ) {
			if ( ! is_array( $value ) || ! is_string( $key ) ) {
				continue;
			}
			if ( 0 === strpos( $key, 'filter_' ) || 'rating_filter' === $key ) {
				$clean            = implode( ',', array_filter( array_map( 'sanitize_title', array_map( 'strval', array_filter( wp_unslash( $value ), 'is_scalar' ) ) ) ) );
				$_GET[ $key ]     = $clean;
				$_REQUEST[ $key ] = $clean;
			}
		}
		// phpcs:enable
	}

	/**
	 * Active filter parameters on the current request.
	 *
	 * @return array<string, string>
	 */
	public static function active_params() {
		$params = array();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( $_GET as $key => $value ) {
			if ( ! is_string( $key ) || ! is_scalar( $value ) || '' === (string) $value ) {
				continue;
			}
			if ( 0 === strpos( $key, 'filter_' ) || 0 === strpos( $key, 'query_type_' ) || in_array( $key, array( 'min_price', 'max_price', 'se_stock', 'rating_filter' ), true ) ) {
				$params[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( (string) $value ) );
			}
		}
		// phpcs:enable

		return $params;
	}

	/**
	 * Whether any product filter is applied.
	 *
	 * @return bool
	 */
	public static function has_active_filters() {
		foreach ( array_keys( self::active_params() ) as $key ) {
			if ( 0 !== strpos( $key, 'query_type_' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * "Only in stock" filter.
	 *
	 * @param array $tax_query Tax query.
	 * @return array
	 */
	public static function stock_tax_query( $tax_query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['se_stock'] ) && 'instock' === $_GET['se_stock'] ) {
			$visibility = wc_get_product_visibility_term_ids();
			if ( ! empty( $visibility['outofstock'] ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $visibility['outofstock'] ),
					'operator' => 'NOT IN',
				);
			}
		}

		return $tax_query;
	}

	/* ---------------------------------------------------------------------
	 * Robots for filtered URLs
	 * ------------------------------------------------------------------ */

	/**
	 * Whether the current request is a filtered product archive that should not be indexed.
	 *
	 * @return bool
	 */
	private static function should_noindex() {
		return samadi_ew_setting( 'filter_noindex' ) && ( is_shop() || is_product_taxonomy() ) && self::has_active_filters();
	}

	/**
	 * Core robots.
	 *
	 * @param array $robots Robots.
	 * @return array
	 */
	public static function wp_robots( $robots ) {
		if ( self::should_noindex() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
		return $robots;
	}

	/**
	 * Yoast SEO robots string.
	 *
	 * @param string $robots Robots.
	 * @return string
	 */
	public static function yoast_robots( $robots ) {
		return self::should_noindex() ? 'noindex, follow' : $robots;
	}

	/**
	 * Rank Math robots array.
	 *
	 * @param array $robots Robots.
	 * @return array
	 */
	public static function rank_math_robots( $robots ) {
		if ( self::should_noindex() ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	}

	/* ---------------------------------------------------------------------
	 * Context data
	 * ------------------------------------------------------------------ */

	/**
	 * The archive term (category/tag/brand/attribute) being viewed, if any.
	 *
	 * @return WP_Term|null
	 */
	private static function context_term() {
		if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			$term = get_queried_object();
			return $term instanceof WP_Term ? $term : null;
		}
		return null;
	}

	/**
	 * term_taxonomy_ids for the context term and its descendants.
	 *
	 * @param WP_Term|null $term Term.
	 * @return int[]
	 */
	private static function context_tt_ids( $term ) {
		if ( ! $term ) {
			return array();
		}

		$tt_ids   = array( (int) $term->term_taxonomy_id );
		$children = is_taxonomy_hierarchical( $term->taxonomy ) ? get_term_children( $term->term_id, $term->taxonomy ) : array();

		if ( ! is_wp_error( $children ) ) {
			foreach ( $children as $child_id ) {
				$child = get_term( $child_id, $term->taxonomy );
				if ( $child instanceof WP_Term ) {
					$tt_ids[] = (int) $child->term_taxonomy_id;
				}
			}
		}

		return $tt_ids;
	}

	/**
	 * Filterable terms (attributes + brands) with product counts within the context, cached.
	 *
	 * @param WP_Term|null $term       Context term.
	 * @param string[]     $taxonomies Taxonomies to inspect.
	 * @return array<string, array<int, array{term_id:int,slug:string,name:string,count:int}>>
	 */
	public static function context_terms( $term, $taxonomies ) {
		global $wpdb;

		$taxonomies = array_values( array_filter( $taxonomies, 'taxonomy_exists' ) );

		if ( ! $taxonomies ) {
			return array();
		}

		$version   = class_exists( 'WC_Cache_Helper' ) ? WC_Cache_Helper::get_transient_version( 'product' ) : '1';
		$cache_key = 'se_filters_' . md5( wp_json_encode( array( $term ? $term->term_taxonomy_id : 0, $taxonomies, $version ) ) );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$tt_ids  = self::context_tt_ids( $term );
		$context = $tt_ids
			? "AND tr.object_id IN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode( ',', array_map( 'absint', $tt_ids ) ) . ') )'
			: '';

		$placeholders = implode( ',', array_fill( 0, count( $taxonomies ), '%s' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tt.taxonomy, t.term_id, t.slug, t.name, COUNT( DISTINCT tr.object_id ) AS cnt
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
				INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_type = 'product' AND p.post_status = 'publish'
				WHERE tt.taxonomy IN ( {$placeholders} ) {$context}
				GROUP BY tt.taxonomy, t.term_id",
				$taxonomies
			)
		);
		// phpcs:enable

		$by_tax = array();
		foreach ( (array) $rows as $row ) {
			$by_tax[ $row->taxonomy ][ (int) $row->term_id ] = (int) $row->cnt;
		}

		$out = array();
		foreach ( $by_tax as $taxonomy => $counts ) {
			// get_terms() applies WooCommerce's configured attribute ordering.
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'include'    => array_keys( $counts ),
					'hide_empty' => false,
				)
			);
			if ( is_wp_error( $terms ) ) {
				continue;
			}
			foreach ( $terms as $t ) {
				$out[ $taxonomy ][] = array(
					'term_id' => (int) $t->term_id,
					'slug'    => $t->slug,
					'name'    => $t->name,
					'count'   => isset( $counts[ $t->term_id ] ) ? $counts[ $t->term_id ] : 0,
				);
			}
		}

		set_transient( $cache_key, $out, DAY_IN_SECONDS );

		return $out;
	}

	/**
	 * Price range within the context.
	 *
	 * @param WP_Term|null $term Term.
	 * @return array{min:float,max:float}
	 */
	public static function price_range( $term ) {
		global $wpdb;

		$tt_ids  = self::context_tt_ids( $term );
		$context = $tt_ids
			? 'AND l.product_id IN ( SELECT object_id FROM ' . $wpdb->term_relationships . ' WHERE term_taxonomy_id IN (' . implode( ',', array_map( 'absint', $tt_ids ) ) . ') )'
			: '';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row( "SELECT MIN( l.min_price ) AS min_price, MAX( l.max_price ) AS max_price FROM {$wpdb->wc_product_meta_lookup} l INNER JOIN {$wpdb->posts} p ON p.ID = l.product_id AND p.post_type = 'product' AND p.post_status = 'publish' WHERE l.max_price > 0 {$context}" );

		return array(
			'min' => $row ? floor( (float) $row->min_price ) : 0,
			'max' => $row ? ceil( (float) $row->max_price ) : 0,
		);
	}

	/**
	 * Base URL of the current product archive (no pagination, no filters).
	 *
	 * @return string
	 */
	public static function base_url() {
		$term = self::context_term();

		if ( $term ) {
			$link = get_term_link( $term );
			return is_wp_error( $link ) ? home_url( '/' ) : $link;
		}

		if ( is_search() ) {
			return home_url( '/' );
		}

		$shop = wc_get_page_permalink( 'shop' );

		return $shop ? $shop : home_url( '/' );
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------ */

	/**
	 * Render the filter form.
	 *
	 * @param array $args {
	 *     @type bool     $price      Show price filter.
	 *     @type bool     $stock      Show stock filter.
	 *     @type bool     $brand      Show brand filter.
	 *     @type string[] $attributes Attribute taxonomies to show (empty = all applicable).
	 *     @type int      $collapse   Show "more" after this many options.
	 * }
	 * @return string
	 */
	public static function render_form( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'price'      => true,
				'stock'      => true,
				'brand'      => true,
				'attributes' => array(),
				'collapse'   => 8,
			)
		);

		$is_archive = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) );

		if ( ! $is_archive ) {
			return Samadi_EW_Components::is_editor()
				? '<p class="se-notice">' . esc_html__( 'فیلترها در صفحات فروشگاه و آرشیو دسته‌بندی محصولات نمایش داده می‌شوند.', 'samadi-elementor-widgets' ) . '</p>'
				: '';
		}

		$term       = self::context_term();
		$active     = self::active_params();
		$brand_tax  = samadi_ew_brand_taxonomy();
		$taxonomies = $args['attributes'] ? array_map( 'sanitize_key', (array) $args['attributes'] ) : wc_get_attribute_taxonomy_names();

		if ( $args['brand'] && 'product_brand' === $brand_tax && ( ! $term || 'product_brand' !== $term->taxonomy ) ) {
			$taxonomies[] = $brand_tax;
		}

		// Don't offer filtering by the attribute archive being viewed.
		if ( $term ) {
			$taxonomies = array_diff( $taxonomies, array( $term->taxonomy ) );
		}

		$data = self::context_terms( $term, $taxonomies );

		ob_start();
		?>
		<form class="se-filter-form" method="get" action="<?php echo esc_url( self::base_url() ); ?>" data-se-filter-form>
			<?php
			// Keep search/sorting parameters.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			foreach ( array( 's', 'post_type', 'orderby' ) as $keep ) {
				if ( isset( $_GET[ $keep ] ) && is_scalar( $_GET[ $keep ] ) && '' !== $_GET[ $keep ] ) {
					printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $keep ), esc_attr( sanitize_text_field( wp_unslash( $_GET[ $keep ] ) ) ) );
				}
			}
			// phpcs:enable

			echo self::active_chips( $data, $active ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside.

			if ( $args['price'] ) {
				echo self::price_group( $term, $active ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( $args['stock'] ) {
				?>
				<fieldset class="se-filter-group" data-taxonomy="stock">
					<legend class="se-filter-group__title"><?php esc_html_e( 'موجودی', 'samadi-elementor-widgets' ); ?></legend>
					<label class="se-filter-option">
						<input type="checkbox" name="se_stock" value="instock" <?php checked( isset( $active['se_stock'] ) && 'instock' === $active['se_stock'] ); ?>>
						<span class="se-filter-option__name"><?php esc_html_e( 'فقط کالاهای موجود', 'samadi-elementor-widgets' ); ?></span>
					</label>
				</fieldset>
				<?php
			}

			if ( $brand_tax && isset( $data[ $brand_tax ] ) ) {
				echo self::terms_group( $brand_tax, __( 'برند', 'samadi-elementor-widgets' ), 'filter_product_brand', $data[ $brand_tax ], $active, 'term_id', (int) $args['collapse'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			foreach ( $data as $taxonomy => $terms ) {
				if ( $taxonomy === $brand_tax || ! taxonomy_is_product_attribute( $taxonomy ) ) {
					continue;
				}
				$attr = wc_attribute_taxonomy_slug( $taxonomy );
				echo self::terms_group( $taxonomy, wc_attribute_label( $taxonomy ), 'filter_' . $attr, $terms, $active, 'slug', (int) $args['collapse'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				printf( '<input type="hidden" name="query_type_%s" value="or">', esc_attr( $attr ) );
			}
			?>
			<div class="se-filter-form__actions">
				<button type="submit" class="se-btn se-btn--primary se-btn--block"><?php esc_html_e( 'اعمال فیلترها', 'samadi-elementor-widgets' ); ?></button>
				<?php if ( self::has_active_filters() ) : ?>
					<a class="se-filter-form__reset" href="<?php echo esc_url( self::url_without( array_keys( $active ) ) ); ?>" data-se-filter-link><?php esc_html_e( 'حذف همه فیلترها', 'samadi-elementor-widgets' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Quick filter chips under the shop toolbar: one pill per filter group that applies to the current
	 * archive (brand, attributes, price). Each chip opens the filter panel at that group.
	 */
	public static function quick_chips() {
		if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() || is_search() ) ) {
			return;
		}

		$term       = self::context_term();
		$brand_tax  = samadi_ew_brand_taxonomy();
		$taxonomies = wc_get_attribute_taxonomy_names();
		if ( 'product_brand' === $brand_tax ) {
			array_unshift( $taxonomies, $brand_tax );
		}
		if ( $term ) {
			$taxonomies = array_diff( $taxonomies, array( $term->taxonomy ) );
		}

		$data   = self::context_terms( $term, $taxonomies );
		$active = self::active_params();
		$chips  = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( empty( $data[ $taxonomy ] ) ) {
				continue;
			}
			$param    = $taxonomy === $brand_tax ? 'filter_product_brand' : 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
			$label    = $taxonomy === $brand_tax ? __( 'برند', 'samadi-elementor-widgets' ) : wc_attribute_label( $taxonomy );
			$selected = isset( $active[ $param ] ) ? count( array_filter( explode( ',', $active[ $param ] ) ) ) : 0;
			$chips[]  = array( $taxonomy, $label, $selected, true );
		}

		$range = self::price_range( $term );
		if ( $range['max'] > $range['min'] ) {
			$chips[] = array( 'price', __( 'محدوده قیمت', 'samadi-elementor-widgets' ), ( isset( $active['min_price'] ) || isset( $active['max_price'] ) ) ? 1 : 0, false );
		}

		/**
		 * Filter the quick filter chips.
		 *
		 * @param array $chips [ taxonomy, label, active count, has dropdown ].
		 */
		$chips = apply_filters( 'samadi_quick_filter_chips', array_slice( $chips, 0, 7 ) );

		if ( ! $chips ) {
			return;
		}

		echo '<div class="se-chips" role="group" aria-label="' . esc_attr__( 'فیلتر سریع', 'samadi-elementor-widgets' ) . '">';
		foreach ( $chips as $chip ) {
			printf(
				'<button type="button" class="se-chip%1$s" data-se-filter-jump="%2$s" aria-controls="se-filters"><span>%3$s</span>%4$s%5$s</button>',
				$chip[2] ? ' is-active' : '',
				esc_attr( $chip[0] ),
				esc_html( $chip[1] ),
				$chip[2] ? '<span class="se-chip__count">' . esc_html( samadi_ew_number( $chip[2] ) ) . '</span>' : '',
				$chip[3] ? samadi_ew_icon( 'chevron-down', 14 ) : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
		echo '</div>';
	}

	/**
	 * Current URL without the given query args (and without pagination).
	 *
	 * @param string[] $keys Keys to remove.
	 * @param array    $set  Keys to set.
	 * @return string
	 */
	private static function url_without( $keys, $set = array() ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query = array_map( 'wp_unslash', array_filter( $_GET, 'is_scalar' ) );
		$query = array_diff_key( $query, array_flip( array_merge( (array) $keys, array( 'paged', 'product-page' ) ) ) );
		$query = array_merge( $query, $set );
		$query = array_map( 'sanitize_text_field', $query );

		return add_query_arg( array_map( 'rawurlencode', $query ), self::base_url() );
	}

	/**
	 * Active filter chips with remove links.
	 *
	 * @param array $data   Context terms.
	 * @param array $active Active params.
	 * @return string
	 */
	private static function active_chips( $data, $active ) {
		$chips = array();

		foreach ( $active as $key => $value ) {
			if ( 0 === strpos( $key, 'filter_' ) ) {
				$values   = array_filter( explode( ',', $value ) );
				$taxonomy = 'filter_product_brand' === $key ? samadi_ew_brand_taxonomy() : wc_attribute_taxonomy_name( substr( $key, 7 ) );
				$field    = 'filter_product_brand' === $key ? 'term_id' : 'slug';
				foreach ( $values as $v ) {
					$name = $v;
					foreach ( isset( $data[ $taxonomy ] ) ? $data[ $taxonomy ] : array() as $t ) {
						if ( (string) $t[ $field ] === (string) $v ) {
							$name = $t['name'];
						}
					}
					if ( $name === $v ) {
						$found = get_term_by( $field === 'slug' ? 'slug' : 'id', $v, $taxonomy );
						$name  = $found ? $found->name : $v;
					}
					$rest    = array_values( array_diff( $values, array( $v ) ) );
					$chips[] = array(
						$name,
						$rest ? self::url_without( array(), array( $key => implode( ',', $rest ) ) ) : self::url_without( array( $key ) ),
					);
				}
			} elseif ( 'se_stock' === $key ) {
				$chips[] = array( __( 'فقط موجود', 'samadi-elementor-widgets' ), self::url_without( array( 'se_stock' ) ) );
			} elseif ( 'min_price' === $key || 'max_price' === $key ) {
				/* translators: %s: price. */
				$label   = 'min_price' === $key ? __( 'از %s', 'samadi-elementor-widgets' ) : __( 'تا %s', 'samadi-elementor-widgets' );
				$chips[] = array( sprintf( $label, wp_strip_all_tags( wc_price( (float) $value, array( 'decimals' => 0 ) ) ) ), self::url_without( array( $key ) ) );
			}
		}

		if ( ! $chips ) {
			return '';
		}

		$html = '<div class="se-filter-chips" aria-label="' . esc_attr__( 'فیلترهای فعال', 'samadi-elementor-widgets' ) . '">';
		foreach ( $chips as $chip ) {
			$html .= sprintf(
				'<a class="se-filter-chip" href="%1$s" data-se-filter-link><span>%2$s</span>%3$s<span class="screen-reader-text">%4$s</span></a>',
				esc_url( $chip[1] ),
				esc_html( $chip[0] ),
				samadi_ew_icon( 'x', 14 ),
				esc_html__( '(حذف)', 'samadi-elementor-widgets' )
			);
		}

		return $html . '</div>';
	}

	/**
	 * Price group.
	 *
	 * @param WP_Term|null $term   Term.
	 * @param array        $active Active params.
	 * @return string
	 */
	private static function price_group( $term, $active ) {
		$range = self::price_range( $term );

		if ( $range['max'] <= 0 || $range['max'] <= $range['min'] ) {
			return '';
		}

		$symbol = get_woocommerce_currency_symbol();

		return sprintf(
			'<fieldset class="se-filter-group se-filter-group--price" data-taxonomy="price"><legend class="se-filter-group__title">%1$s</legend><div class="se-price-inputs"><label><span>%2$s</span><input type="number" inputmode="numeric" name="min_price" min="%4$s" max="%5$s" step="1" value="%6$s" placeholder="%7$s"></label><label><span>%3$s</span><input type="number" inputmode="numeric" name="max_price" min="%4$s" max="%5$s" step="1" value="%8$s" placeholder="%9$s"></label></div><p class="se-filter-group__hint">%10$s</p></fieldset>',
			/* translators: %s: currency symbol. */
			esc_html( sprintf( __( 'محدوده قیمت (%s)', 'samadi-elementor-widgets' ), $symbol ) ),
			esc_html__( 'از', 'samadi-elementor-widgets' ),
			esc_html__( 'تا', 'samadi-elementor-widgets' ),
			esc_attr( $range['min'] ),
			esc_attr( $range['max'] ),
			esc_attr( isset( $active['min_price'] ) ? (string) absint( $active['min_price'] ) : '' ),
			esc_attr( (string) $range['min'] ),
			esc_attr( isset( $active['max_price'] ) ? (string) absint( $active['max_price'] ) : '' ),
			esc_attr( (string) $range['max'] ),
			/* translators: 1: min price, 2: max price. */
			esc_html( sprintf( __( 'قیمت محصولات این بخش: %1$s تا %2$s', 'samadi-elementor-widgets' ), wp_strip_all_tags( wc_price( $range['min'], array( 'decimals' => 0 ) ) ), wp_strip_all_tags( wc_price( $range['max'], array( 'decimals' => 0 ) ) ) ) )
		);
	}

	/**
	 * Checkbox group for a taxonomy.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $label    Label.
	 * @param string $param    Query parameter.
	 * @param array  $terms    Terms.
	 * @param array  $active   Active params.
	 * @param string $field    'slug' | 'term_id'.
	 * @param int    $collapse Collapse after N.
	 * @return string
	 */
	private static function terms_group( $taxonomy, $label, $param, $terms, $active, $field, $collapse ) {
		if ( ! $terms ) {
			return '';
		}

		$selected = isset( $active[ $param ] ) ? array_map( 'strval', explode( ',', $active[ $param ] ) ) : array();
		$html     = '<fieldset class="se-filter-group" data-taxonomy="' . esc_attr( $taxonomy ) . '"><legend class="se-filter-group__title">' . esc_html( $label ) . '</legend>';
		$html    .= '<div class="se-filter-group__options' . ( $collapse && count( $terms ) > $collapse ? ' is-collapsible' : '' ) . '" style="--se-collapse:' . absint( $collapse ) . '">';

		foreach ( $terms as $index => $t ) {
			$value = (string) $t[ $field ];
			$color = sanitize_hex_color( (string) get_term_meta( $t['term_id'], 'se_color', true ) );
			$extra = $collapse && $index >= $collapse && ! in_array( $value, $selected, true );
			$html .= sprintf(
				'<label class="se-filter-option%7$s"><input type="checkbox" name="%1$s[]" value="%2$s" %3$s>%4$s<span class="se-filter-option__name">%5$s</span><span class="se-filter-option__count">%6$s</span></label>',
				esc_attr( $param ),
				esc_attr( $value ),
				checked( in_array( $value, $selected, true ), true, false ),
				$color ? '<span class="se-filter-option__swatch" style="background:' . esc_attr( $color ) . '" aria-hidden="true"></span>' : '',
				esc_html( $t['name'] ),
				esc_html( samadi_ew_number( $t['count'] ) ),
				$extra ? ' is-extra' : ''
			);
		}

		$html .= '</div>';

		if ( $collapse && count( $terms ) > $collapse ) {
			$html .= '<button type="button" class="se-filter-group__more" data-se-filter-more aria-expanded="false">' . esc_html__( 'نمایش بیشتر', 'samadi-elementor-widgets' ) . '</button>';
		}

		return $html . '</fieldset>';
	}
}
