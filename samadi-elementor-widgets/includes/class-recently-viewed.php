<?php
/**
 * Recently viewed products.
 *
 * Product IDs are kept in the visitor's browser (localStorage), so full-page caching keeps working;
 * cards are rendered server-side through a read-only REST endpoint using the theme's product card.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Recently viewed.
 */
class Samadi_EW_Recently_Viewed {

	const MAX = 20;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'woocommerce_after_single_product_summary', array( __CLASS__, 'auto_insert' ), 25 );
	}

	/**
	 * REST route: GET /samadi/v1/products?ids=1,2,3&columns=4
	 */
	public static function routes() {
		register_rest_route(
			'samadi/v1',
			'/products',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'rest_products' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'ids'     => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => static function ( $value ) {
							return implode( ',', array_slice( wp_parse_id_list( (string) $value ), 0, self::MAX ) );
						},
					),
					'columns' => array(
						'type'    => 'integer',
						'default' => 4,
						'minimum' => 1,
						'maximum' => 6,
					),
				),
			)
		);
	}

	/**
	 * Render cards for the given IDs (published, catalog-visible products only).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_products( $request ) {
		$ids = wp_parse_id_list( (string) $request->get_param( 'ids' ) );

		if ( ! $ids ) {
			return new WP_REST_Response( array( 'html' => '' ) );
		}

		$visible = wc_get_products(
			array(
				'include'    => $ids,
				'status'     => 'publish',
				'visibility' => 'catalog',
				'limit'      => self::MAX,
				'return'     => 'ids',
			)
		);

		// Keep the visitor's order (most recent first).
		$ordered = array_values( array_intersect( $ids, array_map( 'absint', $visible ) ) );

		return new WP_REST_Response(
			array(
				'html' => samadi_ew_render_product_ids( $ordered, absint( $request->get_param( 'columns' ) ) ),
			)
		);
	}

	/**
	 * Placeholder container filled by the front-end script.
	 *
	 * @param array $args { title, limit, columns, layout }.
	 * @return string
	 */
	public static function render( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'   => __( 'بازدیدهای اخیر شما', 'samadi-elementor-widgets' ),
				'limit'   => (int) samadi_ew_setting( 'recently_viewed_limit' ),
				'columns' => 4,
				'layout'  => 'carousel',
			)
		);

		$carousel = 'carousel' === $args['layout'];

		return sprintf(
			'<section class="se-recently-viewed" data-se-recently-viewed data-limit="%1$d" data-columns="%2$d" hidden>%3$s%4$s</section>',
			max( 1, min( self::MAX, absint( $args['limit'] ) ) ),
			max( 1, min( 6, absint( $args['columns'] ) ) ),
			Samadi_EW_Components::section_head( $args['title'] ),
			Samadi_EW_Components::maybe_carousel( '<div class="se-products' . ( $carousel ? ' se-products--row' : '' ) . '" data-se-recently-viewed-list></div>', $carousel, $args['title'] )
		);
	}

	/**
	 * Automatically show on single product pages.
	 */
	public static function auto_insert() {
		if ( samadi_ew_setting( 'recently_viewed' ) ) {
			echo self::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render().
		}
	}
}
