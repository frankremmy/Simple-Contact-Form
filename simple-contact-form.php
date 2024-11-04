<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://frankremmy.com
 * @since             1.0.0
 * @package           Simple_Contact_Form
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Contact Form
 * Plugin URI:        https://frankremmy.com
 * Description:       A plugin that lets users add a contact form to any page or post using a shortcode. The form will collect basic information like name, email, and message, and also store the data in the database as well as send an email notification.
 * Version:           1.0.0
 * Author:            Frank Remmy
 * Author URI:        https://frankremmy.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-contact-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SIMPLE_CONTACT_FORM_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-simple-contact-form-activator.php
 */
function activate_simple_contact_form() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-contact-form-activator.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-contact-form-database.php';
	require_once plugin_dir_path( __FILE__ ) . 'public/class-simple-contact-form-public.php';
	$db = new Simple_Contact_Form_Database();
	$db->create_custom_form_tables();
	Simple_Contact_Form_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-simple-contact-form-deactivator.php
 */
function deactivate_simple_contact_form() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-contact-form-deactivator.php';
	Simple_Contact_Form_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_contact_form' );
register_deactivation_hook( __FILE__, 'deactivate_simple_contact_form' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-simple-contact-form.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-simple-contact-form-database.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_simple_contact_form() {

	$plugin = new Simple_Contact_Form();
	$plugin->run();

}
run_simple_contact_form();

