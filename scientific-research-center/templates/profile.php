<?php
/**
 * User Profile Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$username = get_query_var( 'src_profile' );
$role = get_query_var( 'src_role' );

$user = get_user_by( 'slug', $username );

if ( ! $user ) {
	echo '<div class="src-profile-not-found">' . __( 'User not found.', 'scientific-research-center' ) . '</div>';
} else {
	$display_name = $user->display_name;
	$role_display = ucfirst( $role );
	?>
	<div class="src-profile-container monochromatic">
		<header class="src-profile-header">
			<h1><?php echo esc_html( $display_name ); ?></h1>
			<p class="src-profile-role"><?php printf( __( 'Official %s Profile', 'scientific-research-center' ), $role_display ); ?></p>
		</header>

		<div class="src-profile-content card">
			<div class="src-profile-info">
				<h3><?php _e( 'Researcher Information', 'scientific-research-center' ); ?></h3>
				<p><strong><?php _e( 'Username:', 'scientific-research-center' ); ?></strong> <?php echo esc_html( $user->user_login ); ?></p>
				<p><strong><?php _e( 'Member Since:', 'scientific-research-center' ); ?></strong> <?php echo date( 'F Y', strtotime( $user->user_registered ) ); ?></p>
				<p><strong><?php _e( 'Expertise:', 'scientific-research-center' ); ?></strong> <?php _e( 'Scientific Research', 'scientific-research-center' ); ?></p>
			</div>
		</div>
	</div>

	<style>
	.src-profile-container { max-width: 800px; margin: 50px auto; padding: 20px; font-family: sans-serif; text-align: left; }
	.src-profile-header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 20px; }
	.src-profile-role { font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
	.src-profile-content.card { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 12px; }
	.src-profile-info h3 { margin-bottom: 15px; border-left: 4px solid #000; padding-left: 10px; font-size: 18px; }
	.src-profile-info p { margin-bottom: 10px; color: #333; }
	.src-profile-info strong { color: #000; width: 140px; display: inline-block; }
	</style>
	<?php
}

get_footer();
