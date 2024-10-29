<?php

// Create a settings page in the admin dashboard
function scf_add_settings_page() {
	add_options_page(
		'Simple Contact Form Settings',
		'SCF Settings',
		'manage_options',
		'scf_settings',
		'scf_render_settings_page'
	);
}
add_action('admin_menu', 'scf_add_settings_page');

// Render the settings page content
function scf_render_settings_page() {
	?>
    <div class="wrap">
        <h1>Simple Contact Form Settings</h1>
        <form method="post" action="options.php">
			<?php
			settings_fields('scf_settings_group');
			do_settings_sections('scf_settings');
			submit_button();
			?>
        </form>
    </div>
	<?php
}

// Register and define the settings
function scf_register_settings() {
	register_setting('scf_settings_group', 'scf_recipient_email');

	add_settings_section(
		'scf_main_settings',
		'Main Settings',
		'scf_main_settings_section_callback',
		'scf_settings'
	);

	add_settings_field(
		'scf_recipient_email',
		'Recipient Email',
		'scf_recipient_email_callback',
		'scf_settings',
		'scf_main_settings'
	);
}
add_action('admin_init', 'scf_register_settings');

// Callback for the main settings section
function scf_main_settings_section_callback() {
	echo '<p>Enter the email where you want to receive the form submissions.</p>';
}

// Callback for the recipient email field
function scf_recipient_email_callback() {
	$email = get_option('scf_recipient_email', get_option('admin_email'));
	echo '<input type="email" name="scf_recipient_email" value="' . esc_attr($email) . '" />';
}