<?php
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('pulseforms_settings', []);

$log_retention_days = isset($options['log_retention_days']) ? absint($options['log_retention_days']) : 30;
$upload_max_size = isset($options['upload_max_size']) ? absint($options['upload_max_size']) : 5;
$allowed_file_types = isset($options['allowed_file_types']) ? sanitize_text_field($options['allowed_file_types']) : 'jpg,jpeg,png,gif,pdf,doc,docx,txt';
$rate_limit_attempts = isset($options['rate_limit_attempts']) ? absint($options['rate_limit_attempts']) : 5;
$rate_limit_window = isset($options['rate_limit_window']) ? absint($options['rate_limit_window']) : 10;
?>

<div class="pf-admin-wrap">
    <div class="pf-hero">
        <div>
            <p class="pf-eyebrow">Settings</p>
            <h1>PulseForms Settings</h1>
            <p>Manage global security, upload, logging, and rate-limit behavior.</p>
        </div>
    </div>

    <?php if (isset($_GET['pf_saved'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Settings saved successfully.
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pf-builder-shell">
        <?php wp_nonce_field('pulseforms_save_settings'); ?>
        <input type="hidden" name="action" value="pulseforms_save_settings">

        <div class="pf-card">
            <h2>Uploads</h2>
            <p>Control global upload limits for file fields.</p>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="upload_max_size">Maximum Upload Size (MB)</label>
                    <input type="number" id="upload_max_size" name="upload_max_size" min="1" max="25" value="<?php echo esc_attr($upload_max_size); ?>">
                </div>

                <div class="pf-field">
                    <label for="allowed_file_types">Allowed File Types</label>
                    <input type="text" id="allowed_file_types" name="allowed_file_types" value="<?php echo esc_attr($allowed_file_types); ?>">
                    <p class="pf-help">Comma-separated. Example: jpg,png,pdf,docx</p>
                </div>
            </div>
        </div>

        <div class="pf-card">
            <h2>Rate Limiting</h2>
            <p>Protect forms from repeated spam submissions.</p>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="rate_limit_attempts">Attempts Allowed</label>
                    <input type="number" id="rate_limit_attempts" name="rate_limit_attempts" min="1" max="50" value="<?php echo esc_attr($rate_limit_attempts); ?>">
                </div>

                <div class="pf-field">
                    <label for="rate_limit_window">Window Length (Minutes)</label>
                    <input type="number" id="rate_limit_window" name="rate_limit_window" min="1" max="1440" value="<?php echo esc_attr($rate_limit_window); ?>">
                </div>
            </div>
        </div>

        <div class="pf-card">
            <h2>Logs</h2>
            <p>Control how long PulseForms should keep log records.</p>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="log_retention_days">Log Retention Days</label>
                    <input type="number" id="log_retention_days" name="log_retention_days" min="1" max="365" value="<?php echo esc_attr($log_retention_days); ?>">
                </div>
            </div>
        </div>

        <div class="pf-save-bar">
            <button type="submit" class="pf-btn pf-btn-primary">
                <span class="material-symbols-outlined">save</span>
                Save Settings
            </button>
        </div>
    </form>
</div>