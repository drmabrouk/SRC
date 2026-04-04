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

$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'dashboard';
?>

<div class="src-control-panel monochromatic full-width-layout">
	<!-- Mobile Toggle -->
	<button id="src-cp-mobile-toggle" class="src-mobile-only">
		<span class="dashicons dashicons-menu"></span>
	</button>

	<!-- Left Sidebar -->
	<aside class="src-cp-sidebar fixed-sidebar">
		<div class="src-cp-profile horizontal">
			<div class="src-cp-avatar small left" id="src-trigger-dashboard-upload">
				<img src="<?php echo esc_url( $profile_picture_url ); ?>" alt="<?php echo esc_attr( $full_name ); ?>">
				<input type="file" id="src-dashboard-avatar-input" style="display:none;" accept="image/*">
			</div>
			<div class="src-cp-user-info">
				<h4><?php echo esc_html( $full_name ); ?></h4>
				<span class="src-cp-role"><?php echo esc_html( $role_name ); ?></span>
			</div>
			<a href="<?php echo esc_url( home_url( '/profile-completion/' ) ); ?>" class="src-cp-settings-btn" title="<?php _e( 'Edit Profile', 'scientific-research-center' ); ?>">
				<div class="src-gear-circle">
					<span class="dashicons dashicons-admin-generic"></span>
				</div>
			</a>
		</div>

		<nav class="src-cp-nav">
			<ul class="src-collapsible-menu">
				<li class="src-menu-item <?php echo $current_section === 'dashboard' ? 'active' : ''; ?>">
					<a href="?section=dashboard" class="src-menu-toggle">
						<span class="dashicons dashicons-dashboard"></span> <?php _e( 'Overview', 'scientific-research-center' ); ?>
					</a>
				</li>

				<?php if ( in_array( $role, array( 'src_administrator', 'administrator', 'src_supervisor' ) ) ) : ?>
					<li class="src-menu-item <?php echo $current_section === 'users-management' ? 'active' : ''; ?>">
						<a href="?section=users-management" class="src-menu-toggle">
							<span class="dashicons dashicons-groups"></span> <?php _e( 'User Management', 'scientific-research-center' ); ?>
						</a>
					</li>
					<li class="src-menu-item <?php echo $current_section === 'submissions-management' ? 'active' : ''; ?>">
						<a href="?section=submissions-management" class="src-menu-toggle">
							<span class="dashicons dashicons-media-document"></span> <?php _e( 'Submissions', 'scientific-research-center' ); ?>
						</a>
					</li>
					<li class="src-menu-item <?php echo $current_section === 'research-engine' ? 'active' : ''; ?>">
						<a href="?section=research-engine" class="src-menu-toggle">
							<span class="dashicons dashicons-rest-api"></span> <?php _e( 'Search Engine', 'scientific-research-center' ); ?>
						</a>
					</li>
				<?php endif; ?>

				<?php if ( $role === 'src_institution' ) : ?>
					<li class="src-menu-item <?php echo $current_section === 'institution-members' ? 'active' : ''; ?>">
						<a href="?section=institution-members" class="src-menu-toggle">
							<span class="dashicons dashicons-businessperson"></span> <?php _e( 'Institution Members', 'scientific-research-center' ); ?>
						</a>
					</li>
				<?php endif; ?>

				<?php if ( in_array( $role, array( 'src_researcher', 'src_reviewer', 'src_member' ) ) ) : ?>
					<li class="src-menu-item <?php echo $current_section === 'favorites' ? 'active' : ''; ?>">
						<a href="?section=favorites" class="src-menu-toggle">
							<span class="dashicons dashicons-heart"></span> <?php _e( 'My Favorites', 'scientific-research-center' ); ?>
						</a>
					</li>
				<?php endif; ?>

				<li class="src-menu-item <?php echo $current_section === 'settings' ? 'active' : ''; ?>">
					<a href="?section=settings" class="src-menu-toggle">
						<span class="dashicons dashicons-admin-settings"></span> <?php _e( 'Settings', 'scientific-research-center' ); ?>
					</a>
				</li>
			</ul>
		</nav>
	</aside>

	<!-- Right Main Column -->
	<main class="src-cp-main tight-layout">
		<?php if ( $current_section === 'dashboard' ) : ?>
		<div id="src-cp-content-dashboard" class="src-cp-section active">
			<h1><?php printf( __( '%s Control Panel', 'scientific-research-center' ), $role_name ); ?></h1>
			<p><?php _e( 'Welcome to your professional workspace.', 'scientific-research-center' ); ?></p>

			<div class="src-dashboard-metrics">
				<?php
				$total_users = count_users()['total_users'];
				$research_counts = wp_count_posts( 'research_paper' );
				$pending_research = $research_counts->pending;
				$approved_research = $research_counts->publish;
				?>
				<div class="src-metric-card">
					<div class="src-metric-icon"><span class="dashicons dashicons-admin-users"></span></div>
					<div class="src-metric-data">
						<h3><?php echo number_format( $total_users ); ?></h3>
						<span><?php _e( 'Total Platform Users', 'scientific-research-center' ); ?></span>
					</div>
				</div>
				<div class="src-metric-card">
					<div class="src-metric-icon"><span class="dashicons dashicons-upload"></span></div>
					<div class="src-metric-data">
						<h3><?php echo number_format( $pending_research + $approved_research ); ?></h3>
						<span><?php _e( 'Research Submissions', 'scientific-research-center' ); ?></span>
					</div>
				</div>
				<div class="src-metric-card highlight">
					<div class="src-metric-icon"><span class="dashicons dashicons-yes-alt"></span></div>
					<div class="src-metric-data">
						<h3><?php echo number_format( $approved_research ); ?></h3>
						<span><?php _e( 'Approved & Published', 'scientific-research-center' ); ?></span>
					</div>
				</div>
			</div>

			<div class="src-dashboard-cards">
				<div class="src-card">
					<h3><?php _e( 'System Activity Overview', 'scientific-research-center' ); ?></h3>
					<p><?php _e( 'Your platform metrics are up-to-date.', 'scientific-research-center' ); ?></p>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( in_array( $role, array( 'src_administrator', 'administrator', 'src_supervisor' ) ) ) : ?>
			<?php if ( $current_section === 'users-management' ) : ?>
			<div id="src-cp-content-users-management" class="src-cp-section active">
				<h1><?php _e( 'System Users Management', 'scientific-research-center' ); ?></h1>

				<div class="src-search-filters-bar">
					<div class="src-search-bar compact">
						<input type="text" id="src-user-search" placeholder="<?php _e( 'Search by name, email, role...', 'scientific-research-center' ); ?>">
						<span class="dashicons dashicons-search"></span>
					</div>
					<div class="src-search-dropdowns">
						<select id="src-user-role-filter">
							<option value=""><?php _e( 'All Roles', 'scientific-research-center' ); ?></option>
							<?php
							$roles = SRC_Roles::get_roles_definition();
							foreach ( $roles as $slug => $data ) {
								echo '<option value="' . esc_attr( $slug ) . '">' . esc_html( $data['name'] ) . '</option>';
							}
							?>
						</select>
						<select id="src-user-sort">
							<option value="display_name-ASC"><?php _e( 'Name (A-Z)', 'scientific-research-center' ); ?></option>
							<option value="display_name-DESC"><?php _e( 'Name (Z-A)', 'scientific-research-center' ); ?></option>
							<option value="user_registered-DESC"><?php _e( 'Newest First', 'scientific-research-center' ); ?></option>
						</select>
					</div>
				</div>

				<div class="src-user-list-container" id="src-user-list">
					<!-- AJAX Loaded User Table -->
					<div class="src-loading-skeleton"></div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $current_section === 'submissions-management' ) : ?>
			<div id="src-cp-content-submissions-management" class="src-cp-section active">
				<h1><?php _e( 'Submissions Management', 'scientific-research-center' ); ?></h1>

				<div class="src-submission-search-container">
					<div class="src-search-bar compact">
						<input type="text" id="src-sub-search" placeholder="<?php _e( 'Search by title, author, or keywords...', 'scientific-research-center' ); ?>">
						<span class="dashicons dashicons-search"></span>
					</div>
					<div class="src-sub-filters">
						<select id="src-sub-filter-type">
							<option value=""><?php _e( 'All Types', 'scientific-research-center' ); ?></option>
							<option value="thesis"><?php _e( 'Theses', 'scientific-research-center' ); ?></option>
							<option value="paper"><?php _e( 'Papers', 'scientific-research-center' ); ?></option>
							<option value="study"><?php _e( 'Studies', 'scientific-research-center' ); ?></option>
						</select>
						<select id="src-sub-filter-status">
							<option value="pending"><?php _e( 'Pending Only', 'scientific-research-center' ); ?></option>
							<option value="publish"><?php _e( 'Approved', 'scientific-research-center' ); ?></option>
							<option value="draft"><?php _e( 'Rejected', 'scientific-research-center' ); ?></option>
							<option value="any"><?php _e( 'Any Status', 'scientific-research-center' ); ?></option>
						</select>
					</div>
				</div>

				<div class="src-user-list-container" id="src-submission-list">
					<div class="src-loading-skeleton"></div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $current_section === 'research-engine' ) : ?>
			<div id="src-cp-content-research-engine" class="src-cp-section active">
				<h1><?php _e( 'Research Engine Management', 'scientific-research-center' ); ?></h1>

				<div class="src-engine-layout grid-2">
					<div class="src-engine-controls card">
						<h3><?php _e( 'Index Control', 'scientific-research-center' ); ?></h3>
						<p><?php _e( 'Manage how research is indexed and discovered.', 'scientific-research-center' ); ?></p>
						<button class="src-submit-btn"><?php _e( 'Rebuild Search Index', 'scientific-research-center' ); ?></button>
						<button class="src-btn-outline"><?php _e( 'Clear Engine Cache', 'scientific-research-center' ); ?></button>
					</div>

					<div class="src-engine-hierarchy card" style="margin-top: 30px;">
						<h3><?php _e( 'Scientific Hierarchy Management', 'scientific-research-center' ); ?></h3>
						<p><?php _e( 'Add, edit, or remove faculties, specialties, and sub-specialties to organize the global research engine.', 'scientific-research-center' ); ?></p>

						<div class="src-hierarchy-editor">
							<div class="src-field-row">
								<div class="src-field-group">
									<select id="src-hier-type">
										<option value="src_faculty"><?php _e( 'Faculty / College', 'scientific-research-center' ); ?></option>
										<option value="src_specialty"><?php _e( 'Specialty', 'scientific-research-center' ); ?></option>
										<option value="src_sub_specialty"><?php _e( 'Sub-specialty', 'scientific-research-center' ); ?></option>
										<option value="src_institution_tax"><?php _e( 'Registered Institution', 'scientific-research-center' ); ?></option>
									</select>
									<label for="src-hier-type" class="select-label"><?php _e( 'Category Type', 'scientific-research-center' ); ?></label>
								</div>
								<div class="src-field-group">
									<input type="text" id="src-hier-name" placeholder=" ">
									<label for="src-hier-name"><?php _e( 'Name', 'scientific-research-center' ); ?></label>
								</div>
								<div class="src-field-group">
									<select id="src-hier-parent">
										<option value="0"><?php _e( 'None (Root)', 'scientific-research-center' ); ?></option>
									</select>
									<label for="src-hier-parent" class="select-label"><?php _e( 'Parent Category', 'scientific-research-center' ); ?></label>
								</div>
							</div>
							<button class="src-submit-btn" id="src-add-taxonomy-item"><?php _e( 'Add Item', 'scientific-research-center' ); ?></button>
						</div>

						<div class="src-user-list-container" style="margin-top: 30px;">
							<div class="src-loading-skeleton"></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $role === 'src_institution' ) : ?>
			<?php if ( $current_section === 'institution-members' ) : ?>
			<div id="src-cp-content-institution-members" class="src-cp-section active">
				<h1><?php _e( 'Institution Members', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Reviewers and Researchers associated with your institution.', 'scientific-research-center' ); ?></p>

				<div class="src-user-list-container">
					<!-- AJAX Loaded Institution Member Table -->
					<div class="src-loading-skeleton"></div>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( in_array( $role, array( 'src_researcher', 'src_reviewer', 'src_member' ) ) ) : ?>
			<?php if ( $current_section === 'favorites' ) : ?>
			<div id="src-cp-content-favorites" class="src-cp-section active">
				<h1><?php _e( 'My Saved Research', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Quick access to scientific contributions you have marked as favorites.', 'scientific-research-center' ); ?></p>

				<div class="src-card-grid" style="margin-top: 30px;">
					<?php
					$fav_ids = get_user_meta( get_current_user_id(), 'src_favorites', true ) ?: array();
					if ( empty( $fav_ids ) ) {
						echo '<p>' . __( 'You have not saved any research yet.', 'scientific-research-center' ) . '</p>';
					} else {
						$fav_query = new WP_Query( array(
							'post_type' => 'research_paper',
							'post__in'  => $fav_ids,
							'orderby'   => 'post__in'
						) );

						if ( $fav_query->have_posts() ) {
							while ( $fav_query->have_posts() ) {
								$fav_query->the_post();
								$post_id = get_the_ID();
								$type = strip_tags( get_the_term_list( $post_id, 'research_type', '', ', ' ) );
								?>
								<div class="src-research-card card" data-id="<?php echo $post_id; ?>">
									<div class="src-card-header">
										<span class="src-badge"><?php echo esc_html( $type ); ?></span>
										<button class="src-fav-toggle active"><span class="dashicons dashicons-heart"></span></button>
									</div>
									<h3><?php the_title(); ?></h3>
									<div class="src-card-actions">
										<a href="<?php the_permalink(); ?>" class="src-submit-btn"><?php _e( 'View Details', 'scientific-research-center' ); ?></a>
									</div>
								</div>
								<?php
							}
							wp_reset_postdata();
						}
					}
					?>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $current_section === 'settings' ) : ?>
		<div id="src-cp-content-settings" class="src-cp-section active">
			<h1><?php _e( 'Advanced Platform Settings', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Customize the appearance, typography, and functional labels of your research gateway.', 'scientific-research-center' ); ?></p>

			<div class="src-settings-layout grid-2">
				<div class="src-card">
					<h3><span class="dashicons dashicons-admin-appearance"></span> <?php _e( 'Identity & Labels', 'scientific-research-center' ); ?></h3>
					<div class="src-field-group">
						<input type="text" name="custom_label_research" id="set_label_res" placeholder=" " value="<?php echo esc_attr( get_option( 'src_label_research', 'Research' ) ); ?>">
						<label for="set_label_res"><?php _e( 'Research Field Label', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<select id="set_theme_font">
							<option value="system"><?php _e( 'System Default', 'scientific-research-center' ); ?></option>
							<option value="serif"><?php _e( 'Academic Serif', 'scientific-research-center' ); ?></option>
							<option value="mono"><?php _e( 'Monospace', 'scientific-research-center' ); ?></option>
						</select>
						<label for="set_theme_font" class="select-label"><?php _e( 'Platform Font Style', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<div class="src-card">
					<h3><span class="dashicons dashicons-art"></span> <?php _e( 'Visual Theme', 'scientific-research-center' ); ?></h3>
					<div class="src-field-group">
						<select id="set_theme_color">
							<option value="monochrome"><?php _e( 'Strict Monochrome (Standard)', 'scientific-research-center' ); ?></option>
							<option value="midnight"><?php _e( 'Midnight Scholar', 'scientific-research-center' ); ?></option>
						</select>
						<label for="set_theme_color" class="select-label"><?php _e( 'Color Palette', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<input type="text" name="custom_accent" placeholder="#000000" value="#000000">
						<label><?php _e( 'Primary Accent Color', 'scientific-research-center' ); ?></label>
					</div>
				</div>
			</div>

			<div class="src-settings-footer" style="margin-top: 30px; text-align: right;">
				<button class="src-submit-btn"><?php _e( 'Update System Preferences', 'scientific-research-center' ); ?></button>
			</div>
		</div>
		<?php endif; ?>
	</main>
</div>

<?php
get_footer();
