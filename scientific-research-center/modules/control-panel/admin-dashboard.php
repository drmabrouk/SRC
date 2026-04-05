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
				$active_inst = count( get_utils_institutions() );
				?>
				<div class="src-metric-card">
					<div class="src-metric-icon"><span class="dashicons dashicons-admin-users"></span></div>
					<div class="src-metric-data">
						<h3><?php echo number_format( $total_users ); ?></h3>
						<span><?php _e( 'Total Platform Users', 'scientific-research-center' ); ?></span>
					</div>
				</div>
				<div class="src-metric-card">
					<div class="src-metric-icon"><span class="dashicons dashicons-bank"></span></div>
					<div class="src-metric-data">
						<h3><?php echo number_format( $active_inst ); ?></h3>
						<span><?php _e( 'Active Institutions', 'scientific-research-center' ); ?></span>
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

			<div class="src-overview-layout grid-2">
				<div class="src-overview-left">
					<div class="src-card src-quick-access-card">
						<h3><span class="dashicons dashicons-external"></span> <?php _e( 'Quick Access', 'scientific-research-center' ); ?></h3>
						<div class="src-quick-grid">
							<a href="?section=submissions-management" class="src-quick-btn">
								<span class="dashicons dashicons-media-document"></span>
								<p><?php _e( 'Submissions', 'scientific-research-center' ); ?></p>
							</a>
							<a href="?section=research-engine" class="src-quick-btn">
								<span class="dashicons dashicons-rest-api"></span>
								<p><?php _e( 'Search Engine', 'scientific-research-center' ); ?></p>
							</a>
							<a href="?section=users-management" class="src-quick-btn">
								<span class="dashicons dashicons-groups"></span>
								<p><?php _e( 'Users', 'scientific-research-center' ); ?></p>
							</a>
							<a href="<?php echo home_url( '/submit-research/' ); ?>" class="src-quick-btn">
								<span class="dashicons dashicons-upload"></span>
								<p><?php _e( 'Upload', 'scientific-research-center' ); ?></p>
							</a>
						</div>
					</div>

					<div class="src-card src-graph-card">
						<h3><span class="dashicons dashicons-chart-line"></span> <?php _e( 'Research Trends', 'scientific-research-center' ); ?></h3>
						<div class="src-visual-graph monochromatic-bar-chart">
							<div class="src-graph-bar" style="height: 40%;" data-label="Mon"></div>
							<div class="src-graph-bar" style="height: 65%;" data-label="Tue"></div>
							<div class="src-graph-bar" style="height: 50%;" data-label="Wed"></div>
							<div class="src-graph-bar" style="height: 90%;" data-label="Thu"></div>
							<div class="src-graph-bar" style="height: 75%;" data-label="Fri"></div>
							<div class="src-graph-bar" style="height: 30%;" data-label="Sat"></div>
							<div class="src-graph-bar" style="height: 20%;" data-label="Sun"></div>
						</div>
						<p class="src-graph-caption"><?php _e( 'Daily publication counts (Weekly overview)', 'scientific-research-center' ); ?></p>
					</div>
				</div>

				<div class="src-overview-right">
					<?php if ( $pending_research > 0 ) : ?>
						<div class="src-alert-box alert-warning">
							<span class="dashicons dashicons-warning"></span>
							<div class="src-alert-content">
								<strong><?php printf( __( '%d Research Submissions Pending', 'scientific-research-center' ), $pending_research ); ?></strong>
								<p><?php _e( 'Please review pending submissions for approval.', 'scientific-research-center' ); ?></p>
							</div>
							<a href="?section=submissions-management" class="src-link-btn"><?php _e( 'Review', 'scientific-research-center' ); ?></a>
						</div>
					<?php endif; ?>

					<div class="src-card src-activity-card">
						<h3><span class="dashicons dashicons-list-view"></span> <?php _e( 'Recent Activity', 'scientific-research-center' ); ?></h3>
						<div class="src-activity-feed">
							<?php
							global $wpdb;
							$table_name = $wpdb->prefix . 'src_activity_log';
							$logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY event_date DESC LIMIT 5" );

							if ( empty( $logs ) ) : ?>
								<p class="src-empty-msg"><?php _e( 'No recent activity recorded.', 'scientific-research-center' ); ?></p>
							<?php else : ?>
								<?php foreach ( $logs as $log ) : ?>
									<div class="src-activity-item">
										<div class="src-activity-icon type-<?php echo esc_attr( $log->event_type ); ?>"></div>
										<div class="src-activity-details">
											<p><?php echo esc_html( $log->description ); ?></p>
											<span><?php echo esc_html( human_time_diff( strtotime( $log->event_date ), current_time( 'timestamp' ) ) ); ?> <?php _e( 'ago', 'scientific-research-center' ); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( in_array( $role, array( 'src_administrator', 'administrator', 'src_supervisor' ) ) ) : ?>
			<?php if ( $current_section === 'users-management' ) : ?>
			<div id="src-cp-content-users-management" class="src-cp-section active">
				<h1><?php _e( 'System Users Management', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Manage, filter, and monitor all platform users and their respective roles.', 'scientific-research-center' ); ?></p>

				<div class="src-search-filters-bar user-management-filters">
					<div class="src-filters-row main-row">
						<div class="src-search-bar compact">
							<input type="text" id="src-user-search" placeholder="<?php _e( 'Search users...', 'scientific-research-center' ); ?>">
							<span class="dashicons dashicons-search"></span>
						</div>
						<div class="src-action-buttons">
							<button class="src-submit-btn src-add-user-trigger"><span class="dashicons dashicons-plus"></span> <?php _e( 'Add New User', 'scientific-research-center' ); ?></button>
							<button class="src-btn-outline" id="src-export-users-trigger"><span class="dashicons dashicons-download"></span> <?php _e( 'Export JSON', 'scientific-research-center' ); ?></button>
							<div class="src-upload-btn-wrapper">
								<button class="src-btn-outline"><span class="dashicons dashicons-upload"></span> <?php _e( 'Import JSON', 'scientific-research-center' ); ?></button>
								<input type="file" id="src-import-users-input" accept=".json">
							</div>
						</div>
					</div>

					<div class="src-filters-row secondary-row">
						<select id="src-user-role-filter">
							<option value=""><?php _e( 'All Roles', 'scientific-research-center' ); ?></option>
							<?php
							$roles = SRC_Roles::get_roles_definition();
							foreach ( $roles as $slug => $data ) {
								echo '<option value="' . esc_attr( $slug ) . '">' . esc_html( $data['name'] ) . '</option>';
							}
							?>
						</select>
						<select id="src-user-inst-filter">
							<option value=""><?php _e( 'All Institutions', 'scientific-research-center' ); ?></option>
							<?php
							foreach ( get_utils_institutions() as $inst ) {
								echo '<option value="' . esc_attr( $inst ) . '">' . esc_html( $inst ) . '</option>';
							}
							?>
						</select>
						<div class="src-search-bar compact" style="flex: 1;">
							<input type="text" id="src-user-specialty-filter" placeholder="<?php _e( 'Filter by Specialty...', 'scientific-research-center' ); ?>">
						</div>
						<select id="src-user-status-filter">
							<option value=""><?php _e( 'All Statuses', 'scientific-research-center' ); ?></option>
							<option value="active"><?php _e( 'Active', 'scientific-research-center' ); ?></option>
							<option value="suspended"><?php _e( 'Suspended', 'scientific-research-center' ); ?></option>
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

				<!-- Add/Edit User Modal -->
				<div id="src-user-modal" class="src-modal monochromatic">
					<div class="src-modal-content">
						<div class="src-modal-header">
							<h2 id="src-modal-title"><?php _e( 'Add New Platform User', 'scientific-research-center' ); ?></h2>
							<span class="src-modal-close">&times;</span>
						</div>
						<form id="src-user-form">
							<input type="hidden" name="user_id" id="modal_user_id" value="">
							<div class="src-field-row">
								<div class="src-field-group">
									<input type="text" name="first_name" id="add_fn" placeholder=" " required>
									<label for="add_fn"><?php _e( 'First Name', 'scientific-research-center' ); ?></label>
								</div>
								<div class="src-field-group">
									<input type="text" name="last_name" id="add_ln" placeholder=" " required>
									<label for="add_ln"><?php _e( 'Last Name', 'scientific-research-center' ); ?></label>
								</div>
							</div>
							<div class="src-field-row">
								<div class="src-field-group">
									<input type="text" name="username" id="add_user" placeholder=" " required>
									<label for="add_user"><?php _e( 'Username', 'scientific-research-center' ); ?></label>
								</div>
								<div class="src-field-group">
									<input type="email" name="email" id="add_email" placeholder=" " required>
									<label for="add_email"><?php _e( 'Email Address', 'scientific-research-center' ); ?></label>
								</div>
							</div>
							<div class="src-field-row">
								<div class="src-field-group">
									<select name="role" id="add_role" required>
										<?php
										foreach ( SRC_Roles::get_roles_definition() as $slug => $data ) {
											echo '<option value="' . esc_attr( $slug ) . '">' . esc_html( $data['name'] ) . '</option>';
										}
										?>
									</select>
									<label for="add_role" class="select-label"><?php _e( 'Assigned Role', 'scientific-research-center' ); ?></label>
								</div>
								<div class="src-field-group">
									<input type="password" name="password" id="add_pass" placeholder=" " required>
									<label for="add_pass"><?php _e( 'Password', 'scientific-research-center' ); ?></label>
								</div>
							</div>
							<div class="src-field-row">
								<div class="src-field-group">
									<input type="text" name="institution" id="add_inst" placeholder=" " list="src_institution_list">
									<label for="add_inst"><?php _e( 'Affiliated Institution (Optional)', 'scientific-research-center' ); ?></label>
								</div>
								<div class="src-field-group">
									<input type="text" name="specialty" id="add_spec" placeholder=" ">
									<label for="add_spec"><?php _e( 'Scientific Specialty', 'scientific-research-center' ); ?></label>
								</div>
							</div>
							<div class="src-modal-footer">
								<button type="submit" id="src-modal-submit-btn" class="src-submit-btn"><?php _e( 'Create User Account', 'scientific-research-center' ); ?></button>
							</div>
							<div class="src-form-msg"></div>
						</form>
					</div>
				</div>

				<!-- Activity Log Modal -->
				<div id="src-user-log-modal" class="src-modal monochromatic">
					<div class="src-modal-content wide">
						<div class="src-modal-header">
							<h2><?php _e( 'User Activity Audit Log', 'scientific-research-center' ); ?></h2>
							<span class="src-modal-close">&times;</span>
						</div>
						<div id="src-user-log-content">
							<div class="src-loading-skeleton"></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $current_section === 'submissions-management' ) : ?>
			<div id="src-cp-content-submissions-management" class="src-cp-section active">
				<h1><?php _e( 'Submissions Management', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Review and process scientific research submissions from the global community.', 'scientific-research-center' ); ?></p>

				<div class="src-submission-search-container user-management-filters">
					<div class="src-filters-row main-row">
						<div class="src-search-bar compact">
							<input type="text" id="src-sub-search" placeholder="<?php _e( 'Search by title, author, or keywords...', 'scientific-research-center' ); ?>">
							<span class="dashicons dashicons-search"></span>
						</div>
					</div>
					<div class="src-filters-row secondary-row">
						<select id="src-sub-filter-type">
							<option value=""><?php _e( 'All Types', 'scientific-research-center' ); ?></option>
							<option value="thesis"><?php _e( 'Theses', 'scientific-research-center' ); ?></option>
							<option value="paper"><?php _e( 'Papers', 'scientific-research-center' ); ?></option>
							<option value="study"><?php _e( 'Studies', 'scientific-research-center' ); ?></option>
						</select>
						<select id="src-sub-filter-inst">
							<option value=""><?php _e( 'All Institutions', 'scientific-research-center' ); ?></option>
							<?php foreach ( get_utils_institutions() as $inst ) echo '<option value="'.esc_attr($inst).'">'.esc_html($inst).'</option>'; ?>
						</select>
						<select id="src-sub-filter-cat">
							<option value=""><?php _e( 'All Categories', 'scientific-research-center' ); ?></option>
							<?php
							$cats = get_terms( array( 'taxonomy' => 'research_category', 'hide_empty' => false ) );
							foreach ( $cats as $cat ) echo '<option value="'.$cat->slug.'">'.$cat->name.'</option>';
							?>
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

				<!-- Reviewer Assignment Modal -->
				<div id="src-assign-reviewer-modal" class="src-modal monochromatic">
					<div class="src-modal-content">
						<div class="src-modal-header">
							<h2><?php _e( 'Assign Reviewer', 'scientific-research-center' ); ?></h2>
							<span class="src-modal-close">&times;</span>
						</div>
						<form id="src-assign-reviewer-form">
							<input type="hidden" name="sub_id" id="assign_sub_id">
							<div class="src-field-group">
								<select name="reviewer_id" id="assign_reviewer" required>
									<option value=""><?php _e( 'Select a Reviewer...', 'scientific-research-center' ); ?></option>
									<?php
									$reviewers = get_users( array( 'role' => 'src_reviewer' ) );
									foreach ( $reviewers as $rev ) echo '<option value="'.$rev->ID.'">'.esc_html($rev->display_name).'</option>';
									?>
								</select>
								<label for="assign_reviewer" class="select-label"><?php _e( 'Scientific Reviewer', 'scientific-research-center' ); ?></label>
							</div>
							<div class="src-field-group">
								<input type="date" name="deadline" id="assign_deadline" required>
								<label for="assign_deadline" class="select-label"><?php _e( 'Review Deadline', 'scientific-research-center' ); ?></label>
							</div>
							<div class="src-modal-footer">
								<button type="submit" class="src-submit-btn"><?php _e( 'Assign & Notify', 'scientific-research-center' ); ?></button>
							</div>
							<div class="src-form-msg"></div>
						</form>
					</div>
				</div>

				<!-- Submission History Modal -->
				<div id="src-sub-history-modal" class="src-modal monochromatic">
					<div class="src-modal-content wide">
						<div class="src-modal-header">
							<h2><?php _e( 'Submission History & Version Control', 'scientific-research-center' ); ?></h2>
							<span class="src-modal-close">&times;</span>
						</div>
						<div id="src-sub-history-content">
							<div class="src-loading-skeleton"></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $current_section === 'research-engine' ) : ?>
			<div id="src-cp-content-research-engine" class="src-cp-section active">
				<h1><?php _e( 'Research Engine Management', 'scientific-research-center' ); ?></h1>
				<p><?php _e( 'Configure the core discovery engine, indexing rules, and scientific taxonomy hierarchy.', 'scientific-research-center' ); ?></p>

				<div class="src-engine-layout grid-2">
					<div class="src-engine-col-left">
						<div class="src-engine-controls card">
							<h3><span class="dashicons dashicons-admin-settings"></span> <?php _e( 'Index & Display Control', 'scientific-research-center' ); ?></h3>
							<button class="src-submit-btn" id="src-rebuild-index-btn"><?php _e( 'Rebuild Search Index', 'scientific-research-center' ); ?></button>

							<div class="src-display-settings" style="margin-top: 30px;">
								<div class="src-field-group">
									<select id="src-card-design">
										<option value="compact" <?php selected(get_option('src_search_card_design'), 'compact'); ?>><?php _e( 'Academic Compact (Default)', 'scientific-research-center' ); ?></option>
										<option value="detailed" <?php selected(get_option('src_search_card_design'), 'detailed'); ?>><?php _e( 'Information Rich', 'scientific-research-center' ); ?></option>
									</select>
									<label class="select-label"><?php _e( 'Search Result Card Design', 'scientific-research-center' ); ?></label>
								</div>

								<div class="src-meta-toggles">
									<p><strong><?php _e( 'Metadata Visibility', 'scientific-research-center' ); ?></strong></p>
									<?php
									$meta_vis = get_option('src_search_metadata_visibility', array('author', 'institution', 'date'));
									$options = array('author' => 'Author Name', 'institution' => 'Institution', 'date' => 'Publication Date', 'type' => 'Research Type');
									foreach ($options as $key => $label) : ?>
										<label><input type="checkbox" class="src-meta-vis-check" value="<?php echo $key; ?>" <?php checked(in_array($key, $meta_vis)); ?>> <?php echo $label; ?></label>
									<?php endforeach; ?>
								</div>
								<button class="src-btn-outline" id="src-save-search-settings-btn" style="margin-top: 20px;"><?php _e( 'Save Display Preferences', 'scientific-research-center' ); ?></button>
							</div>
						</div>

						<div class="src-engine-analytics card" style="margin-top: 30px;">
							<h3><span class="dashicons dashicons-chart-area"></span> <?php _e( 'Search Analytics', 'scientific-research-center' ); ?></h3>
							<div id="src-search-analytics-content">
								<div class="src-loading-skeleton"></div>
							</div>
						</div>
					</div>

					<div class="src-engine-hierarchy card">
						<h3><span class="dashicons dashicons-category"></span> <?php _e( 'Scientific Hierarchy', 'scientific-research-center' ); ?></h3>
						<p><?php _e( 'Manage faculties, specialties, and taxonomies.', 'scientific-research-center' ); ?></p>

						<div class="src-hierarchy-editor">
							<div class="src-field-row">
								<div class="src-field-group">
									<select id="src-hier-type">
										<option value="src_faculty"><?php _e( 'Faculty', 'scientific-research-center' ); ?></option>
										<option value="src_specialty"><?php _e( 'Specialty', 'scientific-research-center' ); ?></option>
										<option value="src_sub_specialty"><?php _e( 'Sub-specialty', 'scientific-research-center' ); ?></option>
										<option value="src_institution_tax"><?php _e( 'Institution', 'scientific-research-center' ); ?></option>
									</select>
								</div>
								<div class="src-field-group">
									<input type="text" id="src-hier-name" placeholder="Name">
								</div>
							</div>
							<div class="src-field-group">
								<select id="src-hier-parent">
									<option value="0"><?php _e( 'None (Root)', 'scientific-research-center' ); ?></option>
								</select>
							</div>
							<button class="src-submit-btn full-width" id="src-add-taxonomy-item"><?php _e( 'Add Item to Hierarchy', 'scientific-research-center' ); ?></button>
						</div>

						<div class="src-user-list-container">
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

				<div class="src-card-grid">
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
								<div class="src-research-card slide-entry card compact-card" data-id="<?php echo $post_id; ?>">
									<div class="src-card-header">
										<span class="src-badge small-badge"><?php echo esc_html( $type ); ?></span>
										<div class="src-card-top-actions">
											<span class="src-date small-text"><?php echo get_the_date(); ?></span>
											<button class="src-fav-toggle active" title="<?php _e( 'Remove from Favorites', 'scientific-research-center' ); ?>">
												<span class="dashicons dashicons-heart"></span>
											</button>
										</div>
									</div>
									<h3><?php the_title(); ?></h3>
									<div class="src-card-meta academic-meta">
										<div class="src-meta-item"><span class="dashicons dashicons-admin-users"></span> <strong><?php the_author(); ?></strong></div>
										<?php
										$inst = get_user_meta( get_the_author_meta('ID'), 'src_institution', true );
										if ( $inst ) : ?>
											<div class="src-meta-item"><span class="dashicons dashicons-welcome-learn-more"></span> <span><?php echo esc_html( $inst ); ?></span></div>
										<?php endif; ?>
									</div>
									<div class="src-card-actions">
										<a href="<?php the_permalink(); ?>" class="src-view-details-btn"><?php _e( 'View Details', 'scientific-research-center' ); ?></a>
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
				<div class="src-settings-col-left">
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

					<div class="src-card" style="margin-top: 30px;">
						<h3><span class="dashicons dashicons-email"></span> <?php _e( 'Email Templates', 'scientific-research-center' ); ?></h3>
						<div class="src-email-editor">
							<div class="src-field-group">
								<select id="src-email-template-select">
									<option value="verification"><?php _e( 'User Verification', 'scientific-research-center' ); ?></option>
									<option value="submission_received"><?php _e( 'Submission Received', 'scientific-research-center' ); ?></option>
									<option value="approval"><?php _e( 'Research Approved', 'scientific-research-center' ); ?></option>
								</select>
							</div>
							<div class="src-field-group">
								<input type="text" id="src-email-subject" placeholder="Email Subject">
							</div>
							<div class="src-field-group">
								<textarea id="src-email-body" style="height: 150px;" placeholder="Template Body (HTML allowed)"></textarea>
							</div>
							<button class="src-submit-btn full-width" id="src-save-email-tpl-btn"><?php _e( 'Save Template', 'scientific-research-center' ); ?></button>
						</div>
					</div>
				</div>

				<div class="src-settings-col-right">
					<div class="src-card">
						<h3><span class="dashicons dashicons-shield-alt"></span> <?php _e( 'Security & Permissions', 'scientific-research-center' ); ?></h3>
						<div class="src-role-manager">
							<p><strong><?php _e( 'Module Access Control', 'scientific-research-center' ); ?></strong></p>
							<div class="src-field-group">
								<select id="src-role-perm-select">
									<?php foreach ( SRC_Roles::get_roles_definition() as $slug => $data ) : ?>
										<option value="<?php echo $slug; ?>"><?php echo $data['name']; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div id="src-role-caps-list" class="src-checkbox-list">
								<label><input type="checkbox" value="upload_files"> <?php _e( 'File Uploads', 'scientific-research-center' ); ?></label>
								<label><input type="checkbox" value="edit_posts"> <?php _e( 'Edit Submissions', 'scientific-research-center' ); ?></label>
								<label><input type="checkbox" value="publish_posts"> <?php _e( 'Publish Papers', 'scientific-research-center' ); ?></label>
							</div>
							<button class="src-btn-outline" id="src-save-role-perms-btn" style="margin-top: 15px;"><?php _e( 'Update Permissions', 'scientific-research-center' ); ?></button>
						</div>

						<hr style="margin: 30px 0;">

						<div class="src-security-policies">
							<p><strong><?php _e( 'Platform Policies', 'scientific-research-center' ); ?></strong></p>
							<div class="src-field-group">
								<input type="number" id="src-min-pwd" value="<?php echo get_option('src_pwd_min_length', 8); ?>">
								<label><?php _e( 'Min Password Length', 'scientific-research-center' ); ?></label>
							</div>
							<div class="src-field-group">
								<input type="number" id="src-session-time" value="<?php echo get_option('src_session_timeout', 60); ?>">
								<label><?php _e( 'Session Timeout (Minutes)', 'scientific-research-center' ); ?></label>
							</div>
							<button class="src-submit-btn full-width" id="src-save-security-btn"><?php _e( 'Apply Security Policies', 'scientific-research-center' ); ?></button>
						</div>
					</div>

					<div class="src-card" style="margin-top: 30px;">
						<h3><span class="dashicons dashicons-backup"></span> <?php _e( 'Data & Backups', 'scientific-research-center' ); ?></h3>
						<p><?php _e( 'Export all platform data for safe backup.', 'scientific-research-center' ); ?></p>
						<button class="src-btn-outline full-width" onclick="location.href='?section=users-management'"><?php _e( 'Global Data Export (JSON)', 'scientific-research-center' ); ?></button>
					</div>
				</div>
			</div>

			<div class="src-settings-footer">
				<button class="src-submit-btn"><?php _e( 'Update Visual Preferences', 'scientific-research-center' ); ?></button>
			</div>
		</div>
		<?php endif; ?>
	</main>
</div>

<?php
get_footer();
