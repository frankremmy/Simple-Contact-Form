<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://frankremmy.com
 * @since      1.0.0
 *
 * @package    Simple_Contact_Form
 * @subpackage Simple_Contact_Form/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Simple_Contact_Form
 * @subpackage Simple_Contact_Form/public
 * @author     Frank Remmy <ugochukwufrankremmy@outlook.com>
 */
class Simple_Contact_Form_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $simple_contact_form    The ID of this plugin.
	 */
	private $simple_contact_form;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $simple_contact_form       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $simple_contact_form, $version ) {

		$this->simple_contact_form = $simple_contact_form;
		$this->version = $version;

//		Initialize the database class for saving submissions
		$this->db = new Simple_Contact_Form_Database();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_Contact_Form_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_Contact_Form_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->simple_contact_form, plugin_dir_url( __FILE__ ) . 'css/simple-contact-form-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_Contact_Form_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_Contact_Form_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->simple_contact_form, plugin_dir_url( __FILE__ ) . 'js/simple-contact-form-public.js', array( 'jquery' ), $this->version, false );

	}

    public function register_shortcodes() {
        add_shortcode('simple_contact_form', array( $this, 'display_contact_form' ) );
    }

	// Display the contact form on the frontend
	public function display_contact_form($atts) {
		// Define default attributes
		$atts = shortcode_atts(
			array(
				'name_placeholder' => __('Your Name', 'simple-contact-form'),
				'email_placeholder' => __('Your Email', 'simple-contact-form'),
				'message_placeholder' => __('Your Message', 'simple-contact-form'),
				'button_text' => __('Send Message', 'simple-contact-form')
			),
			$atts,
			'simple_contact_form'
		);

		// Handle form submission and get response message
		$response = $this->handle_form_submission();
		$content = '';

		// Show any success or error message
		if (!empty($response)) {
			$content = $response;
		}

		// Display the form with attributes
		$content .= '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
		$content .= wp_nonce_field('scf_form_action', 'scf_form_nonce', true, false);

		$content .= '<p>';
		$content .= __('Name (required)', 'simple-contact-form') . '<br />';
		$content .= '<input type="text" name="scf-name" placeholder="' . esc_attr($atts['name_placeholder']) . '" pattern="[a-zA-Z0-9 ]+" value="' . ( isset($_POST["scf-name"]) ? esc_attr($_POST["scf-name"]) : '' ) . '" size="80" />';
		$content .= '</p>';

		$content .= '<p>';
		$content .= __('Email (required)', 'simple-contact-form') . '<br />';
		$content .= '<input type="email" name="scf-email" placeholder="' . esc_attr($atts['email_placeholder']) . '" value="' . ( isset($_POST["scf-email"]) ? esc_attr($_POST["scf-email"]) : '' ) . '" size="80" />';
		$content .= '</p>';

		$content .= '<p>';
		$content .= __('Message (required)', 'simple-contact-form') . '<br />';
		$content .= '<textarea name="scf-message" placeholder="' . esc_attr($atts['message_placeholder']) . '" rows="10" cols="65">' . ( isset($_POST["scf-message"]) ? esc_attr($_POST["scf-message"]) : '' ) . '</textarea>';
		$content .= '</p>';

		$content .= '<p><input type="submit" name="scf-submitted" value="' . esc_attr($atts['button_text']) . '"/></p>';
		$content .= '</form>';

		return $content;
	}

	// Handle form submissions
	private function handle_form_submission() {
		if (isset($_POST['scf-submitted'])) {
			// Verify nonce for security
			if (!isset($_POST['scf_form_nonce']) || !wp_verify_nonce($_POST['scf_form_nonce'], 'scf_form_action')) {
				return '<div class="scf-error">' . __('Security check failed. Please try again.', 'simple-contact-form') . '</div>';
			}

			// Sanitize input fields
			$name = sanitize_text_field($_POST['scf-name']);
			$email = sanitize_email($_POST['scf-email']);
			$message = sanitize_textarea_field($_POST['scf-message']);

			if (!empty($name) && !empty($email) && !empty($message)) {
				// Save submission to the database
				$saved = $this->db->save_submission($name, $email, $message);

				if ($saved) {
					// Send notification email
					$this->send_notification_email($name, $email, $message);
					return '<div class="scf-success">' . __('Thank you for your message! We will get back to you soon.', 'simple-contact-form') . '</div>';
				} else {
					return '<div class="scf-error">' . __('There was a problem saving your message. Please try again later.', 'simple-contact-form') . '</div>';
				}
			} else {
				return '<div class="scf-error">' . __('Please fill in all required fields.', 'simple-contact-form') . '</div>';
			}
		}

		return ''; // No submission or form not yet submitted
	}

	// Send a notification email to the admin
	private function send_notification_email($name, $email, $message) {
		$admin_email = get_option('scf_recipient_email', get_option('admin_email'));
		$subject = get_option('scf_email_subject', __('New Contact Form Submission', 'simple-contact-form'));
		$body_template = get_option('scf_email_body', __('You have received a new message:', 'simple-contact-form') . "\n\nName: {name}\nEmail: {email}\nMessage:\n{message}\n");

		// Replace placeholders with actual values
		$body = str_replace(
			array('{name}', '{email}', '{message}'),
			array($name, $email, $message),
			$body_template
		);

		// Set email headers
		$headers = array(
			'From: ' . $admin_email,
			'Reply-To: ' . $email
		);

		wp_mail($admin_email, $subject, $body, $headers);
	}
}
