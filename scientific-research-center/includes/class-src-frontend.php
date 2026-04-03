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
				return SRC_PLUGIN_DIR . 'templates/dashboard.php';
			}
		}

		return $template;
	}

	/**
	 * Render the unified Login/Register form
	 */
	public function render_auth_form() {
		if ( is_user_logged_in() ) {
			return sprintf( '<div class="src-auth-container monochromatic"><p>%s <a href="%s">%s</a></p></div>',
				__( 'You are already logged in.', 'scientific-research-center' ),
				wp_logout_url( home_url( '/login-register/' ) ),
				__( 'Logout', 'scientific-research-center' )
			);
		}

		ob_start();
		do_action( 'src_before_auth_form' );
		?>
		<div class="src-auth-container monochromatic">
			<div class="src-welcome-msg">
				<h2><?php _e( 'Welcome to Scientific Research Center', 'scientific-research-center' ); ?></h2>
				<p><?php _e( 'Please sign in or create an account to continue your academic journey.', 'scientific-research-center' ); ?></p>
			</div>

			<div class="src-auth-tabs">
				<button class="src-auth-tab active" data-tab="login"><?php _e( 'Login', 'scientific-research-center' ); ?></button>
				<button class="src-auth-tab" data-tab="register"><?php _e( 'Register', 'scientific-research-center' ); ?></button>
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
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Forgot Password?', 'scientific-research-center' ); ?></a>
						</div>
						<button type="submit" class="src-submit-btn"><?php _e( 'Login', 'scientific-research-center' ); ?></button>

						<div class="src-toggle-footer">
							<p><?php _e( "Don't have an account?", 'scientific-research-center' ); ?> <a href="#" class="src-switch-form" data-tab="register"><?php _e( 'Register now', 'scientific-research-center' ); ?></a></p>
						</div>

						<div class="src-form-msg"></div>
					</form>
				</div>

				<div id="src-register-form" class="src-auth-form">
					<form id="src-register-action">
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
						<div class="src-field-group">
							<input type="text" name="username" id="reg_username" placeholder=" " minlength="4" required>
							<label for="reg_username"><?php _e( 'Username (Min 4 chars)', 'scientific-research-center' ); ?></label>
						</div>
						<div class="src-field-group">
							<input type="email" name="email" id="reg_email" placeholder=" " required>
							<label for="reg_email"><?php _e( 'Email Address', 'scientific-research-center' ); ?></label>
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
						<div class="src-field-group">
							<select name="role" id="reg_role" required>
								<option value="src_member"><?php _e( 'Member', 'scientific-research-center' ); ?></option>
								<option value="src_researcher"><?php _e( 'Researcher', 'scientific-research-center' ); ?></option>
							</select>
							<label for="reg_role" class="select-label"><?php _e( 'I am a...', 'scientific-research-center' ); ?></label>
						</div>

						<div id="src-institution-field" class="src-field-group" style="display:none;">
							<input type="text" name="institution" id="reg_institution" placeholder=" " list="src_institution_list">
							<label for="reg_institution"><?php _e( 'Institution', 'scientific-research-center' ); ?></label>
							<datalist id="src_institution_list">
								<!-- Options populated via JS or hardcoded -->
								<option value="Healthedia Research Center">
								<option value="Global Science Institute">
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
			wp_send_json_success( array(
				'message' => __( 'Login successful! Redirecting...', 'scientific-research-center' ),
				'redirect' => home_url( '/login-register/' )
			) );
		}
	}

	/**
	 * AJAX Register Handler
	 */
	public function handle_ajax_register() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );

		$user_data = array(
			'username' => sanitize_user( $_POST['username'] ),
			'email'    => sanitize_email( $_POST['email'] ),
			'password' => $_POST['password'],
			'role'     => sanitize_text_field( $_POST['role'] ),
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
