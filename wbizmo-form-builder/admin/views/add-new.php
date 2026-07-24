<?php
/**
 * "Add New Form" admin screen template.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pf-admin-wrap">
	<div class="pf-hero">
		<div>
			<p class="pf-eyebrow"><?php esc_html_e( 'Builder', 'wbizmo-form-builder' ); ?></p>
			<h1><?php esc_html_e( 'Add New Form', 'wbizmo-form-builder' ); ?></h1>
			<p><?php esc_html_e( 'Start with a form type, choose a visual theme, and customize the details later.', 'wbizmo-form-builder' ); ?></p>
		</div>
	</div>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag from a redirect after a nonce-verified admin-post action; no state change happens here. ?>
	<?php if ( isset( $_GET['pf_error'] ) ) : ?>
		<div class="pf-notice pf-notice-error">
			<span class="dashicons">error</span>
			<?php esc_html_e( 'Could not create form. Please check the required fields.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pf-builder-shell">
		<?php wp_nonce_field( 'wbizfobu_create_form' ); ?>
		<input type="hidden" name="action" value="wbizfobu_create_form">

		<div class="pf-card">
			<h2><?php esc_html_e( 'Form Details', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Name your form and choose what kind of form you want to create.', 'wbizmo-form-builder' ); ?></p>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="form_name"><?php esc_html_e( 'Form Name', 'wbizmo-form-builder' ); ?></label>
					<input type="text" id="form_name" name="form_name" placeholder="<?php esc_attr_e( 'Example: Contact Form', 'wbizmo-form-builder' ); ?>" required>
				</div>

				<div class="pf-field">
					<label for="form_type"><?php esc_html_e( 'Form Type', 'wbizmo-form-builder' ); ?></label>
					<select id="form_type" name="form_type">
						<option value="contact"><?php esc_html_e( 'Contact Form', 'wbizmo-form-builder' ); ?></option>
						<option value="newsletter"><?php esc_html_e( 'Newsletter Form', 'wbizmo-form-builder' ); ?></option>
						<option value="subscription"><?php esc_html_e( 'Subscription Form', 'wbizmo-form-builder' ); ?></option>
						<option value="multi_step"><?php esc_html_e( 'Multi-Step Form', 'wbizmo-form-builder' ); ?></option>
						<option value="registration"><?php esc_html_e( 'Registration Form', 'wbizmo-form-builder' ); ?></option>
						<option value="login"><?php esc_html_e( 'Login Form', 'wbizmo-form-builder' ); ?></option>
						<option value="custom"><?php esc_html_e( 'Custom Form', 'wbizmo-form-builder' ); ?></option>
					</select>
				</div>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Choose Form Theme', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Wbizmo Form Builder ships with polished default form themes. You can customize colors later.', 'wbizmo-form-builder' ); ?></p>

			<div class="pf-theme-grid">
				<label class="pf-theme-option pf-theme-aurora">
					<input type="radio" name="form_theme" value="aurora" checked>
					<span class="pf-theme-preview">
						<span class="pf-theme-line"></span>
						<span class="pf-theme-input"></span>
						<span class="pf-theme-button"></span>
					</span>
					<strong><?php esc_html_e( 'Aurora', 'wbizmo-form-builder' ); ?></strong>
					<small><?php esc_html_e( 'Clean, soft and modern.', 'wbizmo-form-builder' ); ?></small>
				</label>

				<label class="pf-theme-option pf-theme-noir">
					<input type="radio" name="form_theme" value="noir">
					<span class="pf-theme-preview">
						<span class="pf-theme-line"></span>
						<span class="pf-theme-input"></span>
						<span class="pf-theme-button"></span>
					</span>
					<strong><?php esc_html_e( 'Noir', 'wbizmo-form-builder' ); ?></strong>
					<small><?php esc_html_e( 'Dark and premium.', 'wbizmo-form-builder' ); ?></small>
				</label>

				<label class="pf-theme-option pf-theme-solace">
					<input type="radio" name="form_theme" value="solace">
					<span class="pf-theme-preview">
						<span class="pf-theme-line"></span>
						<span class="pf-theme-input"></span>
						<span class="pf-theme-button"></span>
					</span>
					<strong><?php esc_html_e( 'Solace', 'wbizmo-form-builder' ); ?></strong>
					<small><?php esc_html_e( 'Warm business style.', 'wbizmo-form-builder' ); ?></small>
				</label>
			</div>
		</div>

		<div class="pf-save-bar">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbizmo-form-builder' ) ); ?>" class="pf-btn pf-btn-light">
				<?php esc_html_e( 'Cancel', 'wbizmo-form-builder' ); ?>
			</a>

			<button type="submit" class="pf-btn pf-btn-primary">
				<span class="dashicons">save</span>
				<?php esc_html_e( 'Create Form', 'wbizmo-form-builder' ); ?>
			</button>
		</div>
	</form>
</div>
