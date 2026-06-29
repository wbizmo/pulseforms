# WordPress.org Review Notes

## Submission Information

Original Working Name: PulseForms
Current Plugin Name: Wbizmo Form Builder
Requested Slug: wbizmo-form-builder
Current Package Version: 1.0.5
Original Submission Date: 2026-06-23
Repository: https://github.com/wbizmo/pulseforms

Status:
Ready for WordPress.org resubmission after prefix, branding, shortcode, remote asset, and custom CSS remediation.

---

## Purpose of This Document

This file tracks the WordPress.org review feedback, remediation work, release updates, and packaging history for the plugin now submitted as **Wbizmo Form Builder**.

The GitHub repository remains named `pulseforms` for historical continuity, but the submitted plugin package is:

`wbizmo-form-builder`

---

## Review Feedback Summary

The WordPress.org review process raised the following issues:

- Plugin name/slug similarity concerns around the original PulseForms name.
- Custom CSS input/output was not allowed.
- Remote assets were loaded without opt-in.
- Google Fonts / Material Symbols dependency needed removal.
- GitHub-hosted profile image needed removal.
- Raw form builder JSON needed sanitization before logging.
- Generic or inconsistent internal prefixes needed replacement.
- Options, hooks, classes, constants, AJAX actions, script handles, and shortcodes needed unique plugin-specific prefixes.

---

## Final Public Identity

Plugin Name:
Wbizmo Form Builder

Requested WordPress.org Slug:
wbizmo-form-builder

Package Folder:
wbizmo-form-builder

Main Plugin File:
wbizmo-form-builder.php

Text Domain:
wbizmo-form-builder

Unique Internal Prefix:
wbizfobu

Canonical Shortcode:
[wbizfobu_form id="1"]

---

## Completed Remediation

### Branding and Naming

- Renamed submitted plugin package from PulseForms to Wbizmo Form Builder.
- Updated plugin headers.
- Updated readme metadata.
- Updated WordPress admin branding.
- Updated package folder name.
- Requested new WordPress.org slug: `wbizmo-form-builder`.

### Remote Asset and Privacy Remediation

- Removed Google Fonts dependency.
- Removed Material Symbols dependency.
- Removed remote GitHub avatar loading.
- Confirmed no remote asset references remain in the submitted package.

### Custom CSS Remediation

- Removed Custom CSS editor/input.
- Removed Custom CSS storage.
- Removed frontend Custom CSS rendering.
- Confirmed no custom CSS feature references remain in the submitted package.

### Sanitization and Security Remediation

- Sanitized form builder JSON before decoding/logging.
- Verified nonce usage.
- Verified capability checks.
- Verified server-side validation.
- Verified upload validation.
- Verified user-safe frontend error handling.
- Verified admin-side diagnostic logging.

### Prefix Compliance Remediation

- Replaced old internal package identifiers with `wbizfobu`.
- Updated PHP classes to use `WBIZFOBU_`.
- Updated constants to use `WBIZFOBU_`.
- Updated option names to use `wbizfobu_`.
- Updated database table names to use `wbizfobu_`.
- Updated AJAX actions to use `wbizfobu_`.
- Updated scheduled cleanup hook to use `wbizfobu_`.
- Updated script handles to use `wbizfobu-`.
- Updated localized frontend object to `WbizfobuPublic`.
- Updated shortcode to `[wbizfobu_form]`.
- Removed shortcode aliases from the submitted package.

---

## Release History During Review

### v1.0.1

Initial compliance package after the first review response.

Included:

- Plugin renamed to Wbizmo Form Builder.
- Package folder renamed to `wbizmo-form-builder`.
- Initial remote asset and custom CSS remediation.

### v1.0.2

Version alignment and readme cleanup release.

Included:

- Updated plugin version metadata.
- Updated readme stable tag.
- Corrected package/readme version mismatch.

### v1.0.3

Prefix compliance release.

Included:

- Began replacing old internal identifiers.
- Introduced `wbizfobu` internal prefix.
- Updated classes, constants, options, hooks, and file names.

### v1.0.4

Full branding and prefix cleanup release.

Included:

- Removed remaining old internal identifiers from the submitted package.
- Updated shortcode to `[wbizfobu_form]`.
- Updated package-wide references to use the Wbizmo Form Builder public identity and `wbizfobu` internal prefix.

### v1.0.5

Final shortcode and prefix finalization release.

Included:

- Removed extra `[wbizfobu]` shortcode alias.
- Left `[wbizfobu_form]` as the single canonical shortcode.
- Confirmed no old `PulseForms`, `pulseforms`, `pulseform`, `wbizmo_form`, or `wbizmo_form_builder` identifiers remain in the submitted package.
- Confirmed no remote asset references remain.
- Confirmed no custom CSS feature references remain.
- Confirmed PHP lint passes across the submitted package.
- Rebuilt WordPress.org submission ZIP.

---

## Latest Generated Package

release/wbizmo-form-builder.zip

---

## Latest Validation Summary

Validated before resubmission:

- PHP lint passed.
- Version metadata checked.
- Stable tag checked.
- Shortcode checked.
- Old identifier scan passed.
- Remote asset scan passed.
- Custom CSS scan passed.
- Nonce usage checked.
- Capability checks checked.
- Sanitization usage checked.
- AJAX action prefix scan passed.
- Option name prefix scan passed.
- Database table prefix scan passed.
- Cron hook prefix scan passed.
- Package ZIP rebuilt.

---

## Notes

The repository intentionally remains:

https://github.com/wbizmo/pulseforms

This preserves the original development history.

The WordPress.org package identity is:

Wbizmo Form Builder

The requested WordPress.org slug is:

wbizmo-form-builder
