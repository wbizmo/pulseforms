<?php
/**
 * "Logs" admin screen template.
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
			<p class="pf-eyebrow"><?php esc_html_e( 'Logs', 'wbizmo-form-builder' ); ?></p>
			<h1><?php esc_html_e( 'Error & Activity Logs', 'wbizmo-form-builder' ); ?></h1>
			<p><?php esc_html_e( 'Review frontend failures, PHP issues, submission errors, email failures, and environment problems.', 'wbizmo-form-builder' ); ?></p>
		</div>

		<?php if ( ! empty( $logs ) ) : ?>
			<a
				href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wbizfobu_clear_logs' ), 'wbizfobu_clear_logs' ) ); ?>"
				class="pf-btn pf-btn-light"
				onclick="return confirm('<?php echo esc_js( __( 'Clear all Wbizmo Form Builder logs?', 'wbizmo-form-builder' ) ); ?>');"
			>
				<span class="dashicons">delete_sweep</span>
				<?php esc_html_e( 'Clear Logs', 'wbizmo-form-builder' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<?php // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display flags from a redirect after a nonce-verified admin-post action; no state change happens here. ?>
	<?php if ( isset( $_GET['pf_deleted'] ) ) : ?>
		<div class="pf-notice pf-notice-success">
			<span class="dashicons">check_circle</span>
			<?php esc_html_e( 'Log deleted successfully.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_cleared'] ) ) : ?>
		<div class="pf-notice pf-notice-success">
			<span class="dashicons">check_circle</span>
			<?php esc_html_e( 'Logs cleared successfully.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_error'] ) ) : ?>
		<div class="pf-notice pf-notice-error">
			<span class="dashicons">error</span>
			<?php esc_html_e( 'Something went wrong while updating logs.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>
	<?php // phpcs:enable WordPress.Security.NonceVerification.Recommended ?>

	<?php if ( ! empty( $logs ) ) : ?>
		<div class="pf-card">
			<div class="pf-table-header">
				<div>
					<h2><?php esc_html_e( 'Latest Logs', 'wbizmo-form-builder' ); ?></h2>
					<p><?php esc_html_e( 'Showing the latest 300 log entries.', 'wbizmo-form-builder' ); ?></p>
				</div>
			</div>

			<div class="pf-log-list">
				<?php foreach ( $logs as $log ) : ?>
					<?php
						$details    = json_decode( $log->technical_details, true );
						$delete_url = wp_nonce_url(
							admin_url( 'admin-post.php?action=wbizfobu_delete_log&log_id=' . absint( $log->id ) ),
							'wbizfobu_delete_log_' . absint( $log->id )
						);
					?>

					<article class="pf-log-card pf-log-<?php echo esc_attr( $log->severity ); ?>">
						<div class="pf-log-top">
							<div>
								<div class="pf-log-title-row">
									<span class="pf-log-severity pf-log-severity-<?php echo esc_attr( $log->severity ); ?>">
										<?php echo esc_html( $log->severity ); ?>
									</span>

									<h3><?php echo esc_html( $log->event_type ); ?></h3>
								</div>

								<p><?php echo esc_html( $log->message ); ?></p>

								<div class="pf-log-meta">
									<span><?php echo esc_html( mysql2date( 'M j, Y g:i A', $log->created_at ) ); ?></span>
									<span>
										<?php
										/* translators: %s is the PHP version. */
										echo esc_html( sprintf( __( 'PHP %s', 'wbizmo-form-builder' ), $log->php_version ? $log->php_version : '—' ) );
										?>
									</span>
									<span>
										<?php
										/* translators: %s is the WordPress version. */
										echo esc_html( sprintf( __( 'WP %s', 'wbizmo-form-builder' ), $log->wp_version ? $log->wp_version : '—' ) );
										?>
									</span>
									<span>
										<?php
										/* translators: %s is the plugin version. */
										echo esc_html( sprintf( __( 'Wbizmo Form Builder %s', 'wbizmo-form-builder' ), $log->plugin_version ? $log->plugin_version : '—' ) );
										?>
									</span>
								</div>
							</div>

							<div class="pf-submission-actions">
								<button type="button" class="pf-icon-btn pf-toggle-submission" data-target="pf-log-<?php echo esc_attr( $log->id ); ?>">
									<span class="dashicons">visibility</span>
								</button>

								<button type="button" class="pf-icon-btn pf-copy-log" data-target="pf-log-copy-<?php echo esc_attr( $log->id ); ?>">
									<span class="dashicons">content_copy</span>
								</button>

								<a href="<?php echo esc_url( $delete_url ); ?>" class="pf-icon-btn pf-danger" onclick="return confirm('<?php echo esc_js( __( 'Delete this log?', 'wbizmo-form-builder' ) ); ?>');">
									<span class="dashicons">delete</span>
								</a>
							</div>
						</div>

						<div class="pf-submission-details" id="pf-log-<?php echo esc_attr( $log->id ); ?>">
							<div class="pf-submission-grid">
								<div>
									<strong><?php esc_html_e( 'Log ID', 'wbizmo-form-builder' ); ?></strong>
									<span><?php echo esc_html( $log->id ); ?></span>
								</div>

								<div>
									<strong><?php esc_html_e( 'Form', 'wbizmo-form-builder' ); ?></strong>
									<span>
										<?php
										if ( $log->form_id ) {
											/* translators: 1: form ID, 2: form name. */
											echo esc_html( sprintf( __( '#%1$s — %2$s', 'wbizmo-form-builder' ), $log->form_id, $log->form_name ) );
										} else {
											echo '&mdash;';
										}
										?>
									</span>
								</div>

								<div>
									<strong><?php esc_html_e( 'Submission ID', 'wbizmo-form-builder' ); ?></strong>
									<span><?php echo $log->submission_id ? esc_html( $log->submission_id ) : '&mdash;'; ?></span>
								</div>

								<div>
									<strong><?php esc_html_e( 'User ID', 'wbizmo-form-builder' ); ?></strong>
									<span><?php echo $log->user_id ? esc_html( $log->user_id ) : esc_html__( 'Guest / Unknown', 'wbizmo-form-builder' ); ?></span>
								</div>

								<div>
									<strong><?php esc_html_e( 'Page URL', 'wbizmo-form-builder' ); ?></strong>
									<span class="pf-break">
										<?php if ( ! empty( $log->page_url ) ) : ?>
											<a href="<?php echo esc_url( $log->page_url ); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo esc_html( $log->page_url ); ?>
											</a>
										<?php else : ?>
											&mdash;
										<?php endif; ?>
									</span>
								</div>

								<div>
									<strong><?php esc_html_e( 'User Agent', 'wbizmo-form-builder' ); ?></strong>
									<span class="pf-break"><?php echo esc_html( $log->user_agent ? $log->user_agent : '—' ); ?></span>
								</div>
							</div>

							<div class="pf-log-details">
								<h4><?php esc_html_e( 'Technical Details', 'wbizmo-form-builder' ); ?></h4>

								<pre id="pf-log-copy-<?php echo esc_attr( $log->id ); ?>"><?php echo esc_html( wp_json_encode( $details ? $details : array(), JSON_PRETTY_PRINT ) ); ?></pre>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="pf-card pf-empty">
			<span class="dashicons">receipt_long</span>
			<h2><?php esc_html_e( 'No logs yet', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Wbizmo Form Builder logs will appear here when errors, warnings, blocked submissions, or system issues occur.', 'wbizmo-form-builder' ); ?></p>
		</div>
	<?php endif; ?>
</div>
