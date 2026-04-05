<?php
/**
 * SRC_Activator Class
 * Fired during plugin activation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Activator {

	/**
	 * Main activation logic
	 */
	public static function activate() {
		// Register custom roles
		SRC_Roles::register_roles();

		// Create the Login / Register page
		self::create_auth_page();

		// Create Professional Workspace pages for each role
		self::create_workspace_pages();

		// Create activity log table
		self::create_activity_log_table();

		// Create search analytics table
		self::create_search_analytics_table();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Create Activity Log Table
	 */
	private static function create_activity_log_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'src_activity_log';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			event_type varchar(50) NOT NULL,
			description text NOT NULL,
			event_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Automatically create the unified Login/Register page
	 */
	private static function create_auth_page() {
		$page_title = 'Login / Register';
		$page_content = '[src_auth_form]';
		$page_check = get_page_by_title( $page_title );

		if ( ! isset( $page_check->ID ) ) {
			$new_page = array(
				'post_title'   => $page_title,
				'post_content' => $page_content,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			);
			wp_insert_post( $new_page );
		}

		// Create Profile Completion Page
		$pc_title = 'Profile Completion';
		$pc_check = get_page_by_title( $pc_title );
		if ( ! isset( $pc_check->ID ) ) {
			wp_insert_post( array(
				'post_title'   => $pc_title,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			) );
		}

		// Create Submit Research Page
		$sr_title = 'Submit Research';
		if ( ! isset( get_page_by_title( $sr_title )->ID ) ) {
			wp_insert_post( array(
				'post_title'   => $sr_title,
				'post_content' => '[src_submit_research]',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			) );
		}

		// Create Research Library Page
		$rl_title = 'Research Library';
		$rl_page = get_page_by_title( $rl_title );
		if ( ! isset( $rl_page->ID ) ) {
			$rl_id = wp_insert_post( array(
				'post_title'   => $rl_title,
				'post_content' => '[src_research_library]',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			) );
		} else {
			$rl_id = $rl_page->ID;
		}

		// Create Search Results Page
		$sr_res_title = 'Research Results';
		if ( ! isset( get_page_by_title( $sr_res_title )->ID ) ) {
			wp_insert_post( array(
				'post_title'   => $sr_res_title,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			) );
		}

		// Set as Front Page
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $rl_id );
	}

	/**
	 * Automatically create professional workspace pages for each role
	 */
	private static function create_workspace_pages() {
		$roles = array(
			'src_administrator' => 'Administrator Workspace',
			'src_supervisor'    => 'Supervisor Workspace',
			'src_institution'   => 'Institution Workspace',
			'src_reviewer'      => 'Scientific Reviewer Workspace',
			'src_researcher'    => 'Researcher Workspace',
			'src_member'        => 'Member Workspace',
		);

		foreach ( $roles as $slug => $title ) {
			$page_check = get_page_by_title( $title );
			if ( ! isset( $page_check->ID ) ) {
				wp_insert_post( array(
					'post_title'   => $title,
					'post_content' => '', // Content will be handled by the template
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_type'    => 'page',
				) );
			}
		}
	}
}
