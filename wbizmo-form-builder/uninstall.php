<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$settings = get_option('pulseforms_settings', []);

$remove_data = isset($settings['remove_data_on_uninstall'])
    ? (bool) $settings['remove_data_on_uninstall']
    : false;

if (!$remove_data) {
    return;
}

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pulseforms_forms");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pulseforms_submissions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pulseforms_logs");

delete_option('pulseforms_version');
delete_option('pulseforms_settings');

wp_clear_scheduled_hook('pulseforms_daily_cleanup');