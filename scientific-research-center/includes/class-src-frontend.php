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

		$meta_fields = array( 'country', 'mobile', 'alt_email', 'gender', 'academic_degree' );
		foreach ( $meta_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_user_meta( $user_id, 'src_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
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
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) && ! current_user_can( 'src_institution' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$filter_role = isset( $_POST['role_filter'] ) ? sanitize_text_field( $_POST['role_filter'] ) : '';
		$institution_filter = isset( $_POST['institution_filter'] ) ? sanitize_text_field( $_POST['institution_filter'] ) : '';

		$args = array(
			'search'         => $search ? '*' . $search . '*' : '',
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
		);

		if ( $filter_role ) {
			$args['role'] = $filter_role;
		}

		if ( $institution_filter ) {
			if ( $institution_filter === 'current' ) {
				$institution_filter = get_user_meta( get_current_user_id(), 'src_institution', true );
			}
			$args['meta_key'] = 'src_institution';
			$args['meta_value'] = $institution_filter;
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
							<td class="src-actions">
								<button class="src-icon-btn src-user-act" data-action="edit" data-id="<?php echo $user->ID; ?>" title="Edit"><span class="dashicons dashicons-edit"></span></button>
								<?php if ( $status === 'active' ) : ?>
									<button class="src-icon-btn src-user-act" data-action="suspend" data-id="<?php echo $user->ID; ?>" title="Suspend"><span class="dashicons dashicons-lock"></span></button>
								<?php else : ?>
									<button class="src-icon-btn src-user-act" data-action="reactivate" data-id="<?php echo $user->ID; ?>" title="Reactivate"><span class="dashicons dashicons-unlock"></span></button>
								<?php endif; ?>
								<button class="src-icon-btn src-user-act src-danger" data-action="delete" data-id="<?php echo $user->ID; ?>" title="Delete"><span class="dashicons dashicons-trash"></span></button>
								<button class="src-icon-btn src-user-act" data-action="notify" data-id="<?php echo $user->ID; ?>" title="Notify"><span class="dashicons dashicons-email"></span></button>
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
				wp_send_json_success( array( 'message' => __( 'User suspended.', 'scientific-research-center' ) ) );
				break;
			case 'reactivate':
				update_user_meta( $user_id, 'src_user_status', 'active' );
				wp_send_json_success( array( 'message' => __( 'User reactivated.', 'scientific-research-center' ) ) );
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
		}
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
		wp_enqueue_style( 'src-style', SRC_PLUGIN_URL . 'assets/css/src-style.css', array(), SRC_VERSION );
		wp_enqueue_script( 'src-script', SRC_PLUGIN_URL . 'assets/js/src-script.js', array( 'jquery' ), SRC_VERSION, true );
		wp_localize_script( 'src-script', 'src_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'src_auth_nonce' ),
		) );
	}

	/**
	 * Load custom templates for Dashboards and Profiles
	 */
	public function load_src_templates( $template ) {
		// Handle Profiles
		if ( get_query_var( 'src_profile' ) ) {
			$profile_template = SRC_PLUGIN_DIR . 'templates/profile.php';
			if ( file_exists( $profile_template ) ) {
				return $profile_template;
			}
		}

		// Handle Dashboards
		if ( is_page() ) {
			$slug = get_post_field( 'post_name', get_post() );
			if ( str_contains( $slug, '-dashboard' ) ) {
				return SRC_PLUGIN_DIR . 'templates/admin-dashboard.php';
			}

			if ( $slug === 'profile-completion' ) {
				return SRC_PLUGIN_DIR . 'templates/profile-completion.php';
			}
		}

		return $template;
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
		$info['user_password'] = sanitize_text_field( $_POST['pwd'] );
		$info['remember'] = true;

		$user_signon = wp_signon( $info, false );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json_error( array( 'message' => $user_signon->get_error_message() ) );
		} else {
			$user = get_user_by( 'id', $user_signon->ID );
			$role = ! empty( $user->roles ) ? $user->roles[0] : 'src_member';
			$role_slug = str_replace( 'src_', '', $role );
			if ( $role === 'administrator' ) $role_slug = 'administrator';
			$dashboard_url = home_url( '/' . $role_slug . '-dashboard/' );

			wp_send_json_success( array(
				'message' => __( 'Login successful! Redirecting...', 'scientific-research-center' ),
				'redirect' => $dashboard_url
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
	 * Redirect logged-in users to their role-specific dashboard and handle access control.
	 */
	public function role_based_redirects() {
		$current_post = get_post();
		$current_slug = $current_post ? $current_post->post_name : '';

		if ( is_page( 'login-register' ) && is_user_logged_in() ) {
			$user = wp_get_current_user();
			$role = ! empty( $user->roles ) ? $user->roles[0] : 'src_member';

			// Handle custom roles and administrator role correctly
			$role_slug = str_replace( 'src_', '', $role );
			if ( $role === 'administrator' ) {
				$role_slug = 'administrator';
			}

			$dashboard_slug = $role_slug . '-dashboard';

			if ( $current_slug !== $dashboard_slug ) {
				wp_safe_redirect( home_url( '/' . $dashboard_slug . '/' ) );
				exit;
			}
		}

		// Access control for dashboard pages
		if ( str_contains( $current_slug, '-dashboard' ) ) {
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

			$allowed_dashboard = $role_slug . '-dashboard';
			if ( $current_slug !== $allowed_dashboard ) {
				wp_die( __( 'Access denied. You do not have permission to view this dashboard.', 'scientific-research-center' ) );
			}
		}
	}
}
