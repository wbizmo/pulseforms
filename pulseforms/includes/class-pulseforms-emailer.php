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
            __('New submission from %s', 'pulseforms'),
            $form->name
        );

        $body = $this->build_email_template([
            'title'       => __('New Form Submission', 'pulseforms'),
            'intro'       => sprintf(__('A new submission was received from %s.', 'pulseforms'), $form->name),
            'form'        => $form,
            'submission_id' => $submission_id,
            'clean_data'  => $clean_data,
            'page_url'    => $page_url,
            'footer'      => __('This notification was sent by PulseForms.', 'pulseforms'),
        ]);

        return $this->send($to, $subject, $body);
    }

    public function send_user_confirmation($form, $submission_id, $clean_data, $page_url) {
        $user_email = $this->extract_email_from_submission($clean_data);

        if (!$user_email || !is_email($user_email)) {
            return new WP_Error('missing_user_email', 'PulseForms could not find a valid user email field.');
        }

        $subject = sprintf(
            __('We received your submission - %s', 'pulseforms'),
            get_bloginfo('name')
        );

        $body = $this->build_email_template([
            'title'       => __('Submission Received', 'pulseforms'),
            'intro'       => __('Thank you. Your submission has been received successfully.', 'pulseforms'),
            'form'        => $form,
            'submission_id' => $submission_id,
            'clean_data'  => $clean_data,
            'page_url'    => $page_url,
            'footer'      => sprintf(__('Sent from %s using PulseForms.', 'pulseforms'), get_bloginfo('name')),
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

    private function build_email_template($args) {
        $site_name = get_bloginfo('name');
        $title = isset($args['title']) ? $args['title'] : __('PulseForms Notification', 'pulseforms');
        $intro = isset($args['intro']) ? $args['intro'] : '';
        $form = isset($args['form']) ? $args['form'] : null;
        $submission_id = isset($args['submission_id']) ? absint($args['submission_id']) : 0;
        $clean_data = isset($args['clean_data']) && is_array($args['clean_data']) ? $args['clean_data'] : [];
        $page_url = isset($args['page_url']) ? esc_url($args['page_url']) : '';
        $footer = isset($args['footer']) ? $args['footer'] : '';

        ob_start();
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo esc_html($title); ?></title>
        </head>
        <body style="margin:0;padding:0;background:#f8f6f0;font-family:Arial,sans-serif;color:#0E2238;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f6f0;padding:28px 14px;">
                <tr>
                    <td align="center">
                        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid rgba(14,34,56,0.10);">
                            <tr>
                                <td style="background:#0E2238;color:#ffffff;padding:28px;">
                                    <div style="font-size:13px;letter-spacing:0.08em;text-transform:uppercase;color:#C5A572;font-weight:700;">
                                        <?php echo esc_html($site_name); ?>
                                    </div>
                                    <h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;color:#ffffff;">
                                        <?php echo esc_html($title); ?>
                                    </h1>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:28px;">
                                    <?php if ($intro) : ?>
                                        <p style="margin:0 0 20px;font-size:16px;line-height:1.7;color:rgba(14,34,56,0.75);">
                                            <?php echo esc_html($intro); ?>
                                        </p>
                                    <?php endif; ?>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;background:#f8f6f0;border-radius:18px;padding:16px;">
                                        <tr>
                                            <td style="font-size:14px;color:rgba(14,34,56,0.65);padding:6px 0;">Form</td>
                                            <td align="right" style="font-size:14px;font-weight:700;color:#0E2238;padding:6px 0;">
                                                <?php echo $form ? esc_html($form->name) : '—'; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px;color:rgba(14,34,56,0.65);padding:6px 0;">Submission ID</td>
                                            <td align="right" style="font-size:14px;font-weight:700;color:#0E2238;padding:6px 0;">
                                                #<?php echo esc_html($submission_id); ?>
                                            </td>
                                        </tr>
                                        <?php if ($page_url) : ?>
                                            <tr>
                                                <td style="font-size:14px;color:rgba(14,34,56,0.65);padding:6px 0;">Page</td>
                                                <td align="right" style="font-size:14px;font-weight:700;color:#0E2238;padding:6px 0;">
                                                    <a href="<?php echo esc_url($page_url); ?>" style="color:#0E2238;"><?php echo esc_html($page_url); ?></a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>

                                    <h2 style="font-size:18px;margin:0 0 14px;color:#0E2238;">Details</h2>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                        <?php foreach ($clean_data as $field) : ?>
                                            <?php
                                            $label = isset($field['label']) ? $field['label'] : 'Field';
                                            $value = isset($field['value']) ? $field['value'] : '';
                                            if (is_array($value)) {
                                                $value = implode(', ', $value);
                                            }
                                            ?>
                                            <tr>
                                                <td style="border-bottom:1px solid rgba(14,34,56,0.10);padding:13px 0;color:rgba(14,34,56,0.62);font-size:14px;">
                                                    <?php echo esc_html($label); ?>
                                                </td>
                                                <td align="right" style="border-bottom:1px solid rgba(14,34,56,0.10);padding:13px 0;color:#0E2238;font-size:14px;font-weight:700;">
                                                    <?php echo nl2br(esc_html($value)); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:18px 28px;background:#f8f6f0;color:rgba(14,34,56,0.55);font-size:13px;line-height:1.6;">
                                    <?php echo esc_html($footer); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php

        return ob_get_clean();
    }
}