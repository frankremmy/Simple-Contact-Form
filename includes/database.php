<?php

// Retrieve submissions from the database
function scf_get_submissions($limit, $offset) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

	return $wpdb->get_results($wpdb->prepare(
		"SELECT * FROM $table_name LIMIT %d OFFSET %d",
		$limit,
		$offset
	));
}

// Count total submissions for pagination
function scf_count_submissions() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

	return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
}

// Delete a single submission
function scf_delete_submission($submission_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

	return $wpdb->delete($table_name, ['id' => absint($submission_id)]);
}

// Bulk delete submissions
function scf_bulk_delete_selected_submissions($submission_ids) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

	foreach ($submission_ids as $submission_id) {
		$submission_id = absint($submission_id);
		$wpdb->delete($table_name, ['id' => $submission_id]);
	}
}

// Export submissions to CSV
function scf_export_submissions_to_csv() {
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

// Save a form submission to the database
function scf_save_submission($name, $email, $message) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'scf_submissions';

	return $wpdb->insert(
		$table_name,
		[
			'name' => $name,
			'email' => $email,
			'message' => $message,
			'submitted_at' => current_time('mysql')
		]
	);
}