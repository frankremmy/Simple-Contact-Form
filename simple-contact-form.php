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

// Function to run when the plugin is activated
function scf_activate_plugin(){
    // Add transient to show activation notice in the admin area
    set_transient('scf_activation_notice', true, 5);
}
// Hook the activation function to 'register_activation_hook'
register_activation_hook(__FILE__, 'scf_activate_plugin');

// Function to display the activation notice
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
// Hook it into 'admin_notices'
add_action('admin_notices', 'scf_display_activation_notice');

// Display a simple message with the shortcode
function scf_display_contact_form(){
    $content = '';

    $content .= '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    $content .= '<p>';
    $content .= 'Name (required) <br />';
    $content .= '<input type="text" name="scf-name" pattern="[a-zA-Z0-9 ]+" value="' . (isset($_POST["scf-name"]) ? esc_attr($_POST["scf-name"]) : '') . '" size="80" />';
    $content .= '</p>';
    $content .= '<p>';
    $content .= 'Email (required) <br />';
    $content .= '<input type="email" name="scf-email" value="' . (isset($_POST["scf-email"]) ? esc_attr($_POST["scf-email"]) : '') . '" size="80" />';
    $content .= '</p>';
    $content .= '<p>';
    $content .= 'Message (required) <br />';
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

// Commit 1

