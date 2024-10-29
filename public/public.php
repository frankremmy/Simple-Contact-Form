<?php

// Display the contact form on the frontend
function scf_display_contact_form() {
	$response = scf_handle_form_submission();  // Handle form submission and get the response message
	$content = '';

	// Show any success or error message
	if (!empty($response)) {
		$content = $response;
	}

	// Display the form
	$content .= '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
	$content .= wp_nonce_field('scf_form_action', 'scf_form_nonce', true, false);

	$content .= '<p>';
	$content .= 'Name (required) <br />';
	$content .= '<input type="text" name="scf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset($_POST["scf-name"]) ? esc_attr($_POST["scf-name"]) : '' ) . '" size="80" />';
	$content .= '</p>';
	$content .= '<p>';
	$content .= 'Email (required) <br />';
	$content .= '<input type="email" name="scf-email" value="' . ( isset($_POST["scf-email"]) ? esc_attr($_POST["scf-email"]) : '' ) . '" size="80" />';
	$content .= '</p>';
	$content .= '<p>';
	$content .= 'Message (required) <br />';
	$content .= '<textarea name="scf-message" rows="10" cols="65">' . ( isset($_POST["scf-message"]) ? esc_attr($_POST["scf-message"]) : '' ) . '</textarea>';
	$content .= '</p>';
	$content .= '<p><input type="submit" name="scf-submitted" value="Send"/></p>';
	$content .= '</form>';

	return $content;
}

// Handle form submissions
function scf_handle_form_submission() {
	if (isset($_POST['scf-submitted'])) {
		// Verify nonce
		if (!isset($_POST['scf_form_nonce']) || !wp_verify_nonce($_POST['scf_form_nonce'], 'scf_form_action')) {
			return '<div class="scf-error">Security check failed. Please try again.</div>';
		}

		// Sanitize input fields
		$name = sanitize_text_field($_POST['scf-name']);
		$email = sanitize_email($_POST['scf-email']);
		$message = sanitize_textarea_field($_POST['scf-message']);

		if (!empty($name) && !empty($email) && !empty($message)) {
			// Save to the database
			$saved = scf_save_submission($name, $email, $message);

			if ($saved) {
				// Send notification email
				scf_send_notification_email($name, $email, $message);

				return '<div class="scf-success">Thank you for your message! We will get back to you soon.</div>';
			} else {
				return '<div class="scf-error">There was a problem saving your message. Please try again later.</div>';
			}
		} else {
			return '<div class="scf-error">Please fill in all required fields.</div>';
		}
	}

	return '';  // Return empty if no submission
}

// Send notification email to the admin
function scf_send_notification_email($name, $email, $message) {
	$admin_email = get_option('admin_email');
	$subject = 'New Contact Form Submission';
	$body = "You have received a new message:\n\n";
	$body .= "Name: $name\n";
	$body .= "Email: $email\n";
	$body .= "Message:\n$message\n";

	wp_mail($admin_email, $subject, $body);
}