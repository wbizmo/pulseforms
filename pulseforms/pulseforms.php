<?php
/**
 * Plugin Name: PulseForms
 * Plugin URI: https://github.com/wbizmo/pulseforms
 * Description: A modern, customizable WordPress form builder plugin with submissions, styled forms, email notifications, logs, and shortcode support.
 * Version: 1.0.0
 * Author: Williams
 * Author URI: https://github.com/wbizmo
 * License: MIT
 * Text Domain: pulseforms
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PULSEFORMS_VERSION', '1.0.0');
define('PULSEFORMS_FILE', __FILE__);
define('PULSEFORMS_PATH', plugin_dir_path(__FILE__));
define('PULSEFORMS_URL', plugin_dir_url(__FILE__));
define('PULSEFORMS_BASENAME', plugin_basename(__FILE__));

require_once PULSEFORMS_PATH . 'includes/class-pulseforms-activator.php';
require_once PULSEFORMS_PATH . 'includes/class-pulseforms-deactivator.php';
require_once PULSEFORMS_PATH . 'includes/class-pulseforms-admin.php';
require_once PULSEFORMS_PATH . 'includes/class-pulseforms-logger.php';
require_once PULSEFORMS_PATH . 'includes/class-pulseforms-emailer.php';
require_once PULSEFORMS_PATH . 'includes/class-pulseforms-form-renderer.php';
require_once PULSEFORMS_PATH . 'includes/class-pulseforms-form-processor.php';

register_activation_hook(__FILE__, ['PulseForms_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['PulseForms_Deactivator', 'deactivate']);

function pulseforms_run() {
    $admin = new PulseForms_Admin();
    $admin->init();

    $renderer = new PulseForms_Form_Renderer();
    $renderer->init();

    $processor = new PulseForms_Form_Processor();
    $processor->init();

    add_action('pulseforms_daily_cleanup', 'pulseforms_cleanup_old_logs');
}

function pulseforms_cleanup_old_logs() {
    global $wpdb;

    $settings = get_option('pulseforms_settings', []);
    $days = isset($settings['log_retention_days']) ? absint($settings['log_retention_days']) : 30;
    $days = max(1, min(365, $days));

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}pulseforms_logs WHERE created_at < DATE_SUB(%s, INTERVAL %d DAY)",
            current_time('mysql'),
            $days
        )
    );
}

pulseforms_run();