<?php
/**
 * "All Forms" admin screen template.
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
			<p class="pf-eyebrow"><?php esc_html_e( 'Wbizmo Form Builder', 'wbizmo-form-builder' ); ?></p>
			<h1><?php esc_html_e( 'All Forms', 'wbizmo-form-builder' ); ?></h1>
			<p><?php esc_html_e( 'Create, edit, customize, and embed beautiful WordPress forms.', 'wbizmo-form-builder' ); ?></p>
		</div>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbizmo-form-builder-add-new' ) ); ?>" class="pf-btn pf-btn-primary">
			<span class="dashicons">add</span>
			<?php esc_html_e( 'Add New Form', 'wbizmo-form-builder' ); ?>
		</a>
	</div>

	<?php // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display flags from a redirect after a nonce-verified admin-post action; no state change happens here. ?>
	<?php if ( isset( $_GET['pf_created'] ) ) : ?>
		<div class="pf-notice pf-notice-success"><span class="dashicons">check_circle</span> <?php esc_html_e( 'Form created successfully.', 'wbizmo-form-builder' ); ?></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_updated'] ) ) : ?>
		<div class="pf-notice pf-notice-success"><span class="dashicons">check_circle</span> <?php esc_html_e( 'Form updated successfully.', 'wbizmo-form-builder' ); ?></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_deleted'] ) ) : ?>
		<div class="pf-notice pf-notice-success"><span class="dashicons">check_circle</span> <?php esc_html_e( 'Form deleted successfully.', 'wbizmo-form-builder' ); ?></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['pf_error'] ) ) : ?>
		<div class="pf-notice pf-notice-error"><span class="dashicons">error</span> <?php esc_html_e( 'Something went wrong. Check logs for details.', 'wbizmo-form-builder' ); ?></div>
	<?php endif; ?>
	<?php // phpcs:enable WordPress.Security.NonceVerification.Recommended ?>

	<?php if ( ! empty( $forms ) ) : ?>
		<div class="pf-card">
			<div class="pf-table-header">
				<div>
					<h2><?php esc_html_e( 'Forms', 'wbizmo-form-builder' ); ?></h2>
					<p><?php esc_html_e( 'Copy a shortcode and paste it into any page, post, or page builder.', 'wbizmo-form-builder' ); ?></p>
				</div>
			</div>

			<div class="pf-table-wrap">
				<table class="pf-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'wbizmo-form-builder' ); ?></th>
							<th><?php esc_html_e( 'Type', 'wbizmo-form-builder' ); ?></th>
							<th><?php esc_html_e( 'Theme', 'wbizmo-form-builder' ); ?></th>
							<th><?php esc_html_e( 'Shortcode', 'wbizmo-form-builder' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wbizmo-form-builder' ); ?></th>
							<th><?php esc_html_e( 'Created', 'wbizmo-form-builder' ); ?></th>
							<th class="pf-table-actions"><?php esc_html_e( 'Actions', 'wbizmo-form-builder' ); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ( $forms as $form ) : ?>
							<?php
							$style_settings = json_decode( $form->style_settings, true );
							$theme          = isset( $style_settings['theme'] ) ? $style_settings['theme'] : 'aurora';

							$edit_url = admin_url( 'admin.php?page=wbizmo-form-builder-edit-form&form_id=' . absint( $form->id ) );

							$delete_url = wp_nonce_url(
								admin_url( 'admin-post.php?action=wbizfobu_delete_form&form_id=' . absint( $form->id ) ),
								'wbizfobu_delete_form_' . absint( $form->id )
							);
							?>

							<tr>
								<td><strong><?php echo esc_html( $form->name ); ?></strong></td>

								<td><span class="pf-pill"><?php echo esc_html( ucwords( str_replace( '_', ' ', $form->type ) ) ); ?></span></td>

								<td><?php echo esc_html( ucfirst( $theme ) ); ?></td>

								<td>
									<div class="pf-shortcode-copy">
										<code>[wbizfobu_form id="<?php echo esc_attr( $form->id ); ?>"]</code>
										<button type="button" class="pf-icon-btn pf-copy-shortcode" data-shortcode='[wbizfobu_form id="<?php echo esc_attr( $form->id ); ?>"]'>
											<span class="dashicons">content_copy</span>
										</button>
									</div>
								</td>

								<td><span class="pf-status pf-status-active"><?php echo esc_html( $form->status ); ?></span></td>

								<td><?php echo esc_html( mysql2date( 'M j, Y', $form->created_at ) ); ?></td>

								<td class="pf-table-actions">
									<a href="<?php echo esc_url( $edit_url ); ?>" class="pf-icon-btn" title="<?php esc_attr_e( 'Edit form', 'wbizmo-form-builder' ); ?>">
										<span class="dashicons">edit</span>
									</a>

									<a href="<?php echo esc_url( $delete_url ); ?>" class="pf-icon-btn pf-danger" onclick="return confirm('<?php echo esc_js( __( 'Delete this form? This cannot be undone.', 'wbizmo-form-builder' ) ); ?>');">
										<span class="dashicons">delete</span>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php else : ?>
		<div class="pf-card pf-empty">
			<span class="dashicons">dynamic_form</span>
			<h2><?php esc_html_e( 'No forms yet', 'wbizmo-form-builder' ); ?></h2>
			<p><?php esc_html_e( 'Create your first form and Wbizmo Form Builder will generate a shortcode automatically.', 'wbizmo-form-builder' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbizmo-form-builder-add-new' ) ); ?>" class="pf-btn pf-btn-primary">
				<span class="dashicons">add</span>
				<?php esc_html_e( 'Create First Form', 'wbizmo-form-builder' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
