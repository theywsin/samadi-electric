<?php
/**
 * Store structured data (JSON-LD) on the home page: the business (name, logo, phone, address,
 * social profiles) and the site search. Product and breadcrumb schema come from WooCommerce.
 *
 * Contact details are read from the theme's Customizer (Samadi Electric → contact info). Nothing
 * is printed when an SEO plugin that writes its own schema is active.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schema module.
 */
final class Samadi_Core_Schema {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output' ), 30 );
	}

	/**
	 * Whether an SEO plugin already prints site schema.
	 *
	 * @return bool
	 */
	public static function seo_plugin_active() {
		$active = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );

		return (bool) apply_filters( 'samadi_core_seo_plugin_active', $active );
	}

	/**
	 * Theme setting, when the Samadi theme is active.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	private static function theme( $key ) {
		return function_exists( 'samadi_option' ) ? trim( (string) samadi_option( $key ) ) : '';
	}

	/**
	 * The schema graph.
	 *
	 * @return array
	 */
	public static function graph() {
		$home = home_url( '/' );
		$name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		$store = array(
			'@type' => (string) samadi_core_setting( 'schema_store_type' ),
			'@id'   => $home . '#organization',
			'name'  => $name,
			'url'   => $home,
		);

		$description = wp_specialchars_decode( get_bloginfo( 'description' ), ENT_QUOTES );
		if ( $description ) {
			$store['description'] = $description;
		}

		$logo_id = (int) get_theme_mod( 'custom_logo' );
		$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		if ( $logo ) {
			$store['logo']  = $logo;
			$store['image'] = $logo;
		}

		$phone = self::theme( 'contact_phone' ) ? self::theme( 'contact_phone' ) : self::theme( 'contact_mobile' );
		if ( $phone ) {
			$store['telephone'] = samadi_core_en_digits( $phone );
		}

		$email = self::theme( 'contact_email' );
		if ( $email && is_email( $email ) ) {
			$store['email'] = $email;
		}

		$address = self::theme( 'contact_address' );
		if ( $address ) {
			$country          = function_exists( 'WC' ) && WC()->countries ? WC()->countries->get_base_country() : 'IR';
			$store['address'] = array(
				'@type'          => 'PostalAddress',
				'streetAddress'  => preg_replace( '/\s+/u', ' ', $address ),
				'addressCountry' => $country ? $country : 'IR',
			);
			if ( function_exists( 'WC' ) && WC()->countries && WC()->countries->get_base_city() ) {
				$store['address']['addressLocality'] = WC()->countries->get_base_city();
			}
		}

		$same_as = array();
		foreach ( array( 'social_instagram', 'social_telegram', 'social_linkedin', 'social_whatsapp' ) as $key ) {
			$url = self::theme( $key );
			if ( $url && wp_http_validate_url( $url ) ) {
				$same_as[] = $url;
			}
		}
		if ( $same_as ) {
			$store['sameAs'] = $same_as;
		}

		$website = array(
			'@type'      => 'WebSite',
			'@id'        => $home . '#website',
			'url'        => $home,
			'name'       => $name,
			'inLanguage' => str_replace( '_', '-', get_locale() ),
			'publisher'  => array( '@id' => $home . '#organization' ),
		);

		if ( samadi_core_setting( 'schema_search' ) ) {
			$target = class_exists( 'WooCommerce' ) ? add_query_arg( 'post_type', 'product', home_url( '/' ) ) . '&s={search_term_string}' : home_url( '/?s={search_term_string}' );

			$website['potentialAction'] = array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => $target,
				),
				'query-input' => 'required name=search_term_string',
			);
		}

		/**
		 * Home page schema graph.
		 *
		 * @param array $graph Nodes.
		 */
		return (array) apply_filters( 'samadi_core_schema_graph', array( $store, $website ) );
	}

	/**
	 * Print the JSON-LD block on the home page.
	 */
	public static function output() {
		if ( ! is_front_page() || is_paged() || self::seo_plugin_active() ) {
			return;
		}

		$graph = self::graph();
		if ( ! $graph ) {
			return;
		}

		printf(
			"<script type=\"application/ld+json\" class=\"samadi-core-schema\">%s</script>\n",
			wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => array_values( $graph ),
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
			)
		);
	}
}
