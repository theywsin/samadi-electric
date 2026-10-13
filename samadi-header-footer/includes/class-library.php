<?php
/**
 * Installs designs as Elementor templates and tracks the active header/footer.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template library.
 */
class SHF_Library {

	const INSTALLED = 'shf_installed';
	const ACTIVE    = 'shf_active';

	/**
	 * Part of a design key (header|footer).
	 *
	 * @param string $key Design key.
	 * @return string
	 */
	public static function part( $key ) {
		return 0 === strpos( $key, 'footer' ) ? 'footer' : 'header';
	}

	/**
	 * Whether Elementor Pro's Theme Builder is available.
	 *
	 * @return bool
	 */
	public static function has_pro() {
		return defined( 'ELEMENTOR_PRO_VERSION' ) && class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' );
	}

	/**
	 * Installed template ID for a design (0 if not installed or deleted).
	 *
	 * @param string $key Design key.
	 * @return int
	 */
	public static function installed( $key ) {
		$map = get_option( self::INSTALLED, array() );
		$id  = is_array( $map ) && isset( $map[ $key ] ) ? (int) $map[ $key ] : 0;
		return $id && 'elementor_library' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ? $id : 0;
	}

	/**
	 * Active template ID for a part.
	 *
	 * @param string $part header|footer.
	 * @return int
	 */
	public static function active( $part ) {
		$map = get_option( self::ACTIVE, array() );
		$id  = is_array( $map ) && isset( $map[ $part ] ) ? (int) $map[ $part ] : 0;
		return $id && 'elementor_library' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ? $id : 0;
	}

	/**
	 * Install a design (a fresh copy when $fresh is true).
	 *
	 * @param string $key   Design key.
	 * @param bool   $fresh Always create a new copy.
	 * @return int|WP_Error Template ID.
	 */
	public static function install( $key, $fresh = false ) {
		$catalogue = SHF_Designs::catalogue();
		$part      = self::part( $key );
		if ( empty( $catalogue[ $part ][ $key ] ) ) {
			return new WP_Error( 'shf_unknown', 'طرح ناشناخته است.' );
		}
		if ( ! $fresh && self::installed( $key ) ) {
			return self::installed( $key );
		}

		$elements = SHF_Designs::elements( $key );
		$type     = self::has_pro() ? $part : 'section';
		$title    = $catalogue[ $part ][ $key ]['name'];

		$document = \Elementor\Plugin::$instance->documents->create(
			$type,
			array(
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $document ) || ! $document ) {
			return is_wp_error( $document ) ? $document : new WP_Error( 'shf_create', 'ساخت قالب در Elementor انجام نشد.' );
		}

		$id = (int) $document->get_main_id();
		$document->save( array( 'elements' => $elements ) );

		// Make sure the data is stored even when the current user cannot edit (e.g. WP-CLI without --user).
		if ( ! get_post_meta( $id, '_elementor_data', true ) ) {
			update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
		}
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_shf_design', $key );
		// Full-width blank canvas in the editor (no theme sidebar around the header/footer).
		update_post_meta( $id, '_wp_page_template', 'elementor_canvas' );

		$map         = get_option( self::INSTALLED, array() );
		$map         = is_array( $map ) ? $map : array();
		$map[ $key ] = $id;
		update_option( self::INSTALLED, $map, false );

		return $id;
	}

	/**
	 * Activate a template as the site header/footer.
	 *
	 * @param string $part header|footer.
	 * @param int    $id   Template ID.
	 */
	public static function activate( $part, $id ) {
		$map          = get_option( self::ACTIVE, array() );
		$map          = is_array( $map ) ? $map : array();
		$previous     = isset( $map[ $part ] ) ? (int) $map[ $part ] : 0;
		$map[ $part ] = (int) $id;
		update_option( self::ACTIVE, $map );

		if ( self::has_pro() ) {
			if ( $previous && $previous !== (int) $id ) {
				update_post_meta( $previous, '_elementor_conditions', array() );
			}
			if ( $id && $part === get_post_meta( $id, '_elementor_template_type', true ) ) {
				update_post_meta( $id, '_elementor_conditions', array( 'include/general' ) );
			}
			self::regenerate_pro_conditions();
		}
	}

	/**
	 * Back to the theme's own header/footer.
	 *
	 * @param string $part header|footer.
	 */
	public static function deactivate( $part ) {
		$id = self::active( $part );
		if ( $id && self::has_pro() ) {
			update_post_meta( $id, '_elementor_conditions', array() );
			self::regenerate_pro_conditions();
		}
		$map = get_option( self::ACTIVE, array() );
		$map = is_array( $map ) ? $map : array();
		unset( $map[ $part ] );
		update_option( self::ACTIVE, $map );
	}

	/**
	 * Rebuild Elementor Pro's display-conditions cache.
	 */
	private static function regenerate_pro_conditions() {
		try {
			$module = \ElementorPro\Modules\ThemeBuilder\Module::instance();
			if ( method_exists( $module, 'get_conditions_manager' ) ) {
				$manager = $module->get_conditions_manager();
				if ( method_exists( $manager, 'get_cache' ) ) {
					$manager->get_cache()->regenerate();
				}
			}
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch -- Best effort.
		}
	}

	/**
	 * Elementor edit URL.
	 *
	 * @param int $id Template ID.
	 * @return string
	 */
	public static function edit_url( $id ) {
		$document = \Elementor\Plugin::$instance->documents->get( $id );
		return $document ? $document->get_edit_url() : admin_url( 'post.php?post=' . $id . '&action=elementor' );
	}
}
