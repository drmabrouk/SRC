<?php
/**
 * Role and Permission management class.
 */
class SRC_Roles {

	/**
	 * Create and register custom roles.
	 */
	public static function register_roles() {
		$roles = array(
			'src_system_admin' => array(
				'name'         => __( 'System Administrator', 'scientific-research-center' ),
				'capabilities' => array(
					'manage_src_settings' => true,
					'manage_src_users'    => true,
					'manage_src_papers'   => true,
					'read'                => true,
				),
			),
			'src_editor_in_chief' => array(
				'name'         => __( 'Editor-in-Chief', 'scientific-research-center' ),
				'capabilities' => array(
					'assign_src_reviewers' => true,
					'approve_src_papers'   => true,
					'manage_src_papers'    => true,
					'read'                 => true,
				),
			),
			'src_scientific_reviewer' => array(
				'name'         => __( 'Scientific Reviewer', 'scientific-research-center' ),
				'capabilities' => array(
					'review_src_papers' => true,
					'read'              => true,
				),
			),
			'src_author' => array(
				'name'         => __( 'Author / Researcher', 'scientific-research-center' ),
				'capabilities' => array(
					'upload_src_papers' => true,
					'edit_own_papers'   => true,
					'read'              => true,
				),
			),
			'src_registered_reader' => array(
				'name'         => __( 'Registered Reader', 'scientific-research-center' ),
				'capabilities' => array(
					'read' => true,
				),
			),
		);

		foreach ( $roles as $role_slug => $role_data ) {
			add_role( $role_slug, $role_data['name'], $role_data['capabilities'] );
		}
	}

	/**
	 * Remove custom roles on deactivation (optional).
	 */
	public static function unregister_roles() {
		$roles = array(
			'src_system_admin',
			'src_editor_in_chief',
			'src_scientific_reviewer',
			'src_author',
			'src_registered_reader',
		);

		foreach ( $roles as $role_slug ) {
			remove_role( $role_slug );
		}
	}
}
