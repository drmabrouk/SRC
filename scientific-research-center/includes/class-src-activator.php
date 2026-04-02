<?php
/**
 * Fired during plugin activation.
 */
class SRC_Activator {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		// Register roles
		require_once plugin_dir_path( __FILE__ ) . 'class-src-roles.php';
		SRC_Roles::register_roles();

		// Create essential pages
		self::create_essential_pages();

		// Flush rewrite rules
		require_once plugin_dir_path( __FILE__ ) . 'class-src-rewrites.php';
		SRC_Rewrites::add_rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Programmatically create essential pages.
	 */
	private static function create_essential_pages() {
		$pages = array(
			'src_home' => array(
				'title'   => 'Research Engine',
				'content' => '[src_research_engine]',
				'slug'    => 'research-engine',
			),
			'src_search' => array(
				'title'   => 'Search Results',
				'content' => '[src_search_results]',
				'slug'    => 'search-results',
			),
			'src_auth' => array(
				'title'   => 'Authentication Portal',
				'content' => '[src_auth_portal]',
				'slug'    => 'auth-portal',
			),
			'src_profile' => array(
				'title'   => 'User Profile',
				'content' => '[src_user_profile]',
				'slug'    => 'user-profile',
			),
			'src_submission' => array(
				'title'   => 'Submission Portal',
				'content' => '[src_submission_wizard]',
				'slug'    => 'submission-portal',
			),
		);

		foreach ( $pages as $key => $page_data ) {
			$page_check = get_page_by_path( $page_data['slug'] );
			if ( ! isset( $page_check->ID ) ) {
				$page_id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_title'   => $page_data['title'],
						'post_content' => $page_data['content'],
						'post_status'  => 'publish',
						'post_author'  => 1,
						'post_name'    => $page_data['slug'],
					)
				);
				update_option( $key . '_page_id', $page_id );
			}
		}
	}
}
