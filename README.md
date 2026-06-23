# PulseForms

![PulseForms Preview](assets/pulseforms.png)

**PulseForms** is a modern, customizable WordPress form builder plugin built for clean forms, secure submissions, beautiful styling, email notifications, file uploads, logs, shortcodes, and full form ownership.

PulseForms was created to provide premium-style form plugin features without locking useful functionality behind a paid version.

> Installable plugin package is available in:
>
> `release/pulseforms.zip`

---

## Overview

PulseForms is a lightweight WordPress form builder designed for users who want complete control over how their forms look, behave, and integrate with their websites.

It combines secure form processing, modern styling, submission management, file uploads, email notifications, spam protection, and observability features into a single plugin architecture.

The goal is simple:

**Users should feel like the form belongs to their website, not to a plugin.**

---

## Core Features

* Form creation from WordPress admin
* Shortcode support
* Frontend form rendering
* Contact form template
* Newsletter form template
* Subscription form template
* Multi-step form foundation
* Login form template
* Registration form template
* Custom form template
* Submission storage
* Submission viewer
* Uploaded file display
* Email notifications
* Admin notification emails
* User confirmation emails
* Responsive HTML email templates
* Custom frontend success and error feedback
* User-safe frontend error messages
* Detailed admin error logs
* PHP error logging where capturable
* File upload validation
* Custom captcha
* Honeypot spam protection
* Basic rate limiting
* Global plugin settings
* Log retention cleanup
* Optional uninstall data cleanup
* Material Icons integration
* Lightweight plugin architecture

---

## Form Types

PulseForms currently supports:

* Contact Forms
* Newsletter Forms
* Subscription Forms
* Multi-Step Forms
* Registration Forms
* Login Forms
* Custom Forms

Each form can be edited and embedded anywhere using a shortcode.

Example:

```text
[pulseform id="1"]
```

---

## Form Builder

PulseForms includes a lightweight V1 form builder powered by structured JSON field definitions.

Supported field types:

* Text
* Email
* Phone
* Number
* Textarea
* Select
* Radio
* Checkbox
* Toggle Switch
* Date
* File Upload
* Hidden Field
* HTML Block
* Password

The current architecture was intentionally designed to support future drag-and-drop functionality without requiring a rewrite of the form processing engine.

---

## Styling System

PulseForms includes three built-in frontend form themes:

### Aurora

Clean, modern, bright, and minimal.

### Noir

Dark, premium, elegant, and high-contrast.

### Solace

Warm, business-oriented, and professional.

Forms can also inherit styling directly from the active WordPress theme.

---

## Customization Options

Every form supports:

* Form theme selection
* Style mode selection
* Primary color
* Accent color
* Field radius
* Button radius
* Submit button text
* Success message
* Error message
* Custom CSS

PulseForms replaces default browser styling with consistent, modern form controls including custom checkboxes, radios, toggles, uploads, buttons, and feedback states.

---

## Submission Handling

PulseForms can store form submissions directly inside WordPress.

Submission records include:

* Form name
* Submission ID
* Submission status
* Submitted values
* Source page URL
* User ID or guest label
* Browser information
* Uploaded file links
* Read/unread status
* Deletion controls

---

## File Uploads

PulseForms includes secure file upload support.

Global controls include:

* Maximum upload size
* Allowed file types
* Upload validation
* WordPress media handling
* Uploaded file viewing inside submissions

Default file types:

```text
jpg, jpeg, png, gif, pdf, doc, docx, txt
```

---

## Email Notifications

PulseForms supports:

* Admin notification emails
* User confirmation emails
* Responsive HTML email templates
* Submission detail summaries
* Form name references
* Submission references
* Source page tracking

Login and registration forms do not enable standard submission emails by default.

PulseForms only reports success when all enabled processing actions complete successfully.

If email delivery fails, users receive a safe message while administrators receive full technical details through the logging system.

---

## Error Handling & Logs

PulseForms includes a dedicated admin log viewer.

Logged events may include:

* Severity level
* Event type
* Technical message
* Form ID
* Form name
* Submission ID
* Page URL
* User ID
* Hashed IP
* Browser information
* PHP version
* WordPress version
* PulseForms version
* Timestamp

Frontend visitors never see raw technical errors.

Examples:

```text
Something went wrong. Please try again.
```

```text
Something unexpected went wrong. Please try again later.
```

Full diagnostic details are stored inside:

```text
PulseForms → Logs
```

---

## Security Features

PulseForms includes:

* WordPress nonces
* Honeypot protection
* Custom captcha
* Rate limiting
* Server-side validation
* Sanitized inputs
* Escaped output
* Upload validation
* File type validation
* Upload size validation
* Hashed IP logging
* Permission checks

---

## Global Settings

The settings area provides controls for:

* Maximum upload size
* Allowed file types
* Rate limit attempts
* Rate limit windows
* Log retention
* Optional uninstall cleanup

---

## Admin Pages

```text
PulseForms
├── All Forms
├── Add New
├── Edit Form
├── Submissions
├── Logs
├── Settings
└── Support
```

---

## Support & Creator

PulseForms includes a dedicated creator and support page.

The creator profile image is dynamically loaded from:

```text
https://github.com/wbizmo.png?size=160
```

This ensures the displayed profile image always matches the GitHub account avatar.

---

## Installation

1. Download:

```text
release/pulseforms.zip
```

2. Open WordPress Admin.
3. Navigate to:

```text
Plugins → Add New → Upload Plugin
```

4. Upload `pulseforms.zip`.
5. Activate PulseForms.
6. Create your first form.
7. Copy the generated shortcode into any page, post, or page builder.

---

## Shortcode Usage

```text
[pulseform id="1"]
```

Replace `1` with the actual form ID.

---

## Technical Highlights

* WordPress Plugin Architecture
* Custom Database Tables
* Activation Hooks
* Deactivation Hooks
* Uninstall Handler
* Admin Menu System
* Frontend Renderer
* AJAX Submission Processing
* File Upload Handling
* HTML Email System
* Settings Persistence
* Log Management
* Error Observability
* Secure Form Processing

---

## Why This Project Matters

PulseForms demonstrates practical experience across:

* WordPress Plugin Development
* Form Rendering
* Backend Processing
* Database Design
* Email Systems
* File Uploads
* Security Controls
* Logging Systems
* Admin Interfaces
* Plugin Packaging
* WordPress Standards

The project was designed as a genuine plugin foundation rather than a simple demonstration project.

---

## Roadmap

Planned future enhancements include:

* Visual Drag-and-Drop Builder
* Field Duplication
* Conditional Logic
* Multi-Step Progress Navigation
* CSV Export
* Submission Search & Filtering
* Email Template Editor
* Custom Email Subjects & Bodies
* Google reCAPTCHA
* Webhooks
* Zapier Integration
* Stripe Integration
* PayPal Integration
* Flutterwave Integration
* Square Integration
* Gutenberg Block
* Elementor Widget
* Popup Forms
* Donation Forms
* Survey Forms
* Advanced Analytics

---

## Tech Stack

* WordPress
* PHP
* JavaScript
* jQuery
* HTML
* CSS
* MySQL
* Material Symbols

---

## License

GPL v2 or later

---

## Author

**Williams Ashibuogwu (wbizmo)**

GitHub: https://github.com/wbizmo

Repository: https://github.com/wbizmo/pulseforms

---

## Version

Current Version: **1.0.1**
