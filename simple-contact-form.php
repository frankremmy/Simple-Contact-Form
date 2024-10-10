<?php 
/*
 * Plugin Name:       Simple Contact Form
 * Plugin URI:        https://frankremmy.com
 * Description:       A WordPress plugin that lets users add a contact form to any page or post using a shortcode. The form will collect basic information like name, email, and message, and also store the data in the database as well as send an email notification.
 * Version:           1.0
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