<?php

// Ensure the required files are loaded
require_once plugin_dir_path(__FILE__) . '../includes/database.php'; // Load the database functions

function scf_add_admin_menu(){
	add_menu_page(
		'Simple Contact Form Submissions',
		'SCF Submissions',
		'manage_options',
		'scf-submissions',
		'scf_display_submissions',
		'dashicons-email-alt2',
		80

	);
//	Submenu
	add_submenu_page(
		'scf-submissions',
		'View Submissions',
		'View Submissions',
		'manage_options',
		'view_submissions',
		'scf_display_submissions'
	);
}
add_action( 'admin_menu', 'scf_add_admin_menu');

// Admin Page: Display Submissions
function scf_display_submissions() {
// Check capabilities
	if (!current_user_can('view_scf_submissions') && !current_user_can('manage_options')) {
		wp_die(__('You do not have permissions to view submissions.', 'scf'));
	}
	// Handle single deletion
	if (isset($_GET['delete'])) {
		$id_to_delete = absint($_GET['delete']);
		if (scf_delete_submission($id_to_delete)) {
			echo '<div class="notice notice-success is-dismissible"><p>Submission deleted.</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>Submission not found or already deleted.</p></div>';
		}
		wp_redirect(admin_url('admin.php?page=scf_submissions'));
		exit;
	}

	// Handle bulk delete
	scf_bulk_delete_submissions();

	// Fetch submissions and render the table
	$limit = 10;
	$paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
	$offset = ($paged - 1) * $limit;
	$results = scf_get_submissions($limit, $offset);

	// Admin table for submissions
	echo '<div class="wrap">';
	echo '<h1>Simple Contact Form Submissions</h1>';
	echo '<form method="post" action="">';
	echo '<input type="hidden" name="scf_bulk_action" value="delete" />';
	echo '<a href="' . esc_url(admin_url('admin.php?page=scf_submissions&export=csv')) . '" class="button button-primary">Export to CSV</a>';
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<thead><tr>';
	echo '<td id="cb" class="manage-column column-cb check-column">';
	echo '<input type="checkbox" id="select-all" />';
	echo '</td>';
	echo '<th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Action</th>';
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
			echo '<td><a href="?page=scf_submissions&delete=' . esc_attr($submission->id) . '">Delete</a></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="5">No submissions found.</td></tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '<p><input type="submit" class="button action" value="Delete Selected" /></p>';
	echo '</form>';
	echo '</div>';

	// Add pagination
	$total_submissions = scf_count_submissions();
	$total_pages = ceil($total_submissions / $limit);
	if ($total_pages > 1) {
		$base_url = admin_url('admin.php?page=scf_submissions');
		$first_page_url = $base_url;
		$prev_page_url = $base_url . '&paged=' . max(1, $paged - 1);
		$next_page_url = $base_url . '&paged=' . min($total_pages, $paged + 1);
		$last_page_url = $base_url . '&paged=' . $total_pages;

		echo '<div class="tablenav bottom">';
		echo '<div class="tablenav-pages">';
		printf('<span class="displaying-num">%s items</span>', $total_submissions);
		echo '<span class="pagination-links">';
		echo '<a class="first-page button" href="' . esc_url($first_page_url) . '"><span class="screen-reader-text">First page</span><span aria-hidden="true">&laquo;</span></a>';
		echo '<a class="prev-page button" href="' . esc_url($prev_page_url) . '"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">&lsaquo;</span></a>';
		echo '<span class="paging-input"><span class="tablenav-paging-text">' . esc_html($paged) . ' of <span class="total-pages">' . esc_html($total_pages) . '</span></span></span>';
		echo '<a class="next-page button" href="' . esc_url($next_page_url) . '"><span class="screen-reader-text">Next page</span><span aria-hidden="true">&rsaquo;</span></a>';
		echo '<a class="last-page button" href="' . esc_url($last_page_url) . '"><span class="screen-reader-text">Last page</span><span aria-hidden="true">&raquo;</span></a>';
		echo '</span>';
		echo '</div>';
		echo '</div>';
	}
}

// Handle export to CSV
function scf_export_submission_csv() {
	if (isset($_GET['export']) && $_GET['export'] === 'csv') {
		scf_export_submissions_to_csv();  // Call the refactored function in database.php
	}
}
add_action('admin_init', 'scf_export_submission_csv');

// Handle bulk deletion in the admin panel
function scf_bulk_delete_submissions() {
	if (isset($_POST['scf_bulk_action']) && $_POST['scf_bulk_action'] === 'delete') {
		if (isset($_POST['submission_ids']) && is_array($_POST['submission_ids'])) {
			scf_bulk_delete_selected_submissions($_POST['submission_ids']);  // Call the refactored function in database.php
			echo '<div class="notice notice-success">Selected submissions deleted successfully.</div>';
		} else {
			echo '<div class="notice notice-error">Please select at least one submission to delete.</div>';
		}
	}
}
add_action('admin_init', 'scf_bulk_delete_submissions');