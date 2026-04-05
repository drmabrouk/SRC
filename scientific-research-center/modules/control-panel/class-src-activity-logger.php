<?php
/**
 * SRC_Activity_Logger Class
 * Hooks into WordPress events to record platform activity.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Activity_Logger {

	public function __construct() {
		add_action( 'wp_login', array( $this, 'log_login' ), 10, 2 );
		add_action( 'src_after_user_registration', array( $this, 'log_registration' ), 10, 2 );
		add_action( 'wp_insert_post', array( $this, 'log_new_submission' ), 10, 3 );
		add_action( 'transition_post_status', array( $this, 'log_status_transition' ), 10, 3 );
	}

	public function log_login( $user_login, $user ) {
		src_log_activity( $user->ID, 'login', sprintf( __( 'User %s logged in.', 'scientific-research-center' ), $user->display_name ) );
	}

	public function log_registration( $user_id, $user_data ) {
		src_log_activity( $user_id, 'registration', __( 'New user account registered.', 'scientific-research-center' ) );
	}

	public function log_new_submission( $post_id, $post, $update ) {
		if ( $update || $post->post_type !== 'research_paper' ) {
			return;
		}
		src_log_activity( $post->post_author, 'submission', sprintf( __( 'Submitted new research: %s', 'scientific-research-center' ), $post->post_title ) );
	}

	public function log_status_transition( $new_status, $old_status, $post ) {
		if ( $post->post_type !== 'research_paper' ) {
			return;
		}

		if ( $old_status === 'pending' && $new_status === 'publish' ) {
			src_log_activity( get_current_user_id(), 'approval', sprintf( __( 'Approved research: %s', 'scientific-research-center' ), $post->post_title ) );
		} elseif ( $old_status === 'pending' && $new_status === 'draft' ) {
			src_log_activity( get_current_user_id(), 'rejection', sprintf( __( 'Rejected research: %s', 'scientific-research-center' ), $post->post_title ) );
		}
	}
}
