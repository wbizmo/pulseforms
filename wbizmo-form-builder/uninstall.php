<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$settings = get_option('wbizmo_form_builder_settings', []);

$remove_data = isset($settings['remove_data_on_uninstall'])
    ? (bool) $settings['remove_data_on_uninstall']
    : false;

if (!$remove_data) {
    return;
}

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wbizmo_form_builder_forms");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wbizmo_form_builder_submissions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wbizmo_form_builder_logs");

delete_option('wbizmo_form_builder_version');
delete_option('wbizmo_form_builder_settings');

wp_clear_scheduled_hook('wbizmo_form_builder_daily_cleanup');