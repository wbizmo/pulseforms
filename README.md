# Wbizmo Form Builder

**Wbizmo Form Builder** is a modern WordPress form builder plugin focused on clean form creation, secure submissions, frontend styling, email notifications, file uploads, observability, and full ownership of collected data.

This package is the WordPress.org compliance edition of the original PulseForms project.

The project was renamed during the WordPress.org review process to ensure a distinctive and compliant plugin identity.

---

## Package

Installable package:

release/wbizmo-form-builder.zip

---

## WordPress.org Review History

Documentation for all compliance updates, review notes, fixes, and migration history is available at:

docs/wordpress-org-review-notes.md

This document tracks the transition from PulseForms to Wbizmo Form Builder and records changes made during the WordPress.org approval process.

---

## Overview

Wbizmo Form Builder is designed for site owners who want complete control over their forms without relying on external services.

The plugin combines:

* Form creation
* Frontend rendering
* Secure submission processing
* Submission storage
* Email notifications
* File uploads
* Logging
* Spam protection
* Administrative observability

into a single WordPress-native architecture.

---

## Core Features

* Form creation from WordPress admin
* Frontend form rendering
* Submission storage
* Submission viewer
* Email notifications
* Admin notification emails
* User confirmation emails
* Responsive HTML email templates
* Secure file uploads
* File upload validation
* Honeypot spam protection
* Custom captcha support
* Rate limiting
* Detailed logging
* PHP error capture where available
* Log retention controls
* Global plugin settings
* Secure shortcode embedding
* Responsive frontend styling
* Multiple form templates

---

## Supported Form Types

* Contact Forms
* Newsletter Forms
* Subscription Forms
* Multi-Step Forms
* Registration Forms
* Login Forms
* Custom Forms

---

## Shortcodes

Preferred shortcode:

[wbizmo_form id="1"]

Legacy shortcode:

[pulseform id="1"]

The legacy shortcode is maintained for backward compatibility.

---

## Form Builder

The plugin uses structured field definitions that support future builder enhancements without requiring major architectural changes.

Supported field types:

* Text
* Email
* Phone
* Number
* Textarea
* Select
* Radio
* Checkbox
* Toggle
* Date
* File Upload
* Hidden Field
* HTML Block
* Password

---

## Styling System

Built-in themes:

### Aurora

Clean and modern.

### Noir

Dark and premium.

### Solace

Business-oriented and professional.

Forms may also inherit styles from the active WordPress theme.

---

## Submission Handling

Submission records may include:

* Form name
* Submission ID
* Submission status
* Submitted values
* Source page URL
* User ID
* Browser information
* Uploaded file references
* Read/unread status

---

## File Uploads

Global controls include:

* Maximum upload size
* Allowed file types
* Upload validation
* WordPress media handling

Default file types:

jpg,jpeg,png,gif,pdf,doc,docx,txt

---

## Email Notifications

Supported notification types:

* Admin notifications
* User confirmations
* HTML email templates
* Submission summaries
* Form references
* Submission references

Users only receive success feedback when enabled processing completes successfully.

---

## Logging & Observability

The plugin includes a dedicated log viewer.

Logged events may include:

* Severity
* Event type
* Message
* Form ID
* Form name
* Submission ID
* Page URL
* User ID
* Browser information
* PHP version
* WordPress version
* Plugin version
* Timestamp

Frontend visitors never receive raw technical error details.

---

## Security Features

* WordPress nonces
* Server-side validation
* Input sanitization
* Output escaping
* Honeypot protection
* Custom captcha support
* Rate limiting
* Upload validation
* Permission checks
* Hashed IP logging

---

## Global Settings

Configurable settings include:

* Upload limits
* Allowed file types
* Rate limiting
* Log retention
* Optional uninstall cleanup

---

## Admin Pages

Wbizmo Form Builder

├── All Forms
├── Add New
├── Edit Form
├── Submissions
├── Logs
├── Settings
└── Support

---

## Technical Highlights

* WordPress Plugin Architecture
* Custom Database Tables
* Activation Hooks
* Deactivation Hooks
* Uninstall Handler
* AJAX Submission Processing
* HTML Email System
* Log Management
* Secure Form Processing
* WordPress Standards Compliance

---

## Roadmap

Planned enhancements:

* Visual Drag-and-Drop Builder
* Conditional Logic
* CSV Export
* Submission Search
* Email Template Editor
* Webhooks
* Zapier Integration
* Stripe Integration
* PayPal Integration
* Flutterwave Integration
* Square Integration
* Gutenberg Block
* Elementor Widget
* Popup Forms
* Survey Forms
* Advanced Analytics

---

## Technology

* WordPress
* PHP
* JavaScript
* jQuery
* HTML
* CSS
* MySQL

---

## License

GPL v2 or later

---

## Author

Williams Ashibuogwu (wbizmo)

GitHub:
https://github.com/wbizmo

Repository:
https://github.com/wbizmo/pulseforms

---

## Version

Current Version: 1.0.1
