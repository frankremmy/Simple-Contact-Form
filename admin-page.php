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

    // Return total no of submissions
    $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_submissions/$limit);
    
    // Admin submissions table
    echo '<div class="wrap">';
    echo '<h1>Simple Contact Form Submissions</h1>';

//	Add "Export to CSV" button
	echo '<a href="' . esc_url(admin_url('admin.php?page=scf_submissions&export=csv')) . '" class="button button-primary">Export to CSV</a>';
    echo '<table class="widefat fixed" border-spacing="0">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Action</th></tr></thead>';
    echo '<tbody>';
        if ($results) {
        foreach ($results as $submission) {
            echo '<tr>';
            echo '<td>' . esc_html($submission->name) . '</td>';
            echo '<td>' . esc_html($submission->email) . '</td>';
            echo '<td>' . esc_html($submission->message) . '</td>';
            echo '<td>' . esc_html($submission->submitted_at) . '</td>';
            echo '<td><a href="?page=scf_submissions&delete=' . esc_attr($submission->id) . '">Delete</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4">No submissions found.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';

    // Pagination links
    echo '<div class="tablenav"><div class="tablenav-pages">';
    if ($total_pages > 1) {
        $current_url = set_url_scheme('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '" class="page-numbers ' . ($paged == $i ? 'current' : '') . '">' . $i . '</a>';
        }
    }
    echo '</div></div>';
    echo '</div>';
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