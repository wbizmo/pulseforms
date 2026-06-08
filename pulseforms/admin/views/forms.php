<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pf-admin-wrap">
    <div class="pf-hero">
        <div>
            <p class="pf-eyebrow">PulseForms</p>
            <h1>All Forms</h1>
            <p>Create beautiful, secure, customizable WordPress forms.</p>
        </div>

        <a href="<?php echo esc_url(admin_url('admin.php?page=pulseforms-add-new')); ?>" class="pf-btn pf-btn-primary">
            <span class="material-symbols-outlined">add</span>
            Add New Form
        </a>
    </div>

    <?php if (isset($_GET['pf_created'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Form created successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pf_deleted'])) : ?>
        <div class="pf-notice pf-notice-success">
            <span class="material-symbols-outlined">check_circle</span>
            Form deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['pf_error'])) : ?>
        <div class="pf-notice pf-notice-error">
            <span class="material-symbols-outlined">error</span>
            Something went wrong. Check logs for details.
        </div>
    <?php endif; ?>

    <?php if (!empty($forms)) : ?>
        <div class="pf-card">
            <div class="pf-table-header">
                <div>
                    <h2>Forms</h2>
                    <p>Copy a shortcode and paste it into any page, post, or page builder.</p>
                </div>
            </div>

            <div class="pf-table-wrap">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Theme</th>
                            <th>Shortcode</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="pf-table-actions">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($forms as $form) : ?>
                            <?php
                                $style_settings = json_decode($form->style_settings, true);
                                $theme = isset($style_settings['theme']) ? $style_settings['theme'] : 'aurora';
                                $delete_url = wp_nonce_url(
                                    admin_url('admin-post.php?action=pulseforms_delete_form&form_id=' . absint($form->id)),
                                    'pulseforms_delete_form_' . absint($form->id)
                                );
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($form->name); ?></strong>
                                </td>

                                <td>
                                    <span class="pf-pill"><?php echo esc_html(ucwords(str_replace('_', ' ', $form->type))); ?></span>
                                </td>

                                <td>
                                    <?php echo esc_html(ucfirst($theme)); ?>
                                </td>

                                <td>
                                    <div class="pf-shortcode-copy">
                                        <code>[pulseform id="<?php echo esc_attr($form->id); ?>"]</code>
                                        <button type="button" class="pf-icon-btn pf-copy-shortcode" data-shortcode='[pulseform id="<?php echo esc_attr($form->id); ?>"]'>
                                            <span class="material-symbols-outlined">content_copy</span>
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    <span class="pf-status pf-status-active"><?php echo esc_html($form->status); ?></span>
                                </td>

                                <td>
                                    <?php echo esc_html(mysql2date('M j, Y', $form->created_at)); ?>
                                </td>

                                <td class="pf-table-actions">
                                    <a href="#" class="pf-icon-btn" title="Builder coming next">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>

                                    <a href="<?php echo esc_url($delete_url); ?>" class="pf-icon-btn pf-danger" onclick="return confirm('Delete this form? This cannot be undone.');">
                                        <span class="material-symbols-outlined">delete</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else : ?>
        <div class="pf-card pf-empty">
            <span class="material-symbols-outlined">dynamic_form</span>
            <h2>No forms yet</h2>
            <p>Create your first form and PulseForms will generate a shortcode automatically.</p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=pulseforms-add-new')); ?>" class="pf-btn pf-btn-primary">
                <span class="material-symbols-outlined">add</span>
                Create First Form
            </a>
        </div>
    <?php endif; ?>
</div>