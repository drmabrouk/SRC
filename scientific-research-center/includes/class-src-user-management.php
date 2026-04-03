<?php
/**
 * SRC_User_Management Class
 * Handles import and export of user accounts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_User_Management {

	public function handle_actions() {
		if ( isset( $_POST['src_export_users'] ) ) {
			$this->export_users();
		}

		if ( isset( $_POST['src_import_users'] ) ) {
			$this->import_users();
		}
	}

	/**
	 * Export all users to a JSON format
	 */
	public function export_users() {
		check_admin_referer( 'src_user_export', 'src_export_nonce' );

		$users = get_users();
		$user_data = array();

		foreach ( $users as $user ) {
			$user_data[] = array(
				'user_login'    => $user->user_login,
				'user_email'    => $user->user_email,
				'user_registered' => $user->user_registered,
				'roles'         => $user->roles,
				'meta'          => get_user_meta( $user->ID ),
			);
		}

		$json_data = wp_json_encode( $user_data );
		$filename = 'src_users_export_' . date( 'Y-m-d_H-i' ) . '.json';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		echo $json_data;
		exit;
	}

	/**
	 * Import users from a JSON file
	 */
	public function import_users() {
		check_admin_referer( 'src_user_import', 'src_import_nonce' );

		if ( ! empty( $_FILES['src_import_file']['tmp_name'] ) ) {
			$json_data = file_get_contents( $_FILES['src_import_file']['tmp_name'] );
			$users = json_decode( $json_data, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				add_action( 'admin_notices', function() {
					echo '<div class="error"><p>' . __( 'Invalid JSON file provided.', 'scientific-research-center' ) . '</p></div>';
				} );
				return;
			}

			if ( is_array( $users ) ) {
				$imported_count = 0;
				foreach ( $users as $user_data ) {
					// Check if user exists
					if ( ! email_exists( $user_data['user_email'] ) && ! username_exists( $user_data['user_login'] ) ) {
						$user_id = wp_insert_user( array(
							'user_login' => $user_data['user_login'],
							'user_email' => $user_data['user_email'],
							'user_pass'  => wp_generate_password(),
						) );

						if ( ! is_wp_error( $user_id ) ) {
							$user = new WP_User( $user_id );
							foreach ( $user_data['roles'] as $role ) {
								$user->add_role( $role );
							}

							// Import meta
							foreach ( $user_data['meta'] as $key => $values ) {
								foreach ( $values as $value ) {
									update_user_meta( $user_id, $key, maybe_unserialize( $value ) );
								}
							}
							$imported_count++;
						}
					}
				}
				add_action( 'admin_notices', function() use ($imported_count) {
					printf( '<div class="updated"><p>' . __( '%d users imported successfully!', 'scientific-research-center' ) . '</p></div>', $imported_count );
				} );
			}
		}
	}
}
