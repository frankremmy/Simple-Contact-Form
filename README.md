# Simple Contact Form Plugin

## Overview
The **Simple Contact Form Plugin** provides an easy-to-use, customizable contact form solution for WordPress websites. Built with flexibility and simplicity in mind, this plugin offers powerful features like multi-step forms, conditional logic, and email notifications, all configurable through the WordPress dashboard.

## Installation

1. Git clone the repository and **Upload** the folder to `/wp-content/plugins/` 
2. **Activate** the plugin through the ‘Plugins’ menu in WordPress.
3. Go to **Settings > Simple Contact Form** to access and configure the plugin options.

## Getting Started

### 1. Create and Customize a Form
   - Go to 
   - Customize the form fields, layout, and additional settings, like email notifications for new submissions.
   - Insert placeholder directly within the shortcode (e.g., `[simple_contact_form placeholder="name,email,message"]`).

### 2. Embed the Form
   - Copy the shortcode `[simple_contact_form]`.
   - Paste it into any post, page, or widget where you want the form to appear.

### 3. Notification Settings
   - Go to the **Settings > Simple Contact Form** to enable and configure submission email notifications.
   - Customize the recipient's email address, email subject, and message content.

### 4. Localization Support
   - The plugin is translation-ready. Use Local Translate or any localization tools to begin your localization.

### 5. Reset to Defaults
   - Easily reset all settings to their default values using the **Reset to Defaults** button located in the settings page.

## 6. Compatibility and Testing
   - The plugin has been tested across various themes and plugins to ensure compatibility. For sites with complex customizations, it’s recommended to test the form on a staging or local environment before going live.

## 7. Security
   - The plugin incorporates nonce validation to prevent CSRF attacks, email sanitization, and client/server-side validation for secure data handling.

## Changelog

### v1.0.0
**Release Date:** [01/11/2024]

### 0.2.0-alpha

- Database Storage: Form submissions are now stored in a custom database table for easy management and retrieval. Each submission (name, email, message) is saved securely in the database.
- Settings Page: Added a custom settings page in the WordPress admin dashboard that allows site administrators to set a custom email recipient for contact form notifications. You can now specify the email address where form submissions are sent!
#### Improvements:
- Added a new menu item in the admin dashboard under SCF Submissions to access all form submissions in one place.
Added pagination to the submissions list on the admin page, making it easier to navigate through a large number of submissions.
- Added a delete button for each submission on the admin page, so administrators can remove individual entries as needed.

### 0.1.0-alpha
- Plugin header
- Activate the plugin
- Show admin notice when activated

To know more about the features of the version you installed, read its [release note](https://github.com/frankremmy/simple-contact-form/releases).
For development plans and bug reports, see [issues](https://github.com/frankremmy/simple-contact-form/issues) and the [project table](https://github.com/users/frankremmy/projects/1).

**Future Features:**
- **Core Form Builder:** Create and display custom contact forms using a shortcode.
- **Multi-Step and Conversational Forms:** Enhanced form layouts for a better user experience.
- **Advanced Capabilities:** Conditional logic, submission scheduling, and shortcode attribute control.

**Fixes and Improvements:**
- Resolved 404 error on settings page with cache clearing.
- Fixed nonce validation and activation issues.
- Enhanced email notification content and formatting.

**Known Issues:**
- Minor compatibility issues may occur with heavily customized themes. Staging environment testing is recommended.
- Reset to Default button not showing. 