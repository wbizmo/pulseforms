<?php
/**
 * Handles public form submissions: validation, file uploads, storage, and notifications.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes AJAX form submissions from the public-facing shortcode form.
 */
class WBIZFOBU_Form_Processor {

	/**
	 * Register the AJAX submission handler for logged-in and guest users.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_ajax_wbizfobu_submit_form', array( $this, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_wbizfobu_submit_form', array( $this, 'handle_submission' ) );
	}

	/**
	 * Validate, sanitize, and process an incoming form submission.
	 *
	 * @return void Sends a JSON response and exits via wp_send_json_*().
	 */
	public function handle_submission() {
		try {
			$form_id  = isset( $_POST['wbizfobu_form_id'] ) ? absint( $_POST['wbizfobu_form_id'] ) : 0;
			$page_url = isset( $_POST['wbizfobu_page_url'] ) ? esc_url_raw( wp_unslash( $_POST['wbizfobu_page_url'] ) ) : '';

			if ( ! $form_id ) {
				$this->log_and_fail(
					'warning',
					'missing_form_id',
					'Submission failed because form ID was missing.',
					array(
						'page_url' => $page_url,
					)
				);
			}

			$nonce = isset( $_POST['wbizfobu_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wbizfobu_nonce'] ) ) : '';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wbizfobu_submit_' . $form_id ) ) {
				$this->log_and_fail(
					'warning',
					'nonce_failed',
					'Submission failed nonce verification.',
					array(
						'form_id'  => $form_id,
						'page_url' => $page_url,
					)
				);
			}

			$honeypot = isset( $_POST['wbizfobu_website'] ) ? sanitize_text_field( wp_unslash( $_POST['wbizfobu_website'] ) ) : '';

			if ( ! empty( $honeypot ) ) {
				$this->log_and_fail(
					'warning',
					'honeypot_triggered',
					'Submission blocked by honeypot.',
					array(
						'form_id'  => $form_id,
						'page_url' => $page_url,
					)
				);
			}

			$this->check_rate_limit( $form_id, $page_url );

			$form = $this->get_form( $form_id );

			if ( ! $form || 'active' !== $form->status ) {
				$this->log_and_fail(
					'error',
					'form_unavailable',
					'Submission failed because form was unavailable.',
					array(
						'form_id'  => $form_id,
						'page_url' => $page_url,
					)
				);
			}

			$fields   = json_decode( $form->fields, true );
			$settings = json_decode( $form->settings, true );

			if ( ! is_array( $fields ) ) {
				$this->log_and_fail(
					'error',
					'invalid_fields_json',
					'Submission failed because form fields JSON is invalid.',
					array(
						'form_id'    => $form_id,
						'form_name'  => $form->name,
						'page_url'   => $page_url,
						'raw_fields' => $form->fields,
					)
				);
			}

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			if ( ! empty( $settings['captcha_enabled'] ) ) {
				$captcha_answer = isset( $_POST['wbizfobu_captcha_answer'] ) ? sanitize_text_field( wp_unslash( $_POST['wbizfobu_captcha_answer'] ) ) : '';
				$captcha_hash   = isset( $_POST['wbizfobu_captcha_hash'] ) ? sanitize_text_field( wp_unslash( $_POST['wbizfobu_captcha_hash'] ) ) : '';

				if ( '' === $captcha_answer || '' === $captcha_hash || wp_hash( (string) absint( $captcha_answer ) ) !== $captcha_hash ) {
					$this->log_and_fail(
						'warning',
						'custom_captcha_failed',
						'Submission failed custom captcha verification.',
						array(
							'form_id'   => $form_id,
							'form_name' => $form->name,
							'page_url'  => $page_url,
						),
						__( 'Please complete the security check and try again.', 'wbizmo-form-builder' )
					);
				}
			}

			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw values are read per-field below via sanitize_value_by_type()/sanitize_text_field() before use.
			$posted_fields = isset( $_POST['wbizfobu_fields'] ) && is_array( $_POST['wbizfobu_fields'] )
				? wp_unslash( $_POST['wbizfobu_fields'] )
				: array();
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$clean_data        = array();
			$uploaded_files    = array();
			$validation_errors = array();

			foreach ( $fields as $field ) {
				$field_id   = isset( $field['id'] ) ? sanitize_key( $field['id'] ) : '';
				$field_type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
				$label      = isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : $field_id;
				$required   = ! empty( $field['required'] );

				if ( ! $field_id || in_array( $field_type, array( 'html', 'hidden' ), true ) ) {
					continue;
				}

				if ( 'file' === $field_type ) {
					$file_result = $this->handle_file_upload( $field_id, $label, $required, $form, $page_url );

					if ( is_wp_error( $file_result ) ) {
						$validation_errors[] = $file_result->get_error_message();
						continue;
					}

					if ( ! empty( $file_result ) ) {
						$uploaded_files[ $field_id ] = $file_result;

						$clean_data[ $field_id ] = array(
							'label' => $label,
							'type'  => $field_type,
							'value' => isset( $file_result['name'] ) ? $file_result['name'] : '',
						);
					}

					continue;
				}

				$value = isset( $posted_fields[ $field_id ] ) ? $posted_fields[ $field_id ] : '';

				if ( is_array( $value ) ) {
					$value = array_map( 'sanitize_text_field', $value );
					$empty = empty( $value );
				} else {
					$value = $this->sanitize_value_by_type( $value, $field_type );
					$empty = '' === trim( (string) $value );
				}

				if ( $required && $empty ) {
					/* translators: %s is the form field label. */
					$validation_errors[] = sprintf( __( '%s is required.', 'wbizmo-form-builder' ), $label );
					continue;
				}

				if ( 'email' === $field_type && ! $empty && ! is_email( $value ) ) {
					/* translators: %s is the form field label. */
					$validation_errors[] = sprintf( __( '%s must be a valid email address.', 'wbizmo-form-builder' ), $label );
					continue;
				}

				$clean_data[ $field_id ] = array(
					'label' => $label,
					'type'  => $field_type,
					'value' => $value,
				);
			}

			if ( ! empty( $validation_errors ) ) {
				$this->log_and_fail(
					'info',
					'validation_failed',
					'Submission failed validation.',
					array(
						'form_id'           => $form_id,
						'form_name'         => $form->name,
						'page_url'          => $page_url,
						'validation_errors' => $validation_errors,
					),
					__( 'Please check the form and try again.', 'wbizmo-form-builder' )
				);
			}

			$save_submissions    = isset( $settings['save_submissions'] ) ? (bool) $settings['save_submissions'] : true;
			$admin_email_enabled = isset( $settings['admin_email_enabled'] ) ? (bool) $settings['admin_email_enabled'] : false;
			$user_email_enabled  = isset( $settings['user_email_enabled'] ) ? (bool) $settings['user_email_enabled'] : false;

			$submission_id = null;

			if ( $save_submissions ) {
				$submission_id = $this->save_submission( $form, $clean_data, $uploaded_files, $page_url );

				if ( ! $submission_id ) {
					global $wpdb;

					$this->log_and_fail(
						'error',
						'submission_save_failed',
						'Submission could not be saved to the database.',
						array(
							'form_id'   => $form_id,
							'form_name' => $form->name,
							'page_url'  => $page_url,
							'db_error'  => $wpdb->last_error,
						)
					);
				}
			}

			$emailer = new WBIZFOBU_Emailer();

			if ( $admin_email_enabled ) {
				$admin_email_result = $emailer->send_admin_notification( $form, $submission_id, $clean_data, $page_url );

				if ( is_wp_error( $admin_email_result ) ) {
					$this->log_and_fail(
						'error',
						'admin_email_failed',
						'Admin notification email failed.',
						array(
							'form_id'             => $form_id,
							'form_name'           => $form->name,
							'submission_id'       => $submission_id,
							'page_url'            => $page_url,
							'email_error_code'    => $admin_email_result->get_error_code(),
							'email_error_message' => $admin_email_result->get_error_message(),
						)
					);
				}
			}

			if ( $user_email_enabled ) {
				$user_email_result = $emailer->send_user_confirmation( $form, $submission_id, $clean_data, $page_url );

				if ( is_wp_error( $user_email_result ) ) {
					$this->log_and_fail(
						'error',
						'user_email_failed',
						'User confirmation email failed.',
						array(
							'form_id'             => $form_id,
							'form_name'           => $form->name,
							'submission_id'       => $submission_id,
							'page_url'            => $page_url,
							'email_error_code'    => $user_email_result->get_error_code(),
							'email_error_message' => $user_email_result->get_error_message(),
						)
					);
				}
			}

			wp_send_json_success(
				array(
					'message'       => isset( $settings['success_message'] )
						? sanitize_text_field( $settings['success_message'] )
						: __( 'Thank you. Your submission has been received.', 'wbizmo-form-builder' ),
					'submission_id' => $submission_id,
				)
			);

		} catch ( Throwable $e ) {
			WBIZFOBU_Logger::log(
				'critical',
				'unexpected_php_error',
				'Unexpected PHP error during form submission.',
				array(
					'error_message' => $e->getMessage(),
					'error_file'    => $e->getFile(),
					'error_line'    => $e->getLine(),
					'error_trace'   => $e->getTraceAsString(),
					'php_version'   => PHP_VERSION,
					'wp_version'    => get_bloginfo( 'version' ),
				)
			);

			wp_send_json_error(
				array(
					'message' => __( 'Something unexpected went wrong. Please try again later.', 'wbizmo-form-builder' ),
				),
				500
			);
		}
	}

	/**
	 * Fetch a form by ID.
	 *
	 * @param int $form_id Form ID.
	 * @return object|null Form database row, or null when not found.
	 */
	private function get_form( $form_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Single-row lookup on a custom plugin table, needed fresh for every submission.
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wbizfobu_forms WHERE id = %d",
				$form_id
			)
		);
	}

	/**
	 * Get the plugin's global settings, merged with defaults.
	 *
	 * @return array Plugin settings.
	 */
	private function get_plugin_settings() {
		$defaults = array(
			'upload_max_size'     => 5,
			'allowed_file_types'  => 'jpg,jpeg,png,gif,pdf,doc,docx,txt',
			'rate_limit_attempts' => 5,
			'rate_limit_window'   => 10,
			'log_retention_days'  => 30,
		);

		$settings = get_option( 'wbizfobu_settings', array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Sanitize a submitted field value according to its field type.
	 *
	 * @param mixed  $value Raw submitted value.
	 * @param string $type  Field type (email, textarea, number, url, text, etc.).
	 * @return mixed Sanitized value.
	 */
	private function sanitize_value_by_type( $value, $type ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}

		$value = wp_unslash( $value );

		switch ( $type ) {
			case 'email':
				return sanitize_email( $value );

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'number':
				return is_numeric( $value ) ? $value + 0 : '';

			case 'url':
				return esc_url_raw( $value );

			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Validate and upload a single file field.
	 *
	 * The submission nonce is already verified in handle_submission() before
	 * this method runs, so the $_FILES access below does not repeat that check.
	 *
	 * @param string $field_id Sanitized field ID.
	 * @param string $label    Field label, used in error messages.
	 * @param bool   $required Whether the field is required.
	 * @param object $form     Form database row.
	 * @param string $page_url URL of the page the form was submitted from.
	 * @return array|WP_Error|null Upload data array, WP_Error on failure, or null when no file was submitted and not required.
	 */
	private function handle_file_upload( $field_id, $label, $required, $form, $page_url ) {
		$input_name = 'wbizfobu_fields';

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce already verified in handle_submission() before this method is called.
		if (
			empty( $_FILES[ $input_name ] ) ||
			empty( $_FILES[ $input_name ]['name'][ $field_id ] )
		) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			if ( $required ) {
				/* translators: %s is the form field label. */
				return new WP_Error( 'required_file_missing', sprintf( __( '%s is required.', 'wbizmo-form-builder' ), $label ) );
			}

			return null;
		}

		$plugin_settings = $this->get_plugin_settings();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce already verified in handle_submission() before this method is called.
		$file_name = sanitize_file_name( wp_unslash( $_FILES[ $input_name ]['name'][ $field_id ] ) );
		$file_type = isset( $_FILES[ $input_name ]['type'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_FILES[ $input_name ]['type'][ $field_id ] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- tmp_name is a PHP-generated local temp file path, not user-controlled input.
		$tmp_name = isset( $_FILES[ $input_name ]['tmp_name'][ $field_id ] ) ? $_FILES[ $input_name ]['tmp_name'][ $field_id ] : '';
		$error    = isset( $_FILES[ $input_name ]['error'][ $field_id ] ) ? absint( $_FILES[ $input_name ]['error'][ $field_id ] ) : UPLOAD_ERR_NO_FILE;
		$size     = isset( $_FILES[ $input_name ]['size'][ $field_id ] ) ? absint( $_FILES[ $input_name ]['size'][ $field_id ] ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( UPLOAD_ERR_OK !== $error ) {
			WBIZFOBU_Logger::log(
				'error',
				'file_upload_error',
				'File upload failed with PHP upload error.',
				array(
					'form_id'      => $form->id,
					'form_name'    => $form->name,
					'page_url'     => $page_url,
					'field_id'     => $field_id,
					'field_label'  => $label,
					'upload_error' => $error,
				)
			);

			/* translators: %s is the form field label. */
			return new WP_Error( 'file_upload_error', sprintf( __( '%s could not be uploaded.', 'wbizmo-form-builder' ), $label ) );
		}

		$max_size_mb = isset( $plugin_settings['upload_max_size'] ) ? absint( $plugin_settings['upload_max_size'] ) : 5;
		$max_size    = max( 1, $max_size_mb ) * 1024 * 1024;

		if ( $size > $max_size ) {
			/* translators: %s is the form field label. */
			return new WP_Error( 'file_too_large', sprintf( __( '%s is too large.', 'wbizmo-form-builder' ), $label ) );
		}

		$allowed_mimes = $this->get_allowed_mimes_from_settings( $plugin_settings );

		$file_check = wp_check_filetype_and_ext( $tmp_name, $file_name, $allowed_mimes );

		if ( empty( $file_check['type'] ) ) {
			/* translators: %s is the form field label. */
			return new WP_Error( 'invalid_file_type', sprintf( __( '%s file type is not allowed.', 'wbizmo-form-builder' ), $label ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		// wp_handle_upload() takes $file_array by reference, so it must be a variable, not an inline array literal.
		$file_array = array(
			'name'     => $file_name,
			'type'     => $file_type,
			'tmp_name' => $tmp_name,
			'error'    => $error,
			'size'     => $size,
		);

		$uploaded = wp_handle_upload(
			$file_array,
			array(
				'test_form' => false,
				'mimes'     => $allowed_mimes,
			)
		);

		if ( isset( $uploaded['error'] ) ) {
			WBIZFOBU_Logger::log(
				'error',
				'file_upload_failed',
				'WordPress file upload handler failed.',
				array(
					'form_id'      => $form->id,
					'form_name'    => $form->name,
					'page_url'     => $page_url,
					'field_id'     => $field_id,
					'field_label'  => $label,
					'upload_error' => $uploaded['error'],
				)
			);

			/* translators: %s is the form field label. */
			return new WP_Error( 'file_upload_failed', sprintf( __( '%s could not be uploaded.', 'wbizmo-form-builder' ), $label ) );
		}

		return array(
			'field_id' => $field_id,
			'label'    => $label,
			'name'     => basename( $uploaded['file'] ),
			'url'      => esc_url_raw( $uploaded['url'] ),
			'path'     => sanitize_text_field( $uploaded['file'] ),
			'type'     => sanitize_text_field( $uploaded['type'] ),
			'size'     => $size,
		);
	}

	/**
	 * Build the allowed MIME type map from the plugin's allowed-file-types setting.
	 *
	 * @param array $settings Plugin settings.
	 * @return array Map of extension pattern => MIME type, suitable for wp_handle_upload().
	 */
	private function get_allowed_mimes_from_settings( $settings ) {
		$allowed = isset( $settings['allowed_file_types'] )
			? strtolower( sanitize_text_field( $settings['allowed_file_types'] ) )
			: 'jpg,jpeg,png,gif,pdf,doc,docx,txt';

		$requested_types = array_filter( array_map( 'trim', explode( ',', $allowed ) ) );

		$mime_map = array(
			'jpg'  => array( 'jpg|jpeg|jpe', 'image/jpeg' ),
			'jpeg' => array( 'jpg|jpeg|jpe', 'image/jpeg' ),
			'png'  => array( 'png', 'image/png' ),
			'gif'  => array( 'gif', 'image/gif' ),
			'pdf'  => array( 'pdf', 'application/pdf' ),
			'doc'  => array( 'doc', 'application/msword' ),
			'docx' => array( 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
			'txt'  => array( 'txt', 'text/plain' ),
		);

		$mimes = array();

		foreach ( $requested_types as $type ) {
			if ( ! isset( $mime_map[ $type ] ) ) {
				continue;
			}

			$mimes[ $mime_map[ $type ][0] ] = $mime_map[ $type ][1];
		}

		if ( empty( $mimes ) ) {
			$mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'gif'          => 'image/gif',
				'pdf'          => 'application/pdf',
				'doc'          => 'application/msword',
				'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'txt'          => 'text/plain',
			);
		}

		return $mimes;
	}

	/**
	 * Persist a validated submission to the custom submissions table.
	 *
	 * @param object $form           Form database row.
	 * @param array  $clean_data     Sanitized submitted field data.
	 * @param array  $uploaded_files Uploaded file metadata, keyed by field ID.
	 * @param string $page_url       URL of the page the form was submitted from.
	 * @return int|false Inserted submission ID, or false on failure.
	 */
	private function save_submission( $form, $clean_data, $uploaded_files, $page_url ) {
		global $wpdb;

		$user_id = get_current_user_id();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only insert into a custom plugin table; nothing to cache.
		$inserted = $wpdb->insert(
			$wpdb->prefix . 'wbizfobu_submissions',
			array(
				'form_id'         => absint( $form->id ),
				'form_name'       => sanitize_text_field( $form->name ),
				'submission_data' => wp_json_encode( $clean_data ),
				'files'           => wp_json_encode( $uploaded_files ),
				'user_id'         => $user_id ? $user_id : null,
				'user_ip'         => $this->get_user_ip_hash(),
				'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null,
				'page_url'        => $page_url,
				'status'          => 'unread',
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Log a submission failure and send a JSON error response.
	 *
	 * @param string      $severity       Log severity.
	 * @param string      $event_type     Short machine-readable event identifier.
	 * @param string      $message        Internal log message.
	 * @param array       $context        Structured context data for the log entry.
	 * @param string|null $public_message Message shown to the visitor; falls back to a generic message.
	 * @return void This sends a JSON error response and exits.
	 */
	private function log_and_fail( $severity, $event_type, $message, $context = array(), $public_message = null ) {
		WBIZFOBU_Logger::log( $severity, $event_type, $message, $context );

		wp_send_json_error(
			array(
				'message' => $public_message ? $public_message : __( 'Something went wrong. Please try again.', 'wbizmo-form-builder' ),
			),
			400
		);
	}

	/**
	 * Enforce the per-IP submission rate limit for a form.
	 *
	 * @param int    $form_id  Form ID.
	 * @param string $page_url URL of the page the form was submitted from.
	 * @return void Sends a JSON error response and exits when the limit is exceeded.
	 */
	private function check_rate_limit( $form_id, $page_url ) {
		$ip_hash = $this->get_user_ip_hash();

		if ( ! $ip_hash ) {
			return;
		}

		$plugin_settings = $this->get_plugin_settings();

		$max_attempts = isset( $plugin_settings['rate_limit_attempts'] )
			? max( 1, absint( $plugin_settings['rate_limit_attempts'] ) )
			: 5;

		$window_minutes = isset( $plugin_settings['rate_limit_window'] )
			? max( 1, absint( $plugin_settings['rate_limit_window'] ) )
			: 10;

		$key   = 'wbizfobu_rate_' . md5( $form_id . '_' . $ip_hash );
		$count = (int) get_transient( $key );

		if ( $count >= $max_attempts ) {
			$this->log_and_fail(
				'warning',
				'rate_limit_triggered',
				'Submission blocked by rate limiting.',
				array(
					'form_id'      => $form_id,
					'page_url'     => $page_url,
					'ip_hash'      => $ip_hash,
					'max_attempts' => $max_attempts,
					'window'       => $window_minutes,
				),
				__( 'Too many attempts. Please try again later.', 'wbizmo-form-builder' )
			);
		}

		set_transient( $key, $count + 1, $window_minutes * MINUTE_IN_SECONDS );
	}

	/**
	 * Get a hashed representation of the current visitor's IP address.
	 *
	 * @return string|null Hashed IP, or null when no IP is available.
	 */
	private function get_user_ip_hash() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip ? wp_hash( $ip ) : null;
	}
}
