<?php
/**
 * "Support" admin screen template.
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
			<p class="pf-eyebrow"><?php esc_html_e( 'Support', 'wbizmo-form-builder' ); ?></p>
			<h1><?php esc_html_e( 'About Wbizmo Form Builder', 'wbizmo-form-builder' ); ?></h1>
			<p><?php esc_html_e( 'Wbizmo Form Builder is an open-source WordPress form builder created to make polished, customizable forms freely available.', 'wbizmo-form-builder' ); ?></p>
		</div>
	</div>

	<div class="pf-grid">
		<div class="pf-card">
			<h2><?php esc_html_e( 'About the Plugin', 'wbizmo-form-builder' ); ?></h2>
			<p>
				<?php esc_html_e( 'Wbizmo Form Builder is built for WordPress users who want beautiful forms, clean customization, secure submissions, email notifications, logs, shortcodes, and a better form ownership experience.', 'wbizmo-form-builder' ); ?>
			</p>

			<p>
				<?php esc_html_e( 'The plugin is open source and designed to remain lightweight, extensible, and useful for real WordPress websites.', 'wbizmo-form-builder' ); ?>
			</p>
		</div>

		<div class="pf-card pf-creator-card">
			<div>
				<p class="pf-eyebrow"><?php esc_html_e( 'Created By', 'wbizmo-form-builder' ); ?></p>
				<h2>Williams</h2>
				<p>
					<?php esc_html_e( 'Software Engineer focused on web platforms, backend systems, CMS development, WordPress engineering, automation, and business software.', 'wbizmo-form-builder' ); ?>
				</p>

				<div class="pf-creator-links">
					<a href="https://github.com/wbizmo" target="_blank" rel="noopener noreferrer" class="pf-btn pf-btn-primary">
						<span class="dashicons">code</span>
						GitHub
					</a>

					<a href="https://github.com/wbizmo/pulseforms" target="_blank" rel="noopener noreferrer" class="pf-btn pf-btn-light">
						<span class="dashicons">open_in_new</span>
						<?php esc_html_e( 'Plugin Repository', 'wbizmo-form-builder' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
