<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Activator {
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $forms_table = $wpdb->prefix . 'pulseforms_forms';
        $submissions_table = $wpdb->prefix . 'pulseforms_submissions';
        $logs_table = $wpdb->prefix . 'pulseforms_logs';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_forms = "CREATE TABLE $forms_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            type VARCHAR(80) NOT NULL DEFAULT 'custom',
            fields LONGTEXT NULL,
            settings LONGTEXT NULL,
            style_settings LONGTEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql_submissions = "CREATE TABLE $submissions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id BIGINT(20) UNSIGNED NOT NULL,
            form_name VARCHAR(191) NULL,
            submission_data LONGTEXT NULL,
            files LONGTEXT NULL,
            user_id BIGINT(20) UNSIGNED NULL,
            user_ip VARCHAR(100) NULL,
            user_agent TEXT NULL,
            page_url TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'unread',
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";

        $sql_logs = "CREATE TABLE $logs_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            severity VARCHAR(30) NOT NULL DEFAULT 'info',
            event_type VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            technical_details LONGTEXT NULL,
            form_id BIGINT(20) UNSIGNED NULL,
            form_name VARCHAR(191) NULL,
            submission_id BIGINT(20) UNSIGNED NULL,
            page_url TEXT NULL,
            user_id BIGINT(20) UNSIGNED NULL,
            user_ip VARCHAR(100) NULL,
            user_agent TEXT NULL,
            php_version VARCHAR(50) NULL,
            wp_version VARCHAR(50) NULL,
            plugin_version VARCHAR(50) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY severity (severity),
            KEY event_type (event_type),
            KEY form_id (form_id)
        ) $charset_collate;";

        dbDelta($sql_forms);
        dbDelta($sql_submissions);
        dbDelta($sql_logs);

        add_option('pulseforms_version', PULSEFORMS_VERSION);

        if (!get_option('pulseforms_settings')) {
            add_option('pulseforms_settings', [
                'upload_max_size'     => 5,
                'allowed_file_types'  => 'jpg,jpeg,png,gif,pdf,doc,docx,txt',
                'rate_limit_attempts' => 5,
                'rate_limit_window'   => 10,
                'log_retention_days'  => 30,
            ]);
        }

        if (!wp_next_scheduled('pulseforms_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'pulseforms_daily_cleanup');
        }
    }
}