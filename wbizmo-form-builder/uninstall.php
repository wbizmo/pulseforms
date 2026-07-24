<?php
/**
 * Fires when the plugin is uninstalled and optionally removes plugin data.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$settings = get_option( 'wbizfobu_settings', array() );

$remove_data = isset( $settings['remove_data_on_uninstall'] )
	? (bool) $settings['remove_data_on_uninstall']
	: false;

if ( ! $remove_data ) {
	return;
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Uninstall routine explicitly opted-in by the site admin; removes this plugin's own custom tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wbizfobu_forms" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wbizfobu_submissions" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wbizfobu_logs" );
// phpcs:enable WordPress.DB.DirectDatabaseQuery

delete_option( 'wbizfobu_version' );
delete_option( 'wbizfobu_settings' );

wp_clear_scheduled_hook( 'wbizfobu_daily_cleanup' );
