<?php 
/*
 * Plugin Name:       Simple Contact Form
 * Plugin URI:        https://frankremmy.com
 * Description:       A plugin that lets users add a contact form to any page or post using a shortcode. The form will collect basic information like name, email, and message, and also store the data in the database as well as send an email notification.
 * Version:           0.3.0
 * Requires PHP:      7.4
 * Author:            Frank Remmy
 * Author URI:        https://frankremmy.com/
 * Text Domain:       simple-contact-form
 */

// Check if ABSPATH is defined to prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'admin/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'public/public.php';
require_once plugin_dir_path(__FILE__) . 'settings-page.php';

// Enqueue styles for the form
function scf_enqueue_styles() {
	wp_enqueue_style('scf-styles', plugin_dir_url(__FILE__) . 'assets/css/scf-styles.css');
}
add_action('wp_enqueue_scripts', 'scf_enqueue_styles');

// Add transient to show activation notice in the admin area
function scf_activate_plugin(){
	set_transient('scf_activation_notice', true, 5);
}
register_activation_hook(__FILE__, 'scf_activate_plugin');

// Display activation notice
function scf_display_activation_notice(){
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
		delete_transient('scf_activation_notice');
	}
}