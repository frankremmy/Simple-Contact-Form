<?php
// add menu item
function scf_add_admin_menu(){
	add_menu_page(
		'Simple Contact Form Submissions',
		'SCF Submissions',
		'manage_options',
		'scf_submissions',
		'scf_display_submissions',
		'dashicons-email-alt2',
		80

	);
//	Submenu
	add_submenu_page(
		'scf_submissions',
		'View Submissions',
		'View Submissions',
		'manage_options',
		'view_submissions',
		'scf_display_submissions'
	);
}
add_action( 'admin_menu', 'scf_add_admin_menu');

// Display form submission on admin page
function scf_display_submissions(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

// Deletion handling
	if (isset($_GET['delete'])) {
		$id_to_delete = absint($_GET['delete']); // Make sure it's an integer
		$table_name = $wpdb->prefix . 'scf_submissions';
		$submission_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE id = %d", $id_to_delete));

		if ($submission_exists) {
			$wpdb->delete($table_name, ['id' => $id_to_delete]);
			echo '<div class="notice notice-success is-dismissible"><p>Submission deleted.</p></div>';
			wp_redirect(admin_url('admin.php?page=scf_submissions'));
			exit;
		} else{
			echo '<div class="notice notice-success is-dismissible"><p>Submission not found or already deleted.</p></div>';
		}
	}
	// Pagination logic
	$limit = 10; // No of submissions per page
	$paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
	$offset = ($paged - 1) * $limit;

	// Return db submissions w/ limit and offset
	$results = $wpdb->get_results("SELECT * FROM $table_name LIMIT $limit OFFSET $offset");

	// Admin submissions table
	echo '<div class="wrap">';
	echo '<h1>Simple Contact Form Submissions</h1>';

//	Add bulk delete form
	echo '<form method="post" action="">';
	echo '<input type="hidden" name="scf_bulk_action" value="delete" />';
	echo '<a href="' . esc_url(admin_url('admin.php?page=scf_submissions&export=csv')) . '" class="button button-primary">Export to CSV</a>'; //Add "Export to CSV" button

//	Style table with WP styling classes
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<thead>';
	echo '<tr>';
	echo '<td id="cb" class="manage-column column-cb check-column">';
	echo '<input type="checkbox" id="select-all" />';
	echo '</td>';
	echo '<th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Action</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	if ($results) {
		foreach ($results as $submission) {
			echo '<tr>';
			echo '<th scope="row" class="check-column">';
			echo '<input type="checkbox" name="submission_ids[]" value="' . esc_attr($submission->id) . '" />';
			echo '</th>';
			echo '<td>' . esc_html($submission->name) . '</td>';
			echo '<td>' . esc_html($submission->email) . '</td>';
			echo '<td>' . esc_html($submission->message) . '</td>';
			echo '<td>' . esc_html($submission->submitted_at) . '</td>';
			echo '<td><a href="?page=scf_submissions&delete=' . esc_attr($submission->id) . '">Delete</a></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="5">No submissions found.</td></tr>';
	}

	echo '</tbody>';
	echo '</table>';

//	Add a bulk delete button with WP styling
	echo '<p><input type="submit" class="button action" value="Delete Selected" /></p>';
	echo '</form>';
	echo '</div>';

	// Add Pagination
	$total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name"); 	// Return total no of submissions
	$total_pages = ceil($total_submissions/$limit);
	if ($total_pages > 1) {
		// Construct the pagination URLs manually
		$base_url = admin_url('admin.php?page=scf_submissions');
		$first_page_url = $base_url;
		$prev_page_url = $base_url . '&paged=' . max(1, $paged - 1);
		$next_page_url = $base_url . '&paged=' . min($total_pages, $paged + 1);
		$last_page_url = $base_url . '&paged=' . $total_pages;

		echo '<div class="tablenav bottom">';
		echo '<div class="tablenav-pages">';
		printf('<span class="displaying-num">%s items</span>', $total_submissions);

		// Output the pagination links with manually constructed URLs
		echo '<span class="pagination-links">';
		echo '<a class="first-page button" href="' . esc_url($first_page_url) . '"><span class="screen-reader-text">First page</span><span aria-hidden="true">&laquo;</span></a>';
		echo '<a class="prev-page button" href="' . esc_url($prev_page_url) . '"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">&lsaquo;</span></a>';
		echo '<span class="screen-reader-text">Current Page</span>';
		echo '<span class="paging-input"><span class="tablenav-paging-text">' . esc_html($paged) . ' of <span class="total-pages">' . esc_html($total_pages) . '</span></span></span>';
		echo '<a class="next-page button" href="' . esc_url($next_page_url) . '"><span class="screen-reader-text">Next page</span><span aria-hidden="true">&rsaquo;</span></a>';
		echo '<a class="last-page button" href="' . esc_url($last_page_url) . '"><span class="screen-reader-text">Last page</span><span aria-hidden="true">&raquo;</span></a>';
		echo '</span>';  // End of pagination-links
		echo '</div>';  // End of tablenav-pages
		echo '</div>';  // End of tablenav bottom
	}
}
/**
 * Exports form submission data to a CSV file and forces download if the 'export' GET parameter is set to 'csv'.
 *
 * Retrieves data from the specified database table and formats it as CSV, where each row corresponds to a submission
 * entry including Name, Email, Message, and the time it was submitted.
 *
 * @return void This method does not return a value. Instead, it sends the CSV output directly to the browser.
 */
function scf_export_submission_csv() {
	if (isset($_GET['export']) && $_GET['export'] == 'csv') {
		global $wpdb;
		$table_name = $wpdb->prefix . 'scf_submissions';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=submissions.csv');

		$output = fopen('php://output', 'w');

		fputcsv($output, ['Name', 'Email', 'Message', 'Submitted At']);

		$rows = $wpdb->get_results("SELECT name, email, message, submitted_at FROM $table_name", ARRAY_A);

		foreach ($rows as $row) {
			fputcsv($output, $row);
		}

		fclose($output);
		exit;
	}
}
add_action('admin_init', 'scf_export_submission_csv');

// Handle bulk deletion of submissions
function scf_bulk_delete_submissions() {
	if(isset($_POST['scf_bulk_action']) && $_POST['scf_bulk_action'] == 'delete') {
		if (isset($_POST['submission_ids']) && is_array($_POST['submission_ids'])) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'scf_submissions';
//			 Sanitize and delete each selected submission
			foreach ($_POST['submission_ids'] as $submission_id) {
				$submission_id = absint($submission_id);
				$wpdb->delete($table_name, ['id' => $submission_id]);
			}
//			Provide feedback to admin
			echo '<div class="notice notice-success">Selected submissions deleted successfully.</div>';
		} else{
			echo '<div class="notice notice-error">Please select at least one submission to delete.</div>';
		}
	}
}
add_action('admin_init', 'scf_bulk_delete_submissions');