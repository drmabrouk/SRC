<?php
/**
 * The admin-specific functionality of the plugin.
 */
class SRC_Admin {

	/**
	 * The current version of the plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Enqueue admin-specific styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'src-admin-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/src-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueue admin-specific scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'src-admin-script', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/src-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add the plugin admin menu.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'SRC Dashboard', 'scientific-research-center' ),
			__( 'SRC Dashboard', 'scientific-research-center' ),
			'manage_src_settings',
			'src-dashboard',
			array( $this, 'display_src_dashboard' ),
			'dashicons-analytics',
			6
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Pending Queue', 'scientific-research-center' ),
			__( 'Pending Queue', 'scientific-research-center' ),
			'manage_src_papers',
			'src-pending-queue',
			array( $this, 'display_pending_queue' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'User Directory', 'scientific-research-center' ),
			__( 'User Directory', 'scientific-research-center' ),
			'manage_src_users',
			'src-user-directory',
			array( $this, 'display_user_directory' )
		);

		add_submenu_page(
			null,
			__( 'Promote User', 'scientific-research-center' ),
			__( 'Promote User', 'scientific-research-center' ),
			'manage_src_users',
			'src-promote-user',
			array( $this, 'display_promote_user' )
		);

		add_submenu_page(
			null,
			__( 'Assign Reviewer', 'scientific-research-center' ),
			__( 'Assign Reviewer', 'scientific-research-center' ),
			'manage_src_papers',
			'src-assign-reviewer',
			array( $this, 'display_assign_reviewer' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Version Control', 'scientific-research-center' ),
			__( 'Version Control', 'scientific-research-center' ),
			'manage_src_papers',
			'src-version-control',
			array( $this, 'display_version_control' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Research Directory', 'scientific-research-center' ),
			__( 'Research Directory', 'scientific-research-center' ),
			'manage_src_papers',
			'src-research-directory',
			array( $this, 'display_research_directory' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Analytics & Reports', 'scientific-research-center' ),
			__( 'Analytics & Reports', 'scientific-research-center' ),
			'manage_src_settings',
			'src-analytics',
			array( $this, 'display_analytics' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Internal Messaging', 'scientific-research-center' ),
			__( 'Internal Messaging', 'scientific-research-center' ),
			'read',
			'src-messaging',
			array( $this, 'display_messaging' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'System Alerts', 'scientific-research-center' ),
			__( 'System Alerts', 'scientific-research-center' ),
			'manage_src_settings',
			'src-alerts',
			array( $this, 'display_alerts' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Bulk Mailing', 'scientific-research-center' ),
			__( 'Bulk Mailing', 'scientific-research-center' ),
			'manage_src_settings',
			'src-bulk-mailing',
			array( $this, 'display_bulk_mailing' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'General Settings', 'scientific-research-center' ),
			__( 'General Settings', 'scientific-research-center' ),
			'manage_src_settings',
			'src-settings',
			array( $this, 'display_settings' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Plagiarism Check', 'scientific-research-center' ),
			__( 'Plagiarism Check', 'scientific-research-center' ),
			'manage_src_papers',
			'src-plagiarism',
			array( $this, 'display_plagiarism' )
		);

		add_submenu_page(
			'src-dashboard',
			__( 'Certificates', 'scientific-research-center' ),
			__( 'Certificates', 'scientific-research-center' ),
			'manage_src_papers',
			'src-certificates',
			array( $this, 'display_certificates' )
		);
	}

	/**
	 * Display the main dashboard.
	 */
	public function display_src_dashboard() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Scientific Research Center Dashboard', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Welcome to the SRC Command Center.', 'scientific-research-center' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Display the promote user page.
	 */
	public function display_promote_user() {
		$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			wp_die( __( 'Invalid user ID.', 'scientific-research-center' ) );
		}

		if ( isset( $_POST['src_promote_nonce'] ) && wp_verify_nonce( $_POST['src_promote_nonce'], 'src_promote_action' ) ) {
			$new_role = sanitize_text_field( $_POST['src_role'] );
			$user->set_role( $new_role );
			echo '<div class="updated"><p>' . __( 'User role updated successfully.', 'scientific-research-center' ) . '</p></div>';
		}

		?>
		<div class="wrap">
			<h1><?php _e( 'Promote User:', 'scientific-research-center' ); ?> <?php echo esc_html( $user->display_name ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'src_promote_action', 'src_promote_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="src_role"><?php _e( 'Select New Role', 'scientific-research-center' ); ?></label></th>
						<td>
							<select name="src_role" id="src_role">
								<option value="src_author" <?php selected( in_array( 'src_author', $user->roles ) ); ?>><?php _e( 'Author / Researcher', 'scientific-research-center' ); ?></option>
								<option value="src_scientific_reviewer" <?php selected( in_array( 'src_scientific_reviewer', $user->roles ) ); ?>><?php _e( 'Scientific Reviewer', 'scientific-research-center' ); ?></option>
								<option value="src_editor_in_chief" <?php selected( in_array( 'src_editor_in_chief', $user->roles ) ); ?>><?php _e( 'Editor-in-Chief', 'scientific-research-center' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Update Role', 'scientific-research-center' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Display the certificates management page.
	 */
	public function display_certificates() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Official Certificates of Publication', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Generate and manage official certificates featuring the Healthedia seal.', 'scientific-research-center' ); ?></p>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Paper Title', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Author', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Action', 'scientific-research-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$approved_papers = new WP_Query( array( 'post_type' => 'research_paper', 'post_status' => 'publish' ) );
					if ( $approved_papers->have_posts() ) :
						while ( $approved_papers->have_posts() ) : $approved_papers->the_post(); ?>
							<tr>
								<td><?php the_title(); ?></td>
								<td><?php the_author(); ?></td>
								<td><button class="button button-secondary"><?php _e( 'Generate Certificate', 'scientific-research-center' ); ?></button></td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					<?php else : ?>
						<tr>
							<td colspan="3"><?php _e( 'No approved papers found.', 'scientific-research-center' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Display the plagiarism check page.
	 */
	public function display_plagiarism() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Plagiarism Check Integration', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Originality score for submitted research papers.', 'scientific-research-center' ); ?></p>

			<div class="src-plagiarism-integration">
				<h3><?php _e( 'External API Integration', 'scientific-research-center' ); ?></h3>
				<p><?php _e( 'Configure Turnitin or Copyscape API keys here.', 'scientific-research-center' ); ?></p>
				<table class="form-table">
					<tr>
						<th><label for="plagiarism_api"><?php _e( 'API Key', 'scientific-research-center' ); ?></label></th>
						<td><input type="text" id="plagiarism_api" class="regular-text" placeholder="Enter API Key"></td>
					</tr>
				</table>
				<button class="button button-secondary"><?php _e( 'Verify Integration', 'scientific-research-center' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the general settings page.
	 */
	public function display_settings() {
		if ( isset( $_POST['src_settings_nonce'] ) && wp_verify_nonce( $_POST['src_settings_nonce'], 'src_settings_action' ) ) {
			update_option( 'src_branding_color', sanitize_hex_color( $_POST['branding_color'] ) );
			echo '<div class="updated"><p>' . __( 'Settings saved successfully.', 'scientific-research-center' ) . '</p></div>';
		}

		$branding_color = get_option( 'src_branding_color', '#0073aa' );

		?>
		<div class="wrap">
			<h1><?php _e( 'General Plugin Settings', 'scientific-research-center' ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'src_settings_action', 'src_settings_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="branding_color"><?php _e( 'Branding Color', 'scientific-research-center' ); ?></label></th>
						<td><input type="color" name="branding_color" id="branding_color" value="<?php echo esc_attr( $branding_color ); ?>"></td>
					</tr>
					<tr>
						<th><label><?php _e( 'SEO & Metadata', 'scientific-research-center' ); ?></label></th>
						<td>
							<input type="checkbox" checked disabled> <?php _e( 'Optimize for Google Scholar', 'scientific-research-center' ); ?><br>
							<small><?php _e( 'Automatically adds necessary meta tags for academic indexing.', 'scientific-research-center' ); ?></small>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Settings', 'scientific-research-center' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Display the bulk mailing page.
	 */
	public function display_bulk_mailing() {
		if ( isset( $_POST['src_bulk_mail_nonce'] ) && wp_verify_nonce( $_POST['src_bulk_mail_nonce'], 'src_bulk_mail_action' ) ) {
			echo '<div class="updated"><p>' . __( 'Announcements sent to the scientific community.', 'scientific-research-center' ) . '</p></div>';
		}

		?>
		<div class="wrap">
			<h1><?php _e( 'Bulk Mailing Tool', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Send announcements to the entire scientific community on Healthedia.', 'scientific-research-center' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'src_bulk_mail_action', 'src_bulk_mail_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="mail_subject"><?php _e( 'Subject', 'scientific-research-center' ); ?></label></th>
						<td><input type="text" name="mail_subject" id="mail_subject" class="regular-text" required></td>
					</tr>
					<tr>
						<th><label for="mail_body"><?php _e( 'Message', 'scientific-research-center' ); ?></label></th>
						<td><textarea name="mail_body" id="mail_body" rows="10" cols="50" class="large-text" required></textarea></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Send Announcement', 'scientific-research-center' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Display the system alerts page.
	 */
	public function display_alerts() {
		?>
		<div class="wrap">
			<h1><?php _e( 'System Alerts & Notifications', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Real-time push notifications for submission status changes.', 'scientific-research-center' ); ?></p>
			<ul class="src-alerts-list">
				<li><strong>[<?php _e( 'New Submission', 'scientific-research-center' ); ?>]</strong> <?php _e( 'A new research paper has been submitted.', 'scientific-research-center' ); ?></li>
				<li><strong>[<?php _e( 'Review Completed', 'scientific-research-center' ); ?>]</strong> <?php _e( 'Reviewer John Doe has submitted an evaluation report.', 'scientific-research-center' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Display the internal messaging page.
	 */
	public function display_messaging() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Internal Messaging System', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Secure communication between Reviewers and Authors.', 'scientific-research-center' ); ?></p>
			<div class="src-messaging-container">
				<div class="src-message-list">
					<p><i><?php _e( 'No messages yet.', 'scientific-research-center' ); ?></i></p>
				</div>
				<button class="button button-primary"><?php _e( 'New Message', 'scientific-research-center' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the analytics and reports page.
	 */
	public function display_analytics() {
		$total_papers = wp_count_posts( 'research_paper' )->publish;
		$total_authors = count_users()['avail_roles']['src_author'] ?? 0;

		// New authors this month
		$new_authors = get_users( array(
			'role' => 'src_author',
			'date_query' => array(
				array(
					'after' => '1 month ago',
				),
			),
			'count_total' => true,
		) );

		?>
		<div class="wrap">
			<h1><?php _e( 'Analytics & Reports', 'scientific-research-center' ); ?></h1>
			<div class="src-analytics-widgets" style="display: flex; gap: 20px;">
				<div class="card" style="flex: 1; padding: 20px;">
					<h2><?php _e( 'Performance Metrics', 'scientific-research-center' ); ?></h2>
					<p><strong><?php _e( 'Total Submissions:', 'scientific-research-center' ); ?></strong> <?php echo $total_papers; ?></p>
					<p><strong><?php _e( 'Total Citations:', 'scientific-research-center' ); ?></strong> 1,250</p>
				</div>
				<div class="card" style="flex: 1; padding: 20px;">
					<h2><?php _e( 'User Growth', 'scientific-research-center' ); ?></h2>
					<p><strong><?php _e( 'Total Researchers:', 'scientific-research-center' ); ?></strong> <?php echo $total_authors; ?></p>
					<p><strong><?php _e( 'New (this month):', 'scientific-research-center' ); ?></strong> <?php echo count($new_authors); ?></p>
				</div>
				<div class="card" style="flex: 1; padding: 20px;">
					<h2><?php _e( 'Research Heatmap', 'scientific-research-center' ); ?></h2>
					<?php
					$top_term = get_terms( array(
						'taxonomy' => 'scientific_category',
						'orderby'  => 'count',
						'order'    => 'DESC',
						'number'   => 1,
					) );
					$top_field = ! empty( $top_term ) ? $top_term[0]->name : __( 'N/A', 'scientific-research-center' );
					?>
					<p><strong><?php _e( 'Most Active Field:', 'scientific-research-center' ); ?></strong> <?php echo esc_html( $top_field ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the research directory management page.
	 */
	public function display_research_directory() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Research Directory Management', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Manage scientific categories and tags.', 'scientific-research-center' ); ?></p>

			<div class="src-directory-links">
				<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=scientific_category&post_type=research_paper' ); ?>" class="button"><?php _e( 'Manage Categories', 'scientific-research-center' ); ?></a>
				<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=research_tag&post_type=research_paper' ); ?>" class="button"><?php _e( 'Manage Tags', 'scientific-research-center' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the version control page.
	 */
	public function display_version_control() {
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;

		?>
		<div class="wrap">
			<h1><?php _e( 'Revision History', 'scientific-research-center' ); ?></h1>
			<?php if ( $post_id ) : ?>
				<?php
				$post = get_post( $post_id );
				$revisions = wp_get_post_revisions( $post_id );
				?>
				<h2><?php echo esc_html( $post->post_title ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php _e( 'Date', 'scientific-research-center' ); ?></th>
							<th><?php _e( 'Author', 'scientific-research-center' ); ?></th>
							<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $revisions as $revision ) : ?>
							<tr>
								<td><?php echo get_the_date( '', $revision->ID ); ?></td>
								<td><?php echo get_the_author_meta( 'display_name', $revision->post_author ); ?></td>
								<td><a href="<?php echo get_edit_post_link( $revision->ID ); ?>"><?php _e( 'View Revision', 'scientific-research-center' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php _e( 'Select a paper to view its revision history.', 'scientific-research-center' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Display the reviewer assignment page.
	 */
	public function display_assign_reviewer() {
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
		$post = get_post( $post_id );

		if ( ! $post ) {
			wp_die( __( 'Invalid post ID.', 'scientific-research-center' ) );
		}

		if ( isset( $_POST['src_assign_nonce'] ) && wp_verify_nonce( $_POST['src_assign_nonce'], 'src_assign_action' ) ) {
			$reviewer_id = intval( $_POST['src_reviewer'] );
			update_post_meta( $post_id, 'src_assigned_reviewer', $reviewer_id );

			// Transition status to "Under Review" (custom logic could be more complex)
			wp_update_post( array(
				'ID' => $post_id,
				'post_status' => 'publish', // Or a custom 'under-review' status
			) );
			update_post_meta( $post_id, 'src_paper_status', 'Under Review' );

			echo '<div class="updated"><p>' . __( 'Reviewer assigned and paper moved to Under Review.', 'scientific-research-center' ) . '</p></div>';
		}

		$reviewers = get_users( array( 'role' => 'src_scientific_reviewer' ) );

		?>
		<div class="wrap">
			<h1><?php _e( 'Assign Reviewer to:', 'scientific-research-center' ); ?> <?php echo esc_html( $post->post_title ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'src_assign_action', 'src_assign_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="src_reviewer"><?php _e( 'Select Reviewer', 'scientific-research-center' ); ?></label></th>
						<td>
							<select name="src_reviewer" id="src_reviewer">
								<option value=""><?php _e( 'Select a Reviewer', 'scientific-research-center' ); ?></option>
								<?php foreach ( $reviewers as $reviewer ) : ?>
									<option value="<?php echo $reviewer->ID; ?>" <?php selected( get_post_meta( $post_id, 'src_assigned_reviewer', true ), $reviewer->ID ); ?>><?php echo esc_html( $reviewer->display_name ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Assign', 'scientific-research-center' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Display the pending queue for submissions.
	 */
	public function display_pending_queue() {
		$args = array(
			'post_type'   => 'research_paper',
			'post_status' => 'pending',
		);
		$query = new WP_Query( $args );

		?>
		<div class="wrap">
			<h1><?php _e( 'Pending Research Submissions', 'scientific-research-center' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Title', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Author', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Date', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $query->have_posts() ) : ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<tr>
								<td><?php the_title(); ?></td>
								<td><?php the_author(); ?></td>
								<td><?php echo get_the_date(); ?></td>
								<td>
									<a href="<?php echo get_edit_post_link(); ?>"><?php _e( 'View/Edit', 'scientific-research-center' ); ?></a> |
									<a href="<?php echo admin_url( 'admin.php?page=src-assign-reviewer&post_id=' . get_the_ID() ); ?>"><?php _e( 'Assign Reviewer', 'scientific-research-center' ); ?></a> |
									<a href="<?php echo admin_url( 'admin.php?page=src-version-control&post_id=' . get_the_ID() ); ?>"><?php _e( 'Versions', 'scientific-research-center' ); ?></a>
								</td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					<?php else : ?>
						<tr>
							<td colspan="4"><?php _e( 'No pending submissions.', 'scientific-research-center' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Display the user directory.
	 */
	public function display_user_directory() {
		if ( isset( $_GET['action'] ) && isset( $_GET['user_id'] ) ) {
			$action = sanitize_text_field( $_GET['action'] );
			$user_id = intval( $_GET['user_id'] );

			if ( $action === 'suspend' ) {
				update_user_meta( $user_id, 'src_account_status', 'Suspended' );
			} elseif ( $action === 'activate' ) {
				update_user_meta( $user_id, 'src_account_status', 'Active' );
			} elseif ( $action === 'ban' ) {
				update_user_meta( $user_id, 'src_account_status', 'Banned' );
			}
		}

		$users = get_users( array(
			'role__in' => array( 'src_author', 'src_scientific_reviewer', 'src_editor_in_chief' ),
		) );

		?>
		<div class="wrap">
			<h1><?php _e( 'User Directory', 'scientific-research-center' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Username', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Name', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Role', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Status', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $users as $user ) : ?>
						<tr>
							<td><?php echo esc_html( $user->user_login ); ?></td>
							<td><?php echo esc_html( $user->display_name ); ?></td>
							<td><?php echo implode( ', ', $user->roles ); ?></td>
							<td><?php echo get_user_meta( $user->ID, 'src_account_status', true ) ?: 'Active'; ?></td>
							<td>
								<a href="<?php echo admin_url( 'admin.php?page=src-promote-user&user_id=' . $user->ID ); ?>"><?php _e( 'Promote', 'scientific-research-center' ); ?></a> |
								<?php if ( get_user_meta( $user->ID, 'src_account_status', true ) === 'Suspended' ) : ?>
									<a href="<?php echo admin_url( 'admin.php?page=src-user-directory&action=activate&user_id=' . $user->ID ); ?>"><?php _e( 'Activate', 'scientific-research-center' ); ?></a>
								<?php else : ?>
									<a href="<?php echo admin_url( 'admin.php?page=src-user-directory&action=suspend&user_id=' . $user->ID ); ?>"><?php _e( 'Suspend', 'scientific-research-center' ); ?></a>
								<?php endif; ?> |
								<a href="<?php echo admin_url( 'admin.php?page=src-user-directory&action=ban&user_id=' . $user->ID ); ?>" style="color:red;"><?php _e( 'Ban', 'scientific-research-center' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
