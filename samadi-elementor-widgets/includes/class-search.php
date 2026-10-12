<?php
/**
 * Product search: live suggestions REST endpoint + SKU matching in storefront searches.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Search.
 */
class Samadi_EW_Search {

	const MAX_RESULTS = 8;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_filter( 'posts_search', array( __CLASS__, 'sku_search' ), 10, 2 );
	}

	/**
	 * REST routes.
	 */
	public static function routes() {
		register_rest_route(
			'samadi/v1',
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'rest_search' ),
				// Public, read-only catalog data (the same data shown on the storefront).
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => array( __CLASS__, 'sanitize_term' ),
						'validate_callback' => static function ( $value ) {
							$length = function_exists( 'mb_strlen' ) ? mb_strlen( trim( (string) $value ) ) : strlen( trim( (string) $value ) );
							return is_string( $value ) && $length >= 2 && $length <= 80;
						},
					),
				),
			)
		);
	}

	/**
	 * Normalize a search term: trim, strip tags, convert Persian/Arabic digits and letters.
	 *
	 * @param string $term Term.
	 * @return string
	 */
	public static function sanitize_term( $term ) {
		$term = sanitize_text_field( (string) $term );
		$term = strtr(
			$term,
			array(
				'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
				'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
				'ي' => 'ی', 'ك' => 'ک',
			)
		);

		return trim( $term );
	}

	/**
	 * Live search results.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_search( $request ) {
		$term = (string) $request->get_param( 'q' );

		if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $term ) : strlen( $term ) ) < 2 ) {
			return new WP_REST_Response( array( 'query' => $term, 'products' => array(), 'categories' => array(), 'brands' => array(), 'all' => '' ) );
		}

		/** Data store search covers title, excerpt, content and SKU (incl. variation SKUs → parent). */
		$data_store = WC_Data_Store::load( 'product' );
		$ids        = $data_store->search_products( $term, '', true, false, 60 );
		$ids        = array_filter( array_map( 'absint', (array) $ids ) );

		$products = array();

		if ( $ids ) {
			$found = wc_get_products(
				array(
					'include'    => $ids,
					'status'     => 'publish',
					'type'       => array_keys( wc_get_product_types() ),
					'visibility' => 'search',
					'limit'      => self::MAX_RESULTS,
					'orderby'    => 'title',
					'order'      => 'ASC',
				)
			);

			foreach ( $found as $product ) {
				$image_id   = $product->get_image_id();
				$products[] = array(
					'id'    => $product->get_id(),
					'title' => wp_strip_all_tags( $product->get_name() ),
					'url'   => esc_url_raw( $product->get_permalink() ),
					'image' => $image_id ? esc_url_raw( (string) wp_get_attachment_image_url( $image_id, 'woocommerce_gallery_thumbnail' ) ) : esc_url_raw( wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) ),
					'price' => wp_kses_post( $product->get_price_html() ),
					'sku'   => wc_product_sku_enabled() ? (string) $product->get_sku() : '',
					'stock' => $product->is_in_stock(),
				);
			}
		}

		$response = new WP_REST_Response(
			array(
				'query'      => $term,
				'products'   => $products,
				'categories' => self::terms( 'product_cat', $term ),
				'brands'     => samadi_ew_brand_taxonomy() ? self::terms( samadi_ew_brand_taxonomy(), $term ) : array(),
				'all'        => esc_url_raw( add_query_arg( array( 's' => $term, 'post_type' => 'product' ), home_url( '/' ) ) ),
			)
		);

		// Short public caching is safe: catalog data only, no user-specific content.
		$response->header( 'Cache-Control', 'public, max-age=120' );

		return $response;
	}

	/**
	 * Matching terms of a taxonomy.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $term     Search term.
	 * @return array
	 */
	private static function terms( $taxonomy, $term ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $term,
				'hide_empty' => true,
				'number'     => 4,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return array_map(
			static function ( $t ) {
				return array(
					'name'  => $t->name,
					'url'   => esc_url_raw( get_term_link( $t ) ),
					'count' => (int) $t->count,
				);
			},
			$terms
		);
	}

	/**
	 * Include SKU matches in front-end product searches (main query only).
	 *
	 * @param string   $search Search SQL.
	 * @param WP_Query $query  Query.
	 * @return string
	 */
	public static function sku_search( $search, $query ) {
		global $wpdb;

		if ( '' === $search || is_admin() || ! $query->is_main_query() || ! $query->is_search() || ! samadi_ew_setting( 'search_sku' ) ) {
			return $search;
		}

		$post_type = (array) $query->get( 'post_type' );
		if ( ! in_array( 'product', $post_type, true ) ) {
			return $search;
		}

		$term = self::sanitize_term( (string) $query->get( 's' ) );
		if ( strlen( $term ) < 2 ) {
			return $search;
		}

		$like = '%' . $wpdb->esc_like( $term ) . '%';
		// Products and parents of variations whose SKU matches.
		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT DISTINCT IF( p.post_parent > 0, p.post_parent, p.ID )
				FROM {$wpdb->wc_product_meta_lookup} l
				INNER JOIN {$wpdb->posts} p ON p.ID = l.product_id
				WHERE l.sku LIKE %s AND p.post_type IN ( 'product', 'product_variation' ) AND p.post_status = 'publish'
				LIMIT 200",
				$like
			)
		);

		$ids = array_filter( array_map( 'absint', $ids ) );
		if ( ! $ids ) {
			return $search;
		}

		// " AND (<title/content clauses>) [AND (password clause)]" → " AND ( ID IN (...) OR <title/content clauses>) ..."
		$in  = "{$wpdb->posts}.ID IN (" . implode( ',', $ids ) . ') OR ';
		$pos = strpos( $search, 'AND (' );

		if ( false === $pos ) {
			return $search;
		}

		return substr_replace( $search, 'AND (' . $in, $pos, strlen( 'AND (' ) );
	}
}
