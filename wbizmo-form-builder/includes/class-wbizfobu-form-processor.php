<?php

if (!defined('ABSPATH')) {
    exit;
}

class WBIZFOBU_Form_Processor {
    public function init() {
        add_action('wp_ajax_wbizfobu_submit_form', [$this, 'handle_submission']);
        add_action('wp_ajax_nopriv_wbizfobu_submit_form', [$this, 'handle_submission']);
    }

    public function handle_submission() {
        try {
            $form_id = isset($_POST['wbizfobu_form_id']) ? absint($_POST['wbizfobu_form_id']) : 0;
            $page_url = isset($_POST['wbizfobu_page_url']) ? esc_url_raw(wp_unslash($_POST['wbizfobu_page_url'])) : '';

            if (!$form_id) {
                $this->log_and_fail('warning', 'missing_form_id', 'Submission failed because form ID was missing.', [
                    'page_url' => $page_url,
                ]);
            }

            $nonce = isset($_POST['wbizfobu_nonce']) ? sanitize_text_field(wp_unslash($_POST['wbizfobu_nonce'])) : '';

            if (!$nonce || !wp_verify_nonce($nonce, 'wbizfobu_submit_' . $form_id)) {
                $this->log_and_fail('warning', 'nonce_failed', 'Submission failed nonce verification.', [
                    'form_id'  => $form_id,
                    'page_url' => $page_url,
                ]);
            }

            $honeypot = isset($_POST['wbizfobu_website']) ? sanitize_text_field(wp_unslash($_POST['wbizfobu_website'])) : '';

            if (!empty($honeypot)) {
                $this->log_and_fail('warning', 'honeypot_triggered', 'Submission blocked by honeypot.', [
                    'form_id'  => $form_id,
                    'page_url' => $page_url,
                ]);
            }

            $this->check_rate_limit($form_id, $page_url);

            $form = $this->get_form($form_id);

            if (!$form || $form->status !== 'active') {
                $this->log_and_fail('error', 'form_unavailable', 'Submission failed because form was unavailable.', [
                    'form_id'  => $form_id,
                    'page_url' => $page_url,
                ]);
            }

            $fields = json_decode($form->fields, true);
            $settings = json_decode($form->settings, true);

            if (!is_array($fields)) {
                $this->log_and_fail('error', 'invalid_fields_json', 'Submission failed because form fields JSON is invalid.', [
                    'form_id'    => $form_id,
                    'form_name'  => $form->name,
                    'page_url'   => $page_url,
                    'raw_fields' => $form->fields,
                ]);
            }

            if (!is_array($settings)) {
                $settings = [];
            }

            if (!empty($settings['captcha_enabled'])) {
                $captcha_answer = isset($_POST['wbizfobu_captcha_answer']) ? sanitize_text_field(wp_unslash($_POST['wbizfobu_captcha_answer'])) : '';
                $captcha_hash = isset($_POST['wbizfobu_captcha_hash']) ? sanitize_text_field(wp_unslash($_POST['wbizfobu_captcha_hash'])) : '';

                if ($captcha_answer === '' || $captcha_hash === '' || wp_hash((string) absint($captcha_answer)) !== $captcha_hash) {
                    $this->log_and_fail('warning', 'custom_captcha_failed', 'Submission failed custom captcha verification.', [
                        'form_id'   => $form_id,
                        'form_name' => $form->name,
                        'page_url'  => $page_url,
                    ], __('Please complete the security check and try again.', 'wbizmo-form-builder'));
                }
            }

            $posted_fields = isset($_POST['wbizfobu_fields']) && is_array($_POST['wbizfobu_fields'])
                ? wp_unslash($_POST['wbizfobu_fields'])
                : [];

            $clean_data = [];
            $uploaded_files = [];
            $validation_errors = [];

            foreach ($fields as $field) {
                $field_id = isset($field['id']) ? sanitize_key($field['id']) : '';
                $field_type = isset($field['type']) ? sanitize_key($field['type']) : 'text';
                $label = isset($field['label']) ? sanitize_text_field($field['label']) : $field_id;
                $required = !empty($field['required']);

                if (!$field_id || in_array($field_type, ['html', 'hidden'], true)) {
                    continue;
                }

                if ($field_type === 'file') {
                    $file_result = $this->handle_file_upload($field_id, $label, $required, $form, $page_url);

                    if (is_wp_error($file_result)) {
                        $validation_errors[] = $file_result->get_error_message();
                        continue;
                    }

                    if (!empty($file_result)) {
                        $uploaded_files[$field_id] = $file_result;

                        $clean_data[$field_id] = [
                            'label' => $label,
                            'type'  => $field_type,
                            'value' => isset($file_result['name']) ? $file_result['name'] : '',
                        ];
                    }

                    continue;
                }

                $value = isset($posted_fields[$field_id]) ? $posted_fields[$field_id] : '';

                if (is_array($value)) {
                    $value = array_map('sanitize_text_field', $value);
                    $empty = empty($value);
                } else {
                    $value = $this->sanitize_value_by_type($value, $field_type);
                    $empty = trim((string) $value) === '';
                }

                if ($required && $empty) {
                    $validation_errors[] = $label . ' is required.';
                    continue;
                }

                if ($field_type === 'email' && !$empty && !is_email($value)) {
                    $validation_errors[] = $label . ' must be a valid email address.';
                    continue;
                }

                $clean_data[$field_id] = [
                    'label' => $label,
                    'type'  => $field_type,
                    'value' => $value,
                ];
            }

            if (!empty($validation_errors)) {
                $this->log_and_fail('info', 'validation_failed', 'Submission failed validation.', [
                    'form_id'           => $form_id,
                    'form_name'         => $form->name,
                    'page_url'          => $page_url,
                    'validation_errors' => $validation_errors,
                ], __('Please check the form and try again.', 'wbizmo-form-builder'));
            }

            $save_submissions = isset($settings['save_submissions']) ? (bool) $settings['save_submissions'] : true;
            $admin_email_enabled = isset($settings['admin_email_enabled']) ? (bool) $settings['admin_email_enabled'] : false;
            $user_email_enabled = isset($settings['user_email_enabled']) ? (bool) $settings['user_email_enabled'] : false;

            $submission_id = null;

            if ($save_submissions) {
                $submission_id = $this->save_submission($form, $clean_data, $uploaded_files, $page_url);

                if (!$submission_id) {
                    global $wpdb;

                    $this->log_and_fail('error', 'submission_save_failed', 'Submission could not be saved to the database.', [
                        'form_id'   => $form_id,
                        'form_name' => $form->name,
                        'page_url'  => $page_url,
                        'db_error'  => $wpdb->last_error,
                    ]);
                }
            }

            $emailer = new WBIZFOBU_Emailer();

            if ($admin_email_enabled) {
                $admin_email_result = $emailer->send_admin_notification($form, $submission_id, $clean_data, $page_url);

                if (is_wp_error($admin_email_result)) {
                    $this->log_and_fail('error', 'admin_email_failed', 'Admin notification email failed.', [
                        'form_id'             => $form_id,
                        'form_name'           => $form->name,
                        'submission_id'       => $submission_id,
                        'page_url'            => $page_url,
                        'email_error_code'    => $admin_email_result->get_error_code(),
                        'email_error_message' => $admin_email_result->get_error_message(),
                    ]);
                }
            }

            if ($user_email_enabled) {
                $user_email_result = $emailer->send_user_confirmation($form, $submission_id, $clean_data, $page_url);

                if (is_wp_error($user_email_result)) {
                    $this->log_and_fail('error', 'user_email_failed', 'User confirmation email failed.', [
                        'form_id'             => $form_id,
                        'form_name'           => $form->name,
                        'submission_id'       => $submission_id,
                        'page_url'            => $page_url,
                        'email_error_code'    => $user_email_result->get_error_code(),
                        'email_error_message' => $user_email_result->get_error_message(),
                    ]);
                }
            }

            wp_send_json_success([
                'message' => isset($settings['success_message'])
                    ? sanitize_text_field($settings['success_message'])
                    : __('Thank you. Your submission has been received.', 'wbizmo-form-builder'),
                'submission_id' => $submission_id,
            ]);

        } catch (Throwable $e) {
            WBIZFOBU_Logger::log(
                'critical',
                'unexpected_php_error',
                'Unexpected PHP error during form submission.',
                [
                    'error_message' => $e->getMessage(),
                    'error_file'    => $e->getFile(),
                    'error_line'    => $e->getLine(),
                    'error_trace'   => $e->getTraceAsString(),
                    'php_version'   => PHP_VERSION,
                    'wp_version'    => get_bloginfo('version'),
                ]
            );

            wp_send_json_error([
                'message' => __('Something unexpected went wrong. Please try again later.', 'wbizmo-form-builder'),
            ], 500);
        }
    }

    private function get_form($form_id) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wbizfobu_forms WHERE id = %d",
                $form_id
            )
        );
    }

    private function get_plugin_settings() {
        $defaults = [
            'upload_max_size'     => 5,
            'allowed_file_types'  => 'jpg,jpeg,png,gif,pdf,doc,docx,txt',
            'rate_limit_attempts' => 5,
            'rate_limit_window'   => 10,
            'log_retention_days'  => 30,
        ];

        $settings = get_option('wbizfobu_settings', []);

        if (!is_array($settings)) {
            $settings = [];
        }

        return wp_parse_args($settings, $defaults);
    }

    private function sanitize_value_by_type($value, $type) {
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }

        $value = wp_unslash($value);

        switch ($type) {
            case 'email':
                return sanitize_email($value);

            case 'textarea':
                return sanitize_textarea_field($value);

            case 'number':
                return is_numeric($value) ? $value + 0 : '';

            case 'url':
                return esc_url_raw($value);

            default:
                return sanitize_text_field($value);
        }
    }

    private function handle_file_upload($field_id, $label, $required, $form, $page_url) {
        $input_name = 'wbizfobu_fields';

        if (
            empty($_FILES[$input_name]) ||
            empty($_FILES[$input_name]['name'][$field_id])
        ) {
            if ($required) {
                return new WP_Error('required_file_missing', $label . ' is required.');
            }

            return null;
        }

        $plugin_settings = $this->get_plugin_settings();

        $file_name = sanitize_file_name(wp_unslash($_FILES[$input_name]['name'][$field_id]));
        $file_type = isset($_FILES[$input_name]['type'][$field_id]) ? sanitize_text_field(wp_unslash($_FILES[$input_name]['type'][$field_id])) : '';
        $tmp_name = isset($_FILES[$input_name]['tmp_name'][$field_id]) ? $_FILES[$input_name]['tmp_name'][$field_id] : '';
        $error = isset($_FILES[$input_name]['error'][$field_id]) ? absint($_FILES[$input_name]['error'][$field_id]) : UPLOAD_ERR_NO_FILE;
        $size = isset($_FILES[$input_name]['size'][$field_id]) ? absint($_FILES[$input_name]['size'][$field_id]) : 0;

        if ($error !== UPLOAD_ERR_OK) {
            WBIZFOBU_Logger::log('error', 'file_upload_error', 'File upload failed with PHP upload error.', [
                'form_id'      => $form->id,
                'form_name'    => $form->name,
                'page_url'     => $page_url,
                'field_id'     => $field_id,
                'field_label'  => $label,
                'upload_error' => $error,
            ]);

            return new WP_Error('file_upload_error', $label . ' could not be uploaded.');
        }

        $max_size_mb = isset($plugin_settings['upload_max_size']) ? absint($plugin_settings['upload_max_size']) : 5;
        $max_size = max(1, $max_size_mb) * 1024 * 1024;

        if ($size > $max_size) {
            return new WP_Error('file_too_large', $label . ' is too large.');
        }

        $allowed_mimes = $this->get_allowed_mimes_from_settings($plugin_settings);

        $file_check = wp_check_filetype_and_ext($tmp_name, $file_name, $allowed_mimes);

        if (empty($file_check['type'])) {
            return new WP_Error('invalid_file_type', $label . ' file type is not allowed.');
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $uploaded = wp_handle_upload(
            [
                'name'     => $file_name,
                'type'     => $file_type,
                'tmp_name' => $tmp_name,
                'error'    => $error,
                'size'     => $size,
            ],
            [
                'test_form' => false,
                'mimes'     => $allowed_mimes,
            ]
        );

        if (isset($uploaded['error'])) {
            WBIZFOBU_Logger::log('error', 'file_upload_failed', 'WordPress file upload handler failed.', [
                'form_id'      => $form->id,
                'form_name'    => $form->name,
                'page_url'     => $page_url,
                'field_id'     => $field_id,
                'field_label'  => $label,
                'upload_error' => $uploaded['error'],
            ]);

            return new WP_Error('file_upload_failed', $label . ' could not be uploaded.');
        }

        return [
            'field_id' => $field_id,
            'label'    => $label,
            'name'     => basename($uploaded['file']),
            'url'      => esc_url_raw($uploaded['url']),
            'path'     => sanitize_text_field($uploaded['file']),
            'type'     => sanitize_text_field($uploaded['type']),
            'size'     => $size,
        ];
    }

    private function get_allowed_mimes_from_settings($settings) {
        $allowed = isset($settings['allowed_file_types'])
            ? strtolower(sanitize_text_field($settings['allowed_file_types']))
            : 'jpg,jpeg,png,gif,pdf,doc,docx,txt';

        $requested_types = array_filter(array_map('trim', explode(',', $allowed)));

        $mime_map = [
            'jpg'  => ['jpg|jpeg|jpe', 'image/jpeg'],
            'jpeg' => ['jpg|jpeg|jpe', 'image/jpeg'],
            'png'  => ['png', 'image/png'],
            'gif'  => ['gif', 'image/gif'],
            'pdf'  => ['pdf', 'application/pdf'],
            'doc'  => ['doc', 'application/msword'],
            'docx' => ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt'  => ['txt', 'text/plain'],
        ];

        $mimes = [];

        foreach ($requested_types as $type) {
            if (!isset($mime_map[$type])) {
                continue;
            }

            $mimes[$mime_map[$type][0]] = $mime_map[$type][1];
        }

        if (empty($mimes)) {
            $mimes = [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png'          => 'image/png',
                'gif'          => 'image/gif',
                'pdf'          => 'application/pdf',
                'doc'          => 'application/msword',
                'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt'          => 'text/plain',
            ];
        }

        return $mimes;
    }

    private function save_submission($form, $clean_data, $uploaded_files, $page_url) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'wbizfobu_submissions',
            [
                'form_id'         => absint($form->id),
                'form_name'       => sanitize_text_field($form->name),
                'submission_data' => wp_json_encode($clean_data),
                'files'           => wp_json_encode($uploaded_files),
                'user_id'         => get_current_user_id() ?: null,
                'user_ip'         => $this->get_user_ip_hash(),
                'user_agent'      => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_textarea_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : null,
                'page_url'        => $page_url,
                'status'          => 'unread',
                'created_at'      => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        if (!$inserted) {
            return false;
        }

        return $wpdb->insert_id;
    }

    private function log_and_fail($severity, $event_type, $message, $context = [], $public_message = null) {
        WBIZFOBU_Logger::log($severity, $event_type, $message, $context);

        wp_send_json_error([
            'message' => $public_message ?: __('Something went wrong. Please try again.', 'wbizmo-form-builder'),
        ], 400);
    }

    private function check_rate_limit($form_id, $page_url) {
        $ip_hash = $this->get_user_ip_hash();

        if (!$ip_hash) {
            return;
        }

        $plugin_settings = $this->get_plugin_settings();

        $max_attempts = isset($plugin_settings['rate_limit_attempts'])
            ? max(1, absint($plugin_settings['rate_limit_attempts']))
            : 5;

        $window_minutes = isset($plugin_settings['rate_limit_window'])
            ? max(1, absint($plugin_settings['rate_limit_window']))
            : 10;

        $key = 'wbizfobu_rate_' . md5($form_id . '_' . $ip_hash);
        $count = (int) get_transient($key);

        if ($count >= $max_attempts) {
            $this->log_and_fail('warning', 'rate_limit_triggered', 'Submission blocked by rate limiting.', [
                'form_id'      => $form_id,
                'page_url'     => $page_url,
                'ip_hash'      => $ip_hash,
                'max_attempts' => $max_attempts,
                'window'       => $window_minutes,
            ], __('Too many attempts. Please try again later.', 'wbizmo-form-builder'));
        }

        set_transient($key, $count + 1, $window_minutes * MINUTE_IN_SECONDS);
    }

    private function get_user_ip_hash() {
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