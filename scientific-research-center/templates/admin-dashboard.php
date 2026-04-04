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

<div class="src-control-panel monochromatic">
	<!-- Left Sidebar -->
	<aside class="src-cp-sidebar">
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

				<div class="src-user-list-container">
					<!-- AJAX Loaded User Table -->
					<div class="src-loading-skeleton"></div>
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

<style>
.src-control-panel { display: flex; min-height: 80vh; margin: 20px; background: #fff; border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
.src-cp-sidebar { width: 280px; background: #f9f9f9; border-right: 1px solid #eee; padding: 30px 0; }
.src-cp-main { flex: 1; padding: 40px; background: #fff; }

.src-cp-profile { padding: 0 25px 30px; border-bottom: 1px solid #eee; margin-bottom: 20px; position: relative; display: flex; align-items: center; gap: 15px; }
.src-cp-avatar img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #000; }
.src-cp-user-info h4 { margin: 0; font-size: 16px; font-weight: 700; color: #000; }
.src-cp-role { font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; }
.src-cp-edit-btn { position: absolute; right: 20px; top: 0; color: #999; transition: color 0.3s; }
.src-cp-edit-btn:hover { color: #000; }

.src-cp-nav ul { list-style: none; padding: 0; margin: 0; }
.src-cp-nav li { padding: 15px 25px; cursor: pointer; color: #666; font-weight: 600; transition: all 0.3s; border-left: 4px solid transparent; display: flex; align-items: center; gap: 10px; }
.src-cp-nav li:hover { background: #eee; color: #000; }
.src-cp-nav li.active { background: #eee; color: #000; border-left-color: #000; }

.src-cp-section { display: none; }
.src-cp-section.active { display: block; animation: fadeIn 0.4s ease-in-out; }

.src-search-bar { position: relative; margin-bottom: 30px; }
.src-search-bar input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ccc; border-radius: 10px; outline: none; }
.src-search-bar .dashicons-search { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }

.src-loading-skeleton { width: 100%; height: 200px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton 1.5s infinite; border-radius: 10px; }
@keyframes skeleton { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>

<?php
get_footer();
