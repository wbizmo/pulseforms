<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pf-admin-wrap">
    <div class="pf-hero">
        <div>
            <p class="pf-eyebrow">Submissions</p>
            <h1>Form Submissions</h1>
            <p>View form entries, metadata, uploaded files, source pages, and submitted values.</p>
        </div>
    </div>

    <?php if (isset($_GET['pf_deleted'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Submission deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pf_read'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Submission marked as read.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pf_error'])) : ?>
        <div class="pf-notice pf-notice-error">
            <span class="material-symbols-outlined">error</span>
            Something went wrong. Check logs for details.
        </div>
    <?php endif; ?>

    <?php if (!empty($submissions)) : ?>
        <div class="pf-card">
            <div class="pf-table-header">
                <div>
                    <h2>Recent Submissions</h2>
                    <p>Showing the latest 200 submissions.</p>
                </div>
            </div>

            <div class="pf-submission-list">
                <?php foreach ($submissions as $submission) : ?>
                    <?php
                    $data = json_decode($submission->submission_data, true);
                    $files = json_decode($submission->files, true);

                    $delete_url = wp_nonce_url(
                        admin_url('admin-post.php?action=pulseforms_delete_submission&submission_id=' . absint($submission->id)),
                        'pulseforms_delete_submission_' . absint($submission->id)
                    );

                    $mark_read_url = wp_nonce_url(
                        admin_url('admin-post.php?action=pulseforms_mark_submission_read&submission_id=' . absint($submission->id)),
                        'pulseforms_mark_submission_read_' . absint($submission->id)
                    );
                    ?>

                    <article class="pf-submission-card <?php echo $submission->status === 'unread' ? 'is-unread' : ''; ?>">
                        <div class="pf-submission-top">
                            <div>
                                <div class="pf-submission-title-row">
                                    <h3><?php echo esc_html($submission->form_name ?: 'Untitled Form'); ?></h3>

                                    <span class="pf-status <?php echo $submission->status === 'unread' ? 'pf-status-unread' : 'pf-status-active'; ?>">
                                        <?php echo esc_html($submission->status); ?>
                                    </span>
                                </div>

                                <p>
                                    Submission #<?php echo esc_html($submission->id); ?> ·
                                    <?php echo esc_html(mysql2date('M j, Y g:i A', $submission->created_at)); ?>
                                </p>
                            </div>

                            <div class="pf-submission-actions">
                                <?php if ($submission->status === 'unread') : ?>
                                    <a href="<?php echo esc_url($mark_read_url); ?>" class="pf-icon-btn" title="Mark as read">
                                        <span class="material-symbols-outlined">done_all</span>
                                    </a>
                                <?php endif; ?>

                                <button type="button" class="pf-icon-btn pf-toggle-submission" data-target="pf-submission-<?php echo esc_attr($submission->id); ?>">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>

                                <a href="<?php echo esc_url($delete_url); ?>" class="pf-icon-btn pf-danger" onclick="return confirm('Delete this submission? This cannot be undone.');">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </div>
                        </div>

                        <div class="pf-submission-details" id="pf-submission-<?php echo esc_attr($submission->id); ?>">
                            <div class="pf-submission-grid">
                                <div>
                                    <strong>Form ID</strong>
                                    <span><?php echo esc_html($submission->form_id); ?></span>
                                </div>

                                <div>
                                    <strong>User ID</strong>
                                    <span><?php echo $submission->user_id ? esc_html($submission->user_id) : 'Guest'; ?></span>
                                </div>

                                <div>
                                    <strong>Page URL</strong>
                                    <span class="pf-break">
                                        <?php if (!empty($submission->page_url)) : ?>
                                            <a href="<?php echo esc_url($submission->page_url); ?>" target="_blank" rel="noopener noreferrer">
                                                <?php echo esc_html($submission->page_url); ?>
                                            </a>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div>
                                    <strong>User Agent</strong>
                                    <span class="pf-break"><?php echo esc_html($submission->user_agent ?: '—'); ?></span>
                                </div>
                            </div>

                            <div class="pf-submitted-values">
                                <h4>Submitted Values</h4>

                                <?php if (is_array($data) && !empty($data)) : ?>
                                    <div class="pf-value-list">
                                        <?php foreach ($data as $field) : ?>
                                            <?php
                                            $label = isset($field['label']) ? $field['label'] : 'Field';
                                            $value = isset($field['value']) ? $field['value'] : '';
                                            ?>
                                            <div class="pf-value-row">
                                                <strong><?php echo esc_html($label); ?></strong>

                                                <?php if (is_array($value)) : ?>
                                                    <span><?php echo esc_html(implode(', ', $value)); ?></span>
                                                <?php else : ?>
                                                    <span><?php echo nl2br(esc_html($value)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <p>No readable submission data found.</p>
                                <?php endif; ?>
                            </div>

                            <?php if (is_array($files) && !empty($files)) : ?>
                                <div class="pf-submitted-files">
                                    <h4>Uploaded Files</h4>

                                    <div class="pf-file-list">
                                        <?php foreach ($files as $file) : ?>
                                            <a class="pf-file-card" href="<?php echo esc_url($file['url']); ?>" target="_blank" rel="noopener noreferrer">
                                                <span class="material-symbols-outlined">attach_file</span>
                                                <span>
                                                    <strong><?php echo esc_html($file['label'] ?? 'Uploaded File'); ?></strong>
                                                    <small><?php echo esc_html($file['name'] ?? 'File'); ?></small>
                                                </span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="pf-card pf-empty">
            <span class="material-symbols-outlined">inbox</span>
            <h2>No submissions yet</h2>
            <p>Once users submit a PulseForms form, their entries will appear here.</p>
        </div>
    <?php endif; ?>
</div>