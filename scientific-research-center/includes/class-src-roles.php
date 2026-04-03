<?php
/**
 * SRC_Roles Class
 * Handles registration of custom user roles and hierarchical capabilities.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Roles {

	public function __construct() {
		// Initialization
	}

	/**
	 * Register custom roles with hierarchical permissions.
	 */
	public static function register_roles() {
		$roles_data = self::get_roles_definition();

		foreach ( $roles_data as $slug => $data ) {
			add_role( $slug, $data['name'], $data['capabilities'] );
		}
	}

	/**
	 * Remove custom roles.
	 */
	public static function remove_roles() {
		reset( self::get_roles_definition() );
		foreach ( self::get_roles_definition() as $slug => $data ) {
			remove_role( $slug );
		}
	}

	/**
	 * Define roles and their hierarchical capabilities.
	 */
	public static function get_roles_definition() {
		// Define core capabilities for Member (base level)
		$member_caps = array(
			'read' => true,
		);

		// Researcher adds to Member
		$researcher_caps = array_merge( $member_caps, array(
			'upload_files' => true,
			'edit_posts'   => true,
		) );

		// Reviewer adds to Researcher
		$reviewer_caps = array_merge( $researcher_caps, array(
			'publish_posts' => true,
			'edit_published_posts' => true,
		) );

		// Institution adds to Reviewer
		$institution_caps = array_merge( $reviewer_caps, array(
			'list_users' => true,
		) );

		// Supervisor adds to Institution
		$supervisor_caps = array_merge( $institution_caps, array(
			'edit_others_posts' => true,
			'moderate_comments' => true,
		) );

		// Administrator (plugin level) adds to Supervisor
		$admin_caps = array_merge( $supervisor_caps, array(
			'manage_options' => true,
			'install_plugins' => true,
		) );

		return array(
			'src_administrator' => array(
				'name'         => __( 'Administrator', 'scientific-research-center' ),
				'capabilities' => $admin_caps,
			),
			'src_supervisor' => array(
				'name'         => __( 'Supervisor', 'scientific-research-center' ),
				'capabilities' => $supervisor_caps,
			),
			'src_institution' => array(
				'name'         => __( 'Institution', 'scientific-research-center' ),
				'capabilities' => $institution_caps,
			),
			'src_reviewer'   => array(
				'name'         => __( 'Reviewer', 'scientific-research-center' ),
				'capabilities' => $reviewer_caps,
			),
			'src_researcher' => array(
				'name'         => __( 'Researcher', 'scientific-research-center' ),
				'capabilities' => $researcher_caps,
			),
			'src_member'     => array(
				'name'         => __( 'Member', 'scientific-research-center' ),
				'capabilities' => $member_caps,
			),
		);
	}
}
