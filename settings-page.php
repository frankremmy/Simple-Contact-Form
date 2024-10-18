<?php

// Register the settings page
function scf_add_settings_page(){
	add_options_page(
		'Settings',
		'SCF Settings',
		'manage_options',
		'scf-settings',
		'scf_render_settings_page'
	);
}
add_action('admin_menu', 'scf_add_settings_page');

// Render the settings page
function scf_render_settings_page() {
	?>
    <div class="wrap">
        <h1>Simple Contact Form Settings</h1>
        <form method="post" action="options.php">
			<?php
			settings_fields('scf_settings_group'); // Output security fields for the registered setting
			do_settings_sections('scf-settings'); // Output setting sections and fields
			submit_button(); // Submit button
			?>
        </form>
    </div>
	<?php
}

// Register the settings field and section
function scf_register_settings() {
	register_setting('scf_settings_group', 'scf_recipient_email');

	add_settings_section(
		'scf_settings_section',
		'Email Notification Settings',
		'scf_settings_section_callback',
		'scf-settings'
	);
	add_settings_field(
		'scf_recipient_email',
		'Recipient Email',
		'scf_recipient_email_callback',
        'scf-settings',
		'scf_settings_section'
	);
}
add_action('admin_init', 'scf_register_settings');

// Callback the email section
function scf_settings_section_callback() {
	echo '<p>Set the email address to receive notifications for form submissions.</p>';
}

// Callback the email field
function scf_recipient_email_callback() {
	$recipient_email = get_option('scf_recipient_email');
	echo '<input type="email" name="scf_recipient_email" value="' . esc_attr($recipient_email) . '">';
}