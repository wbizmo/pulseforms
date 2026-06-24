<?php
if (!defined('ABSPATH')) {
    exit;
}

$site_name     = isset($site_name) ? $site_name : get_bloginfo('name');
$title         = isset($title) ? $title : __('New Form Submission', 'wbizmo-form-builder');
$intro         = isset($intro) ? $intro : __('A new form submission has been received.', 'wbizmo-form-builder');
$form          = isset($form) ? $form : null;
$submission_id = isset($submission_id) ? absint($submission_id) : 0;
$clean_data    = isset($clean_data) && is_array($clean_data) ? $clean_data : [];
$page_url      = isset($page_url) ? esc_url($page_url) : '';
$footer        = isset($footer) ? $footer : __('This notification was sent by Wbizmo Form Builder.', 'wbizmo-form-builder');
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
                            <p style="margin:0 0 20px;font-size:16px;line-height:1.7;color:rgba(14,34,56,0.75);">
                                <?php echo esc_html($intro); ?>
                            </p>

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
                                            <a href="<?php echo esc_url($page_url); ?>" style="color:#0E2238;">
                                                <?php echo esc_html($page_url); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>

                            <h2 style="font-size:18px;margin:0 0 14px;color:#0E2238;">Submitted Details</h2>

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