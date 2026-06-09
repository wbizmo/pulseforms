<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Form_Processor {
    public function init() {
        add_action('wp_ajax_pulseforms_submit_form', [$this, 'handle_submission']);
        add_action('wp_ajax_nopriv_pulseforms_submit_form', [$this, 'handle_submission']);
    }

    public function handle_submission() {
        try {
            $form_id = isset($_POST['pulseforms_form_id']) ? absint($_POST['pulseforms_form_id']) : 0;
            $page_url = isset($_POST['pulseforms_page_url']) ? esc_url_raw(wp_unslash($_POST['pulseforms_page_url'])) : '';

            if (!$form_id) {
                $this->log_and_fail('warning', 'missing_form_id', 'Submission failed because form ID was missing.', [
                    'page_url' => $page_url,
                ]);
            }

            $nonce = isset($_POST['pulseforms_nonce']) ? sanitize_text_field(wp_unslash($_POST['pulseforms_nonce'])) : '';

            if (!$nonce || !wp_verify_nonce($nonce, 'pulseforms_submit_' . $form_id)) {
                $this->log_and_fail('warning', 'nonce_failed', 'Submission failed nonce verification.', [
                    'form_id' => $form_id,
                    'page_url' => $page_url,
                ]);
            }

            $honeypot = isset($_POST['pulseforms_website']) ? sanitize_text_field(wp_unslash($_POST['pulseforms_website'])) : '';

            if (!empty($honeypot)) {
                $this->log_and_fail('warning', 'honeypot_triggered', 'Submission blocked by honeypot.', [
                    'form_id' => $form_id,
                    'page_url' => $page_url,
                ]);
            }

            $form = $this->get_form($form_id);

            if (!$form || $form->status !== 'active') {
                $this->log_and_fail('error', 'form_unavailable', 'Submission failed because form was unavailable.', [
                    'form_id' => $form_id,
                    'page_url' => $page_url,
                ]);
            }

            $fields = json_decode($form->fields, true);
            $settings = json_decode($form->settings, true);

            if (!is_array($fields)) {
                $this->log_and_fail('error', 'invalid_fields_json', 'Submission failed because form fields JSON is invalid.', [
                    'form_id' => $form_id,
                    'form_name' => $form->name,
                    'page_url' => $page_url,
                    'raw_fields' => $form->fields,
                ]);
            }

            $posted_fields = isset($_POST['pulseforms_fields']) && is_array($_POST['pulseforms_fields'])
                ? wp_unslash($_POST['pulseforms_fields'])
                : [];

            $clean_data = [];
            $validation_errors = [];

            foreach ($fields as $field) {
                $field_id = isset($field['id']) ? sanitize_key($field['id']) : '';
                $field_type = isset($field['type']) ? sanitize_key($field['type']) : 'text';
                $label = isset($field['label']) ? sanitize_text_field($field['label']) : $field_id;
                $required = !empty($field['required']);

                if (!$field_id || in_array($field_type, ['html', 'hidden'], true)) {
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
                    'form_id' => $form_id,
                    'form_name' => $form->name,
                    'page_url' => $page_url,
                    'validation_errors' => $validation_errors,
                ], __('Please check the form and try again.', 'pulseforms'));
            }

            $save_submissions = isset($settings['save_submissions']) ? (bool) $settings['save_submissions'] : true;

            $submission_id = null;

            if ($save_submissions) {
                $submission_id = $this->save_submission($form, $clean_data, $page_url);

                if (!$submission_id) {
                    global $wpdb;

                    $this->log_and_fail('error', 'submission_save_failed', 'Submission could not be saved to the database.', [
                        'form_id' => $form_id,
                        'form_name' => $form->name,
                        'page_url' => $page_url,
                        'db_error' => $wpdb->last_error,
                    ]);
                }
            }

            wp_send_json_success([
                'message' => isset($settings['success_message'])
                    ? sanitize_text_field($settings['success_message'])
                    : __('Thank you. Your submission has been received.', 'pulseforms'),
                'submission_id' => $submission_id,
            ]);

        } catch (Throwable $e) {
            PulseForms_Logger::log(
                'critical',
                'unexpected_php_error',
                'Unexpected PHP error during form submission.',
                [
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'error_trace' => $e->getTraceAsString(),
                    'php_version' => PHP_VERSION,
                    'wp_version' => get_bloginfo('version'),
                ]
            );

            wp_send_json_error([
                'message' => __('Something unexpected went wrong. Please try again later.', 'pulseforms'),
            ], 500);
        }
    }

    private function get_form($form_id) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}pulseforms_forms WHERE id = %d",
                $form_id
            )
        );
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

    private function save_submission($form, $clean_data, $page_url) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'pulseforms_submissions',
            [
                'form_id'         => absint($form->id),
                'form_name'       => sanitize_text_field($form->name),
                'submission_data' => wp_json_encode($clean_data),
                'files'           => null,
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
        PulseForms_Logger::log($severity, $event_type, $message, $context);

        wp_send_json_error([
            'message' => $public_message ?: __('Something went wrong. Please try again.', 'pulseforms'),
        ], 400);
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