<?php

if (!defined('ABSPATH')) {
    exit;
}

class PulseForms_Emailer {
    public function send_admin_notification($form, $submission_id, $clean_data, $page_url) {
        $to = get_option('admin_email');

        if (!$to || !is_email($to)) {
            return new WP_Error('invalid_admin_email', 'The site admin email address is invalid.');
        }

        $subject = sprintf(
            __('New submission from %s', 'wbizmo-form-builder'),
            $form->name
        );

        $body = $this->render_template('admin-notification.php', [
            'site_name'     => get_bloginfo('name'),
            'title'         => __('New Form Submission', 'wbizmo-form-builder'),
            'intro'         => sprintf(__('A new submission was received from %s.', 'wbizmo-form-builder'), $form->name),
            'form'          => $form,
            'submission_id' => $submission_id,
            'clean_data'    => $clean_data,
            'page_url'      => $page_url,
            'footer'        => __('This notification was sent by Wbizmo Form Builder.', 'wbizmo-form-builder'),
        ]);

        return $this->send($to, $subject, $body);
    }

    public function send_user_confirmation($form, $submission_id, $clean_data, $page_url) {
        $user_email = $this->extract_email_from_submission($clean_data);

        if (!$user_email || !is_email($user_email)) {
            return new WP_Error('missing_user_email', 'Wbizmo Form Builder could not find a valid user email field.');
        }

        $subject = sprintf(
            __('We received your submission - %s', 'wbizmo-form-builder'),
            get_bloginfo('name')
        );

        $body = $this->render_template('user-confirmation.php', [
            'site_name'     => get_bloginfo('name'),
            'title'         => __('Submission Received', 'wbizmo-form-builder'),
            'intro'         => __('Thank you. Your submission has been received successfully.', 'wbizmo-form-builder'),
            'form'          => $form,
            'submission_id' => $submission_id,
            'clean_data'    => $clean_data,
            'page_url'      => $page_url,
            'footer'        => sprintf(__('Sent from %s using Wbizmo Form Builder.', 'wbizmo-form-builder'), get_bloginfo('name')),
        ]);

        return $this->send($user_email, $subject, $body);
    }

    private function send($to, $subject, $body) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        $sent = wp_mail($to, $subject, $body, $headers);

        if (!$sent) {
            return new WP_Error('wp_mail_failed', 'wp_mail returned false.');
        }

        return true;
    }

    private function render_template($template, $args = []) {
        $template_path = PULSEFORMS_PATH . 'templates/emails/' . $template;

        if (!file_exists($template_path)) {
            return '';
        }

        extract($args, EXTR_SKIP);

        ob_start();
        include $template_path;
        return ob_get_clean();
    }

    private function extract_email_from_submission($clean_data) {
        foreach ($clean_data as $field) {
            if (
                isset($field['type'], $field['value']) &&
                $field['type'] === 'email' &&
                is_email($field['value'])
            ) {
                return sanitize_email($field['value']);
            }
        }

        return null;
    }
}