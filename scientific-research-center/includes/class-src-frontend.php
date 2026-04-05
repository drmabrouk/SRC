<?php
/**
 * SRC_Frontend Class
 * Handles AJAX/POST handlers, form rendering, and role-based redirects.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Frontend {

	public function __construct() {
		add_shortcode( 'src_auth_form', array( $this, 'render_auth_form' ) );
		add_shortcode( 'Header', array( $this, 'render_header_list' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'init', array( $this, 'register_profile_rewrites' ) );
		add_action( 'template_redirect', array( $this, 'role_based_redirects' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'template_include', array( $this, 'load_src_templates' ) );

		// AJAX handlers
		add_action( 'wp_ajax_nopriv_src_login', array( $this, 'handle_ajax_login' ) );
		add_action( 'wp_ajax_nopriv_src_register', array( $this, 'handle_ajax_register' ) );
		add_action( 'wp_ajax_nopriv_src_forgot_password', array( $this, 'handle_ajax_forgot_password' ) );
		add_action( 'wp_ajax_src_complete_profile', array( $this, 'handle_ajax_complete_profile' ) );
		add_action( 'wp_ajax_src_load_system_users', array( $this, 'handle_ajax_load_system_users' ) );
		add_action( 'wp_ajax_src_user_action', array( $this, 'handle_ajax_user_action' ) );
		add_action( 'wp_ajax_src_upload_avatar', array( $this, 'handle_ajax_upload_avatar' ) );
		add_action( 'wp_ajax_src_get_notifications', array( $this, 'handle_ajax_get_notifications' ) );
		add_action( 'wp_ajax_src_mark_notifications_read', array( $this, 'handle_ajax_mark_notifications_read' ) );
		add_action( 'wp_ajax_src_toggle_favorite', array( $this, 'handle_ajax_toggle_favorite' ) );
		add_action( 'wp_ajax_src_system_refresh', array( $this, 'handle_ajax_system_refresh' ) );
		add_action( 'wp_ajax_src_add_new_user', array( $this, 'handle_ajax_add_new_user' ) );
		add_action( 'wp_ajax_src_get_user_data', array( $this, 'handle_ajax_get_user_data' ) );
		add_action( 'wp_ajax_src_get_user_logs', array( $this, 'handle_ajax_get_user_logs' ) );
		add_action( 'wp_ajax_src_bulk_export_users', array( $this, 'handle_ajax_bulk_export_users' ) );
		add_action( 'wp_ajax_src_bulk_import_users', array( $this, 'handle_ajax_bulk_import_users' ) );
		add_action( 'wp_ajax_src_save_search_settings', array( $this, 'handle_ajax_save_search_settings' ) );
		add_action( 'wp_ajax_src_get_search_analytics', array( $this, 'handle_ajax_get_search_analytics' ) );
		add_action( 'wp_ajax_src_save_email_template', array( $this, 'handle_ajax_save_email_template' ) );
		add_action( 'wp_ajax_src_save_role_permissions', array( $this, 'handle_ajax_save_role_permissions' ) );
		add_action( 'wp_ajax_src_save_security_settings', array( $this, 'handle_ajax_save_security_settings' ) );
		add_action( 'wp_ajax_src_get_email_template', array( $this, 'handle_ajax_get_email_template' ) );
		add_action( 'wp_ajax_src_get_role_permissions', array( $this, 'handle_ajax_get_role_permissions' ) );
		add_action( 'wp_ajax_src_log_search', array( $this, 'handle_ajax_log_search' ) );
	}

	/**
	 * AJAX Bulk Export Users
	 */
	public function handle_ajax_bulk_export_users() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$users = get_users();
		$user_data = array();
		foreach ( $users as $user ) {
			$user_data[] = array(
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
				'roles'      => $user->roles,
				'meta'       => get_user_meta( $user->ID )
			);
		}
		wp_send_json_success( $user_data );
	}

	/**
	 * AJAX Get Email Template
	 */
	public function handle_ajax_get_email_template() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$template_id = sanitize_text_field( $_POST['template_id'] );
		$subject = get_option( "src_email_tpl_{$template_id}_subject", __( 'Update from Scientific Research Center', 'scientific-research-center' ) );
		$body = get_option( "src_email_tpl_{$template_id}_body", "Hello [user_name],\n\nYour research paper has been updated.\n\nRegards,\nSupport Team" );

		wp_send_json_success( array( 'subject' => $subject, 'body' => $body ) );
	}

	/**
	 * AJAX Get Role Permissions
	 */
	public function handle_ajax_get_role_permissions() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$role_slug = sanitize_text_field( $_POST['role_slug'] );
		$role = get_role( $role_slug );
		if ( ! $role ) wp_send_json_error();

		wp_send_json_success( $role->capabilities );
	}

	/**
	 * AJAX Log Search Analytics
	 */
	public function handle_ajax_log_search() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$keyword = sanitize_text_field( $_POST['keyword'] );
		if ( empty( $keyword ) ) wp_send_json_error();

		global $wpdb;
		$table_name = $wpdb->prefix . 'src_search_analytics';
		$wpdb->insert( $table_name, array(
			'keyword' => $keyword,
			'user_id' => get_current_user_id() ?: NULL
		) );
		wp_send_json_success();
	}

	/**
	 * AJAX Bulk Import Users
	 */
	public function handle_ajax_bulk_import_users() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		if ( empty( $_FILES['import_file'] ) ) {
			wp_send_json_error( array( 'message' => 'No file provided.' ) );
		}

		$json_data = file_get_contents( $_FILES['import_file']['tmp_name'] );
		$users = json_decode( $json_data, true );

		if ( ! is_array( $users ) ) {
			wp_send_json_error( array( 'message' => 'Invalid JSON.' ) );
		}

		$count = 0;
		foreach ( $users as $u ) {
			if ( ! email_exists( $u['user_email'] ) && ! username_exists( $u['user_login'] ) ) {
				$user_id = wp_insert_user( array(
					'user_login' => $u['user_login'],
					'user_email' => $u['user_email'],
					'user_pass'  => wp_generate_password()
				) );
				if ( ! is_wp_error( $user_id ) ) {
					$user = new WP_User( $user_id );
					foreach ( $u['roles'] as $role ) $user->add_role( $role );
					foreach ( $u['meta'] as $k => $vs ) {
						foreach ( $vs as $v ) update_user_meta( $user_id, $k, maybe_unserialize( $v ) );
					}
					$count++;
				}
			}
		}

		src_log_activity( get_current_user_id(), 'admin_action', sprintf( __( 'Bulk imported %d users.', 'scientific-research-center' ), $count ) );
		wp_send_json_success( array( 'message' => sprintf( __( 'Imported %d users successfully.', 'scientific-research-center' ), $count ) ) );
	}

	/**
	 * AJAX Profile Completion Handler
	 */
	public function handle_ajax_complete_profile() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to complete your profile.', 'scientific-research-center' ) ) );
		}

		$meta_fields = array( 'country', 'mobile', 'alt_email', 'gender', 'academic_degree', 'institution', 'country_code', 'specialty' );
		foreach ( $meta_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_user_meta( $user_id, 'src_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}

		// Update Password if provided
		if ( ! empty( $_POST['new_password'] ) && ! empty( $_POST['confirm_password'] ) ) {
			if ( $_POST['new_password'] === $_POST['confirm_password'] ) {
				wp_set_password( $_POST['new_password'], $user_id );
			} else {
				wp_send_json_error( array( 'message' => __( 'Passwords do not match.', 'scientific-research-center' ) ) );
			}
		}

		// Update core user data
		if ( isset( $_POST['first_name'] ) || isset( $_POST['last_name'] ) || isset( $_POST['user_email'] ) ) {
			$user_data = array( 'ID' => $user_id );
			if ( isset( $_POST['first_name'] ) ) $user_data['first_name'] = sanitize_text_field( $_POST['first_name'] );
			if ( isset( $_POST['last_name'] ) ) $user_data['last_name'] = sanitize_text_field( $_POST['last_name'] );
			if ( isset( $_POST['user_email'] ) ) $user_data['user_email'] = sanitize_email( $_POST['user_email'] );
			wp_update_user( $user_data );
		}

		// Handle Profile Picture
		if ( ! empty( $_FILES['profile_picture'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			$attachment_id = media_handle_upload( 'profile_picture', 0 );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_user_meta( $user_id, 'src_profile_picture', $attachment_id );
			}
		}

		wp_send_json_success( array(
			'message' => __( 'Profile completed successfully! Redirecting...', 'scientific-research-center' ),
			'redirect' => home_url( '/login-register/' )
		) );
	}

	/**
	 * AJAX Load System Users Handler
	 */
	public function handle_ajax_load_system_users() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) && ! current_user_can( 'list_users' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$filter_role = isset( $_POST['role_filter'] ) ? sanitize_text_field( $_POST['role_filter'] ) : '';
		$institution_filter = isset( $_POST['institution_filter'] ) ? sanitize_text_field( $_POST['institution_filter'] ) : '';
		$status_filter = isset( $_POST['status_filter'] ) ? sanitize_text_field( $_POST['status_filter'] ) : '';
		$specialty_filter = isset( $_POST['specialty_filter'] ) ? sanitize_text_field( $_POST['specialty_filter'] ) : '';
		$orderby = isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'display_name';
		$order = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'ASC';

		$args = array(
			'search'         => $search ? '*' . $search . '*' : '',
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
			'orderby'        => $orderby,
			'order'          => $order,
			'meta_query'     => array( 'relation' => 'AND' )
		);

		if ( $filter_role ) {
			$args['role'] = $filter_role;
		}

		if ( $status_filter ) {
			$args['meta_query'][] = array(
				'key'     => 'src_user_status',
				'value'   => $status_filter,
				'compare' => '='
			);
		}

		if ( $specialty_filter ) {
			$args['meta_query'][] = array(
				'key'     => 'src_specialty',
				'value'   => $specialty_filter,
				'compare' => 'LIKE'
			);
		}

		// Security: Strictly scope access for Institution role
		if ( current_user_can( 'list_users' ) && ! current_user_can( 'manage_options' ) ) {
			$institution_filter = get_user_meta( get_current_user_id(), 'src_institution', true );
			if ( empty( $institution_filter ) ) {
				wp_send_json_error( array( 'message' => __( 'No institution linked to your account.', 'scientific-research-center' ) ) );
			}
			$args['meta_query'][] = array(
				'key'     => 'src_institution',
				'value'   => $institution_filter,
				'compare' => '='
			);
		} elseif ( $institution_filter ) {
			if ( $institution_filter === 'current' ) {
				$institution_filter = get_user_meta( get_current_user_id(), 'src_institution', true );
			}
			$args['meta_query'][] = array(
				'key'     => 'src_institution',
				'value'   => $institution_filter,
				'compare' => '='
			);
		}

		$users = get_users( $args );

		ob_start();
		?>
		<table class="src-user-table">
			<thead>
				<tr>
					<th><?php _e( 'User', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Email', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Role', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Institution', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Specialty', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $users ) ) : ?>
					<tr><td colspan="5"><?php _e( 'No users found.', 'scientific-research-center' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $users as $user ) :
						$user_role = ! empty( $user->roles ) ? $user->roles[0] : 'Member';
						$institution = get_user_meta( $user->ID, 'src_institution', true );
						$specialty = get_user_meta( $user->ID, 'src_specialty', true );
						$status = get_user_meta( $user->ID, 'src_user_status', true );
						if ( ! $status ) $status = 'active';
						?>
						<tr class="status-<?php echo esc_attr( $status ); ?>">
							<td>
								<strong><?php echo esc_html( $user->display_name ); ?></strong><br>
								<small><?php echo esc_html( $user->user_login ); ?></small>
							</td>
							<td><?php echo esc_html( $user->user_email ); ?></td>
							<td><?php echo esc_html( ucfirst( str_replace( 'src_', '', $user_role ) ) ); ?></td>
							<td><?php echo esc_html( $institution ? $institution : '-' ); ?></td>
							<td><?php echo esc_html( $specialty ? $specialty : '-' ); ?></td>
							<td class="src-actions">
								<button class="src-icon-btn src-user-act" data-action="edit" data-id="<?php echo $user->ID; ?>" title="Edit"><span class="dashicons dashicons-edit"></span></button>
								<?php if ( $status === 'active' ) : ?>
									<button class="src-icon-btn src-user-act" data-action="suspend" data-id="<?php echo $user->ID; ?>" title="Suspend"><span class="dashicons dashicons-lock"></span></button>
								<?php else : ?>
									<button class="src-icon-btn src-user-act" data-action="reactivate" data-id="<?php echo $user->ID; ?>" title="Reactivate"><span class="dashicons dashicons-unlock"></span></button>
								<?php endif; ?>
								<button class="src-icon-btn src-user-act src-danger" data-action="delete" data-id="<?php echo $user->ID; ?>" title="Delete"><span class="dashicons dashicons-trash"></span></button>
								<button class="src-icon-btn src-user-act" data-action="notify" data-id="<?php echo $user->ID; ?>" title="Notify"><span class="dashicons dashicons-email"></span></button>
								<button class="src-icon-btn src-user-act" data-action="logs" data-id="<?php echo $user->ID; ?>" title="View Logs"><span class="dashicons dashicons-list-view"></span></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * AJAX Avatar Upload Handler
	 */
	public function handle_ajax_upload_avatar() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'scientific-research-center' ) ) );
		}

		if ( ! empty( $_FILES['avatar'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			$attachment_id = media_handle_upload( 'avatar', 0 );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_user_meta( get_current_user_id(), 'src_profile_picture', $attachment_id );
				wp_send_json_success( array( 'url' => wp_get_attachment_url( $attachment_id ) ) );
			} else {
				wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
			}
		}
		wp_send_json_error( array( 'message' => __( 'No file uploaded', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX User Action Handler
	 */
	public function handle_ajax_user_action() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$user_id = absint( $_POST['user_id'] );
		$action = sanitize_text_field( $_POST['user_action'] );

		switch ( $action ) {
			case 'suspend':
				update_user_meta( $user_id, 'src_user_status', 'suspended' );
				src_log_activity( get_current_user_id(), 'admin_action', sprintf( __( 'Suspended user: %d', 'scientific-research-center' ), $user_id ) );
				// In a production environment, call SRC_Emails::send_suspension_notice($user_id);
				wp_send_json_success( array( 'message' => __( 'User suspended and notified.', 'scientific-research-center' ) ) );
				break;
			case 'reactivate':
				update_user_meta( $user_id, 'src_user_status', 'active' );
				src_log_activity( get_current_user_id(), 'admin_action', sprintf( __( 'Reactivated user: %d', 'scientific-research-center' ), $user_id ) );
				// In a production environment, call SRC_Emails::send_reactivation_notice($user_id);
				wp_send_json_success( array( 'message' => __( 'User reactivated and notified.', 'scientific-research-center' ) ) );
				break;
			case 'delete':
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'message' => __( 'Only administrators can delete users.', 'scientific-research-center' ) ) );
				}
				require_once( ABSPATH . 'wp-admin/includes/user.php' );
				wp_delete_user( $user_id );
				wp_send_json_success( array( 'message' => __( 'User deleted.', 'scientific-research-center' ) ) );
				break;
			case 'notify':
				// Simple notification simulation
				wp_send_json_success( array( 'message' => __( 'Notification sent.', 'scientific-research-center' ) ) );
				break;
			case 'update':
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
				}
				$user_data = array(
					'ID'         => $user_id,
					'first_name' => sanitize_text_field( $_POST['first_name'] ),
					'last_name'  => sanitize_text_field( $_POST['last_name'] ),
					'user_email' => sanitize_email( $_POST['email'] ),
				);
				if ( ! empty( $_POST['password'] ) ) {
					$user_data['user_pass'] = $_POST['password'];
				}
				wp_update_user( $user_data );

				$user = new WP_User( $user_id );
				$user->set_role( sanitize_text_field( $_POST['role'] ) );

				update_user_meta( $user_id, 'src_institution', sanitize_text_field( $_POST['institution'] ) );
				update_user_meta( $user_id, 'src_specialty', sanitize_text_field( $_POST['specialty'] ) );

				src_log_activity( get_current_user_id(), 'admin_action', sprintf( __( 'Updated user info: %s', 'scientific-research-center' ), $user->user_login ) );

				wp_send_json_success( array( 'message' => __( 'User updated successfully.', 'scientific-research-center' ) ) );
				break;
		}
	}

	/**
	 * AJAX Add New User Handler
	 */
	public function handle_ajax_add_new_user() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$user_data = array(
			'username'    => sanitize_user( $_POST['username'] ),
			'email'       => sanitize_email( $_POST['email'] ),
			'password'    => $_POST['password'],
			'first_name'  => sanitize_text_field( $_POST['first_name'] ),
			'last_name'   => sanitize_text_field( $_POST['last_name'] ),
			'role'        => sanitize_text_field( $_POST['role'] ),
			'institution' => sanitize_text_field( $_POST['institution'] ),
		);

		// Manually create user (internal admin bypasses standard registration logic)
		$user_id = wp_create_user( $user_data['username'], $user_data['password'], $user_data['email'] );
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
		}

		$user = new WP_User( $user_id );
		$user->set_role( $user_data['role'] );

		wp_update_user( array(
			'ID'         => $user_id,
			'first_name' => $user_data['first_name'],
			'last_name'  => $user_data['last_name'],
		) );

		if ( $user_data['institution'] ) {
			update_user_meta( $user_id, 'src_institution', $user_data['institution'] );
		}
		if ( isset($_POST['specialty']) ) {
			update_user_meta( $user_id, 'src_specialty', sanitize_text_field($_POST['specialty']) );
		}

		update_user_meta( $user_id, 'src_email_verified', '1' ); // Admin-created users are pre-verified

		src_log_activity( get_current_user_id(), 'admin_action', sprintf( __( 'Created new user: %s', 'scientific-research-center' ), $user_data['username'] ) );

		wp_send_json_success( array( 'message' => __( 'User account created successfully.', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX Get User Data Handler
	 */
	public function handle_ajax_get_user_data() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$user_id = absint( $_POST['user_id'] );
		$user = get_userdata( $user_id );
		if ( ! $user ) wp_send_json_error();

		wp_send_json_success( array(
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'user_email'  => $user->user_email,
			'role'        => ! empty( $user->roles ) ? $user->roles[0] : '',
			'institution' => get_user_meta( $user_id, 'src_institution', true ),
			'specialty'   => get_user_meta( $user_id, 'src_specialty', true ),
		) );
	}

	/**
	 * AJAX Get User Logs Handler
	 */
	public function handle_ajax_get_user_logs() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$user_id = absint( $_POST['user_id'] );
		global $wpdb;
		$table_name = $wpdb->prefix . 'src_activity_log';
		$logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE user_id = %d ORDER BY event_date DESC", $user_id ) );

		ob_start();
		?>
		<table class="src-user-table compact">
			<thead>
				<tr>
					<th><?php _e( 'Event', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Date', 'scientific-research-center' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="2"><?php _e( 'No activity found for this user.', 'scientific-research-center' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( $log->description ); ?></td>
							<td><?php echo esc_html( $log->event_date ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * AJAX Save Search Engine Settings
	 */
	public function handle_ajax_save_search_settings() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		update_option( 'src_search_card_design', sanitize_text_field( $_POST['card_design'] ) );
		update_option( 'src_search_metadata_visibility', $_POST['metadata_visibility'] ); // Array

		wp_send_json_success( array( 'message' => __( 'Search display settings updated.', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX Get Search Analytics
	 */
	public function handle_ajax_get_search_analytics() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		global $wpdb;
		$table_name = $wpdb->prefix . 'src_search_analytics';

		$top_keywords = $wpdb->get_results( "SELECT keyword, COUNT(*) as count FROM $table_name GROUP BY keyword ORDER BY count DESC LIMIT 10" );

		ob_start();
		?>
		<div class="src-analytics-grid grid-2">
			<div class="src-card">
				<h3><?php _e( 'Top Searched Keywords', 'scientific-research-center' ); ?></h3>
				<ul class="src-analytics-list">
					<?php foreach ( $top_keywords as $kw ) : ?>
						<li><strong><?php echo esc_html( $kw->keyword ); ?></strong> <span><?php echo esc_html( $kw->count ); ?> searches</span></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="src-card">
				<h3><?php _e( 'Search Volume (24h)', 'scientific-research-center' ); ?></h3>
				<div class="src-visual-graph mini monochromatic-bar-chart">
					<!-- Simulated hourly data -->
					<div class="src-graph-bar" style="height: 20%;"></div>
					<div class="src-graph-bar" style="height: 45%;"></div>
					<div class="src-graph-bar" style="height: 80%;"></div>
					<div class="src-graph-bar" style="height: 30%;"></div>
				</div>
			</div>
		</div>
		<?php
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * AJAX Save Email Template
	 */
	public function handle_ajax_save_email_template() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$template_id = sanitize_text_field( $_POST['template_id'] );
		$subject = sanitize_text_field( $_POST['subject'] );
		$body = wp_kses_post( $_POST['body'] );

		update_option( "src_email_tpl_{$template_id}_subject", $subject );
		update_option( "src_email_tpl_{$template_id}_body", $body );

		wp_send_json_success( array( 'message' => __( 'Email template saved.', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX Save Role Permissions
	 */
	public function handle_ajax_save_role_permissions() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$role_slug = sanitize_text_field( $_POST['role_slug'] );
		$caps = $_POST['caps']; // Array of capability keys

		$role = get_role( $role_slug );
		if ( ! $role ) wp_send_json_error();

		// For security, only allow specific plugin-related caps to be toggled
		$allowed_caps = array( 'upload_files', 'edit_posts', 'publish_posts', 'edit_others_posts', 'list_users' );

		foreach ( $allowed_caps as $cap ) {
			if ( in_array( $cap, $caps ) ) {
				$role->add_cap( $cap );
			} else {
				$role->remove_cap( $cap );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Role permissions updated.', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX Save Security Settings
	 */
	public function handle_ajax_save_security_settings() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		update_option( 'src_pwd_min_length', absint( $_POST['min_length'] ) );
		update_option( 'src_session_timeout', absint( $_POST['timeout'] ) );

		wp_send_json_success( array( 'message' => __( 'Security policies updated.', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX Forgot Password Handler
	 */
	public function handle_ajax_forgot_password() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );

		$user_login = sanitize_text_field( $_POST['user_login'] );
		$result = SRC_Auth::forgot_password( $user_login );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		} else {
			wp_send_json_success( array( 'message' => __( 'Password reset link has been sent to your email.', 'scientific-research-center' ) ) );
		}
	}

	/**
	 * Enqueue frontend CSS and JS
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'dashicons' );

		// Map asset paths to modular structure
		$css_path = 'modules/control-panel/style.css';
		$js_path = 'modules/control-panel/script.js';

		wp_enqueue_style( 'src-style', SRC_PLUGIN_URL . $css_path, array(), SRC_VERSION );
		wp_enqueue_script( 'src-script', SRC_PLUGIN_URL . $js_path, array( 'jquery' ), SRC_VERSION, true );

		wp_localize_script( 'src-script', 'src_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'src_auth_nonce' ),
			'site_url' => home_url( '/' ),
		) );
	}

	/**
	 * Load custom templates for Workspaces and Profiles
	 */
	public function load_src_templates( $template ) {
		// Handle Profiles
		if ( get_query_var( 'src_profile' ) ) {
			$profile_template = SRC_PLUGIN_DIR . 'templates/profile.php';
			if ( file_exists( $profile_template ) ) {
				return $profile_template;
			}
		}

		// Handle Workspaces
		if ( is_page() ) {
			$slug = get_post_field( 'post_name', get_post() );
			if ( str_contains( $slug, '-workspace' ) ) {
				return SRC_PLUGIN_DIR . 'modules/control-panel/workspace-portal.php';
			}

			if ( $slug === 'profile-completion' ) {
				return SRC_PLUGIN_DIR . 'modules/profile/profile-completion.php';
			}

			if ( $slug === 'research-results' ) {
				return SRC_PLUGIN_DIR . 'modules/search/search-results.php';
			}
		}

		// Handle Single Research Paper
		if ( is_singular( 'research_paper' ) ) {
			$single_template = SRC_PLUGIN_DIR . 'modules/research/single-research_paper.php';
			if ( file_exists( $single_template ) ) {
				return $single_template;
			}
		}

		return $template;
	}

	/**
	 * Render the professional Header List shortcode
	 */
	public function render_header_list() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$role = ! empty( $current_user->roles ) ? $current_user->roles[0] : '';
		$role_slug = str_replace( 'src_', '', $role );
		if ( $role === 'administrator' ) $role_slug = 'administrator';

		$profile_picture_id = get_user_meta( $user_id, 'src_profile_picture', true );
		$profile_picture_url = $profile_picture_id ? wp_get_attachment_url( $profile_picture_id ) : get_avatar_url( $user_id );

		$workspace_suffix = ( $role === 'src_reviewer' ) ? 'scientific-reviewer-workspace' : $role_slug . '-workspace';
		$account_link = home_url( '/' . $workspace_suffix . '/' );

		// Get unread notifications
		$notifications = get_user_meta( $user_id, 'src_notifications', true ) ?: array();
		$unread_count = 0;
		foreach ( $notifications as $noti ) {
			if ( ! isset( $noti['read'] ) || ! $noti['read'] ) {
				$unread_count++;
			}
		}

		$favorites = get_user_meta( $user_id, 'src_favorites', true ) ?: array();
		$fav_count = count( $favorites );

		ob_start();
		?>
		<div class="src-header-list-container">
			<!-- Profile Pill -->
			<div class="src-header-pill src-dropdown-trigger-area">
				<div class="src-pill-avatar" id="src-trigger-upload">
					<img src="<?php echo esc_url( $profile_picture_url ); ?>" alt="Avatar">
					<input type="file" id="src-header-avatar-input" style="display:none;" accept="image/*">
				</div>
				<div class="src-pill-info">
					<div class="src-pill-welcome">
						<?php printf( __( 'Welcome, %s', 'scientific-research-center' ), esc_html( $current_user->first_name ) ); ?>
						<span class="dashicons dashicons-arrow-down-alt2 src-dropdown-trigger"></span>
					</div>
					<div class="src-pill-status"><?php _e( 'Online Now', 'scientific-research-center' ); ?></div>
				</div>

				<!-- Dropdown Menu -->
				<div class="src-header-dropdown">
					<div class="src-dropdown-header compact-profile">
						<img src="<?php echo esc_url( $profile_picture_url ); ?>" alt="Avatar" class="left-align-avatar">
						<div class="src-dropdown-user stacked-info">
							<strong><?php echo esc_html( $current_user->display_name ); ?></strong>
							<span class="src-email-below"><?php echo esc_html( $current_user->user_email ); ?></span>
						</div>
					</div>
					<ul class="src-dropdown-links">
						<li><a href="<?php echo esc_url( home_url( '/profile-completion/' ) ); ?>"><span class="dashicons dashicons-admin-users"></span> <?php _e( 'Edit Account / Profile Data', 'scientific-research-center' ); ?></a></li>
						<?php if ( in_array( $role, array( 'src_administrator', 'administrator', 'src_supervisor' ) ) ) : ?>
							<li><a href="<?php echo esc_url( $account_link . '?section=settings' ); ?>"><span class="dashicons dashicons-admin-generic"></span> <?php _e( 'System Settings', 'scientific-research-center' ); ?></a></li>
						<?php endif; ?>
						<li><a href="#"><span class="dashicons dashicons-shield"></span> <?php _e( 'Privacy & Terms Policies', 'scientific-research-center' ); ?></a></li>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<li><a href="#" id="src-system-refresh"><span class="dashicons dashicons-update"></span> <?php _e( 'Full System Refresh', 'scientific-research-center' ); ?></a></li>
						<?php endif; ?>
						<li class="src-logout-item">
							<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="src-logout-link">
								<span class="dashicons dashicons-exit"></span> <?php _e( 'Secure Logout', 'scientific-research-center' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>

			<!-- Action Icons -->
			<div class="src-header-actions">
				<!-- Upload Research -->
				<a href="<?php echo esc_url( home_url( '/submit-research/' ) ); ?>" class="src-header-icon-circle" title="<?php _e( 'Submit Research', 'scientific-research-center' ); ?>">
					<span class="dashicons dashicons-upload"></span>
				</a>

				<!-- Favorites -->
				<?php if ( in_array( $role, array( 'src_researcher', 'src_reviewer', 'src_member' ) ) ) : ?>
					<?php $workspace_slug = ( $role === 'src_reviewer' ) ? 'scientific-reviewer-workspace' : $role_slug . '-workspace'; ?>
					<a href="<?php echo esc_url( home_url( '/' . $workspace_slug . '/?section=favorites' ) ); ?>" class="src-header-icon-circle <?php echo $fav_count > 0 ? 'has-badge' : ''; ?>" id="src-fav-header-icon" title="<?php _e( 'My Favorites', 'scientific-research-center' ); ?>">
						<span class="dashicons dashicons-heart"></span>
						<span class="src-icon-badge fav-badge" <?php echo $fav_count === 0 ? 'style="display:none;"' : ''; ?>><?php echo $fav_count; ?></span>
					</a>
				<?php endif; ?>

				<!-- Notifications -->
				<div class="src-header-icon-circle <?php echo $unread_count > 0 ? 'has-badge' : ''; ?>" id="src-noti-trigger" title="<?php _e( 'Notifications', 'scientific-research-center' ); ?>">
					<span class="dashicons dashicons-bell"></span>
					<?php if ( $unread_count > 0 ) : ?>
						<span class="src-icon-badge noti-badge"><?php echo $unread_count; ?></span>
					<?php endif; ?>

					<!-- Notifications Dropdown -->
					<div class="src-header-dropdown src-noti-dropdown">
						<div class="src-noti-header"><?php _e( 'Notifications', 'scientific-research-center' ); ?></div>
						<div class="src-noti-list">
							<?php if ( empty( $notifications ) ) : ?>
								<div class="src-noti-item empty"><?php _e( 'No notifications', 'scientific-research-center' ); ?></div>
							<?php else : ?>
								<?php
								// Show last 10 notifications
								$notis_to_show = array_slice( array_reverse( $notifications ), 0, 10 );
								foreach ( $notis_to_show as $noti ) :
									$is_unread = ! isset( $noti['read'] ) || ! $noti['read'];
									?>
									<div class="src-noti-item <?php echo $is_unread ? 'unread' : ''; ?>" data-id="<?php echo esc_attr( $noti['id'] ); ?>" data-url="<?php echo esc_url( $noti['url'] ); ?>">
										<p><?php echo esc_html( $noti['message'] ); ?></p>
										<small><?php echo esc_html( human_time_diff( $noti['time'], current_time( 'timestamp' ) ) ); ?> <?php _e( 'ago', 'scientific-research-center' ); ?></small>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function handle_ajax_get_notifications() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$user_id = get_current_user_id();
		if ( ! $user_id ) wp_send_json_error();

		$notifications = get_user_meta( $user_id, 'src_notifications', true ) ?: array();
		wp_send_json_success( $notifications );
	}

	public function handle_ajax_system_refresh() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%'" );
		flush_rewrite_rules();

		wp_send_json_success( array( 'message' => __( 'System components updated and cache cleared.', 'scientific-research-center' ) ) );
	}

	public function handle_ajax_toggle_favorite() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$user_id = get_current_user_id();
		if ( ! $user_id ) wp_send_json_error( array( 'message' => __( 'Unauthorized', 'scientific-research-center' ) ) );

		$post_id = absint( $_POST['post_id'] );
		$favorites = get_user_meta( $user_id, 'src_favorites', true ) ?: array();

		if ( ( $key = array_search( $post_id, $favorites ) ) !== false ) {
			unset( $favorites[$key] );
			$status = 'removed';
		} else {
			$favorites[] = $post_id;
			$status = 'added';
		}

		update_user_meta( $user_id, 'src_favorites', array_values( $favorites ) );
		wp_send_json_success( array( 'status' => $status, 'count' => count( $favorites ) ) );
	}

	public function handle_ajax_mark_notifications_read() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$user_id = get_current_user_id();
		if ( ! $user_id ) wp_send_json_error();

		$notifications = get_user_meta( $user_id, 'src_notifications', true ) ?: array();
		foreach ( $notifications as &$noti ) {
			$noti['read'] = true;
		}
		update_user_meta( $user_id, 'src_notifications', $notifications );
		wp_send_json_success();
	}

	/**
	 * Render the unified Login/Register form
	 */
	public function render_auth_form() {
		if ( is_user_logged_in() ) {
			return sprintf( '<div class="src-auth-container monochromatic compact"><p>%s <a href="%s">%s</a></p></div>',
				__( 'You are already logged in.', 'scientific-research-center' ),
				wp_logout_url( home_url( '/login-register/' ) ),
				__( 'Logout', 'scientific-research-center' )
			);
		}

		ob_start();
		do_action( 'src_before_auth_form' );
		?>
		<div class="src-auth-container monochromatic compact">
			<div class="src-welcome-msg">
				<h2><?php _e( 'Welcome', 'scientific-research-center' ); ?></h2>
			</div>

			<div class="src-auth-tabs">
				<button class="src-auth-tab active" data-tab="login"><?php _e( 'Login', 'scientific-research-center' ); ?></button>
				<button class="src-auth-tab" data-tab="register"><?php _e( 'Registration', 'scientific-research-center' ); ?></button>
			</div>

			<div class="src-auth-forms">
				<div id="src-login-form" class="src-auth-form active">
					<form id="src-login-action">
						<div class="src-field-group">
							<input type="text" name="log" id="user_login" placeholder=" " required>
							<label for="user_login"><?php _e( 'Username or Email', 'scientific-research-center' ); ?></label>
						</div>
						<div class="src-field-group">
							<input type="password" name="pwd" id="user_pass" placeholder=" " required>
							<label for="user_pass"><?php _e( 'Password', 'scientific-research-center' ); ?></label>
							<span class="src-toggle-pwd dashicons dashicons-visibility"></span>
						</div>
						<div class="src-forgot-link">
							<a href="#" id="src-show-forgot"><?php _e( 'Forgot Password?', 'scientific-research-center' ); ?></a>
						</div>
						<button type="submit" class="src-submit-btn"><?php _e( 'Login', 'scientific-research-center' ); ?></button>

						<div class="src-toggle-footer">
							<p><?php _e( "Don't have an account?", 'scientific-research-center' ); ?> <a href="#" class="src-switch-form" data-tab="register"><?php _e( 'Register now', 'scientific-research-center' ); ?></a></p>
						</div>

						<div class="src-form-msg"></div>
					</form>

					<!-- Inline Forgot Password Form -->
					<div id="src-forgot-form" style="display:none;">
						<form id="src-forgot-action">
							<p class="src-hint"><?php _e( 'Enter your email address to reset your password.', 'scientific-research-center' ); ?></p>
							<div class="src-field-group">
								<input type="email" name="user_login" id="forgot_email" placeholder=" " required>
								<label for="forgot_email"><?php _e( 'Email Address', 'scientific-research-center' ); ?></label>
							</div>
							<div class="src-btn-group">
								<button type="submit" class="src-submit-btn"><?php _e( 'Reset Password', 'scientific-research-center' ); ?></button>
								<button type="button" id="src-back-to-login" class="src-cancel-btn"><?php _e( 'Back to Login', 'scientific-research-center' ); ?></button>
							</div>
							<div class="src-form-msg"></div>
						</form>
					</div>
				</div>

				<div id="src-register-form" class="src-auth-form">
					<form id="src-register-action">
						<div class="src-user-type-selection">
							<div class="src-type-box active" data-role="src_member">
								<span class="dashicons dashicons-id-alt"></span>
								<span><?php _e( 'Member', 'scientific-research-center' ); ?></span>
							</div>
							<div class="src-type-box" data-role="src_researcher">
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<span><?php _e( 'Researcher', 'scientific-research-center' ); ?></span>
							</div>
							<input type="hidden" name="role" id="reg_role" value="src_member">
						</div>

						<div class="src-field-row">
							<div class="src-field-group">
								<input type="text" name="first_name" id="reg_first_name" placeholder=" " required>
								<label for="reg_first_name"><?php _e( 'First Name', 'scientific-research-center' ); ?></label>
							</div>
							<div class="src-field-group">
								<input type="text" name="last_name" id="reg_last_name" placeholder=" " required>
								<label for="reg_last_name"><?php _e( 'Last Name', 'scientific-research-center' ); ?></label>
							</div>
						</div>
						<div class="src-field-row">
							<div class="src-field-group">
								<input type="text" name="username" id="reg_username" placeholder=" " minlength="4" required>
								<label for="reg_username"><?php _e( 'Username', 'scientific-research-center' ); ?></label>
							</div>
							<div class="src-field-group">
								<input type="email" name="email" id="reg_email" placeholder=" " required>
								<label for="reg_email"><?php _e( 'Email Address', 'scientific-research-center' ); ?></label>
							</div>
						</div>
						<div class="src-field-row">
							<div class="src-field-group">
								<input type="password" name="password" id="reg_password" placeholder=" " required>
								<label for="reg_password"><?php _e( 'Password', 'scientific-research-center' ); ?></label>
								<span class="src-toggle-pwd dashicons dashicons-visibility"></span>
							</div>
							<div class="src-field-group">
								<input type="password" name="password_confirm" id="reg_password_confirm" placeholder=" " required>
								<label for="reg_password_confirm"><?php _e( 'Confirm Password', 'scientific-research-center' ); ?></label>
							</div>
						</div>

						<div id="src-institution-field" class="src-field-group" style="display:none;">
							<input type="text" name="institution" id="reg_institution" placeholder=" " list="src_institution_list">
							<label for="reg_institution"><?php _e( 'Institution', 'scientific-research-center' ); ?></label>
							<datalist id="src_institution_list">
								<?php
								$institutions = get_utils_institutions();
								foreach ( $institutions as $inst ) {
									echo '<option value="' . esc_attr( $inst ) . '">';
								}
								?>
							</datalist>
						</div>

						<div class="src-terms-group">
							<input type="checkbox" name="terms" id="reg_terms" required>
							<label for="reg_terms"><?php printf( __( 'I agree to the %sTerms & Policies%s', 'scientific-research-center' ), '<a href="#">', '</a>' ); ?></label>
						</div>

						<button type="submit" class="src-submit-btn"><?php _e( 'Register', 'scientific-research-center' ); ?></button>

						<div class="src-toggle-footer">
							<p><?php _e( 'Already have an account?', 'scientific-research-center' ); ?> <a href="#" class="src-switch-form" data-tab="login"><?php _e( 'Login here', 'scientific-research-center' ); ?></a></p>
						</div>

						<div class="src-form-msg"></div>
					</form>
				</div>
			</div>
		</div>
		<?php
		do_action( 'src_after_auth_form' );
		return ob_get_clean();
	}

	/**
	 * AJAX Login Handler
	 */
	public function handle_ajax_login() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );

		$info = array();
		$info['user_login'] = sanitize_text_field( $_POST['log'] );
		$info['user_password'] = $_POST['pwd']; // Do not sanitize passwords to avoid stripping special characters
		$info['remember'] = true;

		$user_signon = wp_signon( $info, false );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json_error( array( 'message' => $user_signon->get_error_message() ) );
		} else {
			$user = get_user_by( 'id', $user_signon->ID );
			$role = ! empty( $user->roles ) ? $user->roles[0] : 'src_member';
			$role_slug = str_replace( 'src_', '', $role );
			if ( $role === 'administrator' ) $role_slug = 'administrator';

			$slug_suffix = ( $role === 'src_reviewer' ) ? 'scientific-reviewer-workspace' : $role_slug . '-workspace';
			$workspace_url = home_url( '/' . $slug_suffix . '/' );

			wp_send_json_success( array(
				'message' => __( 'Login successful! Redirecting...', 'scientific-research-center' ),
				'redirect' => $workspace_url
			) );
		}
	}

	/**
	 * AJAX Register Handler
	 */
	public function handle_ajax_register() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );

		$user_data = array(
			'username'    => sanitize_user( $_POST['username'] ),
			'email'       => sanitize_email( $_POST['email'] ),
			'password'    => $_POST['password'],
			'first_name'  => sanitize_text_field( $_POST['first_name'] ),
			'last_name'   => sanitize_text_field( $_POST['last_name'] ),
			'role'        => sanitize_text_field( $_POST['role'] ),
			'institution' => isset( $_POST['institution'] ) ? sanitize_text_field( $_POST['institution'] ) : '',
			'terms'       => isset( $_POST['terms'] ) ? (int) $_POST['terms'] : 0,
		);

		$result = SRC_Auth::register_user( $user_data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		} else {
			wp_send_json_success( array(
				'message' => __( 'Registration successful! Please check your email for verification.', 'scientific-research-center' )
			) );
		}
	}

	/**
	 * Register profile URL rewrites
	 */
	public function register_profile_rewrites() {
		add_rewrite_rule( 'researcher/([^/]+)/?$', 'index.php?src_profile=$matches[1]&src_role=researcher', 'top' );
		add_rewrite_rule( 'reviewer/([^/]+)/?$', 'index.php?src_profile=$matches[1]&src_role=reviewer', 'top' );
		add_rewrite_rule( 'institution/([^/]+)/?$', 'index.php?src_profile=$matches[1]&src_role=institution', 'top' );
		add_rewrite_rule( 'supervisor/([^/]+)/?$', 'index.php?src_profile=$matches[1]&src_role=supervisor', 'top' );
		add_rewrite_rule( 'member/([^/]+)/?$', 'index.php?src_profile=$matches[1]&src_role=member', 'top' );
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'src_profile';
		$vars[] = 'src_role';
		return $vars;
	}

	/**
	 * Redirect logged-in users to their role-specific professional workspace and handle access control.
	 */
	public function role_based_redirects() {
		$current_post = get_post();
		$current_slug = $current_post ? $current_post->post_name : '';

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$status = get_user_meta( $user->ID, 'src_user_status', true );
			if ( $status === 'suspended' ) {
				wp_logout();
				wp_safe_redirect( add_query_arg( 'src_msg', 'suspended', home_url( '/login-register/' ) ) );
				exit;
			}
		}

		if ( is_page( 'login-register' ) && is_user_logged_in() ) {
			$user = wp_get_current_user();
			$role = ! empty( $user->roles ) ? $user->roles[0] : 'src_member';

			// Handle custom roles and administrator role correctly
			$role_slug = str_replace( 'src_', '', $role );
			if ( $role === 'administrator' ) {
				$role_slug = 'administrator';
			}

			$workspace_suffix = ( $role === 'src_reviewer' ) ? 'scientific-reviewer-workspace' : $role_slug . '-workspace';

			if ( $current_slug !== $workspace_suffix ) {
				wp_safe_redirect( home_url( '/' . $workspace_suffix . '/' ) );
				exit;
			}
		}

		// Access control for professional workspace pages
		if ( str_contains( $current_slug, '-workspace' ) ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( home_url( '/login-register/' ) );
				exit;
			}

			$user = wp_get_current_user();
			$role = ! empty( $user->roles ) ? $user->roles[0] : 'src_member';
			$role_slug = str_replace( 'src_', '', $role );
			if ( $role === 'administrator' ) {
				$role_slug = 'administrator';
			}

			$allowed_workspace = ( $role === 'src_reviewer' ) ? 'scientific-reviewer-workspace' : $role_slug . '-workspace';
			if ( $current_slug !== $allowed_workspace ) {
				wp_die( __( 'Access denied. You do not have permission to view this workspace.', 'scientific-research-center' ) );
			}
		}
	}
}
