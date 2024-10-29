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
add_action('admin_notices', 'scf_display_activation_notice');

// Register the shortcode to display the contact form
function scf_register_shortcode() {
	add_shortcode('simple_contact_form', 'scf_display_contact_form');
}
add_action('init', 'scf_register_shortcode');

// Create the database table for form submissions
function scf_create_submissions_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        message text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
register_activation_hook(__FILE__, 'scf_create_submissions_table');

// Register the dashboard widget
function scf_add_dashboard_widget() {
	wp_add_dashboard_widget(
		'scf_dashboard_widget',  // Widget slug
		'Simple Contact Form Submissions',  // Widget title
		'scf_display_dashboard_widget'  // Display function
	);
}
add_action('wp_dashboard_setup', 'scf_add_dashboard_widget');

// Enqueue custom styles for the dashboard widget
function scf_dashboard_widget_styles() {
	echo '<style>
        #scf_dashboard_widget ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        #scf_dashboard_widget li {
            margin-bottom: 10px;
        }
        #scf_dashboard_widget hr {
            margin-top: 5px;
            margin-bottom: 10px;
        }
    </style>';
}
add_action('admin_head', 'scf_dashboard_widget_styles');

// Display content in the dashboard widget
function scf_display_dashboard_widget() {
	global $wpdb;
	$table_name =   $wpdb->prefix . 'scf_submissions';

//    Fetch the 5 most recent submissions
	$results = $wpdb->get_results("SELECT name, email, message, submitted_at FROM $table_name ORDER BY submitted_at DESC LIMIT 5");
	if ( !empty( $results ) ) {
		echo '<ul>';
		foreach ( $results as $submission ) {
			echo '<li>';
			echo '<strong>' . esc_html($submission->name) . '</strong> (' . esc_html($submission->email) . ')<br/>';
			echo esc_html($submission->message) . '<br />';
			echo '<em>Submitted on: ' . esc_html(date('F j, Y, g:i a', strtotime($submission->submitted_at))) . '</em>';
			echo '<li><hr>';
		}
		echo '</ul>';
		echo '<p><a href="' . esc_url(admin_url('admin.php?page=scf_submissions')) . '">View All Submissions</a></p>';
	} else {
		echo '<p>No recent submissions found.</p>';
	}
}