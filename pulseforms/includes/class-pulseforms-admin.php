<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Admin {
    public function init() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_post_pulseforms_create_form', [$this, 'handle_create_form']);
        add_action('admin_post_pulseforms_delete_form', [$this, 'handle_delete_form']);
        add_action('admin_post_pulseforms_delete_submission', [$this, 'handle_delete_submission']);
        add_action('admin_post_pulseforms_mark_submission_read', [$this, 'handle_mark_submission_read']);
        add_action('admin_post_pulseforms_delete_log', [$this, 'handle_delete_log']);
        add_action('admin_post_pulseforms_clear_logs', [$this, 'handle_clear_logs']);
    }

    public function register_admin_menu() {
        add_menu_page(__('PulseForms', 'pulseforms'), __('PulseForms', 'pulseforms'), 'manage_options', 'pulseforms', [$this, 'render_forms_page'], 'dashicons-feedback', 26);

        add_submenu_page('pulseforms', __('All Forms', 'pulseforms'), __('All Forms', 'pulseforms'), 'manage_options', 'pulseforms', [$this, 'render_forms_page']);
        add_submenu_page('pulseforms', __('Add New', 'pulseforms'), __('Add New', 'pulseforms'), 'manage_options', 'pulseforms-add-new', [$this, 'render_add_new_page']);
        add_submenu_page('pulseforms', __('Submissions', 'pulseforms'), __('Submissions', 'pulseforms'), 'manage_options', 'pulseforms-submissions', [$this, 'render_submissions_page']);
        add_submenu_page('pulseforms', __('Logs', 'pulseforms'), __('Logs', 'pulseforms'), 'manage_options', 'pulseforms-logs', [$this, 'render_logs_page']);
        add_submenu_page('pulseforms', __('Settings', 'pulseforms'), __('Settings', 'pulseforms'), 'manage_options', 'pulseforms-settings', [$this, 'render_settings_page']);
        add_submenu_page('pulseforms', __('Support', 'pulseforms'), __('Support', 'pulseforms'), 'manage_options', 'pulseforms-support', [$this, 'render_support_page']);
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

    public function get_forms() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pulseforms_forms ORDER BY created_at DESC");
    }

    public function get_form($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pulseforms_forms WHERE id = %d", absint($id)));
    }

    public function get_submissions() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pulseforms_submissions ORDER BY created_at DESC LIMIT 200");
    }

    public function get_submission($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pulseforms_submissions WHERE id = %d", absint($id)));
    }

    public function get_logs() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pulseforms_logs ORDER BY created_at DESC LIMIT 300");
    }

    public function get_log($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pulseforms_logs WHERE id = %d", absint($id)));
    }

    public function handle_create_form() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to create forms.', 'pulseforms'));
        }

        check_admin_referer('pulseforms_create_form');

        global $wpdb;

        $name = isset($_POST['form_name']) ? sanitize_text_field(wp_unslash($_POST['form_name'])) : '';
        $type = isset($_POST['form_type']) ? sanitize_key(wp_unslash($_POST['form_type'])) : 'custom';
        $theme = isset($_POST['form_theme']) ? sanitize_key(wp_unslash($_POST['form_theme'])) : 'aurora';

        if (empty($name)) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms-add-new&pf_error=missing_name'));
            exit;
        }

        $allowed_types = ['contact', 'newsletter', 'subscription', 'multi_step', 'registration', 'login', 'custom'];
        if (!in_array($type, $allowed_types, true)) {
            $type = 'custom';
        }

        $allowed_themes = ['aurora', 'noir', 'solace'];
        if (!in_array($theme, $allowed_themes, true)) {
            $theme = 'aurora';
        }

        $settings = [
            'admin_email_enabled' => !in_array($type, ['login', 'registration'], true),
            'user_email_enabled'  => !in_array($type, ['login', 'registration'], true),
            'save_submissions'    => true,
            'honeypot_enabled'    => true,
            'captcha_enabled'     => false,
            'success_message'     => __('Thank you. Your submission has been received.', 'pulseforms'),
            'error_message'       => __('Something went wrong. Please try again.', 'pulseforms'),
            'submit_text'         => $this->get_default_submit_text($type),
        ];

        $style_settings = [
            'theme'          => $theme,
            'style_mode'     => 'pulse',
            'primary_color'  => '#0E2238',
            'accent_color'   => '#C5A572',
            'button_radius'  => '14',
            'field_radius'   => '14',
            'custom_css'     => '',
        ];

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'pulseforms_forms',
            [
                'name'           => $name,
                'type'           => $type,
                'fields'         => wp_json_encode($this->get_default_fields_for_type($type)),
                'settings'       => wp_json_encode($settings),
                'style_settings' => wp_json_encode($style_settings),
                'status'         => 'active',
                'created_at'     => current_time('mysql'),
                'updated_at'     => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if (!$inserted) {
            PulseForms_Logger::log('error', 'form_create_failed', 'PulseForms could not create the form.', [
                'form_name' => $name,
                'form_type' => $type,
                'db_error'  => $wpdb->last_error,
            ]);

            wp_safe_redirect(admin_url('admin.php?page=pulseforms-add-new&pf_error=create_failed'));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=pulseforms&pf_created=1'));
        exit;
    }

    public function handle_delete_form() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to delete forms.', 'pulseforms'));
        }

        $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

        if (!$form_id) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms&pf_error=missing_form'));
            exit;
        }

        check_admin_referer('pulseforms_delete_form_' . $form_id);

        global $wpdb;

        $form = $this->get_form($form_id);

        if (!$form) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms&pf_error=form_not_found'));
            exit;
        }

        $deleted = $wpdb->delete($wpdb->prefix . 'pulseforms_forms', ['id' => $form_id], ['%d']);

        if (!$deleted) {
            PulseForms_Logger::log('error', 'form_delete_failed', 'PulseForms could not delete the form.', [
                'form_id'   => $form_id,
                'form_name' => $form->name,
                'db_error'  => $wpdb->last_error,
            ]);

            wp_safe_redirect(admin_url('admin.php?page=pulseforms&pf_error=delete_failed'));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=pulseforms&pf_deleted=1'));
        exit;
    }

    public function handle_delete_submission() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to delete submissions.', 'pulseforms'));
        }

        $submission_id = isset($_GET['submission_id']) ? absint($_GET['submission_id']) : 0;

        if (!$submission_id) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_error=missing_submission'));
            exit;
        }

        check_admin_referer('pulseforms_delete_submission_' . $submission_id);

        global $wpdb;

        $submission = $this->get_submission($submission_id);

        if (!$submission) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_error=submission_not_found'));
            exit;
        }

        $deleted = $wpdb->delete($wpdb->prefix . 'pulseforms_submissions', ['id' => $submission_id], ['%d']);

        if (!$deleted) {
            PulseForms_Logger::log('error', 'submission_delete_failed', 'PulseForms could not delete the submission.', [
                'submission_id' => $submission_id,
                'db_error'      => $wpdb->last_error,
            ]);

            wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_error=delete_failed'));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_deleted=1'));
        exit;
    }

    public function handle_mark_submission_read() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to update submissions.', 'pulseforms'));
        }

        $submission_id = isset($_GET['submission_id']) ? absint($_GET['submission_id']) : 0;

        if (!$submission_id) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_error=missing_submission'));
            exit;
        }

        check_admin_referer('pulseforms_mark_submission_read_' . $submission_id);

        global $wpdb;

        $updated = $wpdb->update(
            $wpdb->prefix . 'pulseforms_submissions',
            ['status' => 'read'],
            ['id' => $submission_id],
            ['%s'],
            ['%d']
        );

        if ($updated === false) {
            PulseForms_Logger::log('error', 'submission_mark_read_failed', 'PulseForms could not mark the submission as read.', [
                'submission_id' => $submission_id,
                'db_error'      => $wpdb->last_error,
            ]);

            wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_error=mark_read_failed'));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=pulseforms-submissions&pf_read=1'));
        exit;
    }

    public function handle_delete_log() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to delete logs.', 'pulseforms'));
        }

        $log_id = isset($_GET['log_id']) ? absint($_GET['log_id']) : 0;

        if (!$log_id) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms-logs&pf_error=missing_log'));
            exit;
        }

        check_admin_referer('pulseforms_delete_log_' . $log_id);

        global $wpdb;

        $deleted = $wpdb->delete($wpdb->prefix . 'pulseforms_logs', ['id' => $log_id], ['%d']);

        if (!$deleted) {
            wp_safe_redirect(admin_url('admin.php?page=pulseforms-logs&pf_error=delete_failed'));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=pulseforms-logs&pf_deleted=1'));
        exit;
    }

    public function handle_clear_logs() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to clear logs.', 'pulseforms'));
        }

        check_admin_referer('pulseforms_clear_logs');

        global $wpdb;

        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}pulseforms_logs");

        wp_safe_redirect(admin_url('admin.php?page=pulseforms-logs&pf_cleared=1'));
        exit;
    }

    private function get_default_submit_text($type) {
        $map = [
            'contact'      => __('Send Message', 'pulseforms'),
            'newsletter'   => __('Subscribe', 'pulseforms'),
            'subscription' => __('Subscribe', 'pulseforms'),
            'multi_step'   => __('Submit Form', 'pulseforms'),
            'registration' => __('Create Account', 'pulseforms'),
            'login'        => __('Login', 'pulseforms'),
            'custom'       => __('Submit', 'pulseforms'),
        ];

        return $map[$type] ?? __('Submit', 'pulseforms');
    }

    private function get_default_fields_for_type($type) {
        $base_name = [
            [
                'id'          => 'name',
                'type'        => 'text',
                'label'       => __('Name', 'pulseforms'),
                'placeholder' => __('Enter your name', 'pulseforms'),
                'required'    => true,
                'width'       => 'full',
            ],
        ];

        $email = [
            [
                'id'          => 'email',
                'type'        => 'email',
                'label'       => __('Email Address', 'pulseforms'),
                'placeholder' => __('Enter your email address', 'pulseforms'),
                'required'    => true,
                'width'       => 'full',
            ],
        ];

        if ($type === 'contact') {
            return array_merge($base_name, $email, [
                [
                    'id'          => 'message',
                    'type'        => 'textarea',
                    'label'       => __('Message', 'pulseforms'),
                    'placeholder' => __('Write your message', 'pulseforms'),
                    'required'    => true,
                    'width'       => 'full',
                ],
            ]);
        }

        if (in_array($type, ['newsletter', 'subscription'], true)) {
            return $email;
        }

        if ($type === 'registration') {
            return array_merge($base_name, $email, [
                [
                    'id'          => 'password',
                    'type'        => 'password',
                    'label'       => __('Password', 'pulseforms'),
                    'placeholder' => __('Create a password', 'pulseforms'),
                    'required'    => true,
                    'width'       => 'full',
                ],
            ]);
        }

        if ($type === 'login') {
            return [
                [
                    'id'          => 'username',
                    'type'        => 'text',
                    'label'       => __('Username or Email', 'pulseforms'),
                    'placeholder' => __('Enter username or email', 'pulseforms'),
                    'required'    => true,
                    'width'       => 'full',
                ],
                [
                    'id'          => 'password',
                    'type'        => 'password',
                    'label'       => __('Password', 'pulseforms'),
                    'placeholder' => __('Enter password', 'pulseforms'),
                    'required'    => true,
                    'width'       => 'full',
                ],
            ];
        }

        return array_merge($base_name, $email);
    }

    public function render_forms_page() {
        $forms = $this->get_forms();
        require PULSEFORMS_PATH . 'admin/views/forms.php';
    }

    public function render_add_new_page() {
        require PULSEFORMS_PATH . 'admin/views/add-new.php';
    }

    public function render_submissions_page() {
        $submissions = $this->get_submissions();
        require PULSEFORMS_PATH . 'admin/views/submissions.php';
    }

    public function render_logs_page() {
        $logs = $this->get_logs();
        require PULSEFORMS_PATH . 'admin/views/logs.php';
    }

    public function render_settings_page() {
        require PULSEFORMS_PATH . 'admin/views/settings.php';
    }

    public function render_support_page() {
        require PULSEFORMS_PATH . 'admin/views/support.php';
    }
}