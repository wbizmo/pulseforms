<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Admin {
    public function init() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_admin_menu() {
        add_menu_page(
            __('PulseForms', 'pulseforms'),
            __('PulseForms', 'pulseforms'),
            'manage_options',
            'pulseforms',
            [$this, 'render_forms_page'],
            'dashicons-feedback',
            26
        );

        add_submenu_page(
            'pulseforms',
            __('All Forms', 'pulseforms'),
            __('All Forms', 'pulseforms'),
            'manage_options',
            'pulseforms',
            [$this, 'render_forms_page']
        );

        add_submenu_page(
            'pulseforms',
            __('Add New', 'pulseforms'),
            __('Add New', 'pulseforms'),
            'manage_options',
            'pulseforms-add-new',
            [$this, 'render_add_new_page']
        );

        add_submenu_page(
            'pulseforms',
            __('Submissions', 'pulseforms'),
            __('Submissions', 'pulseforms'),
            'manage_options',
            'pulseforms-submissions',
            [$this, 'render_submissions_page']
        );

        add_submenu_page(
            'pulseforms',
            __('Logs', 'pulseforms'),
            __('Logs', 'pulseforms'),
            'manage_options',
            'pulseforms-logs',
            [$this, 'render_logs_page']
        );

        add_submenu_page(
            'pulseforms',
            __('Settings', 'pulseforms'),
            __('Settings', 'pulseforms'),
            'manage_options',
            'pulseforms-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'pulseforms',
            __('Support', 'pulseforms'),
            __('Support', 'pulseforms'),
            'manage_options',
            'pulseforms-support',
            [$this, 'render_support_page']
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'pulseforms') === false) {
            return;
        }

        wp_enqueue_style(
            'pulseforms-material-icons',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,200,0,0',
            [],
            null
        );

        wp_enqueue_style(
            'pulseforms-admin',
            PULSEFORMS_URL . 'assets/css/admin.css',
            [],
            PULSEFORMS_VERSION
        );

        wp_enqueue_script(
            'pulseforms-admin',
            PULSEFORMS_URL . 'assets/js/admin.js',
            ['jquery'],
            PULSEFORMS_VERSION,
            true
        );
    }

    public function render_forms_page() {
        require PULSEFORMS_PATH . 'admin/views/forms.php';
    }

    public function render_add_new_page() {
        require PULSEFORMS_PATH . 'admin/views/add-new.php';
    }

    public function render_submissions_page() {
        require PULSEFORMS_PATH . 'admin/views/submissions.php';
    }

    public function render_logs_page() {
        require PULSEFORMS_PATH . 'admin/views/logs.php';
    }

    public function render_settings_page() {
        require PULSEFORMS_PATH . 'admin/views/settings.php';
    }

    public function render_support_page() {
        require PULSEFORMS_PATH . 'admin/views/support.php';
    }
}