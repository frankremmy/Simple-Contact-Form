<?php

function scf_add_admin_menu(){
    add_menu_page(
        'Simple Contact Form Submissions', //Page titel
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
    $results = $wpdb->get_results("SELECT * FROM $table_name"); //Retrieve submissions from db
    
    //Display submissions
    echo '<div class="wrap">';
    echo '<h1>Simple Contact For Submissions</h1>';
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr></thead>';
    echo '<tbody>';
    if ($results){
        foreach ($results as $submissions) {
            echo '<tr>';
            echo '<td>' . esc_html($submissions->name) . '</td>';
            echo '<td>' . esc_html($submissions->email) . '</td>';
            echo '<td>' . esc_html($submissions->message) . '</td>';
            echo '<td>' . esc_html($submissions->submitted_at) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4">No submissions found.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}