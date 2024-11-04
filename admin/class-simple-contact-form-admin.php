<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://frankremmy.com
 * @since      1.0.0
 *
 * @package    Simple_Contact_Form
 * @subpackage Simple_Contact_Form/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simple_Contact_Form
 * @subpackage Simple_Contact_Form/admin
 * @author     Frank Remmy <ugochukwufrankremmy@outlook.com>
 */
class Simple_Contact_Form_Admin {

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
	 * @param      string    $simple_contact_form       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $simple_contact_form, $version ) {

		$this->simple_contact_form = $simple_contact_form;
		$this->version = $version;

		//	Initialize the database class
		$db = new Simple_Contact_Form_Database();
		$submissions = $db->get_submissions(10, 0); // Retrieve submissions

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->simple_contact_form, plugin_dir_url( __FILE__ ) . 'css/simple-contact-form-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->simple_contact_form, plugin_dir_url( __FILE__ ) . 'js/simple-contact-form-admin.js', array( 'jquery' ), $this->version, false );

	}

//	Method to add the plugin admin menu
	public function add_plugin_admin_menu() {
		add_menu_page(
			__('Simple Contact Form Submissions', 'simple-contact-form'),
			__('SCF Submissions','simple-contact-form'),
			'manage_options',
			'scf-submissions',
			array( $this, 'display_plugin_admin_page'),
			'dashicons-email-alt2',
			80
		);

		add_submenu_page(
		'scf-submissions',
			__('View Submissions', 'simple-contact-form'),
			__('View Submissions', 'simple-contact-form'),
			'manage_options',
			'view-submissions',
			array($this, 'display_plugin_admin_page')
		);

		add_options_page(
			__('Simple Contact Form Settings', 'simple-contact-form'),
			__('SCF Settings', 'simple-contact-form'),
			'manage_options',
			'scf-settings',
			array($this, 'display_settings_page')
		);
	}
	// Display the submissions page
	public function display_plugin_admin_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scf_submissions';

		// Handle single deletion
		if (isset($_GET['delete'])) {
			$id_to_delete = absint($_GET['delete']);
			if ($this->delete_submission($id_to_delete)) {
				echo '<div class="notice notice-success is-dismissible"><p>' . __('Submission deleted.', 'simple-contact-form') . '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . __('Submission not found or already deleted.', 'simple-contact-form') . '</p></div>';
			}
			wp_redirect(admin_url('admin.php?page=scf-submissions'));
			exit;
		}

		// Handle bulk delete
		$this->bulk_delete_submissions();

		// Fetch submissions and render the table
		$limit = 10;
		$paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
		$offset = ($paged - 1) * $limit;
		$results = $this->get_submissions($limit, $offset);

		// Admin table for submissions
		echo '<div class="wrap">';
		echo '<h1>' . __('Simple Contact Form Submissions', 'simple-contact-form') . '</h1>';
		echo '<form method="post" action="">';
		echo '<input type="hidden" name="scf_bulk_action" value="delete" />';
		echo '<a href="' . esc_url(admin_url('admin.php?page=scf-submissions&export=csv')) . '" class="button button-primary">' . __('Export to CSV', 'simple-contact-form') . '</a>';
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<td id="cb" class="manage-column column-cb check-column">';
		echo '<input type="checkbox" id="select-all" />';
		echo '</td>';
		echo '<th>' . __('Name', 'simple-contact-form') . '</th>';
		echo '<th>' . __('Email', 'simple-contact-form') . '</th>';
		echo '<th>' . __('Message', 'simple-contact-form') . '</th>';
		echo '<th>' . __('Date', 'simple-contact-form') . '</th>';
		echo '<th>' . __('Action', 'simple-contact-form') . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if (!empty($results)) {
			foreach ($results as $submission) {
				echo '<tr>';
				echo '<th scope="row" class="check-column"><input type="checkbox" name="submission_ids[]" value="' . esc_attr($submission->id) . '" /></th>';
				echo '<td>' . esc_html($submission->name) . '</td>';
				echo '<td>' . esc_html($submission->email) . '</td>';
				echo '<td>' . esc_html($submission->message) . '</td>';
				echo '<td>' . esc_html($submission->submitted_at) . '</td>';
				echo '<td><a href="?page=scf-submissions&delete=' . esc_attr($submission->id) . '">' . __('Delete', 'simple-contact-form') . '</a></td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="5">' . __('No submissions found.', 'simple-contact-form') . '</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';
		echo '<p><input type="submit" class="button action" value="' . __('Delete Selected', 'simple-contact-form') . '"/></p>';
		echo '</form>';
		echo '</div>';

		// Add pagination
		$total_submissions = $this->count_submissions();
		$total_pages = ceil($total_submissions / $limit);
		if ($total_pages > 1) {
			$this->render_pagination($paged, $total_pages);
		}
	}

	// Method for handling single submission deletion
	private function delete_submission($id) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scf_submissions';
		return $wpdb->delete($table_name, array('id' => $id), array('%d'));
	}

	// Method for handling bulk deletion of submissions
	private function bulk_delete_submissions() {
		if (isset($_POST['scf_bulk_action']) && $_POST['scf_bulk_action'] === 'delete') {
			if (isset($_POST['submission_ids']) && is_array($_POST['submission_ids'])) {
				foreach ($_POST['submission_ids'] as $submission_id) {
					$this->delete_submission(absint($submission_id));
				}
				echo '<div class="notice notice-success">' . __('Selected submissions deleted successfully.', 'simple-contact-form') . '</div>';
			} else {
				echo '<div class="notice notice-error">' . __('Please select at least one submission to delete.', 'simple-contact-form') . '</div>';
			}
		}
	}

	// Fetch submissions with pagination
	private function get_submissions($limit, $offset) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scf_submissions';
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name LIMIT %d OFFSET %d", $limit, $offset));
	}

	// Count the total number of submissions
	private function count_submissions() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scf_submissions';
		return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
	}

	// Render pagination links
	private function render_pagination($paged, $total_pages) {
		$base_url = admin_url('admin.php?page=scf-submissions');
		$first_page_url = $base_url;
		$prev_page_url = $base_url . '&paged=' . max(1, $paged - 1);
		$next_page_url = $base_url . '&paged=' . min($total_pages, $paged + 1);
		$last_page_url = $base_url . '&paged=' . $total_pages;

		echo '<div class="tablenav bottom">';
		echo '<div class="tablenav-pages">';
		printf('<span class="displaying-num">%s items</span>', $this->count_submissions());
		echo '<span class="pagination-links">';
		echo '<a class="first-page button" href="' . esc_url($first_page_url) . '"><span class="screen-reader-text">' . __('First page', 'simple-contact-form') . '</span><span aria-hidden="true">&laquo;</span></a>';
		echo '<a class="prev-page button" href="' . esc_url($prev_page_url) . '"><span class="screen-reader-text">' . __('Previous page', 'simple-contact-form') . '</span><span aria-hidden="true">&lsaquo;</span></a>';
		echo '<span class="paging-input"><span class="tablenav-paging-text">' . esc_html($paged) . ' of <span class="total-pages">' . esc_html($total_pages) . '</span></span></span>';
		echo '<a class="next-page button" href="' . esc_url($next_page_url) . '"><span class="screen-reader-text">' . __('Next page', 'simple-contact-form') . '</span><span aria-hidden="true">&rsaquo;</span></a>';
		echo '<a class="last-page button" href="' . esc_url($last_page_url) . '"><span class="screen-reader-text">' . __('Last page', 'simple-contact-form') . '</span><span aria-hidden="true">&raquo;</span></a>';
		echo '</span>';
		echo '</div>';
		echo '</div>';
	}

//	Display the settings page
	// Display the settings page content
	public function display_settings_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'simple-contact-form'));
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Simple Contact Form Settings', 'simple-contact-form') . '</h1>';
		echo '<form method="post" action="options.php">';

		settings_fields('scf_settings_group');
		do_settings_sections('scf-settings');
		submit_button();

		echo '</form>';
		echo '</div>';
	}

	// Register settings for the plugin
	public function register_settings() {
		register_setting(
			'scf_settings_group',      // Option group
			'scf_recipient_email'      // Option name
		);
		register_setting(
			'scf_settings_group',
			'scf_email_subject'
		);
		register_setting(
			'scf_settings_group',
			'scf_email_body'
		);

		add_settings_section(
			'scf_email_settings_section',
			__('Email Notification Settings', 'simple-contact-form'),
			array($this, 'email_settings_section_callback'),
			'scf-settings'
		);

		add_settings_field(
			'scf_recipient_email',
			__('Recipient Email', 'simple-contact-form'),
			array($this, 'recipient_email_callback'),
			'scf-settings',
			'scf_email_settings_section'
		);

		add_settings_field(
			'scf_email_subject',
			__('Email Subject', 'simple-contact-form'),
			array($this, 'email_subject_callback'),
			'scf-settings',
			'scf_email_settings_section'
		);

		add_settings_field(
			'scf_email_body',
			__('Email Body Template', 'simple-contact-form'),
			array($this, 'email_body_callback'),
			'scf-settings',
			'scf_email_settings_section'
		);
	}

	// Section callback for email settings
	public function email_settings_section_callback() {
		echo '<p>' . __('Customize the email notifications sent when a form is submitted.', 'simple-contact-form') . '</p>';
	}

	// Callback to render the recipient email input
	public function recipient_email_callback() {
		$email = get_option('scf_recipient_email', get_option('admin_email'));
		echo '<input type="email" name="scf_recipient_email" value="' . esc_attr($email) . '" />';
	}

	// Callback to render the email subject input
	public function email_subject_callback() {
		$subject = get_option('scf_email_subject', __('New Contact Form Submission', 'simple-contact-form'));
		echo '<input type="text" name="scf_email_subject" value="' . esc_attr($subject) . '" />';
	}

	// Callback to render the email body template input
	public function email_body_callback() {
		$body = get_option('scf_email_body', __("You have received a new message:\n\nName: {name}\nEmail: {email}\nMessage:\n{message}\n", 'simple-contact-form'));
		echo '<textarea name="scf_email_body" rows="5" cols="50">' . esc_textarea($body) . '</textarea>';
	}
}