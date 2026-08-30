<?php
/**
 * Sends admin notification and user confirmation emails for form submissions.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds and sends transactional emails for form submissions.
 */
class WBIZFOBU_Emailer {

	/**
	 * Send the admin notification email for a submission.
	 *
	 * @param object $form          Form database row.
	 * @param int    $submission_id Saved submission ID, or null when not saved.
	 * @param array  $clean_data    Sanitized submitted field data.
	 * @param string $page_url      URL of the page the form was submitted from.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function send_admin_notification( $form, $submission_id, $clean_data, $page_url ) {
		$to = get_option( 'admin_email' );

		if ( ! $to || ! is_email( $to ) ) {
			return new WP_Error( 'invalid_admin_email', __( 'The site admin email address is invalid.', 'wbizmo-form-builder' ) );
		}

		// translators: %s is the form name.
		$subject = sprintf( __( 'New submission from %s', 'wbizmo-form-builder' ), $form->name );

		$body = $this->render_template(
			'admin-notification.php',
			array(
				'site_name'     => get_bloginfo( 'name' ),
				'title'         => __( 'New Form Submission', 'wbizmo-form-builder' ),
				/* translators: %s is the form name. */
				'intro'         => sprintf( __( 'A new submission was received from %s.', 'wbizmo-form-builder' ), $form->name ),
				'form'          => $form,
				'submission_id' => $submission_id,
				'clean_data'    => $clean_data,
				'page_url'      => $page_url,
				'footer'        => __( 'This notification was sent by Wbizmo Form Builder.', 'wbizmo-form-builder' ),
			)
		);

		return $this->send( $to, $subject, $body );
	}

	/**
	 * Send the confirmation email to the person who submitted the form.
	 *
	 * @param object $form          Form database row.
	 * @param int    $submission_id Saved submission ID, or null when not saved.
	 * @param array  $clean_data    Sanitized submitted field data.
	 * @param string $page_url      URL of the page the form was submitted from.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function send_user_confirmation( $form, $submission_id, $clean_data, $page_url ) {
		$user_email = $this->extract_email_from_submission( $clean_data );

		if ( ! $user_email || ! is_email( $user_email ) ) {
			return new WP_Error( 'missing_user_email', __( 'Wbizmo Form Builder could not find a valid user email field.', 'wbizmo-form-builder' ) );
		}

		/* translators: %s is the site name. */
		$subject = sprintf( __( 'We received your submission - %s', 'wbizmo-form-builder' ), get_bloginfo( 'name' ) );

		$body = $this->render_template(
			'user-confirmation.php',
			array(
				'site_name'     => get_bloginfo( 'name' ),
				'title'         => __( 'Submission Received', 'wbizmo-form-builder' ),
				'intro'         => __( 'Thank you. Your submission has been received successfully.', 'wbizmo-form-builder' ),
				'form'          => $form,
				'submission_id' => $submission_id,
				'clean_data'    => $clean_data,
				'page_url'      => $page_url,
				/* translators: %s is the site name. */
				'footer'        => sprintf( __( 'Sent from %s using Wbizmo Form Builder.', 'wbizmo-form-builder' ), get_bloginfo( 'name' ) ),
			)
		);

		return $this->send( $user_email, $subject, $body );
	}

	/**
	 * Send an HTML email via wp_mail().
	 *
	 * @param string $to      Recipient email address.
	 * @param string $subject Email subject.
	 * @param string $body    Rendered HTML body.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	private function send( $to, $subject, $body ) {
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( ! $sent ) {
			return new WP_Error( 'wp_mail_failed', __( 'wp_mail returned false.', 'wbizmo-form-builder' ) );
		}

		return true;
	}

	/**
	 * Render an email template with the given variables and return the output.
	 *
	 * @param string $template Template file name relative to templates/emails/.
	 * @param array  $args     Variables made available to the template.
	 * @return string Rendered HTML, or an empty string if the template is missing.
	 */
	private function render_template( $template, $args = array() ) {
		$template_path = WBIZFOBU_PATH . 'templates/emails/' . $template;

		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		$site_name     = isset( $args['site_name'] ) ? $args['site_name'] : '';
		$title         = isset( $args['title'] ) ? $args['title'] : '';
		$intro         = isset( $args['intro'] ) ? $args['intro'] : '';
		$form          = isset( $args['form'] ) ? $args['form'] : null;
		$submission_id = isset( $args['submission_id'] ) ? $args['submission_id'] : 0;
		$clean_data    = isset( $args['clean_data'] ) ? $args['clean_data'] : array();
		$page_url      = isset( $args['page_url'] ) ? $args['page_url'] : '';
		$footer        = isset( $args['footer'] ) ? $args['footer'] : '';

		ob_start();
		include $template_path;
		return ob_get_clean();
	}

	/**
	 * Find the first valid email field value in the submitted data.
	 *
	 * @param array $clean_data Sanitized submitted field data.
	 * @return string|null Email address, or null when none is found.
	 */
	private function extract_email_from_submission( $clean_data ) {
		foreach ( $clean_data as $field ) {
			if (
				isset( $field['type'], $field['value'] ) &&
				'email' === $field['type'] &&
				is_email( $field['value'] )
			) {
				return sanitize_email( $field['value'] );
			}
		}

		return null;
	}
}
