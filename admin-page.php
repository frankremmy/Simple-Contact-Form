<?php
// add menu item
function scf_add_admin_menu(){
    add_menu_page(
        'Simple Contact Form Submissions', //Page title
        'SCF Submissions', // Menu title
        'manage_options', //Capability
        'scf_submissions', //Menu slug
        'scf_display_submissions' //callback function
    );
}
add_action( 'admin_menu', 'scf_add_admin_menu');

// Display form submission on admin page
function scf_display_submissions(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'scf_submissions';

    // Pagination settings
    $limit = 5; // No of submissions per page
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $offset = ($paged - 1) * $limit;
    // $results = $wpdb->get_results("SELECT * FROM $table_name"); //Retrieve submissions from db

    // Return db submissions w/ limit and offset
    $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT $limit OFFSET $offset");

    // Return total no of submissions
    $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_submissions/$limit);
    
    // Admin submissions table
    echo '<div class="wrap">';
    echo '<h1>Simple Contact Form Submissions</h1>';
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Action</th></tr></thead>';
    echo '<tbody>';
        if ($results) {
        foreach ($results as $submission) {
            echo '<tr>';
            echo '<td>' . esc_html($submission->name) . '</td>';
            echo '<td>' . esc_html($submission->email) . '</td>';
            echo '<td>' . esc_html($submission->message) . '</td>';
            echo '<td>' . esc_html($submission->submitted_at) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No submissions found.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';

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