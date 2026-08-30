<?php
/**
 * "Settings" admin screen template.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option( 'wbizfobu_settings', array() );

$log_retention_days       = isset( $options['log_retention_days'] ) ? absint( $options['log_retention_days'] ) : 30;
$upload_max_size          = isset( $options['upload_max_size'] ) ? absint( $options['upload_max_size'] ) : 5;
$allowed_file_types       = isset( $options['allowed_file_types'] ) ? sanitize_text_field( $options['allowed_file_types'] ) : 'jpg,jpeg,png,gif,pdf,doc,docx,txt';
$rate_limit_attempts      = isset( $options['rate_limit_attempts'] ) ? absint( $options['rate_limit_attempts'] ) : 5;
$rate_limit_window        = isset( $options['rate_limit_window'] ) ? absint( $options['rate_limit_window'] ) : 10;
$remove_data_on_uninstall = isset( $options['remove_data_on_uninstall'] ) ? (bool) $options['remove_data_on_uninstall'] : false;
?>

<div class="pf-admin-wrap">
	<div class="pf-hero">
		<div>
			<p class="pf-eyebrow"><?php esc_html_e( 'Settings', 'wbizmo-form-builder' ); ?></p>
			<h1><?php esc_html_e( 'Wbizmo Form Builder Settings', 'wbizmo-form-builder' ); ?></h1>
			<p><?php esc_html_e( 'Manage global security, upload, logging, rate-limit, and uninstall behavior.', 'wbizmo-form-builder' ); ?></p>
		</div>
	</div>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag from a redirect after a nonce-verified admin-post action; no state change happens here. ?>
	<?php if ( isset( $_GET['pf_saved'] ) ) : ?>
		<div class="pf-notice pf-notice-success">
			<span class="dashicons">check_circle</span>
			<?php esc_html_e( 'Settings saved successfully.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pf-builder-shell">
		<?php wp_nonce_field( 'wbizfobu_save_settings' ); ?>
		<input type="hidden" name="action" value="wbizfobu_save_settings">

		<div class="pf-card">
			<h2><?php esc_html_e( 'Uploads', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Control global upload limits for file fields.', 'wbizmo-form-builder' ); ?></p>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="upload_max_size"><?php esc_html_e( 'Maximum Upload Size (MB)', 'wbizmo-form-builder' ); ?></label>
					<input type="number" id="upload_max_size" name="upload_max_size" min="1" max="25" value="<?php echo esc_attr( $upload_max_size ); ?>">
				</div>

				<div class="pf-field">
					<label for="allowed_file_types"><?php esc_html_e( 'Allowed File Types', 'wbizmo-form-builder' ); ?></label>
					<input type="text" id="allowed_file_types" name="allowed_file_types" value="<?php echo esc_attr( $allowed_file_types ); ?>">
					<p class="pf-help"><?php esc_html_e( 'Comma-separated. Example: jpg,png,pdf,docx', 'wbizmo-form-builder' ); ?></p>
				</div>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Rate Limiting', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Protect forms from repeated spam submissions.', 'wbizmo-form-builder' ); ?></p>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="rate_limit_attempts"><?php esc_html_e( 'Attempts Allowed', 'wbizmo-form-builder' ); ?></label>
					<input type="number" id="rate_limit_attempts" name="rate_limit_attempts" min="1" max="50" value="<?php echo esc_attr( $rate_limit_attempts ); ?>">
				</div>

				<div class="pf-field">
					<label for="rate_limit_window"><?php esc_html_e( 'Window Length (Minutes)', 'wbizmo-form-builder' ); ?></label>
					<input type="number" id="rate_limit_window" name="rate_limit_window" min="1" max="1440" value="<?php echo esc_attr( $rate_limit_window ); ?>">
				</div>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Logs', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Control how long Wbizmo Form Builder should keep log records.', 'wbizmo-form-builder' ); ?></p>

			<div class="pf-form-grid">
				<div class="pf-field">
					<label for="log_retention_days"><?php esc_html_e( 'Log Retention Days', 'wbizmo-form-builder' ); ?></label>
					<input type="number" id="log_retention_days" name="log_retention_days" min="1" max="365" value="<?php echo esc_attr( $log_retention_days ); ?>">
				</div>
			</div>
		</div>

		<div class="pf-card">
			<h2><?php esc_html_e( 'Uninstall Behavior', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Choose whether Wbizmo Form Builder should preserve or remove plugin data when the plugin is uninstalled.', 'wbizmo-form-builder' ); ?></p>

			<label class="pf-switch-row">
				<input type="checkbox" name="remove_data_on_uninstall" value="1" <?php checked( $remove_data_on_uninstall ); ?>>
				<span class="pf-switch-ui"></span>
				<span><?php esc_html_e( 'Remove all Wbizmo Form Builder forms, submissions, logs, and settings when plugin is uninstalled', 'wbizmo-form-builder' ); ?></span>
			</label>

			<p class="pf-help">
				<?php esc_html_e( 'Leave this disabled if you want to keep your forms and submissions after uninstalling. Enable it only when you want a full cleanup.', 'wbizmo-form-builder' ); ?>
			</p>
		</div>

		<div class="pf-save-bar">
			<button type="submit" class="pf-btn pf-btn-primary">
				<span class="dashicons">save</span>
				<?php esc_html_e( 'Save Settings', 'wbizmo-form-builder' ); ?>
			</button>
		</div>
	</form>
</div>
