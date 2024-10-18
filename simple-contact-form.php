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
        ?>  <div class='notice notice-success is-dismissible'>
            <p><?php _e('Simple Contact Form plugin activated successfully!', 'scf-plugin'); ?></p>
            </div>
        <?php
        // Delete transient show it only show once
        delete_transient('scf_activation_notice');
    }   
}
// Hook into 'admin_notices'
add_action('admin_notices', 'scf_display_activation_notice');

// Process the form submission and send an email
function scf_handle_form_submission(){
    global $wpdb;

    // Validate nonce
    if(!isset($_POST['scf_form_nonce']) || !wp_verify_nonce($_POST['scf_form_nonce'], 'scf_form_action')) {
        return "<div class='notice notice-error'><p>Security check failed. Please try again.</p></div>";
    }

    // Check if the form is submitted
    if (isset($_POST['scf-submitted'])) {
        // clean the form inputs
        $name = sanitize_text_field($_POST["scf-name"]);
        $email = sanitize_email($_POST["scf-email"]);
        $message = sanitize_textarea_field( $_POST["scf-message"]);

        // Validate required fields and insert the data into the custom table
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

            // Prepare the email notification
            $to = get_option('admin_email');
            $subject = 'New Contact Form Submission';
            $body = "You have received a new contact form submission. \n\n";
            $body .= "Name: $name \n";
	        $body .= "Email: $email\n";
	        $body .= "Message: \n$message\n";
            $headers = array( 'Content-type: text/plain; charset=UTF-8' );

            // Send the email
            wp_mail($to, $subject, $body, $headers);

            // Get the custom email address from the settings and fallback if not set
            $to = get_option('scf_recipient_email');
            if (!$to) {
                $to = get_option('admin_email');
            }

            // Success & error messages
	        return '<div class="notice notice-success"><p>Thank you for your message! We will get back to you soon.</p></div>';
        } else {
	        return '<div class="notice notice-error"><p>Please fill in all required fields.</p></div>';
        }
    }
}

// Display a simple message with the shortcode
function scf_display_contact_form(){

    $response = scf_handle_form_submission();
    $content = '';

    if (!empty($response)) {
        $content = $response;
    }

	$content .= '<form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';
    $content .= wp_nonce_field('scf_form_action', 'scf_form_nonce', true, false ); // Add nonce field
	$content .= '<p>';
	$content .= 'Name (required) <br/>';
	$content .= '<input type="text" name="scf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["scf-name"] ) ? esc_attr( $_POST["scf-name"] ) : '' ) . '" size="80" />';
	$content .= '</p>';
	$content .= '<p>';
	$content .= 'Email (required) <br/>';
	$content .= '<input type="email" name="scf-email" value="' . ( isset( $_POST["scf-email"] ) ? esc_attr( $_POST["scf-email"] ) : '' ) . '" size="80" />';
	$content .= '</p>';
	$content .= '<p>';
	$content .= 'Message (required) <br/>';
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
