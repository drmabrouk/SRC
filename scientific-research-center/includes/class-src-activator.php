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
