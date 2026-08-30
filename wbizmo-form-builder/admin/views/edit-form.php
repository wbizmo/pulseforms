<?php
/**
 * "Edit Form" admin screen template.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields         = json_decode( $form->fields, true );
$settings       = json_decode( $form->settings, true );
$style_settings = json_decode( $form->style_settings, true );

if ( ! is_array( $fields ) ) {
	$fields = array();
}

if ( ! is_array( $settings ) ) {
	$settings = array();
}

if ( ! is_array( $style_settings ) ) {
	$style_settings = array();
}

$theme         = isset( $style_settings['theme'] ) ? $style_settings['theme'] : 'aurora';
$style_mode    = isset( $style_settings['style_mode'] ) ? $style_settings['style_mode'] : 'pulse';
$primary_color = isset( $style_settings['primary_color'] ) ? $style_settings['primary_color'] : '#0E2238';
$accent_color  = isset( $style_settings['accent_color'] ) ? $style_settings['accent_color'] : '#C5A572';
$field_radius  = isset( $style_settings['field_radius'] ) ? $style_settings['field_radius'] : '14';
$button_radius = isset( $style_settings['button_radius'] ) ? $style_settings['button_radius'] : '14';

$admin_email_enabled = ! empty( $settings['admin_email_enabled'] );
$user_email_enabled  = ! empty( $settings['user_email_enabled'] );
$save_submissions    = ! empty( $settings['save_submissions'] );
$honeypot_enabled    = ! empty( $settings['honeypot_enabled'] );
$captcha_enabled     = ! empty( $settings['captcha_enabled'] );
$submit_text         = isset( $settings['submit_text'] ) ? $settings['submit_text'] : __( 'Submit', 'wbizmo-form-builder' );
$success_message     = isset( $settings['success_message'] ) ? $settings['success_message'] : __( 'Thank you. Your submission has been received.', 'wbizmo-form-builder' );
$error_message       = isset( $settings['error_message'] ) ? $settings['error_message'] : __( 'Something went wrong. Please try again.', 'wbizmo-form-builder' );
?>

<div class="pf-admin-wrap">
	<div class="pf-hero">
		<div>
			<p class="pf-eyebrow"><?php esc_html_e( 'Edit Form', 'wbizmo-form-builder' ); ?></p>
			<h1><?php echo esc_html( $form->name ); ?></h1>
			<p><?php esc_html_e( 'Customize fields, styling, email behavior, security, and frontend messages.', 'wbizmo-form-builder' ); ?></p>
		</div>

		<div class="pf-shortcode-copy">
			<code>[wbizfobu_form id="<?php echo esc_attr( $form->id ); ?>"]</code>
			<button type="button" class="pf-icon-btn pf-copy-shortcode" data-shortcode='[wbizfobu_form id="<?php echo esc_attr( $form->id ); ?>"]'>
				<span class="dashicons">content_copy</span>
			</button>
		</div>
	</div>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag from a redirect after a nonce-verified admin-post action; no state change happens here. ?>
	<?php if ( isset( $_GET['pf_error'] ) ) : ?>
		<div class="pf-notice pf-notice-error">
			<span class="dashicons">error</span>
			<?php esc_html_e( 'Something went wrong while updating the form.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pf-builder-shell">
		<?php wp_nonce_field( 'wbizfobu_update_form_' . absint( $form->id ) ); ?>
		<input type="hidden" name="action" value="wbizfobu_update_form">
		<input type="hidden" name="form_id" value="<?php echo esc_attr( $form->id ); ?>">

		<div class="pf-card">
			<h2><?php esc_html_e( 'Basic Details', 'wbizmo-form-builder' ); ?></h2>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="form_name"><?php esc_html_e( 'Form Name', 'wbizmo-form-builder' ); ?></label>
					<input type="text" id="form_name" name="form_name" value="<?php echo esc_attr( $form->name ); ?>" required>
				</div>

				<div class="pf-field">
					<label for="form_status"><?php esc_html_e( 'Status', 'wbizmo-form-builder' ); ?></label>
					<select id="form_status" name="form_status">
						<option value="active" <?php selected( $form->status, 'active' ); ?>><?php esc_html_e( 'Active', 'wbizmo-form-builder' ); ?></option>
						<option value="inactive" <?php selected( $form->status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'wbizmo-form-builder' ); ?></option>
					</select>
				</div>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Fields', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Fast V1 editor: edit the form fields as JSON. The visual drag-and-drop builder can be layered on top later.', 'wbizmo-form-builder' ); ?></p>

			<div class="pf-field">
				<label for="form_fields"><?php esc_html_e( 'Fields JSON', 'wbizmo-form-builder' ); ?></label>
				<textarea id="form_fields" name="form_fields" rows="14"><?php echo esc_textarea( wp_json_encode( $fields, JSON_PRETTY_PRINT ) ); ?></textarea>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Email Settings', 'wbizmo-form-builder' ); ?></h2>

			<div class="pf-toggle-grid">
				<label class="pf-switch-row">
					<input type="checkbox" name="admin_email_enabled" value="1" <?php checked( $admin_email_enabled ); ?>>
					<span class="pf-switch-ui"></span>
					<span><?php esc_html_e( 'Send admin notification email', 'wbizmo-form-builder' ); ?></span>
				</label>

				<label class="pf-switch-row">
					<input type="checkbox" name="user_email_enabled" value="1" <?php checked( $user_email_enabled ); ?>>
					<span class="pf-switch-ui"></span>
					<span><?php esc_html_e( 'Send user confirmation email', 'wbizmo-form-builder' ); ?></span>
				</label>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Submission & Security', 'wbizmo-form-builder' ); ?></h2>

			<div class="pf-toggle-grid">
				<label class="pf-switch-row">
					<input type="checkbox" name="save_submissions" value="1" <?php checked( $save_submissions ); ?>>
					<span class="pf-switch-ui"></span>
					<span><?php esc_html_e( 'Save submissions in WordPress admin', 'wbizmo-form-builder' ); ?></span>
				</label>

				<label class="pf-switch-row">
					<input type="checkbox" name="honeypot_enabled" value="1" <?php checked( $honeypot_enabled ); ?>>
					<span class="pf-switch-ui"></span>
					<span><?php esc_html_e( 'Enable honeypot spam protection', 'wbizmo-form-builder' ); ?></span>
				</label>

				<label class="pf-switch-row">
					<input type="checkbox" name="captcha_enabled" value="1" <?php checked( $captcha_enabled ); ?>>
					<span class="pf-switch-ui"></span>
					<span><?php esc_html_e( 'Enable custom captcha later', 'wbizmo-form-builder' ); ?></span>
				</label>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Messages', 'wbizmo-form-builder' ); ?></h2>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="submit_text"><?php esc_html_e( 'Submit Button Text', 'wbizmo-form-builder' ); ?></label>
					<input type="text" id="submit_text" name="submit_text" value="<?php echo esc_attr( $submit_text ); ?>">
				</div>

				<div class="pf-field">
					<label for="success_message"><?php esc_html_e( 'Success Message', 'wbizmo-form-builder' ); ?></label>
					<input type="text" id="success_message" name="success_message" value="<?php echo esc_attr( $success_message ); ?>">
				</div>
			</div>

			<div class="pf-field">
				<label for="error_message"><?php esc_html_e( 'Fallback Error Message', 'wbizmo-form-builder' ); ?></label>
				<input type="text" id="error_message" name="error_message" value="<?php echo esc_attr( $error_message ); ?>">
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Style Settings', 'wbizmo-form-builder' ); ?></h2>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="form_theme"><?php esc_html_e( 'Theme', 'wbizmo-form-builder' ); ?></label>
					<select id="form_theme" name="form_theme">
						<option value="aurora" <?php selected( $theme, 'aurora' ); ?>><?php esc_html_e( 'Aurora', 'wbizmo-form-builder' ); ?></option>
						<option value="noir" <?php selected( $theme, 'noir' ); ?>><?php esc_html_e( 'Noir', 'wbizmo-form-builder' ); ?></option>
						<option value="solace" <?php selected( $theme, 'solace' ); ?>><?php esc_html_e( 'Solace', 'wbizmo-form-builder' ); ?></option>
					</select>
				</div>

				<div class="pf-field">
					<label for="style_mode"><?php esc_html_e( 'Style Mode', 'wbizmo-form-builder' ); ?></label>
					<select id="style_mode" name="style_mode">
						<option value="pulse" <?php selected( $style_mode, 'pulse' ); ?>><?php esc_html_e( 'Wbizmo Form Builder styling', 'wbizmo-form-builder' ); ?></option>
						<option value="inherit" <?php selected( $style_mode, 'inherit' ); ?>><?php esc_html_e( 'Inherit theme styling', 'wbizmo-form-builder' ); ?></option>
					</select>
				</div>

				<div class="pf-field">
					<label for="primary_color"><?php esc_html_e( 'Primary Color', 'wbizmo-form-builder' ); ?></label>
					<input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr( $primary_color ); ?>">
				</div>

				<div class="pf-field">
					<label for="accent_color"><?php esc_html_e( 'Accent Color', 'wbizmo-form-builder' ); ?></label>
					<input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr( $accent_color ); ?>">
				</div>

				<div class="pf-field">
					<label for="field_radius"><?php esc_html_e( 'Field Radius', 'wbizmo-form-builder' ); ?></label>
					<input type="number" id="field_radius" name="field_radius" value="<?php echo esc_attr( $field_radius ); ?>">
				</div>

				<div class="pf-field">
					<label for="button_radius"><?php esc_html_e( 'Button Radius', 'wbizmo-form-builder' ); ?></label>
					<input type="number" id="button_radius" name="button_radius" value="<?php echo esc_attr( $button_radius ); ?>">
				</div>
			</div>
		</div>

		<div class="pf-save-bar">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbizmo-form-builder' ) ); ?>" class="pf-btn pf-btn-light"><?php esc_html_e( 'Cancel', 'wbizmo-form-builder' ); ?></a>

			<button type="submit" class="pf-btn pf-btn-primary">
				<span class="dashicons">save</span>
				<?php esc_html_e( 'Save Changes', 'wbizmo-form-builder' ); ?>
			</button>
		</div>
	</form>
</div>
