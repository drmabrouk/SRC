<?php
/**
 * Role Dashboard Template Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$current_user = wp_get_current_user();

// Determine role name from current page slug or user
$slug = get_post_field( 'post_name', get_post() );
$role_slug = str_replace( '-dashboard', '', $slug );
$role_name = ucfirst( $role_slug );

// Map special role slugs if necessary
$role_mapping = array(
	'src_administrator' => 'Administrator',
	'src_supervisor'    => 'Supervisor',
	'src_institution'   => 'Institution',
	'src_reviewer'      => 'Reviewer',
	'src_researcher'    => 'Researcher',
	'src_member'        => 'Member',
	'administrator'     => 'Administrator',
);

// If the slug matches a known role, use the mapped name
foreach ( $role_mapping as $s => $n ) {
	if ( str_contains( $s, $role_slug ) ) {
		$role_name = $n;
		break;
	}
}
?>

<div class="src-dashboard-container monochromatic">
	<header class="src-dashboard-header">
		<h1><?php printf( __( '%s Dashboard', 'scientific-research-center' ), $role_name ); ?></h1>
		<p><?php printf( __( 'Welcome back, %s!', 'scientific-research-center' ), $current_user->display_name ); ?></p>
	</header>

	<div class="src-dashboard-content card">
		<?php do_action( 'src_dashboard_before_content', $role_slug, $current_user ); ?>

		<h3><?php _e( 'Your Activity', 'scientific-research-center' ); ?></h3>
		<p><?php printf( __( 'This is your personalized area as a %s.', 'scientific-research-center' ), $role_name ); ?></p>

		<?php do_action( 'src_dashboard_content_' . $role_slug, $current_user ); ?>

		<div class="src-dashboard-stats">
			<div class="src-stat-item">
				<strong><?php _e( 'Account Status', 'scientific-research-center' ); ?></strong>
				<span><?php _e( 'Verified', 'scientific-research-center' ); ?></span>
			</div>
			<div class="src-stat-item">
				<strong><?php _e( 'Role', 'scientific-research-center' ); ?></strong>
				<span><?php echo esc_html( $role_name ); ?></span>
			</div>
		</div>
	</div>
</div>

<style>
.src-dashboard-container { max-width: 900px; margin: 40px auto; padding: 20px; font-family: sans-serif; }
.src-dashboard-header { margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
.src-dashboard-content.card { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 12px; }
.src-dashboard-stats { display: flex; gap: 20px; margin-top: 20px; }
.src-stat-item { flex: 1; padding: 15px; background: #f9f9f9; border-radius: 8px; border: 1px solid #eee; text-align: center; }
.src-stat-item strong { display: block; font-size: 12px; color: #666; text-transform: uppercase; }
.src-stat-item span { font-size: 18px; font-weight: bold; color: #000; }
</style>

<?php
get_footer();
