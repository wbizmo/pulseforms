# PulseForms

![PulseForms Preview](assets/pulseforms.png)

**PulseForms** is a modern, customizable WordPress form builder plugin built for clean forms, secure submissions, beautiful styling, email notifications, file uploads, logs, shortcodes, and full form ownership.

PulseForms was created to provide premium-style form plugin features without locking the useful parts behind a paid version.

> Installable plugin package will be available in:
>
> `release/pulseforms.zip`

---

## Overview

PulseForms is a lightweight WordPress plugin for building and managing forms directly inside WordPress.

It supports form creation, shortcode embedding, frontend rendering, styled form themes, submissions, file uploads, email notifications, error logging, custom captcha, honeypot protection, rate limiting, and admin settings.

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
* Multi-step form template foundation
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
* Material Icons only
* Lightweight plugin structure

---

## Form Types

PulseForms currently supports these form starting points:

* Contact Form
* Newsletter Form
* Subscription Form
* Multi-Step Form
* Registration Form
* Login Form
* Custom Form

Each form can be edited and embedded using a shortcode.

Example:

```text
[pulseform id="1"]
```

---

## Form Builder

PulseForms includes a fast V1 form editor using structured JSON fields.

Supported field types include:

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

The JSON editor allows quick form customization while keeping the plugin lightweight. A visual drag-and-drop builder can be added later on top of the existing architecture.

---

## Styling System

PulseForms includes its own frontend styling system with three built-in form themes:

### Aurora

Clean, soft, modern, and light.

### Noir

Dark, premium, and elegant.

### Solace

Warm, business-friendly, and refined.

Forms can also be configured to inherit theme styling.

---

## Customization Options

Each form supports customization for:

* Form theme
* Style mode
* Primary color
* Accent color
* Field radius
* Button radius
* Submit button text
* Success message
* Error message
* Custom CSS

PulseForms also removes the ugly default browser feel from form controls and provides styled versions of inputs, buttons, checkboxes, radios, toggles, uploads, and feedback messages.

---

## Submission Handling

PulseForms can store form submissions in the WordPress database.

The admin submission viewer includes:

* Form name
* Submission ID
* Submission status
* Submitted values
* Page URL
* User ID or guest label
* Browser/user agent
* Uploaded file links
* Read/unread status
* Delete action

---

## File Uploads

PulseForms supports file upload fields with security-focused controls.

Global upload settings include:

* Maximum upload size
* Allowed file types
* File validation
* WordPress upload handling
* Uploaded file display in admin submissions

Default allowed file types:

```text
jpg, jpeg, png, gif, pdf, doc, docx, txt
```

---

## Email Notifications

PulseForms supports both admin and user emails.

Available email features:

* Admin notification email
* User confirmation email
* Responsive HTML email templates
* Submission details inside email
* Form name
* Submission reference
* Source page URL
* User-submitted fields

Login and registration forms do not enable normal submission emails by default.

PulseForms only returns success to the frontend when the enabled submission actions complete successfully.

If email sending fails, the user sees a safe error message and the real issue is stored in the admin logs.

---

## Error Handling and Logs

PulseForms includes an admin log viewer for debugging and observability.

Logs can include:

* Severity
* Event type
* Message
* Technical details
* Form ID
* Form name
* Submission ID
* Page URL
* User ID
* Hashed user IP
* Browser/user agent
* PHP version
* WordPress version
* PulseForms version
* Timestamp

Frontend users never see raw technical errors.

Instead, they see safe messages such as:

```text
Something went wrong. Please try again.
```

or:

```text
Something unexpected went wrong. Please try again later.
```

The full technical reason is stored for admins inside:

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
* File type validation
* Upload size validation
* Hashed IP logging
* Permission checks for admin actions

---

## Global Settings

The settings page includes controls for:

* Maximum upload size
* Allowed file types
* Rate limit attempts
* Rate limit time window
* Log retention days
* Optional full data removal on uninstall

---

## Admin Pages

PulseForms adds the following WordPress admin sections:

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

## Support / Creator Section

PulseForms includes a support/about page showing the plugin creator and project repository.

The creator profile image is pulled from GitHub:

```text
https://github.com/wbizmo.png?size=160
```

This keeps the profile image current with the GitHub account avatar.

---

## Repository Structure

```text
.
├── pulseforms/
│   ├── admin/
│   │   └── views/
│   │       ├── add-new.php
│   │       ├── edit-form.php
│   │       ├── forms.php
│   │       ├── logs.php
│   │       ├── settings.php
│   │       ├── submissions.php
│   │       └── support.php
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   ├── admin.css
│   │   │   └── public.css
│   │   └── js/
│   │       ├── admin.js
│   │       └── public.js
│   │
│   ├── includes/
│   │   ├── class-pulseforms-activator.php
│   │   ├── class-pulseforms-admin.php
│   │   ├── class-pulseforms-deactivator.php
│   │   ├── class-pulseforms-emailer.php
│   │   ├── class-pulseforms-form-processor.php
│   │   ├── class-pulseforms-form-renderer.php
│   │   ├── class-pulseforms-logger.php
│   │   ├── class-pulseforms-security.php
│   │   └── class-pulseforms-submissions.php
│   │
│   ├── templates/
│   │   └── emails/
│   │       ├── admin-notification.php
│   │       └── user-confirmation.php
│   │
│   ├── pulseforms.php
│   ├── readme.txt
│   └── uninstall.php
│
├── assets/
│   └── pulseforms.png
│
├── release/
│   └── pulseforms.zip
│
└── README.md
```

---

## Installation

1. Download the packaged plugin:

```text
release/pulseforms.zip
```

2. Go to WordPress admin.

3. Navigate to:

```text
Plugins → Add New → Upload Plugin
```

4. Upload:

```text
pulseforms.zip
```

5. Activate the plugin.

6. Go to:

```text
PulseForms → Add New
```

7. Create a form.

8. Copy the shortcode into a page, post, or page builder.

---

## Shortcode Usage

```text
[pulseform id="1"]
```

Replace `1` with the actual form ID.

---

## Technical Highlights

* WordPress plugin architecture
* Custom database tables
* Activation hooks
* Deactivation hooks
* Uninstall handler
* Admin menu system
* Admin views
* Frontend shortcode renderer
* AJAX form processing
* File upload handling
* Email notification system
* Template-based emails
* Settings persistence
* Scheduled log cleanup
* Error logging system
* Safe frontend failure handling

---

## Why This Project Matters

PulseForms demonstrates practical WordPress plugin engineering across several important areas:

* Admin dashboard development
* Form rendering
* Secure form processing
* Database design
* Email systems
* File uploads
* Logging systems
* Security controls
* UI styling
* Plugin packaging
* WordPress standards awareness

It is designed as a real plugin foundation, not just a small demo.

---

## Roadmap

Possible future improvements:

* Visual drag-and-drop builder
* Field duplication
* Conditional logic
* Multi-step progress navigation
* CSV export
* Submission search and filtering
* Email template editor
* Custom email subject/body settings
* Google reCAPTCHA integration
* Webhook support
* Zapier integration
* Stripe integration
* PayPal integration
* Flutterwave integration
* Square integration
* Gutenberg block
* Elementor widget
* Popup forms
* Donation forms
* Survey forms
* Advanced analytics

---

## Tech Stack

* WordPress
* PHP
* JavaScript
* jQuery
* HTML
* CSS
* MySQL
* Material Symbols / Material Icons

---

## License

MIT License

---

## Author

**Williams**

GitHub: https://github.com/wbizmo

Repository: https://github.com/wbizmo/pulseforms

---

## Version

Current Version: **1.0.0**
