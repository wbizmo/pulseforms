<?php
/**
 * Persists plugin events to the custom logs table.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes structured log entries for submissions, errors, and admin activity.
 */
class WBIZFOBU_Logger {

	/**
	 * Insert a log entry into the custom logs table.
	 *
	 * @param string $severity   Log severity, e.g. 'info', 'warning', 'error', 'critical'.
	 * @param string $event_type Short machine-readable event identifier.
	 * @param string $message    Human-readable log message.
	 * @param array  $context    Optional structured context data for the event.
	 * @return void
	 */
	public static function log( $severity, $event_type, $message, $context = array() ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wbizfobu_logs';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write-only insert into a custom plugin table; nothing to cache.
		$wpdb->insert(
			$table,
			array(
				'severity'          => sanitize_text_field( $severity ),
				'event_type'        => sanitize_text_field( $event_type ),
				'message'           => sanitize_textarea_field( $message ),
				'technical_details' => wp_json_encode( $context ),
				'form_id'           => isset( $context['form_id'] ) ? absint( $context['form_id'] ) : null,
				'form_name'         => isset( $context['form_name'] ) ? sanitize_text_field( $context['form_name'] ) : null,
				'submission_id'     => isset( $context['submission_id'] ) ? absint( $context['submission_id'] ) : null,
				'page_url'          => isset( $context['page_url'] ) ? esc_url_raw( $context['page_url'] ) : null,
				'user_id'           => get_current_user_id() ? get_current_user_id() : null,
				'user_ip'           => self::get_user_ip(),
				'user_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null,
				'php_version'       => PHP_VERSION,
				'wp_version'        => get_bloginfo( 'version' ),
				'plugin_version'    => WBIZFOBU_VERSION,
				'created_at'        => current_time( 'mysql' ),
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	/**
	 * Get a hashed representation of the current visitor's IP address.
	 *
	 * @return string|null Hashed IP, or null when no IP is available.
	 */
	private static function get_user_ip() {
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
