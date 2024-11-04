<?php

/**
 * Fired during plugin activation
 *
 * @link       https://frankremmy.com
 * @since      1.0.0
 *
 * @package    Simple_Contact_Form
 * @subpackage Simple_Contact_Form/admin/partials
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Simple_Contact_Form
 * @subpackage Simple_Contact_Form/admin/partials
 * @author     Frank Remmy <ugochukwufrankremmy@outlook.com>
 */

class Simple_Contact_Form_Database {
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'scf_submissions';
	}

//	Create database table for submissions on plugin activation
	public function create_submissions_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            message text NOT NULL,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

//	Save a new submission to the database
	public function save_submission( $name, $email, $message ) {
		global $wpdb;

		return $wpdb->insert(
			$this->table_name,
			array(
				'name'=> sanitize_text_field($name),
				'email'=> sanitize_email($email),
				'message'=> sanitize_text_field($message),
			),
			array(
				'%s',
				'%s',
				'%s',
			)
		);
	}

//	Retrieve submissions with optional pagination
	public function get_submissions( $limit = 10, $offset = 0 ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT * FROM {$this->table_name} ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
			$limit,
			$offset
		);

		return $wpdb->get_results( $query );
	}

//	Count total number of submissions
	public function count_submissions() {
		global $wpdb;

		return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
	}
	
//	Delete a submission by ID
	public function delete_submission( $id ) {
		global $wpdb;
		
		return $wpdb->delete($this->table_name, array('id' => absint($id)), array('%d'));
	}
	
//	Bulk delete submissions by IDs
	public function bulk_delete_submissions( $ids ) {
		global $wpdb;

		$ids = array_map('absint', $ids );
		$ids_placeholder = implode(',', array_fill(0, count( $ids ), '%d'));

		return $wpdb->query(
			$wpdb->prepare("DELETE FROM {$this->table_name} WHERE id IN ($ids_placeholder)", ...$ids)
		);
	}
}