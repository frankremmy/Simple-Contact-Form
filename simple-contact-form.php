<?php 
/*
 * Plugin Name:       Simple Contact Form
 * Plugin URI:        https://frankremmy.com
 * Description:       A WordPress plugin that lets users add a contact form to any page or post using a shortcode. The form will collect basic information like name, email, and message, and also store the data in the database as well as send an email notification.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Frank Remmy
 * Author URI:        https://frankremmy.com/
 * Text Domain:       simple-contact-form
 */

//  Check if ABSPATH is define, and exit if not
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Run when the plugin is activated
function scf_activate_plugin(){
    // Add transient to show activation notice in the admin area
    set_transient('scf_activation_notice', true, 5);
}
// Hook to 'register_activation_hook'
register_activation_hook(__FILE__, 'scf_activate_plugin');

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

// Process the form submission
function scf_handle_form_submission(){
    global $wpdb;

    // Check if the form is submitted
    if (isset($_POST['scf-submitted'])) {
        // clean the form inputs
        $name = sanitize_text_field($_POST["scf-name"]);
        $email = sanitize_email($_POST["scf-email"]);
        $message = sanitize_textarea_field( $_POST["scf-message"] );

        // Validate required fields
        if (!empty($name) && !empty($email) && !empty($message)) {
            // Add the data into the custom table
            $table_name = $wpdb->prefix . 'scf_submissions';
            $table_name = $wpdb->insert(
                $table_name,
                [
                    'name' => $name,
                    'email' => $email,
                    'message' => $message
                ]
                );
            // Prepare the success message
            $success_message = '<div class="notice notice-success"><p>Thank you for your message!</p></div>';
            return $success_message;
        } else {
            // Prepare the error message
            $error_message = '<div class="notice notice-error"><p>Please fill in all required fields.</p></div>';
            return $error_message;
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

    $content .= '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    $content .= '<p>';
    $content .= 'Name (required) <br/>';
    $content .= '<input type="text" name="scf-name" pattern="[a-zA-Z0-9 ]+" value="' . (isset($_POST["scf-name"]) ? esc_attr($_POST["scf-name"]) : '') . '" size="80" />';
    $content .= '</p>';
    $content .= '<p>';
    $content .= 'Email (required) <br/>';
    $content .= '<input type="email" name="scf-email" value="' . (isset($_POST["scf-email"]) ? esc_attr($_POST["scf-email"]) : '') . '" size="80" />';
    $content .= '</p>';
    $content .= '<p>';
    $content .= 'Message (required) <br/>';
    $content .= '<textarea name="scf-message" rows="10" cols="65">' . (isset($_POST["scf-message"]) ? esc_attr($_POST["scf-message"]) : '') . '</textarea>';
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
        PRIMARY_KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
// Hook into plugin activation
register_activation_hook(__FILE__, 'scf_create_custom_table');