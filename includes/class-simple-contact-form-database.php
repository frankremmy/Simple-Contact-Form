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
	private $table_forms;
	private $table_form_fields;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'scf_submissions';
		$this->table_forms = $wpdb->prefix . 'scf_forms';
		$this->table_form_fields = $wpdb->prefix . 'scf_form_fields';
	}

//	Create database table for submissions on plugin activation
	public function create_custom_form_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE {$this->table_name} (
	            id mediumint(9) NOT NULL AUTO_INCREMENT,
	            name varchar(100) NOT NULL,
	            email varchar(100) NOT NULL,
	            message text NOT NULL,
	            submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
	            PRIMARY KEY  (id)
	        ) $charset_collate;;
	
			CREATE TABLE {$this->table_forms} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
	                title varchar(255) NOT NULL,
	                shortcode varchar(50) NOT NULL,
	                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
	                PRIMARY KEY  (id)
	            ) $charset_collate;
	
	        CREATE TABLE {$this->table_form_fields} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
	                form_id mediumint(9) NOT NULL,
	                field_type varchar(50) NOT NULL,
	                label varchar(255) NOT NULL,
	                placeholder varchar(255) DEFAULT NULL,
	                options text DEFAULT NULL, -- For dropdowns, checkboxes, etc.
	                                                                         required boolean DEFAULT false,
	                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
	                PRIMARY KEY  (id),
	                FOREIGN KEY (form_id) REFERENCES {$this->table_forms}(id) ON DELETE CASCADE
	            ) $charset_collate;
			";

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

	// Save a new form and return its ID
	public function save_form($title) {
		global $wpdb;

		$shortcode = '[simple_contact_form id="' . uniqid() . '" title="' . sanitize_title($title) . '"]';
		$wpdb->insert(
			$this->table_forms,
			array(
				'title' => sanitize_text_field($title),
				'shortcode' => $shortcode,
			),
			array(
				'%s', '%s'
			)
		);
		return $wpdb->insert_id;
	}

	// Save individual fields for a form
	public function save_form_field($form_id, $field_type, $label, $placeholder = '', $options = '', $required = false) {
		global $wpdb;

		$wpdb->insert(
			$this->table_form_fields,
			array(
				'form_id' => absint($form_id),
				'field_type' => sanitize_text_field($field_type),
				'label' => sanitize_text_field($label),
				'placeholder' => sanitize_text_field($placeholder),
				'options' => maybe_serialize($options), // For fields with multiple options
				'required' => (bool) $required
			),
			array(
				'%d', '%s', '%s', '%s', '%s', '%d'
			)
		);
		return $wpdb->insert_id;
	}

	// Retrieve all forms
	public function get_all_forms() {
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM {$this->table_forms} ORDER BY created_at DESC");
	}

	// Retrieve fields for a specific form
	public function get_form_fields($form_id) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT * FROM {$this->table_form_fields} WHERE form_id = %d ORDER BY id ASC", $form_id);
		return $wpdb->get_results($query);
	}

	// Delete a form and its fields
	public function delete_form($form_id) {
		global $wpdb;
		return $wpdb->delete($this->table_forms, array('id' => absint($form_id)), array('%d'));
	}
}

