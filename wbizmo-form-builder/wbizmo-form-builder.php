<?php
/**
 * Plugin Name: Wbizmo Form Builder
 * Plugin URI: https://github.com/wbizmo/wbizfobu
 * Description: A modern, customizable WordPress form builder plugin with submissions, styled forms, email notifications, logs, and shortcode support.
 * Version: 1.0.4
 * Author: Williams Ashibuogwu
 * Author URI: https://github.com/wbizmo
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wbizmo-form-builder
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WBIZFOBU_VERSION', '1.0.4');
define('WBIZFOBU_FILE', __FILE__);
define('WBIZFOBU_PATH', plugin_dir_path(__FILE__));
define('WBIZFOBU_URL', plugin_dir_url(__FILE__));
define('WBIZFOBU_BASENAME', plugin_basename(__FILE__));

require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-activator.php';
require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-deactivator.php';
require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-admin.php';
require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-logger.php';
require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-emailer.php';
require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-form-renderer.php';
require_once WBIZFOBU_PATH . 'includes/class-wbizfobu-form-processor.php';

register_activation_hook(__FILE__, ['WBIZFOBU_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['WBIZFOBU_Deactivator', 'deactivate']);

function wbizfobu_run() {
    $admin = new WBIZFOBU_Admin();
    $admin->init();

    $renderer = new WBIZFOBU_Form_Renderer();
    $renderer->init();

    $processor = new WBIZFOBU_Form_Processor();
    $processor->init();

    add_action('wbizfobu_daily_cleanup', 'wbizfobu_cleanup_old_logs');
}

function wbizfobu_cleanup_old_logs() {
    global $wpdb;

    $settings = get_option('wbizfobu_settings', []);
    $days = isset($settings['log_retention_days']) ? absint($settings['log_retention_days']) : 30;
    $days = max(1, min(365, $days));

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wbizfobu_logs WHERE created_at < DATE_SUB(%s, INTERVAL %d DAY)",
            current_time('mysql'),
            $days
        )
    );
}

wbizfobu_run();
