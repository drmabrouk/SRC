<?php
/**
 * SRC_Emails Class
 * Handles email verification tokens and sending professional monochromatic emails.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Emails {

	public function __construct() {
		// Initialization
	}

	/**
	 * Send verification email
	 */
	public static function send_verification_email( $user_id ) {
		$user = get_userdata( $user_id );
		$token = self::generate_verification_token( $user_id );
		$verification_url = add_query_arg( array(
			'src_action' => 'verify_email',
			'token'      => $token,
			'user_id'    => $user_id,
		), home_url( '/login-register/' ) );

		$subject = __( 'Verify Your Account - Scientific Research Center', 'scientific-research-center' );
		$message = self::get_email_template(
			__( 'Welcome to Scientific Research Center', 'scientific-research-center' ),
			sprintf( __( 'Please confirm your email to complete your registration as a %s. After confirmation, you will be guided to complete your official researcher profile.', 'scientific-research-center' ), self::get_role_name( $user ) ),
			$verification_url,
			__( 'Confirm Registration', 'scientific-research-center' )
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $user->user_email, $subject, $message, $headers );
	}

	/**
	 * Generate and store a verification token
	 */
	public static function generate_verification_token( $user_id ) {
		$token = bin2hex( random_bytes( 32 ) );
		update_user_meta( $user_id, 'src_email_verification_token', $token );
		return $token;
	}

	/**
	 * Get monochromatic email template
	 */
	private static function get_email_template( $title, $body, $btn_url = '', $btn_text = '' ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<style>
				body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px; }
				.container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; border: 1px solid #e0e0e0; }
				h1 { color: #000000; font-size: 24px; margin-bottom: 20px; text-align: center; }
				p { color: #333333; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
				.button { display: inline-block; padding: 15px 30px; background-color: #000000; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: bold; text-align: center; width: 100%; box-sizing: border-box; }
				.footer { margin-top: 40px; text-align: center; color: #999999; font-size: 12px; }
			</style>
		</head>
		<body>
			<div class="container">
				<h1><?php echo esc_html( $title ); ?></h1>
				<p><?php echo wp_kses_post( $body ); ?></p>
				<?php if ( $btn_url && $btn_text ) : ?>
					<a href="<?php echo esc_url( $btn_url ); ?>" class="button"><?php echo esc_html( $btn_text ); ?></a>
				<?php endif; ?>
				<div class="footer">
					&copy; <?php echo date( 'Y' ); ?> Scientific Research Center. All rights reserved.<br>
					Support: support@healthedia.org
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get readable role name
	 */
	private static function get_role_name( $user ) {
		$roles = $user->roles;
		if ( empty( $roles ) ) return 'Member';

		$role_definitions = SRC_Roles::get_roles_definition();
		$primary_role = $roles[0];

		return isset( $role_definitions[ $primary_role ] ) ? $role_definitions[ $primary_role ]['name'] : 'Member';
	}
}
