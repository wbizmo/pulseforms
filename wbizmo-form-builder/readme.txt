=== Wbizmo Form Builder ===
Contributors: wbizmo
Tags: forms, contact form, form builder, submissions, wordpress forms
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, customizable WordPress form builder plugin with submissions, email notifications, file uploads, logs, shortcodes, captcha, and rate limiting.

== Description ==

Wbizmo Form Builder is a lightweight and customizable WordPress form builder plugin created for users who want clean forms, secure submissions, email notifications, submission management, file uploads, and polished frontend styling.

The plugin includes form creation, shortcode embedding, styled frontend forms, email notifications, submission storage, file uploads, custom captcha, honeypot protection, rate limiting, error logs, and admin-friendly submission viewing.

Wbizmo Form Builder was originally developed under the working name PulseForms and was renamed before WordPress.org resubmission to provide a clearer, more distinctive plugin identity.

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

== Shortcodes ==

Use this shortcode to display a form:

[wbizmo_form id="1"]

The legacy shortcode is also supported for older development installs:

[pulseform id="1"]

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

= Are login and registration forms treated differently? =

Yes. Login and registration forms do not enable normal submission emails by default.

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

= 1.0.1 =
* Renamed submitted package from PulseForms to Wbizmo Form Builder for WordPress.org compliance and clearer public identity.
* Updated plugin headers, readme metadata, and GPL license references.
* Prepared package folder for WordPress.org resubmission.

= 1.0.0 =
* Initial development release under the PulseForms working name.
