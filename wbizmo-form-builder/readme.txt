=== Wbizmo Form Builder ===
Contributors: wbizmo
Tags: forms, contact form, form builder, submissions, wordpress forms
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight WordPress form builder plugin for creating, styling, embedding, and managing secure forms.

== Description ==

Wbizmo Form Builder is a lightweight WordPress form builder plugin for creating and managing forms directly inside WordPress.

It includes form creation, shortcode embedding, frontend rendering, submission storage, email notifications, file uploads, custom captcha, honeypot protection, rate limiting, logging, and admin-friendly submission management.

The plugin is designed to be simple, secure, extensible, and WordPress-native.

== Features ==

* Create multiple forms
* Form shortcode support
* Contact forms
* Newsletter forms
* Subscription forms
* Login form template
* Registration form template
* Custom form template
* Styled frontend form output
* Three default visual themes: Aurora, Noir, and Solace
* Theme inherit mode
* Custom form colors
* Custom field radius
* Custom button radius
* Email notifications to admin
* Confirmation emails to users
* Default responsive HTML email templates
* Submission storage
* Submission viewer
* File upload fields
* Uploaded file viewing in admin
* Custom captcha
* Honeypot spam protection
* Basic rate limiting
* Error and activity logs
* PHP error logging where capturable
* Log retention cleanup
* Uninstall cleanup option
* Lightweight architecture

== Shortcode ==

Use this shortcode to display a form:

[wbizfobu_form id="1"]

== Installation ==

1. Download the plugin ZIP file.
2. Go to WordPress Admin.
3. Navigate to Plugins > Add New.
4. Click Upload Plugin.
5. Upload wbizmo-form-builder.zip.
6. Activate the plugin.
7. Go to Wbizmo Form Builder in the WordPress admin menu.
8. Create your first form.
9. Copy the shortcode into any page, post, or page builder.

== Frequently Asked Questions ==

= Does Wbizmo Form Builder store submissions? =

Yes. Submissions can be stored in the WordPress database and viewed from the plugin admin area.

= Can I disable submission storage? =

Yes. Each form includes a setting to enable or disable saved submissions.

= Does Wbizmo Form Builder send emails? =

Yes. The plugin can send admin notification emails and user confirmation emails for supported form types.

= Does Wbizmo Form Builder support file uploads? =

Yes. File uploads are supported with allowed file type and maximum size settings.

= Does Wbizmo Form Builder include spam protection? =

Yes. The plugin includes honeypot protection, custom captcha, and basic rate limiting.

= Can I customize the form design? =

Yes. Forms can use built-in styling, inherit theme styling, use preset themes, custom colors, and radius controls.

== Screenshots ==

1. Forms admin page.
2. Form creation screen.
3. Frontend form display.
4. Submission viewer.
5. Logs viewer.
6. Settings page.

== Changelog ==

= 1.0.4 =
* Completed internal prefix cleanup using the wbizfobu prefix.
* Removed old package identifiers from the submitted plugin package.
* Updated shortcode to [wbizfobu_form].
* Updated options, hooks, actions, script handles, constants, and internal names for WordPress.org compliance.

= 1.0.3 =
* Added unique internal plugin prefix.
* Updated class names, constants, options, hooks, and package files.

= 1.0.2 =
* Updated WordPress.org package branding and metadata.
* Removed custom CSS functionality and remote assets.

= 1.0.1 =
* Internal review package update.

= 1.0.0 =
* Initial development release.
