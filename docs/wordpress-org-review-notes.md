# WordPress.org Review Notes

## Submission Information

Plugin: PulseForms
Slug: pulseforms
Version Submitted: 1.0.1
Submission Date: 2026-06-23

Status: Awaiting Review

Automated Scan Result: PASS

---

## Notes

This file tracks all WordPress.org review feedback, fixes, commits, and release updates related to the PulseForms plugin approval process.

Repository:
https://github.com/wbizmo/pulseforms

Plugin Directory Slug:
https://wordpress.org/plugins/pulseforms/

## Wbizmo Form Builder Rename and WordPress.org Compliance Notes

The plugin was originally developed under the working name **PulseForms**.

For WordPress.org resubmission, the distributable plugin package is being renamed to **Wbizmo Form Builder** with the requested slug:

`wbizmo-form-builder`

The GitHub repository may remain named `pulseforms` for project continuity and historical context, but the submitted WordPress plugin package uses the compliant public identity.

### Naming Timeline

| Version | Public/Working Name |
| --- | --- |
| 1.0.0 development build | PulseForms |
| 1.0.1+ WordPress.org package | Wbizmo Form Builder |

### Review-Focused Changes Planned

- Rename submitted plugin package to Wbizmo Form Builder.
- Use `wbizmo-form-builder` as the WordPress.org slug request.
- Remove arbitrary custom CSS input/output.
- Remove remote asset loading.
- Remove dynamic GitHub avatar loading.
- Sanitize raw JSON before logging.
- Keep form styling system intact through controlled settings and built-in themes.
- Rebuild ZIP as `release/wbizmo-form-builder.zip`.


## Wbizmo Form Builder Rename and WordPress.org Compliance Notes

The plugin was originally developed under the working name **PulseForms**.

For WordPress.org resubmission, the distributable plugin package is being renamed to **Wbizmo Form Builder** with the requested slug:

`wbizmo-form-builder`

The GitHub repository may remain named `pulseforms` for project continuity and historical context, but the submitted WordPress plugin package uses the compliant public identity.

### Naming Timeline

| Version | Public/Working Name |
| --- | --- |
| 1.0.0 development build | PulseForms |
| 1.0.1+ WordPress.org package | Wbizmo Form Builder |

### Review-Focused Changes Planned

- Rename submitted plugin package to Wbizmo Form Builder.
- Use `wbizmo-form-builder` as the WordPress.org slug request.
- Remove arbitrary custom CSS input/output.
- Remove remote asset loading.
- Remove dynamic GitHub avatar loading.
- Sanitize raw JSON before logging.
- Keep form styling system intact through controlled settings and built-in themes.
- Rebuild ZIP as `release/wbizmo-form-builder.zip`.


## Sprint 2 Compliance Updates

- Removed arbitrary Custom CSS feature.
- Removed Custom CSS storage.
- Removed frontend inline style output.
- Removed Google Fonts / Material Symbols remote asset loading.
- Sanitized form_fields before JSON decoding and logging.
- Preparing package for WordPress.org compliance review.


## Sprint 3 Package Identity Updates

- Updated compliant package branding to Wbizmo Form Builder.
- Updated WordPress admin page slugs in the package folder.
- Updated text domain references in the package folder.
- Updated plugin option names, scheduled cleanup hook, and database table names for the compliant package.
- Added `[wbizmo_form]` shortcode.
- Kept `[pulseform]` as a legacy alias for development/demo compatibility.


## Final Compliance Package Build

- Fixed invalid PHP identifiers caused by branding rename.
- Confirmed PHP syntax lint passes across the Wbizmo Form Builder package.
- Removed remaining remote GitHub avatar image.
- Removed remaining Material Symbols references.
- Added `[wbizmo_form]` shortcode while preserving `[pulseform]` as a legacy alias.
- Built resubmission ZIP as `release/wbizmo-form-builder.zip`.


---

## Release v1.0.1

Release Date: 2026-06-24

Status:
Ready for WordPress.org Resubmission

Release Title:
Wbizmo Form Builder 1.0.1 – WordPress.org Compliance Release

Summary

This release completes the first WordPress.org compliance remediation cycle.

The project transitions from the original PulseForms working identity to the public WordPress.org package identity:

Wbizmo Form Builder

Completed During Review Cycle

- Removed arbitrary Custom CSS functionality.
- Removed frontend Custom CSS rendering.
- Removed remote GitHub avatar loading.
- Removed Google Fonts dependency.
- Removed Material Symbols dependency.
- Improved sanitization around form builder JSON processing.
- Added WordPress.org compliant package branding.
- Added [wbizmo_form] shortcode.
- Preserved [pulseform] shortcode for backwards compatibility.
- Updated package namespace references.
- Updated option namespace references.
- Updated scheduled cleanup namespace references.
- Updated database table namespace references.

Validation

- PHP lint passed.
- Remote asset scan passed.
- Compliance audit passed.
- Shortcode validation passed.
- Package validation passed.

Generated Package

release/wbizmo-form-builder.zip

Repository

https://github.com/wbizmo/pulseforms

Notes

The GitHub repository intentionally remains named "pulseforms" for project continuity and historical traceability.

The WordPress.org package and public plugin identity use:

wbizmo-form-builder


---

## Release v1.0.2

Release Date: 2026-06-24

Status:
Ready for WordPress.org Resubmission

Release Title:
Wbizmo Form Builder 1.0.2 – WordPress.org Compliance Release

Summary

This release completes the WordPress.org compliance remediation cycle and formally transitions the submitted package from PulseForms to Wbizmo Form Builder.

Completed During Review Cycle

- Renamed submitted plugin package to Wbizmo Form Builder.
- Renamed WordPress.org package folder to wbizmo-form-builder.
- Removed arbitrary Custom CSS functionality.
- Removed frontend Custom CSS rendering.
- Removed remote GitHub avatar loading.
- Removed Google Fonts dependency.
- Removed Material Symbols dependency.
- Improved sanitization around form builder JSON processing.
- Added [wbizmo_form] shortcode.
- Preserved [pulseform] shortcode for backwards compatibility.
- Updated package namespace references.
- Updated option namespace references.
- Updated cleanup scheduler namespace references.
- Updated database table namespace references.

Validation

- PHP lint passed.
- Remote asset scan passed.
- Compliance audit passed.
- Shortcode validation passed.
- Package validation passed.

Generated Package

release/wbizmo-form-builder.zip

Repository

https://github.com/wbizmo/pulseforms


---

## Release v1.0.4

Status:
Ready for WordPress.org Resubmission

Release Title:
Wbizmo Form Builder 1.0.4 – Full Prefix and Branding Compliance Release

Summary

This release completes the full internal branding and prefix cleanup requested during WordPress.org review.

The submitted package now uses the Wbizmo Form Builder public identity and the unique internal prefix:

wbizfobu

Completed During Review Cycle

- Removed remaining old PulseForms identifiers from the submitted package.
- Removed old pulseforms and pulseform internal references from package code.
- Updated PHP class names to use the WBIZFOBU_ prefix.
- Updated plugin constants to use the WBIZFOBU_ prefix.
- Updated internal include filenames to use the class-wbizfobu-* naming pattern.
- Updated options, hooks, AJAX actions, transients, script handles, and table names to use wbizfobu-based naming.
- Updated shortcode to [wbizfobu_form].
- Removed legacy shortcode aliases from the submitted package.
- Confirmed no old package identifiers remain in the Wbizmo Form Builder distributable folder.
- Confirmed PHP lint passes across the package.
- Rebuilt WordPress.org submission ZIP.

Generated Package

release/wbizmo-form-builder.zip


---

## Release v1.0.5

Status:
Ready for WordPress.org Resubmission

Release Title:
Wbizmo Form Builder 1.0.5 – Shortcode and Prefix Finalization Release

Summary

This release removes the extra `[wbizfobu]` shortcode alias and leaves `[wbizfobu_form]` as the single canonical shortcode in the submitted package.

Completed During Review Cycle

- Removed extra `[wbizfobu]` shortcode alias.
- Updated admin-displayed shortcodes to `[wbizfobu_form]`.
- Confirmed no old PulseForms identifiers remain in the submitted package.
- Confirmed no old `pulseforms` or `pulseform` identifiers remain in the submitted package.
- Confirmed no old `wbizmo_form` or `wbizmo_form_builder` identifiers remain in the submitted package.
- Confirmed no remote asset references remain.
- Confirmed no custom CSS feature references remain.
- Confirmed PHP lint passes across the package.
- Rebuilt WordPress.org submission ZIP.

Generated Package

release/wbizmo-form-builder.zip

