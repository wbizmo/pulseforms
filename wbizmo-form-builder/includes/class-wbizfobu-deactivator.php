<?php
/**
 * Handles plugin deactivation tasks.
 *
 * @package Wbizmo_Form_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clears scheduled events on plugin deactivation.
 */
class WBIZFOBU_Deactivator {

	/**
	 * Remove the scheduled log cleanup cron event.
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'wbizfobu_daily_cleanup' );
	}
}
