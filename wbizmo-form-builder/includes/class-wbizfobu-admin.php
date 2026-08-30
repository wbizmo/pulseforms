<?php
/**
 * Handles the plugin's admin menu, screens, and admin-post CRUD actions.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers admin menus/screens and handles form, submission, log, and settings CRUD.
 */
class WBIZFOBU_Admin {

	/**
	 * Register admin hooks and admin-post action handlers.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		add_action( 'admin_post_wbizfobu_create_form', array( $this, 'handle_create_form' ) );
		add_action( 'admin_post_wbizfobu_update_form', array( $this, 'handle_update_form' ) );
		add_action( 'admin_post_wbizfobu_delete_form', array( $this, 'handle_delete_form' ) );

		add_action( 'admin_post_wbizfobu_delete_submission', array( $this, 'handle_delete_submission' ) );
		add_action( 'admin_post_wbizfobu_mark_submission_read', array( $this, 'handle_mark_submission_read' ) );

		add_action( 'admin_post_wbizfobu_delete_log', array( $this, 'handle_delete_log' ) );
		add_action( 'admin_post_wbizfobu_clear_logs', array( $this, 'handle_clear_logs' ) );

		add_action( 'admin_post_wbizfobu_save_settings', array( $this, 'handle_save_settings' ) );
	}

	/**
	 * Register the plugin's top-level admin menu and submenu pages.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Wbizmo Form Builder', 'wbizmo-form-builder' ),
			__( 'Wbizmo Form Builder', 'wbizmo-form-builder' ),
			'manage_options',
			'wbizmo-form-builder',
			array( $this, 'render_forms_page' ),
			'dashicons-feedback',
			26
		);

		add_submenu_page( 'wbizmo-form-builder', __( 'All Forms', 'wbizmo-form-builder' ), __( 'All Forms', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder', array( $this, 'render_forms_page' ) );
		add_submenu_page( 'wbizmo-form-builder', __( 'Add New', 'wbizmo-form-builder' ), __( 'Add New', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder-add-new', array( $this, 'render_add_new_page' ) );
		add_submenu_page( 'wbizmo-form-builder', __( 'Edit Form', 'wbizmo-form-builder' ), __( 'Edit Form', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder-edit-form', array( $this, 'render_edit_form_page' ) );
		add_submenu_page( 'wbizmo-form-builder', __( 'Submissions', 'wbizmo-form-builder' ), __( 'Submissions', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder-submissions', array( $this, 'render_submissions_page' ) );
		add_submenu_page( 'wbizmo-form-builder', __( 'Logs', 'wbizmo-form-builder' ), __( 'Logs', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder-logs', array( $this, 'render_logs_page' ) );
		add_submenu_page( 'wbizmo-form-builder', __( 'Settings', 'wbizmo-form-builder' ), __( 'Settings', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder-settings', array( $this, 'render_settings_page' ) );
		add_submenu_page( 'wbizmo-form-builder', __( 'Support', 'wbizmo-form-builder' ), __( 'Support', 'wbizmo-form-builder' ), 'manage_options', 'wbizmo-form-builder-support', array( $this, 'render_support_page' ) );
	}

	/**
	 * Enqueue admin CSS/JS on the plugin's own screens only.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'wbizmo-form-builder' ) === false ) {
			return;
		}
		wp_enqueue_style(
			'wbizfobu-admin',
			WBIZFOBU_URL . 'assets/css/admin.css',
			array(),
			WBIZFOBU_VERSION
		);

		wp_enqueue_script(
			'wbizfobu-admin',
			WBIZFOBU_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WBIZFOBU_VERSION,
			true
		);
	}

	/**
	 * Get all forms, most recently created first.
	 *
	 * @return array Array of form database rows.
	 */
	public function get_forms() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin list view on a custom plugin table; always needs fresh data.
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wbizfobu_forms ORDER BY created_at DESC" );
	}

	/**
	 * Fetch a single form by ID.
	 *
	 * @param int $id Form ID.
	 * @return object|null Form database row, or null when not found.
	 */
	public function get_form( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Single-row lookup on a custom plugin table.
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wbizfobu_forms WHERE id = %d", absint( $id ) )
		);
	}

	/**
	 * Get the most recent submissions, capped at 200 rows.
	 *
	 * @return array Array of submission database rows.
	 */
	public function get_submissions() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin list view on a custom plugin table, bounded to the latest 200 rows.
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wbizfobu_submissions ORDER BY created_at DESC LIMIT 200" );
	}

	/**
	 * Fetch a single submission by ID.
	 *
	 * @param int $id Submission ID.
	 * @return object|null Submission database row, or null when not found.
	 */
	public function get_submission( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Single-row lookup on a custom plugin table.
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wbizfobu_submissions WHERE id = %d", absint( $id ) )
		);
	}

	/**
	 * Get the most recent log entries, capped at 300 rows.
	 *
	 * @return array Array of log database rows.
	 */
	public function get_logs() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin list view on a custom plugin table, bounded to the latest 300 rows.
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wbizfobu_logs ORDER BY created_at DESC LIMIT 300" );
	}

	/**
	 * Handle the admin-post action that creates a new form.
	 *
	 * @return void
	 */
	public function handle_create_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to create forms.', 'wbizmo-form-builder' ) );
		}

		check_admin_referer( 'wbizfobu_create_form' );

		global $wpdb;

		$name  = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : '';
		$type  = isset( $_POST['form_type'] ) ? sanitize_key( wp_unslash( $_POST['form_type'] ) ) : 'custom';
		$theme = isset( $_POST['form_theme'] ) ? sanitize_key( wp_unslash( $_POST['form_theme'] ) ) : 'aurora';

		if ( empty( $name ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-add-new&pf_error=missing_name' ) );
			exit;
		}

		$allowed_types = array( 'contact', 'newsletter', 'subscription', 'multi_step', 'registration', 'login', 'custom' );
		if ( ! in_array( $type, $allowed_types, true ) ) {
			$type = 'custom';
		}

		$allowed_themes = array( 'aurora', 'noir', 'solace' );
		if ( ! in_array( $theme, $allowed_themes, true ) ) {
			$theme = 'aurora';
		}

		$settings = array(
			'admin_email_enabled' => ! in_array( $type, array( 'login', 'registration' ), true ),
			'user_email_enabled'  => ! in_array( $type, array( 'login', 'registration' ), true ),
			'save_submissions'    => true,
			'honeypot_enabled'    => true,
			'captcha_enabled'     => false,
			'success_message'     => __( 'Thank you. Your submission has been received.', 'wbizmo-form-builder' ),
			'error_message'       => __( 'Something went wrong. Please try again.', 'wbizmo-form-builder' ),
			'submit_text'         => $this->get_default_submit_text( $type ),
		);

		$style_settings = array(
			'theme'         => $theme,
			'style_mode'    => 'pulse',
			'primary_color' => '#0E2238',
			'accent_color'  => '#C5A572',
			'button_radius' => '14',
			'field_radius'  => '14',
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only insert into a custom plugin table; nothing to cache.
		$inserted = $wpdb->insert(
			$wpdb->prefix . 'wbizfobu_forms',
			array(
				'name'           => $name,
				'type'           => $type,
				'fields'         => wp_json_encode( $this->get_default_fields_for_type( $type ) ),
				'settings'       => wp_json_encode( $settings ),
				'style_settings' => wp_json_encode( $style_settings ),
				'status'         => 'active',
				'created_at'     => current_time( 'mysql' ),
				'updated_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			WBIZFOBU_Logger::log(
				'error',
				'form_create_failed',
				'Wbizmo Form Builder could not create the form.',
				array(
					'form_name' => $name,
					'form_type' => $type,
					'db_error'  => $wpdb->last_error,
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-add-new&pf_error=create_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_created=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that updates an existing form.
	 *
	 * @return void
	 */
	public function handle_update_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to update forms.', 'wbizmo-form-builder' ) );
		}

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

		if ( ! $form_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_error=missing_form' ) );
			exit;
		}

		check_admin_referer( 'wbizfobu_update_form_' . $form_id );

		$form = $this->get_form( $form_id );

		if ( ! $form ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_error=form_not_found' ) );
			exit;
		}

		global $wpdb;

		$name       = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : '';
		$status     = isset( $_POST['form_status'] ) ? sanitize_key( wp_unslash( $_POST['form_status'] ) ) : 'active';
		$fields_raw = isset( $_POST['form_fields'] ) ? sanitize_textarea_field( wp_unslash( $_POST['form_fields'] ) ) : '';

		if ( empty( $name ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-edit-form&form_id=' . $form_id . '&pf_error=missing_name' ) );
			exit;
		}

		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'active';
		}

		$decoded_fields = json_decode( $fields_raw, true );

		if ( ! is_array( $decoded_fields ) ) {
			WBIZFOBU_Logger::log(
				'error',
				'form_update_invalid_json',
				'Form update failed because fields JSON was invalid.',
				array(
					'form_id'    => $form_id,
					'form_name'  => $name,
					'json_error' => json_last_error_msg(),
					'raw_fields' => $fields_raw,
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-edit-form&form_id=' . $form_id . '&pf_error=invalid_json' ) );
			exit;
		}

		$sanitized_fields = $this->sanitize_fields_array( $decoded_fields );

		$settings = array(
			'admin_email_enabled' => isset( $_POST['admin_email_enabled'] ),
			'user_email_enabled'  => isset( $_POST['user_email_enabled'] ),
			'save_submissions'    => isset( $_POST['save_submissions'] ),
			'honeypot_enabled'    => isset( $_POST['honeypot_enabled'] ),
			'captcha_enabled'     => isset( $_POST['captcha_enabled'] ),
			'success_message'     => isset( $_POST['success_message'] ) ? sanitize_text_field( wp_unslash( $_POST['success_message'] ) ) : __( 'Thank you. Your submission has been received.', 'wbizmo-form-builder' ),
			'error_message'       => isset( $_POST['error_message'] ) ? sanitize_text_field( wp_unslash( $_POST['error_message'] ) ) : __( 'Something went wrong. Please try again.', 'wbizmo-form-builder' ),
			'submit_text'         => isset( $_POST['submit_text'] ) ? sanitize_text_field( wp_unslash( $_POST['submit_text'] ) ) : __( 'Submit', 'wbizmo-form-builder' ),
		);

		$theme      = isset( $_POST['form_theme'] ) ? sanitize_key( wp_unslash( $_POST['form_theme'] ) ) : 'aurora';
		$style_mode = isset( $_POST['style_mode'] ) ? sanitize_key( wp_unslash( $_POST['style_mode'] ) ) : 'pulse';

		if ( ! in_array( $theme, array( 'aurora', 'noir', 'solace' ), true ) ) {
			$theme = 'aurora';
		}

		if ( ! in_array( $style_mode, array( 'pulse', 'inherit' ), true ) ) {
			$style_mode = 'pulse';
		}

		$style_settings = array(
			'theme'         => $theme,
			'style_mode'    => $style_mode,
			'primary_color' => isset( $_POST['primary_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['primary_color'] ) ) : '#0E2238',
			'accent_color'  => isset( $_POST['accent_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['accent_color'] ) ) : '#C5A572',
			'button_radius' => isset( $_POST['button_radius'] ) ? absint( $_POST['button_radius'] ) : 14,
			'field_radius'  => isset( $_POST['field_radius'] ) ? absint( $_POST['field_radius'] ) : 14,
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only update on a custom plugin table; nothing to cache.
		$updated = $wpdb->update(
			$wpdb->prefix . 'wbizfobu_forms',
			array(
				'name'           => $name,
				'fields'         => wp_json_encode( $sanitized_fields ),
				'settings'       => wp_json_encode( $settings ),
				'style_settings' => wp_json_encode( $style_settings ),
				'status'         => $status,
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => $form_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			WBIZFOBU_Logger::log(
				'error',
				'form_update_failed',
				'Wbizmo Form Builder could not update the form.',
				array(
					'form_id'   => $form_id,
					'form_name' => $name,
					'db_error'  => $wpdb->last_error,
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-edit-form&form_id=' . $form_id . '&pf_error=update_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_updated=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that deletes a form.
	 *
	 * @return void
	 */
	public function handle_delete_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to delete forms.', 'wbizmo-form-builder' ) );
		}

		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		if ( ! $form_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_error=missing_form' ) );
			exit;
		}

		check_admin_referer( 'wbizfobu_delete_form_' . $form_id );

		global $wpdb;

		$form = $this->get_form( $form_id );

		if ( ! $form ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_error=form_not_found' ) );
			exit;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only delete on a custom plugin table; nothing to cache.
		$deleted = $wpdb->delete( $wpdb->prefix . 'wbizfobu_forms', array( 'id' => $form_id ), array( '%d' ) );

		if ( ! $deleted ) {
			WBIZFOBU_Logger::log(
				'error',
				'form_delete_failed',
				'Wbizmo Form Builder could not delete the form.',
				array(
					'form_id'   => $form_id,
					'form_name' => $form->name,
					'db_error'  => $wpdb->last_error,
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_error=delete_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder&pf_deleted=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that deletes a submission.
	 *
	 * @return void
	 */
	public function handle_delete_submission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to delete submissions.', 'wbizmo-form-builder' ) );
		}

		$submission_id = isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : 0;

		if ( ! $submission_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_error=missing_submission' ) );
			exit;
		}

		check_admin_referer( 'wbizfobu_delete_submission_' . $submission_id );

		global $wpdb;

		$submission = $this->get_submission( $submission_id );

		if ( ! $submission ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_error=submission_not_found' ) );
			exit;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only delete on a custom plugin table; nothing to cache.
		$deleted = $wpdb->delete( $wpdb->prefix . 'wbizfobu_submissions', array( 'id' => $submission_id ), array( '%d' ) );

		if ( ! $deleted ) {
			WBIZFOBU_Logger::log(
				'error',
				'submission_delete_failed',
				'Wbizmo Form Builder could not delete the submission.',
				array(
					'submission_id' => $submission_id,
					'db_error'      => $wpdb->last_error,
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_error=delete_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_deleted=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that marks a submission as read.
	 *
	 * @return void
	 */
	public function handle_mark_submission_read() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to update submissions.', 'wbizmo-form-builder' ) );
		}

		$submission_id = isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : 0;

		if ( ! $submission_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_error=missing_submission' ) );
			exit;
		}

		check_admin_referer( 'wbizfobu_mark_submission_read_' . $submission_id );

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only update on a custom plugin table; nothing to cache.
		$updated = $wpdb->update(
			$wpdb->prefix . 'wbizfobu_submissions',
			array( 'status' => 'read' ),
			array( 'id' => $submission_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			WBIZFOBU_Logger::log(
				'error',
				'submission_mark_read_failed',
				'Wbizmo Form Builder could not mark the submission as read.',
				array(
					'submission_id' => $submission_id,
					'db_error'      => $wpdb->last_error,
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_error=mark_read_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-submissions&pf_read=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that deletes a single log entry.
	 *
	 * @return void
	 */
	public function handle_delete_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to delete logs.', 'wbizmo-form-builder' ) );
		}

		$log_id = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;

		if ( ! $log_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-logs&pf_error=missing_log' ) );
			exit;
		}

		check_admin_referer( 'wbizfobu_delete_log_' . $log_id );

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only delete on a custom plugin table; nothing to cache.
		$deleted = $wpdb->delete( $wpdb->prefix . 'wbizfobu_logs', array( 'id' => $log_id ), array( '%d' ) );

		if ( ! $deleted ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-logs&pf_error=delete_failed' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-logs&pf_deleted=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that clears all log entries.
	 *
	 * @return void
	 */
	public function handle_clear_logs() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to clear logs.', 'wbizmo-form-builder' ) );
		}

		check_admin_referer( 'wbizfobu_clear_logs' );

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Explicit admin action to clear this plugin's own log table.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wbizfobu_logs" );

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-logs&pf_cleared=1' ) );
		exit;
	}

	/**
	 * Handle the admin-post action that saves the plugin's global settings.
	 *
	 * @return void
	 */
	public function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to save settings.', 'wbizmo-form-builder' ) );
		}

		check_admin_referer( 'wbizfobu_save_settings' );

		$allowed_file_types = isset( $_POST['allowed_file_types'] )
			? sanitize_text_field( wp_unslash( $_POST['allowed_file_types'] ) )
			: 'jpg,jpeg,png,gif,pdf,doc,docx,txt';

		$settings = array(
			'upload_max_size'          => isset( $_POST['upload_max_size'] )
				? max( 1, min( 25, absint( $_POST['upload_max_size'] ) ) )
				: 5,

			'allowed_file_types'       => $allowed_file_types,

			'rate_limit_attempts'      => isset( $_POST['rate_limit_attempts'] )
				? max( 1, min( 50, absint( $_POST['rate_limit_attempts'] ) ) )
				: 5,

			'rate_limit_window'        => isset( $_POST['rate_limit_window'] )
				? max( 1, min( 1440, absint( $_POST['rate_limit_window'] ) ) )
				: 10,

			'log_retention_days'       => isset( $_POST['log_retention_days'] )
				? max( 1, min( 365, absint( $_POST['log_retention_days'] ) ) )
				: 30,

			'remove_data_on_uninstall' => isset( $_POST['remove_data_on_uninstall'] ),
		);

		update_option( 'wbizfobu_settings', $settings );

		wp_safe_redirect( admin_url( 'admin.php?page=wbizmo-form-builder-settings&pf_saved=1' ) );
		exit;
	}

	/**
	 * Sanitize a decoded fields array submitted from the form builder JSON editor.
	 *
	 * @param array $fields Raw decoded fields array.
	 * @return array Sanitized fields array.
	 */
	private function sanitize_fields_array( $fields ) {
		$allowed_types = array(
			'text',
			'email',
			'phone',
			'number',
			'textarea',
			'select',
			'radio',
			'checkbox',
			'toggle',
			'date',
			'file',
			'hidden',
			'html',
			'password',
		);

		$clean = array();

		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$id   = isset( $field['id'] ) ? sanitize_key( $field['id'] ) : '';
			$type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';

			if ( ! $id ) {
				continue;
			}

			if ( ! in_array( $type, $allowed_types, true ) ) {
				$type = 'text';
			}

			$clean_field = array(
				'id'          => $id,
				'type'        => $type,
				'label'       => isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : ucfirst( $id ),
				'placeholder' => isset( $field['placeholder'] ) ? sanitize_text_field( $field['placeholder'] ) : '',
				'required'    => ! empty( $field['required'] ),
				'width'       => isset( $field['width'] ) && in_array( $field['width'], array( 'full', 'half' ), true ) ? sanitize_key( $field['width'] ) : 'full',
			);

			if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
				$clean_field['options'] = array_map( 'sanitize_text_field', $field['options'] );
			}

			$clean[] = $clean_field;
		}

		return $clean;
	}

	/**
	 * Get the default submit button text for a form type.
	 *
	 * @param string $type Form type.
	 * @return string Default submit button text.
	 */
	private function get_default_submit_text( $type ) {
		$map = array(
			'contact'      => __( 'Send Message', 'wbizmo-form-builder' ),
			'newsletter'   => __( 'Subscribe', 'wbizmo-form-builder' ),
			'subscription' => __( 'Subscribe', 'wbizmo-form-builder' ),
			'multi_step'   => __( 'Submit Form', 'wbizmo-form-builder' ),
			'registration' => __( 'Create Account', 'wbizmo-form-builder' ),
			'login'        => __( 'Login', 'wbizmo-form-builder' ),
			'custom'       => __( 'Submit', 'wbizmo-form-builder' ),
		);

		return $map[ $type ] ?? __( 'Submit', 'wbizmo-form-builder' );
	}

	/**
	 * Get the default field set for a newly created form of a given type.
	 *
	 * @param string $type Form type.
	 * @return array Array of field definitions.
	 */
	private function get_default_fields_for_type( $type ) {
		$base_name = array(
			array(
				'id'          => 'name',
				'type'        => 'text',
				'label'       => __( 'Name', 'wbizmo-form-builder' ),
				'placeholder' => __( 'Enter your name', 'wbizmo-form-builder' ),
				'required'    => true,
				'width'       => 'full',
			),
		);

		$email = array(
			array(
				'id'          => 'email',
				'type'        => 'email',
				'label'       => __( 'Email Address', 'wbizmo-form-builder' ),
				'placeholder' => __( 'Enter your email address', 'wbizmo-form-builder' ),
				'required'    => true,
				'width'       => 'full',
			),
		);

		if ( 'contact' === $type ) {
			return array_merge(
				$base_name,
				$email,
				array(
					array(
						'id'          => 'message',
						'type'        => 'textarea',
						'label'       => __( 'Message', 'wbizmo-form-builder' ),
						'placeholder' => __( 'Write your message', 'wbizmo-form-builder' ),
						'required'    => true,
						'width'       => 'full',
					),
				)
			);
		}

		if ( in_array( $type, array( 'newsletter', 'subscription' ), true ) ) {
			return $email;
		}

		if ( 'registration' === $type ) {
			return array_merge(
				$base_name,
				$email,
				array(
					array(
						'id'          => 'password',
						'type'        => 'password',
						'label'       => __( 'Password', 'wbizmo-form-builder' ),
						'placeholder' => __( 'Create a password', 'wbizmo-form-builder' ),
						'required'    => true,
						'width'       => 'full',
					),
				)
			);
		}

		if ( 'login' === $type ) {
			return array(
				array(
					'id'          => 'username',
					'type'        => 'text',
					'label'       => __( 'Username or Email', 'wbizmo-form-builder' ),
					'placeholder' => __( 'Enter username or email', 'wbizmo-form-builder' ),
					'required'    => true,
					'width'       => 'full',
				),
				array(
					'id'          => 'password',
					'type'        => 'password',
					'label'       => __( 'Password', 'wbizmo-form-builder' ),
					'placeholder' => __( 'Enter password', 'wbizmo-form-builder' ),
					'required'    => true,
					'width'       => 'full',
				),
			);
		}

		return array_merge( $base_name, $email );
	}

	/**
	 * Render the "All Forms" admin page.
	 *
	 * @return void
	 */
	public function render_forms_page() {
		$forms = $this->get_forms();
		require WBIZFOBU_PATH . 'admin/views/forms.php';
	}

	/**
	 * Render the "Add New Form" admin page.
	 *
	 * @return void
	 */
	public function render_add_new_page() {
		require WBIZFOBU_PATH . 'admin/views/add-new.php';
	}

	/**
	 * Render the "Edit Form" admin page.
	 *
	 * @return void
	 */
	public function render_edit_form_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only page lookup, not a state change; the screen is already capability-gated via add_submenu_page().
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		$form    = $this->get_form( $form_id );

		if ( ! $form ) {
			echo '<div class="pf-admin-wrap"><div class="pf-card"><h2>' . esc_html__( 'Form not found.', 'wbizmo-form-builder' ) . '</h2><p>' . esc_html__( 'The requested form could not be found.', 'wbizmo-form-builder' ) . '</p></div></div>';
			return;
		}

		require WBIZFOBU_PATH . 'admin/views/edit-form.php';
	}

	/**
	 * Render the "Submissions" admin page.
	 *
	 * @return void
	 */
	public function render_submissions_page() {
		$submissions = $this->get_submissions();
		require WBIZFOBU_PATH . 'admin/views/submissions.php';
	}

	/**
	 * Render the "Logs" admin page.
	 *
	 * @return void
	 */
	public function render_logs_page() {
		$logs = $this->get_logs();
		require WBIZFOBU_PATH . 'admin/views/logs.php';
	}

	/**
	 * Render the "Settings" admin page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		require WBIZFOBU_PATH . 'admin/views/settings.php';
	}

	/**
	 * Render the "Support" admin page.
	 *
	 * @return void
	 */
	public function render_support_page() {
		require WBIZFOBU_PATH . 'admin/views/support.php';
	}
}
