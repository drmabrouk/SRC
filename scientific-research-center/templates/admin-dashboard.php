<?php
/**
 * Unified Control Panel Template
 * Independent interface for all roles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$current_user = wp_get_current_user();
$role = ! empty( $current_user->roles ) ? $current_user->roles[0] : '';
$profile_picture_id = get_user_meta( $current_user->ID, 'src_profile_picture', true );
$profile_picture_url = $profile_picture_id ? wp_get_attachment_url( $profile_picture_id ) : get_avatar_url( $current_user->ID );
$full_name = trim( $current_user->first_name . ' ' . $current_user->last_name );
if ( empty( $full_name ) ) $full_name = $current_user->display_name;

$role_definitions = SRC_Roles::get_roles_definition();
$role_name = isset( $role_definitions[ $role ] ) ? $role_definitions[ $role ]['name'] : ucfirst( str_replace( 'src_', '', $role ) );
?>

<div class="src-control-panel monochromatic full-width-layout">
	<!-- Mobile Toggle -->
	<button id="src-cp-mobile-toggle" class="src-mobile-only">
		<span class="dashicons dashicons-menu"></span>
	</button>

	<!-- Left Sidebar -->
	<aside class="src-cp-sidebar fixed-sidebar">
		<div class="src-cp-profile">
			<div class="src-cp-avatar">
				<img src="<?php echo esc_url( $profile_picture_url ); ?>" alt="<?php echo esc_attr( $full_name ); ?>">
			</div>
			<div class="src-cp-user-info">
				<h4><?php echo esc_html( $full_name ); ?></h4>
				<span class="src-cp-role"><?php echo esc_html( $role_name ); ?></span>
			</div>
			<a href="<?php echo esc_url( home_url( '/profile-completion/' ) ); ?>" class="src-cp-edit-btn" title="<?php _e( 'Edit Profile', 'scientific-research-center' ); ?>">
				<span class="dashicons dashicons-edit"></span>
			</a>
		</div>

		<nav class="src-cp-nav">
			<ul>
				<li class="active" data-section="dashboard">
					<span class="dashicons dashicons-dashboard"></span> <?php _e( 'Dashboard', 'scientific-research-center' ); ?>
				</li>

				<?php if ( in_array( $role, array( 'src_administrator', 'administrator', 'src_supervisor' ) ) ) : ?>
					<li data-section="users-management">
						<span class="dashicons dashicons-groups"></span> <?php _e( 'System Users', 'scientific-research-center' ); ?>
					</li>
					<li data-section="submissions-management">
						<span class="dashicons dashicons-media-document"></span> <?php _e( 'Submissions', 'scientific-research-center' ); ?>
					</li>
					<li data-section="research-engine">
						<span class="dashicons dashicons-rest-api"></span> <?php _e( 'Research Engine', 'scientific-research-center' ); ?>
					</li>
				<?php endif; ?>

				<?php if ( $role === 'src_institution' ) : ?>
					<li data-section="institution-members">
						<span class="dashicons dashicons-businessperson"></span> <?php _e( 'Institution Members', 'scientific-research-center' ); ?>
					</li>
				<?php endif; ?>

				<li data-section="settings">
					<span class="dashicons dashicons-admin-settings"></span> <?php _e( 'Settings', 'scientific-research-center' ); ?>
				</li>
			</ul>
		</nav>
	</aside>

	<!-- Right Main Column -->
	<main class="src-cp-main">
		<div id="src-cp-content-dashboard" class="src-cp-section active">
			<h1><?php printf( __( '%s Control Panel', 'scientific-research-center' ), $role_name ); ?></h1>
			<p><?php _e( 'Welcome to your professional workspace.', 'scientific-research-center' ); ?></p>

			<div class="src-dashboard-cards">
				<div class="src-card">
					<h3><?php _e( 'Recent Activity', 'scientific-research-center' ); ?></h3>
					<p><?php _e( 'No recent activity found.', 'scientific-research-center' ); ?></p>
				</div>
			</div>
		</div>

		<?php if ( in_array( $role, array( 'src_administrator', 'administrator', 'src_supervisor' ) ) ) : ?>
			<div id="src-cp-content-users-management" class="src-cp-section">
				<h1><?php _e( 'System Users Management', 'scientific-research-center' ); ?></h1>

				<div class="src-search-bar">
					<input type="text" id="src-user-search" placeholder="<?php _e( 'Search by name, email, role...', 'scientific-research-center' ); ?>">
					<span class="dashicons dashicons-search"></span>
				</div>

				<div class="src-user-list-container" id="src-user-list">
					<!-- AJAX Loaded User Table -->
					<div class="src-loading-skeleton"></div>
				</div>
			</div>

			<div id="src-cp-content-submissions-management" class="src-cp-section">
				<h1><?php _e( 'Submissions Management', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Review and moderate pending scientific research submissions.', 'scientific-research-center' ); ?></p>
				<div class="src-user-list-container" id="src-submission-list">
					<div class="src-loading-skeleton"></div>
				</div>
			</div>

			<div id="src-cp-content-research-engine" class="src-cp-section">
				<h1><?php _e( 'Research Engine Management', 'scientific-research-center' ); ?></h1>
				<div class="src-engine-controls card">
					<h3><?php _e( 'Index Control', 'scientific-research-center' ); ?></h3>
					<p><?php _e( 'Manage how research is indexed and discovered.', 'scientific-research-center' ); ?></p>
					<button class="src-submit-btn"><?php _e( 'Rebuild Search Index', 'scientific-research-center' ); ?></button>
					<button class="src-btn-outline"><?php _e( 'Clear Engine Cache', 'scientific-research-center' ); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $role === 'src_institution' ) : ?>
			<div id="src-cp-content-institution-members" class="src-cp-section">
				<h1><?php _e( 'Institution Members', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Reviewers and Researchers associated with your institution.', 'scientific-research-center' ); ?></p>

				<div class="src-user-list-container">
					<!-- AJAX Loaded Institution Member Table -->
					<div class="src-loading-skeleton"></div>
				</div>
			</div>
		<?php endif; ?>

		<div id="src-cp-content-settings" class="src-cp-section">
			<h1><?php _e( 'Account Settings', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Manage your preferences and security settings.', 'scientific-research-center' ); ?></p>
		</div>
	</main>
</div>

<?php
get_footer();
