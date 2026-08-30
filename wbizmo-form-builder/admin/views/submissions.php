<?php
/**
 * "Submissions" admin screen template.
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
			<p class="pf-eyebrow"><?php esc_html_e( 'Submissions', 'wbizmo-form-builder' ); ?></p>
			<h1><?php esc_html_e( 'Form Submissions', 'wbizmo-form-builder' ); ?></h1>
			<p><?php esc_html_e( 'View form entries, metadata, uploaded files, source pages, and submitted values.', 'wbizmo-form-builder' ); ?></p>
		</div>
	</div>

	<?php // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display flags from a redirect after a nonce-verified admin-post action; no state change happens here. ?>
	<?php if ( isset( $_GET['pf_deleted'] ) ) : ?>
		<div class="pf-notice pf-notice-success">
			<span class="dashicons">check_circle</span>
			<?php esc_html_e( 'Submission deleted successfully.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_read'] ) ) : ?>
		<div class="pf-notice pf-notice-success">
			<span class="dashicons">check_circle</span>
			<?php esc_html_e( 'Submission marked as read.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_error'] ) ) : ?>
		<div class="pf-notice pf-notice-error">
			<span class="dashicons">error</span>
			<?php esc_html_e( 'Something went wrong. Check logs for details.', 'wbizmo-form-builder' ); ?>
		</div>
	<?php endif; ?>
	<?php // phpcs:enable WordPress.Security.NonceVerification.Recommended ?>

	<?php if ( ! empty( $submissions ) ) : ?>
		<div class="pf-card">
			<div class="pf-table-header">
				<div>
					<h2><?php esc_html_e( 'Recent Submissions', 'wbizmo-form-builder' ); ?></h2>
					<p><?php esc_html_e( 'Showing the latest 200 submissions.', 'wbizmo-form-builder' ); ?></p>
				</div>
			</div>

			<div class="pf-submission-list">
				<?php foreach ( $submissions as $submission ) : ?>
					<?php
					$data  = json_decode( $submission->submission_data, true );
					$files = json_decode( $submission->files, true );

					$delete_url = wp_nonce_url(
						admin_url( 'admin-post.php?action=wbizfobu_delete_submission&submission_id=' . absint( $submission->id ) ),
						'wbizfobu_delete_submission_' . absint( $submission->id )
					);

					$mark_read_url = wp_nonce_url(
						admin_url( 'admin-post.php?action=wbizfobu_mark_submission_read&submission_id=' . absint( $submission->id ) ),
						'wbizfobu_mark_submission_read_' . absint( $submission->id )
					);
					?>

					<article class="pf-submission-card <?php echo 'unread' === $submission->status ? 'is-unread' : ''; ?>">
						<div class="pf-submission-top">
							<div>
								<div class="pf-submission-title-row">
									<h3><?php echo esc_html( $submission->form_name ? $submission->form_name : __( 'Untitled Form', 'wbizmo-form-builder' ) ); ?></h3>

									<span class="pf-status <?php echo 'unread' === $submission->status ? 'pf-status-unread' : 'pf-status-active'; ?>">
										<?php echo esc_html( $submission->status ); ?>
									</span>
								</div>

								<p>
									<?php
									printf(
										/* translators: 1: submission ID, 2: submission date. */
										esc_html__( 'Submission #%1$s · %2$s', 'wbizmo-form-builder' ),
										esc_html( $submission->id ),
										esc_html( mysql2date( 'M j, Y g:i A', $submission->created_at ) )
									);
									?>
								</p>
							</div>

							<div class="pf-submission-actions">
								<?php if ( 'unread' === $submission->status ) : ?>
									<a href="<?php echo esc_url( $mark_read_url ); ?>" class="pf-icon-btn" title="<?php esc_attr_e( 'Mark as read', 'wbizmo-form-builder' ); ?>">
										<span class="dashicons">done_all</span>
									</a>
								<?php endif; ?>

								<button type="button" class="pf-icon-btn pf-toggle-submission" data-target="pf-submission-<?php echo esc_attr( $submission->id ); ?>">
									<span class="dashicons">visibility</span>
								</button>

								<a href="<?php echo esc_url( $delete_url ); ?>" class="pf-icon-btn pf-danger" onclick="return confirm('<?php echo esc_js( __( 'Delete this submission? This cannot be undone.', 'wbizmo-form-builder' ) ); ?>');">
									<span class="dashicons">delete</span>
								</a>
							</div>
						</div>

						<div class="pf-submission-details" id="pf-submission-<?php echo esc_attr( $submission->id ); ?>">
							<div class="pf-submission-grid">
								<div>
									<strong><?php esc_html_e( 'Form ID', 'wbizmo-form-builder' ); ?></strong>
									<span><?php echo esc_html( $submission->form_id ); ?></span>
								</div>

								<div>
									<strong><?php esc_html_e( 'User ID', 'wbizmo-form-builder' ); ?></strong>
									<span><?php echo $submission->user_id ? esc_html( $submission->user_id ) : esc_html__( 'Guest', 'wbizmo-form-builder' ); ?></span>
								</div>

								<div>
									<strong><?php esc_html_e( 'Page URL', 'wbizmo-form-builder' ); ?></strong>
									<span class="pf-break">
										<?php if ( ! empty( $submission->page_url ) ) : ?>
											<a href="<?php echo esc_url( $submission->page_url ); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo esc_html( $submission->page_url ); ?>
											</a>
										<?php else : ?>
											&mdash;
										<?php endif; ?>
									</span>
								</div>

								<div>
									<strong><?php esc_html_e( 'User Agent', 'wbizmo-form-builder' ); ?></strong>
									<span class="pf-break"><?php echo esc_html( $submission->user_agent ? $submission->user_agent : '—' ); ?></span>
								</div>
							</div>

							<div class="pf-submitted-values">
								<h4><?php esc_html_e( 'Submitted Values', 'wbizmo-form-builder' ); ?></h4>

								<?php if ( is_array( $data ) && ! empty( $data ) ) : ?>
									<div class="pf-value-list">
										<?php foreach ( $data as $field ) : ?>
											<?php
											$label = isset( $field['label'] ) ? $field['label'] : __( 'Field', 'wbizmo-form-builder' );
											$value = isset( $field['value'] ) ? $field['value'] : '';
											?>
											<div class="pf-value-row">
												<strong><?php echo esc_html( $label ); ?></strong>

												<?php if ( is_array( $value ) ) : ?>
													<span><?php echo esc_html( implode( ', ', $value ) ); ?></span>
												<?php else : ?>
													<span><?php echo nl2br( esc_html( $value ) ); ?></span>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<p><?php esc_html_e( 'No readable submission data found.', 'wbizmo-form-builder' ); ?></p>
								<?php endif; ?>
							</div>

							<?php if ( is_array( $files ) && ! empty( $files ) ) : ?>
								<div class="pf-submitted-files">
									<h4><?php esc_html_e( 'Uploaded Files', 'wbizmo-form-builder' ); ?></h4>

									<div class="pf-file-list">
										<?php foreach ( $files as $file ) : ?>
											<a class="pf-file-card" href="<?php echo esc_url( $file['url'] ); ?>" target="_blank" rel="noopener noreferrer">
												<span class="dashicons">attach_file</span>
												<span>
													<strong><?php echo esc_html( isset( $file['label'] ) ? $file['label'] : __( 'Uploaded File', 'wbizmo-form-builder' ) ); ?></strong>
													<small><?php echo esc_html( isset( $file['name'] ) ? $file['name'] : __( 'File', 'wbizmo-form-builder' ) ); ?></small>
												</span>
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="pf-card pf-empty">
			<span class="dashicons">inbox</span>
			<h2><?php esc_html_e( 'No submissions yet', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Once users submit a Wbizmo Form Builder form, their entries will appear here.', 'wbizmo-form-builder' ); ?></p>
		</div>
	<?php endif; ?>
</div>
