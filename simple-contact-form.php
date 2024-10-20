<?php 
/*
 * Plugin Name:       Simple Contact Form
 * Plugin URI:        https://frankremmy.com
 * Description:       A plugin that lets users add a contact form to any page or post using a shortcode. The form will collect basic information like name, email, and message, and also store the data in the database as well as send an email notification.
 * Version:           0.2
 * Requires PHP:      7.4
 * Author:            Frank Remmy
 * Author URI:        https://frankremmy.com/
 * Text Domain:       simple-contact-form
 */

//  Check if ABSPATH is define, and exit if not
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include submission page
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin-page.php';
}

// Include the settings page
if (is_admin()) {
	require_once plugin_dir_path(__FILE__) . 'settings-page.php';
}

// Run when the plugin is activated
function scf_activate_plugin(){
    // Add transient to show activation notice in the admin area
    set_transient('scf_activation_notice', true, 5);
}
// Hook to 'register_activation_hook'
register_activation_hook(__FILE__, 'scf_activate_plugin');

// Enqueue scripts
function scf_enqueue_styles() {
	wp_enqueue_style('scf-styles', plugin_dir_url(__FILE__) . 'assets/css/scf-styles.css');
}
add_action('wp_enqueue_scripts', 'scf_enqueue_styles');

// Create a custom table when plugin is activated
function scf_create_custom_table(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'scf_submissions';
    
    // Query statement to create the table
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint (9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        message text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
// Hook into plugin activation
register_activation_hook(__FILE__, 'scf_create_custom_table');

// Display the activation notice
function scf_display_activation_notice(){
    // Check if transient is set
    if (get_transient('scf_activation_notice')){
        $settings_url = admin_url('options-general.php?page=scf-settings');
        ?>
        <div class='notice notice-success is-dismissible'>
            <p><?php printf(
                    __('Simple Contact Form plugin activated! Go to <a href="%s"> Settings > SCF Settings</a> to configure the plugin.', 'scf'),
                    esc_url($settings_url)
                );
            ?>
            </p>
        </div>
        <?php
        // Delete transient to show it only show once
        delete_transient('scf_activation_notice');
    }   
}
// Hook into 'admin_notices'
add_action('admin_notices', 'scf_display_activation_notice');

/**
 * Handles the submission of the contact form, including validation, sanitization, and storage of form data.
 *
 * This function checks for nonce validation to ensure security, sanitizes the input fields, validates required fields,
 * inserts valid submissions into a custom database table, and sends email notifications to the admin.
 * It returns appropriate success or error messages based on the form submission outcome.
 *
 * @return string Feedback message to be displayed to the user after form submission, indicating success or failure.
 */
function scf_handle_form_submission(){
    global $wpdb;
    if (isset($_POST['scf-submitted'])) {
        if (!isset($_POST['scf_form_nonce']) || !wp_verify_nonce($_POST['scf_form_nonce'], 'scf_form_action')) {
		    return '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
	    }
        $name = sanitize_text_field($_POST["scf-name"]);
        $email = sanitize_email($_POST["scf-email"]);
        $message = sanitize_textarea_field( $_POST["scf-message"]);

        if (!empty($name) && !empty($email) && !empty($message)) {
            $table_name = $wpdb->prefix . 'scf_submissions';
            $wpdb->insert(
                $table_name,
                [
                    'name' => $name,
                    'email' => $email,
                    'message' => $message
                ]
            );
            $to = get_option('admin_email');
            $subject = 'New Contact Form Submission';
            $body = "You have received a new contact form submission. \n\n";
            $body .= "Name: $name \n";
	        $body .= "Email: $email\n";
	        $body .= "Message: \n$message\n";
            $headers = array( 'Content-type: text/plain; charset=UTF-8' );

            wp_mail($to, $subject, $body, $headers);

            $to = get_option('scf_recipient_email');
            if (!$to) {
                $to = get_option('admin_email');
            }
            return '<div class="scf-success"><p>Thank you for your message! We will get back to you soon.</p></div>';
        } else {
	        return '<div class="scf-error"><p>Please fill in all required fields.</p></div>';
        }
    }
}

/**
 * Generates and displays the contact form with validation and security measures.
 *
 * The form includes fields for name, email, and message with basic validation and nonce field for security.
 * Handles the display of any form submission messages.
 *
 * @return string The HTML content for the contact form, including any response messages.
 */
function scf_display_contact_form(){
    $response = scf_handle_form_submission();
    $content = '';

    if (!empty($response)) {
        $content = $response;
    }

	$content .= '<form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';
    $content .= wp_nonce_field('scf_form_action', 'scf_form_nonce', true, false ); // Add nonce field

	$content .= '<p>';
	$content .= 'Name (required) <br />';
	$content .= '<input type="text" name="scf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["scf-name"] ) ? esc_attr( $_POST["scf-name"] ) : '' ) . '" size="80" />';
	$content .= '</p>';
	$content .= '<p>';
	$content .= 'Email (required) <br />';
	$content .= '<input type="email" name="scf-email" value="' . ( isset( $_POST["scf-email"] ) ? esc_attr( $_POST["scf-email"] ) : '' ) . '" size="80" />';
	$content .= '</p>';
	$content .= '<p>';
	$content .= 'Message (required) <br />';
	$content .= '<textarea name="scf-message" rows="10" cols="65">' . ( isset( $_POST["scf-message"] ) ? esc_attr( $_POST["scf-message"] ) : '' ) . '</textarea>';
	$content .= '</p>';
	$content .= '<p><input type="submit" name="scf-submitted" value="Send"/></p>';
	$content .= '</form>';

	return $content;
}

// Register the shortcode
function scf_register_shortcodes(){
    add_shortcode('simple_contact_form', 'scf_display_contact_form');
}
add_action( 'init', 'scf_register_shortcodes');
