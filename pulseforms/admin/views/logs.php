<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pf-admin-wrap">
    <div class="pf-hero">
        <div>
            <p class="pf-eyebrow">Logs</p>
            <h1>Error & Activity Logs</h1>
            <p>Review frontend failures, PHP issues, submission errors, email failures, and environment problems.</p>
        </div>

        <?php if (!empty($logs)) : ?>
            <a
                href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=pulseforms_clear_logs'), 'pulseforms_clear_logs')); ?>"
                class="pf-btn pf-btn-light"
                onclick="return confirm('Clear all PulseForms logs?');"
            >
                <span class="material-symbols-outlined">delete_sweep</span>
                Clear Logs
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['pf_deleted'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Log deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pf_cleared'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Logs cleared successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pf_error'])) : ?>
        <div class="pf-notice pf-notice-error">
            <span class="material-symbols-outlined">error</span>
            Something went wrong while updating logs.
        </div>
    <?php endif; ?>

    <?php if (!empty($logs)) : ?>
        <div class="pf-card">
            <div class="pf-table-header">
                <div>
                    <h2>Latest Logs</h2>
                    <p>Showing the latest 300 log entries.</p>
                </div>
            </div>

            <div class="pf-log-list">
                <?php foreach ($logs as $log) : ?>
                    <?php
                        $details = json_decode($log->technical_details, true);
                        $delete_url = wp_nonce_url(
                            admin_url('admin-post.php?action=pulseforms_delete_log&log_id=' . absint($log->id)),
                            'pulseforms_delete_log_' . absint($log->id)
                        );
                    ?>

                    <article class="pf-log-card pf-log-<?php echo esc_attr($log->severity); ?>">
                        <div class="pf-log-top">
                            <div>
                                <div class="pf-log-title-row">
                                    <span class="pf-log-severity pf-log-severity-<?php echo esc_attr($log->severity); ?>">
                                        <?php echo esc_html($log->severity); ?>
                                    </span>

                                    <h3><?php echo esc_html($log->event_type); ?></h3>
                                </div>

                                <p><?php echo esc_html($log->message); ?></p>

                                <div class="pf-log-meta">
                                    <span><?php echo esc_html(mysql2date('M j, Y g:i A', $log->created_at)); ?></span>
                                    <span>PHP <?php echo esc_html($log->php_version ?: '—'); ?></span>
                                    <span>WP <?php echo esc_html($log->wp_version ?: '—'); ?></span>
                                    <span>PulseForms <?php echo esc_html($log->plugin_version ?: '—'); ?></span>
                                </div>
                            </div>

                            <div class="pf-submission-actions">
                                <button type="button" class="pf-icon-btn pf-toggle-submission" data-target="pf-log-<?php echo esc_attr($log->id); ?>">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>

                                <button type="button" class="pf-icon-btn pf-copy-log" data-target="pf-log-copy-<?php echo esc_attr($log->id); ?>">
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>

                                <a href="<?php echo esc_url($delete_url); ?>" class="pf-icon-btn pf-danger" onclick="return confirm('Delete this log?');">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </div>
                        </div>

                        <div class="pf-submission-details" id="pf-log-<?php echo esc_attr($log->id); ?>">
                            <div class="pf-submission-grid">
                                <div>
                                    <strong>Log ID</strong>
                                    <span><?php echo esc_html($log->id); ?></span>
                                </div>

                                <div>
                                    <strong>Form</strong>
                                    <span>
                                        <?php echo $log->form_id ? esc_html('#' . $log->form_id . ' — ' . $log->form_name) : '—'; ?>
                                    </span>
                                </div>

                                <div>
                                    <strong>Submission ID</strong>
                                    <span><?php echo $log->submission_id ? esc_html($log->submission_id) : '—'; ?></span>
                                </div>

                                <div>
                                    <strong>User ID</strong>
                                    <span><?php echo $log->user_id ? esc_html($log->user_id) : 'Guest / Unknown'; ?></span>
                                </div>

                                <div>
                                    <strong>Page URL</strong>
                                    <span class="pf-break">
                                        <?php if (!empty($log->page_url)) : ?>
                                            <a href="<?php echo esc_url($log->page_url); ?>" target="_blank" rel="noopener noreferrer">
                                                <?php echo esc_html($log->page_url); ?>
                                            </a>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div>
                                    <strong>User Agent</strong>
                                    <span class="pf-break"><?php echo esc_html($log->user_agent ?: '—'); ?></span>
                                </div>
                            </div>

                            <div class="pf-log-details">
                                <h4>Technical Details</h4>

                                <pre id="pf-log-copy-<?php echo esc_attr($log->id); ?>"><?php echo esc_html(wp_json_encode($details ?: [], JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="pf-card pf-empty">
            <span class="material-symbols-outlined">receipt_long</span>
            <h2>No logs yet</h2>
            <p>PulseForms logs will appear here when errors, warnings, blocked submissions, or system issues occur.</p>
        </div>
    <?php endif; ?>
</div>