<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Logger {
    public static function log($severity, $event_type, $message, $context = []) {
        global $wpdb;

        $table = $wpdb->prefix . 'pulseforms_logs';

        $wpdb->insert(
            $table,
            [
                'severity'          => sanitize_text_field($severity),
                'event_type'        => sanitize_text_field($event_type),
                'message'           => sanitize_textarea_field($message),
                'technical_details' => wp_json_encode($context),
                'form_id'           => isset($context['form_id']) ? absint($context['form_id']) : null,
                'form_name'         => isset($context['form_name']) ? sanitize_text_field($context['form_name']) : null,
                'submission_id'     => isset($context['submission_id']) ? absint($context['submission_id']) : null,
                'page_url'          => isset($context['page_url']) ? esc_url_raw($context['page_url']) : null,
                'user_id'           => get_current_user_id() ?: null,
                'user_ip'           => self::get_user_ip(),
                'user_agent'        => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_textarea_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : null,
                'php_version'       => PHP_VERSION,
                'wp_version'        => get_bloginfo('version'),
                'plugin_version'    => PULSEFORMS_VERSION,
                'created_at'        => current_time('mysql'),
            ],
            [
                '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
            ]
        );
    }

    private static function get_user_ip() {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }

        return $ip ? wp_hash($ip) : null;
    }
}