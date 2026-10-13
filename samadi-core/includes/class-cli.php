<?php
/**
 * WP-CLI commands.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Samadi Electric setup from the command line.
 */
class Samadi_Core_CLI {

	/**
	 * Install and activate WooCommerce, Elementor and Samadi Elementor Widgets.
	 *
	 * ## EXAMPLES
	 *
	 *     wp samadi plugins --user=admin
	 *
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function plugins( $args, $assoc_args ) {
		foreach ( Samadi_Core_Plugins::required() as $slug => $plugin ) {
			$result = Samadi_Core_Plugins::install_and_activate( $slug );
			if ( is_wp_error( $result ) ) {
				WP_CLI::warning( $plugin['name'] . ': ' . $result->get_error_message() );
			} else {
				WP_CLI::log( $plugin['name'] . ': active' );
			}
		}
	}

	/**
	 * Import the full demo.
	 *
	 * ## OPTIONS
	 *
	 * [--step=<step>]
	 * : Run a single step only.
	 *
	 * ## EXAMPLES
	 *
	 *     wp samadi import --user=admin
	 *
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function import( $args, $assoc_args ) {
		$steps = isset( $assoc_args['step'] ) ? array( $assoc_args['step'] => $assoc_args['step'] ) : Samadi_Core_Demo_Importer::steps();
		foreach ( $steps as $step => $label ) {
			try {
				WP_CLI::log( $label . ': ' . Samadi_Core_Demo_Importer::run_step( $step ) );
			} catch ( Throwable $e ) {
				WP_CLI::error( $label . ': ' . $e->getMessage() );
			}
		}
		WP_CLI::success( 'Demo imported.' );
	}

	/**
	 * Remove everything the demo import created.
	 *
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function remove( $args, $assoc_args ) {
		WP_CLI::success( Samadi_Core_Demo_Importer::remove() . ' objects removed.' );
	}
}
