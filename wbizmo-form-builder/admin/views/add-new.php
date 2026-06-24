<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pf-admin-wrap">
    <div class="pf-hero">
        <div>
            <p class="pf-eyebrow">Builder</p>
            <h1>Add New Form</h1>
            <p>Start with a form type, choose a visual theme, and customize the details later.</p>
        </div>
    </div>

    <?php if (isset($_GET['pf_error'])) : ?>
        <div class="pf-notice pf-notice-error">
            <span class="material-symbols-outlined">error</span>
            Could not create form. Please check the required fields.
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pf-builder-shell">
        <?php wp_nonce_field('pulseforms_create_form'); ?>
        <input type="hidden" name="action" value="pulseforms_create_form">

        <div class="pf-card">
            <h2>Form Details</h2>
            <p>Name your form and choose what kind of form you want to create.</p>

            <div class="pf-form-grid">
                <div class="pf-field">
                    <label for="form_name">Form Name</label>
                    <input type="text" id="form_name" name="form_name" placeholder="Example: Contact Form" required>
                </div>

                <div class="pf-field">
                    <label for="form_type">Form Type</label>
                    <select id="form_type" name="form_type">
                        <option value="contact">Contact Form</option>
                        <option value="newsletter">Newsletter Form</option>
                        <option value="subscription">Subscription Form</option>
                        <option value="multi_step">Multi-Step Form</option>
                        <option value="registration">Registration Form</option>
                        <option value="login">Login Form</option>
                        <option value="custom">Custom Form</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="pf-card">
            <h2>Choose Form Theme</h2>
            <p>Wbizmo Form Builder ships with polished default form themes. You can customize colors later.</p>

            <div class="pf-theme-grid">
                <label class="pf-theme-option pf-theme-aurora">
                    <input type="radio" name="form_theme" value="aurora" checked>
                    <span class="pf-theme-preview">
                        <span class="pf-theme-line"></span>
                        <span class="pf-theme-input"></span>
                        <span class="pf-theme-button"></span>
                    </span>
                    <strong>Aurora</strong>
                    <small>Clean, soft and modern.</small>
                </label>

                <label class="pf-theme-option pf-theme-noir">
                    <input type="radio" name="form_theme" value="noir">
                    <span class="pf-theme-preview">
                        <span class="pf-theme-line"></span>
                        <span class="pf-theme-input"></span>
                        <span class="pf-theme-button"></span>
                    </span>
                    <strong>Noir</strong>
                    <small>Dark and premium.</small>
                </label>

                <label class="pf-theme-option pf-theme-solace">
                    <input type="radio" name="form_theme" value="solace">
                    <span class="pf-theme-preview">
                        <span class="pf-theme-line"></span>
                        <span class="pf-theme-input"></span>
                        <span class="pf-theme-button"></span>
                    </span>
                    <strong>Solace</strong>
                    <small>Warm business style.</small>
                </label>
            </div>
        </div>

        <div class="pf-save-bar">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wbizmo-form-builder')); ?>" class="pf-btn pf-btn-light">
                Cancel
            </a>

            <button type="submit" class="pf-btn pf-btn-primary">
                <span class="material-symbols-outlined">save</span>
                Create Form
            </button>
        </div>
    </form>
</div>