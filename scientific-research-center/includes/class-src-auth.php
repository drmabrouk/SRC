<?php
/**
 * SRC_Auth Class
 * Manages user registration, login, and email verification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Auth {

	public function __construct() {
		add_action( 'init', array( $this, 'handle_verification' ) );
		add_filter( 'authenticate', array( $this, 'check_verification_on_login' ), 30, 3 );
	}

	/**
	 * Register a new user
	 */
	public static function register_user( $user_data ) {
		$username = $user_data['username'];
		$email    = $user_data['email'];
		$password = $user_data['password'];
		$role     = ! empty( $user_data['role'] ) ? $user_data['role'] : 'src_member';

		// Basic validation
		if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
			return new WP_Error( 'src_missing_fields', __( 'Please fill in all required fields.', 'scientific-research-center' ) );
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'src_invalid_email', __( 'Invalid email address.', 'scientific-research-center' ) );
		}

		if ( username_exists( $username ) ) {
			return new WP_Error( 'src_username_taken', __( 'Username already exists.', 'scientific-research-center' ) );
		}

		if ( email_exists( $email ) ) {
			return new WP_Error( 'src_email_taken', __( 'Email already exists.', 'scientific-research-center' ) );
		}

		// Role Whitelisting (Security)
		$allowed_roles = apply_filters( 'src_allowed_registration_roles', array(
			'src_researcher',
			'src_member'
		) );

		if ( ! in_array( $role, $allowed_roles ) ) {
			return new WP_Error( 'src_invalid_role', __( 'Invalid role selection.', 'scientific-research-center' ) );
		}

		// Create user
		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Set role
		$user = new WP_User( $user_id );
		$user->set_role( $role );

		// Update First and Last Name
		wp_update_user( array(
			'ID'         => $user_id,
			'first_name' => sanitize_text_field( $user_data['first_name'] ),
			'last_name'  => sanitize_text_field( $user_data['last_name'] ),
		) );

		// Save Institution meta
		if ( isset( $user_data['institution'] ) ) {
			update_user_meta( $user_id, 'src_institution', sanitize_text_field( $user_data['institution'] ) );
		}

		// Mark as unverified
		update_user_meta( $user_id, 'src_email_verified', '0' );

		// Trigger Hook for After Registration
		do_action( 'src_after_user_registration', $user_id, $user_data );

		// Send verification email
		SRC_Emails::send_verification_email( $user_id );

		return $user_id;
	}

	/**
	 * Handle email verification via token link
	 */
	public function handle_verification() {
		if ( isset( $_GET['src_action'] ) && $_GET['src_action'] === 'verify_email' && isset( $_GET['token'] ) && isset( $_GET['user_id'] ) ) {
			$token   = sanitize_text_field( $_GET['token'] );
			$user_id = absint( $_GET['user_id'] );

			$stored_token = get_user_meta( $user_id, 'src_email_verification_token', true );

			if ( $token === $stored_token ) {
				update_user_meta( $user_id, 'src_email_verified', '1' );
				delete_user_meta( $user_id, 'src_email_verification_token' );

				// Trigger Hook for After Email Verification
				do_action( 'src_user_email_verified', $user_id );

				// Redirect to login page with success message
				wp_safe_redirect( apply_filters( 'src_verification_redirect_url', add_query_arg( 'src_msg', 'verified', home_url( '/login-register/' ) ), $user_id ) );
				exit;
			} else {
				wp_die( __( 'Invalid or expired verification token.', 'scientific-research-center' ) );
			}
		}
	}

	/**
	 * Prevent login if email is not verified
	 */
	public function check_verification_on_login( $user, $username, $password ) {
		if ( is_wp_error( $user ) || empty( $user ) ) {
			return $user;
		}

		$is_verified = get_user_meta( $user->ID, 'src_email_verified', true );

		if ( $is_verified === '0' ) {
			return new WP_Error( 'src_not_verified', __( 'Your account is not verified. Please check your email.', 'scientific-research-center' ) );
		}

		return $user;
	}

	/**
	 * Handle user login
	 */
	public static function login_user( $creds ) {
		$user = wp_signon( $creds, false );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		return $user;
	}
}
