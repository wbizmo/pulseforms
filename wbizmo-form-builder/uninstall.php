<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$settings = get_option('wbizfobu_settings', []);

$remove_data = isset($settings['remove_data_on_uninstall'])
    ? (bool) $settings['remove_data_on_uninstall']
    : false;

if (!$remove_data) {
    return;
}

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wbizfobu_forms");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wbizfobu_submissions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wbizfobu_logs");

delete_option('wbizfobu_version');
delete_option('wbizfobu_settings');

wp_clear_scheduled_hook('wbizfobu_daily_cleanup');