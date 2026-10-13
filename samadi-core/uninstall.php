<?php
/**
 * Uninstall: remove plugin data only when the site owner opted in (Samadi → Settings → Advanced).
 *
 * @package Samadi_Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$samadi_core_settings = get_option( 'samadi_core_settings', array() );

if ( empty( $samadi_core_settings['delete_data'] ) ) {
	return;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}samadi_subscribers" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
delete_metadata( 'user', 0, 'samadi_wishlist', '', true );
delete_option( 'samadi_core_settings' );
delete_option( 'samadi_core_db_version' );
