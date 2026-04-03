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

		// Create Dashboard pages for each role
		self::create_dashboard_pages();

		// Flush rewrite rules
		flush_rewrite_rules();
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
	}

	/**
	 * Automatically create dashboard pages for each role
	 */
	private static function create_dashboard_pages() {
		$roles = array(
			'src_administrator' => 'Administrator Dashboard',
			'src_supervisor'    => 'Supervisor Dashboard',
			'src_institution'   => 'Institution Dashboard',
			'src_reviewer'      => 'Reviewer Dashboard',
			'src_researcher'    => 'Researcher Dashboard',
			'src_member'        => 'Member Dashboard',
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
