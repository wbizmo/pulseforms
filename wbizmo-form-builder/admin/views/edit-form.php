<?php
if (!defined('ABSPATH')) {
    exit;
}

$fields = json_decode($form->fields, true);
$settings = json_decode($form->settings, true);
$style_settings = json_decode($form->style_settings, true);

if (!is_array($fields)) {
    $fields = [];
}

if (!is_array($settings)) {
    $settings = [];
}

if (!is_array($style_settings)) {
    $style_settings = [];
}

$theme = isset($style_settings['theme']) ? $style_settings['theme'] : 'aurora';
$style_mode = isset($style_settings['style_mode']) ? $style_settings['style_mode'] : 'pulse';
$primary_color = isset($style_settings['primary_color']) ? $style_settings['primary_color'] : '#0E2238';
$accent_color = isset($style_settings['accent_color']) ? $style_settings['accent_color'] : '#C5A572';
$field_radius = isset($style_settings['field_radius']) ? $style_settings['field_radius'] : '14';
$button_radius = isset($style_settings['button_radius']) ? $style_settings['button_radius'] : '14';
$custom_css = isset($style_settings['custom_css']) ? $style_settings['custom_css'] : '';

$admin_email_enabled = !empty($settings['admin_email_enabled']);
$user_email_enabled = !empty($settings['user_email_enabled']);
$save_submissions = !empty($settings['save_submissions']);
$honeypot_enabled = !empty($settings['honeypot_enabled']);
$captcha_enabled = !empty($settings['captcha_enabled']);
$submit_text = isset($settings['submit_text']) ? $settings['submit_text'] : 'Submit';
$success_message = isset($settings['success_message']) ? $settings['success_message'] : 'Thank you. Your submission has been received.';
$error_message = isset($settings['error_message']) ? $settings['error_message'] : 'Something went wrong. Please try again.';
?>

<div class="pf-admin-wrap">
    <div class="pf-hero">
        <div>
            <p class="pf-eyebrow">Edit Form</p>
            <h1><?php echo esc_html($form->name); ?></h1>
            <p>Customize fields, styling, email behavior, security, and frontend messages.</p>
        </div>

        <div class="pf-shortcode-copy">
            <code>[pulseform id="<?php echo esc_attr($form->id); ?>"]</code>
            <button type="button" class="pf-icon-btn pf-copy-shortcode" data-shortcode='[pulseform id="<?php echo esc_attr($form->id); ?>"]'>
                <span class="material-symbols-outlined">content_copy</span>
            </button>
        </div>
    </div>

    <?php if (isset($_GET['pf_error'])) : ?>
        <div class="pf-notice pf-notice-error">
            <span class="material-symbols-outlined">error</span>
            Something went wrong while updating the form.
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pf-builder-shell">
        <?php wp_nonce_field('pulseforms_update_form_' . absint($form->id)); ?>
        <input type="hidden" name="action" value="pulseforms_update_form">
        <input type="hidden" name="form_id" value="<?php echo esc_attr($form->id); ?>">

        <div class="pf-card">
            <h2>Basic Details</h2>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="form_name">Form Name</label>
                    <input type="text" id="form_name" name="form_name" value="<?php echo esc_attr($form->name); ?>" required>
                </div>

                <div class="pf-field">
                    <label for="form_status">Status</label>
                    <select id="form_status" name="form_status">
                        <option value="active" <?php selected($form->status, 'active'); ?>>Active</option>
                        <option value="inactive" <?php selected($form->status, 'inactive'); ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="pf-card">
            <h2>Fields</h2>
            <p>Fast V1 editor: edit the form fields as JSON. The visual drag-and-drop builder can be layered on top later.</p>

            <div class="pf-field">
                <label for="form_fields">Fields JSON</label>
                <textarea id="form_fields" name="form_fields" rows="14"><?php echo esc_textarea(wp_json_encode($fields, JSON_PRETTY_PRINT)); ?></textarea>
            </div>
        </div>

        <div class="pf-card">
            <h2>Email Settings</h2>

            <div class="pf-toggle-grid">
                <label class="pf-switch-row">
                    <input type="checkbox" name="admin_email_enabled" value="1" <?php checked($admin_email_enabled); ?>>
                    <span class="pf-switch-ui"></span>
                    <span>Send admin notification email</span>
                </label>

                <label class="pf-switch-row">
                    <input type="checkbox" name="user_email_enabled" value="1" <?php checked($user_email_enabled); ?>>
                    <span class="pf-switch-ui"></span>
                    <span>Send user confirmation email</span>
                </label>
            </div>
        </div>

        <div class="pf-card">
            <h2>Submission & Security</h2>

            <div class="pf-toggle-grid">
                <label class="pf-switch-row">
                    <input type="checkbox" name="save_submissions" value="1" <?php checked($save_submissions); ?>>
                    <span class="pf-switch-ui"></span>
                    <span>Save submissions in WordPress admin</span>
                </label>

                <label class="pf-switch-row">
                    <input type="checkbox" name="honeypot_enabled" value="1" <?php checked($honeypot_enabled); ?>>
                    <span class="pf-switch-ui"></span>
                    <span>Enable honeypot spam protection</span>
                </label>

                <label class="pf-switch-row">
                    <input type="checkbox" name="captcha_enabled" value="1" <?php checked($captcha_enabled); ?>>
                    <span class="pf-switch-ui"></span>
                    <span>Enable custom captcha later</span>
                </label>
            </div>
        </div>

        <div class="pf-card">
            <h2>Messages</h2>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="submit_text">Submit Button Text</label>
                    <input type="text" id="submit_text" name="submit_text" value="<?php echo esc_attr($submit_text); ?>">
                </div>

                <div class="pf-field">
                    <label for="success_message">Success Message</label>
                    <input type="text" id="success_message" name="success_message" value="<?php echo esc_attr($success_message); ?>">
                </div>
            </div>

            <div class="pf-field">
                <label for="error_message">Fallback Error Message</label>
                <input type="text" id="error_message" name="error_message" value="<?php echo esc_attr($error_message); ?>">
            </div>
        </div>

        <div class="pf-card">
            <h2>Style Settings</h2>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="form_theme">Theme</label>
                    <select id="form_theme" name="form_theme">
                        <option value="aurora" <?php selected($theme, 'aurora'); ?>>Aurora</option>
                        <option value="noir" <?php selected($theme, 'noir'); ?>>Noir</option>
                        <option value="solace" <?php selected($theme, 'solace'); ?>>Solace</option>
                    </select>
                </div>

                <div class="pf-field">
                    <label for="style_mode">Style Mode</label>
                    <select id="style_mode" name="style_mode">
                        <option value="pulse" <?php selected($style_mode, 'pulse'); ?>>PulseForms styling</option>
                        <option value="inherit" <?php selected($style_mode, 'inherit'); ?>>Inherit theme styling</option>
                    </select>
                </div>

                <div class="pf-field">
                    <label for="primary_color">Primary Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($primary_color); ?>">
                </div>

                <div class="pf-field">
                    <label for="accent_color">Accent Color</label>
                    <input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr($accent_color); ?>">
                </div>

                <div class="pf-field">
                    <label for="field_radius">Field Radius</label>
                    <input type="number" id="field_radius" name="field_radius" value="<?php echo esc_attr($field_radius); ?>">
                </div>

                <div class="pf-field">
                    <label for="button_radius">Button Radius</label>
                    <input type="number" id="button_radius" name="button_radius" value="<?php echo esc_attr($button_radius); ?>">
                </div>
            </div>

            <div class="pf-field">
                <label for="custom_css">Custom CSS</label>
                <textarea id="custom_css" name="custom_css" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
            </div>
        </div>

        <div class="pf-save-bar">
            <a href="<?php echo esc_url(admin_url('admin.php?page=pulseforms')); ?>" class="pf-btn pf-btn-light">Cancel</a>

            <button type="submit" class="pf-btn pf-btn-primary">
                <span class="material-symbols-outlined">save</span>
                Save Changes
            </button>
        </div>
    </form>
</div>