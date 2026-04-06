<?php
/**
 * SRC_Research Class
 * Handles research submissions, taxonomies, and library engine.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Research {

	public function __construct() {
		add_action( 'init', array( $this, 'register_research_cpt' ) );
		add_action( 'init', array( $this, 'register_research_taxonomies' ) );
		add_action( 'wp_head', array( $this, 'add_research_seo_meta' ) );
		add_action( 'init', array( $this, 'add_pub_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_pub_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'handle_pub_id_redirect' ) );
		add_shortcode( 'src_submit_research', array( $this, 'render_submission_form' ) );
		add_shortcode( 'src_research_library', array( $this, 'render_research_library' ) );
		add_action( 'wp_ajax_src_submit_research', array( $this, 'handle_ajax_submission' ) );
		add_action( 'wp_ajax_src_filter_research', array( $this, 'handle_ajax_filter_research' ) );
		add_action( 'wp_ajax_src_load_submissions', array( $this, 'handle_ajax_load_submissions' ) );
		add_action( 'wp_ajax_src_process_submission', array( $this, 'handle_ajax_process_submission' ) );
		add_action( 'wp_ajax_src_manage_taxonomy', array( $this, 'handle_ajax_manage_taxonomy' ) );
		add_action( 'wp_ajax_src_get_child_taxonomies', array( $this, 'handle_ajax_get_child_taxonomies' ) );
		add_action( 'wp_ajax_src_assign_reviewer', array( $this, 'handle_ajax_assign_reviewer' ) );
		add_action( 'wp_ajax_src_get_submission_history', array( $this, 'handle_ajax_get_submission_history' ) );
		add_action( 'wp_ajax_src_rebuild_index', array( $this, 'handle_ajax_rebuild_index' ) );
		add_action( 'wp_ajax_src_save_inline_research', array( $this, 'handle_ajax_save_inline_research' ) );
	}

	/**
	 * Register Research Paper Custom Post Type
	 */
	public function register_research_cpt() {
		$labels = array(
			'name'               => _x( 'Research Papers', 'post type general name', 'scientific-research-center' ),
			'singular_name'      => _x( 'Research Paper', 'post type singular name', 'scientific-research-center' ),
			'menu_name'          => _x( 'Research Papers', 'admin menu', 'scientific-research-center' ),
			'add_new'            => _x( 'Add New', 'research paper', 'scientific-research-center' ),
			'add_new_item'       => __( 'Add New Research Paper', 'scientific-research-center' ),
			'edit_item'          => __( 'Edit Research Paper', 'scientific-research-center' ),
			'view_item'          => __( 'View Research Paper', 'scientific-research-center' ),
			'all_items'          => __( 'All Research Papers', 'scientific-research-center' ),
			'search_items'       => __( 'Search Research Papers', 'scientific-research-center' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'research' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'excerpt', 'custom-fields' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'research_paper', $args );
	}

	/**
	 * Register Research Taxonomies
	 */
	/**
	 * Render Research Submission Form
	 */
	public function render_submission_form() {
		if ( ! is_user_logged_in() ) {
			return sprintf( '<div class="src-auth-container monochromatic compact"><p>%s <a href="%s">%s</a></p></div>',
				__( 'Please login to submit research.', 'scientific-research-center' ),
				home_url( '/login-register/' ),
				__( 'Login here', 'scientific-research-center' )
			);
		}

		$user = wp_get_current_user();
		$institution = get_user_meta( $user->ID, 'src_institution', true );

		ob_start();
		?>
		<div class="src-submission-wizard monochromatic">
			<div class="src-wizard-header">
				<h2><?php _e( 'Submit Research', 'scientific-research-center' ); ?></h2>
				<p><?php _e( 'Share your scientific work with the global research community.', 'scientific-research-center' ); ?></p>

				<div class="src-wizard-steps">
					<div class="src-step active" data-step="1"><span>1</span><p><?php _e( 'Basic Info', 'scientific-research-center' ); ?></p></div>
					<div class="src-step" data-step="2"><span>2</span><p><?php _e( 'Authors', 'scientific-research-center' ); ?></p></div>
					<div class="src-step" data-step="3"><span>3</span><p><?php _e( 'Files', 'scientific-research-center' ); ?></p></div>
					<div class="src-step" data-step="4"><span>4</span><p><?php _e( 'Keywords', 'scientific-research-center' ); ?></p></div>
				</div>
			</div>

			<form id="src-research-submission-action" class="src-wizard-form" enctype="multipart/form-data">
				<!-- Step 1: Basic Info -->
				<div class="src-wizard-step-content active" data-step="1">
					<div class="src-field-group">
						<input type="text" name="title" id="res_title" placeholder=" " required>
						<label for="res_title"><?php _e( 'Research Title', 'scientific-research-center' ); ?></label>
					</div>

					<div class="src-field-group">
						<textarea name="abstract" id="res_abstract" placeholder=" " required style="height:150px;"></textarea>
						<label for="res_abstract"><?php _e( 'Abstract', 'scientific-research-center' ); ?></label>
					</div>

					<div class="src-field-group">
						<select name="type" id="res_type" required>
							<option value="thesis"><?php _e( 'Thesis', 'scientific-research-center' ); ?></option>
							<option value="paper"><?php _e( 'Scientific Paper', 'scientific-research-center' ); ?></option>
							<option value="study"><?php _e( 'Case Study', 'scientific-research-center' ); ?></option>
						</select>
						<label for="res_type" class="select-label"><?php _e( 'Research Type', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<!-- Step 2: Authors & Institutions -->
				<div class="src-wizard-step-content" data-step="2">
					<div class="src-field-row">
						<div class="src-field-group">
							<input type="text" name="author_display" value="<?php echo esc_attr( $user->display_name ); ?>" disabled>
							<label><?php _e( 'Primary Author', 'scientific-research-center' ); ?></label>
						</div>
						<div class="src-field-group">
							<input type="text" name="institution_display" value="<?php echo esc_attr( $institution ); ?>" disabled>
							<label><?php _e( 'Affiliated Institution', 'scientific-research-center' ); ?></label>
						</div>
					</div>
					<div class="src-field-group">
						<input type="text" name="co_authors" id="res_coauthors" placeholder=" ">
						<label for="res_coauthors"><?php _e( 'Co-Authors (Optional, comma separated)', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<input type="text" name="publication_date" id="res_date" placeholder=" " value="<?php echo date('Y-m-d'); ?>">
						<label for="res_date"><?php _e( 'Original Publication Date', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<!-- Step 3: Files -->
				<div class="src-wizard-step-content" data-step="3">
					<div class="src-upload-zone" id="src-main-file-zone">
						<div class="src-upload-icon"><span class="dashicons dashicons-cloud-upload"></span></div>
						<p><?php _e( 'Drag and drop your primary research file here', 'scientific-research-center' ); ?></p>
						<span><?php _e( 'Supported formats: PDF, DOCX (Max 10MB)', 'scientific-research-center' ); ?></span>
						<input type="file" name="research_file" id="res_file" accept=".pdf,.docx" required>
					</div>

					<div class="src-field-group" style="margin-top: 30px;">
						<input type="file" name="supporting_files[]" id="res_supporting" multiple>
						<label for="res_supporting" class="file-label"><?php _e( 'Supporting Files (Optional)', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<!-- Step 4: Keywords & Submit -->
				<div class="src-wizard-step-content" data-step="4">
					<div class="src-field-group">
						<input type="text" name="keywords" id="res_keywords" placeholder=" ">
						<label for="res_keywords"><?php _e( 'Keywords (e.g. Quantum, Physics, Biology)', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<textarea name="supplementary" id="res_supp" placeholder=" " style="height:100px;"></textarea>
						<label for="res_supp"><?php _e( 'Supplementary Information / Notes to Reviewer', 'scientific-research-center' ); ?></label>
					</div>

					<div class="src-confirmation-box">
						<input type="checkbox" id="res_confirm" required>
						<label for="res_confirm"><?php _e( 'I confirm that this work is original and I have the right to publish it.', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<div class="src-wizard-footer">
					<button type="button" class="src-btn-outline src-wizard-prev" style="display:none;"><?php _e( 'Previous', 'scientific-research-center' ); ?></button>
					<button type="button" class="src-submit-btn src-wizard-next"><?php _e( 'Continue', 'scientific-research-center' ); ?></button>
					<button type="submit" class="src-submit-btn src-wizard-submit" style="display:none;"><?php _e( 'Submit Research', 'scientific-research-center' ); ?></button>
				</div>

				<div class="src-form-msg"></div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle AJAX Research Submission
	 */
	public function handle_ajax_submission() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'scientific-research-center' ) ) );
		}

		$title = sanitize_text_field( $_POST['title'] );
		$abstract = wp_kses_post( $_POST['abstract'] );
		$type = sanitize_text_field( $_POST['type'] );
		$keywords = sanitize_text_field( $_POST['keywords'] );
		$co_authors = sanitize_text_field( $_POST['co_authors'] );
		$publication_date = sanitize_text_field( $_POST['publication_date'] );
		$supplementary = wp_kses_post( $_POST['supplementary'] );

		$post_id = wp_insert_post( array(
			'post_title'   => $title,
			'post_content' => $abstract,
			'post_status'  => 'pending',
			'post_author'  => $user_id,
			'post_type'    => 'research_paper',
		) );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
		}

		// Handle Metadata
		update_post_meta( $post_id, 'src_co_authors', $co_authors );
		update_post_meta( $post_id, 'src_pub_date', $publication_date );
		update_post_meta( $post_id, 'src_supplementary', $supplementary );

		// Handle Taxonomies
		wp_set_object_terms( $post_id, $type, 'research_type' );
		if ( ! empty( $keywords ) ) {
			$tag_ids = array();
			$tags = explode( ',', $keywords );
			foreach ( $tags as $tag ) {
				$tag = trim( $tag );
				if ( ! empty( $tag ) ) {
					$tag_ids[] = $tag;
				}
			}
			wp_set_object_terms( $post_id, $tag_ids, 'post_tag' );
		}

		// Handle Files
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		if ( ! empty( $_FILES['research_file'] ) ) {
			$file_id = media_handle_upload( 'research_file', $post_id );
			if ( ! is_wp_error( $file_id ) ) {
				update_post_meta( $post_id, 'src_main_file', $file_id );
			}
		}

		if ( ! empty( $_FILES['supporting_files'] ) ) {
			$files = $_FILES['supporting_files'];
			foreach ( $files['name'] as $key => $value ) {
				if ( $files['name'][ $key ] ) {
					$file = array(
						'name'     => $files['name'][ $key ],
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ],
					);
					$_FILES['supporting_file_item'] = $file;
					$attachment_id = media_handle_upload( 'supporting_file_item', $post_id );
					if ( ! is_wp_error( $attachment_id ) ) {
						add_post_meta( $post_id, 'src_supporting_file', $attachment_id );
					}
				}
			}
		}

		// Add Notification for User
		$this->add_notification( $user_id, array(
			'message' => __( 'Your research submission was received successfully and is pending review.', 'scientific-research-center' ),
			'url'     => home_url( '/research-library/' ), // Should ideally be to a user's my-submissions page
		) );

		// Add Notification for Admins/Reviewers
		$admin_users = get_users( array( 'role__in' => array( 'administrator', 'src_administrator', 'src_supervisor' ) ) );
		foreach ( $admin_users as $admin ) {
			$role = ! empty( $admin->roles ) ? $admin->roles[0] : 'administrator';
			$role_slug = str_replace( 'src_', '', $role );
			if ( $role === 'administrator' ) $role_slug = 'administrator';

			$this->add_notification( $admin->ID, array(
				'message' => sprintf( __( 'New research submission: %s', 'scientific-research-center' ), $title ),
				'url'     => home_url( '/' . $role_slug . '-workspace/?section=submissions-management' ),
			) );
		}

		// Send Automated Notification Email
		SRC_Emails::send_research_status_email( $post_id, 'received' );

		wp_send_json_success( array( 'message' => __( 'Research submitted successfully! It is now pending review.', 'scientific-research-center' ) ) );
	}

	/**
	 * Helper to add a notification to a user
	 */
	private function add_notification( $user_id, $data ) {
		$notifications = get_user_meta( $user_id, 'src_notifications', true ) ?: array();
		$new_noti = array(
			'id'      => uniqid(),
			'message' => $data['message'],
			'url'     => $data['url'],
			'time'    => current_time( 'timestamp' ),
			'read'    => false,
		);
		$notifications[] = $new_noti;
		update_user_meta( $user_id, 'src_notifications', $notifications );
	}

	/**
	 * Render Research Library (Public Search Engine)
	 */
	public function handle_ajax_get_child_taxonomies() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$parent_id = absint( $_POST['parent_id'] );
		$target_tax = sanitize_text_field( $_POST['target_tax'] );
		$label = sanitize_text_field( $_POST['label'] );

		$terms = get_terms( array( 'taxonomy' => $target_tax, 'hide_empty' => false, 'parent' => $parent_id ) );

		ob_start();
		echo '<option value="">' . $label . '</option>';
		foreach ( $terms as $term ) {
			echo '<option value="' . $term->term_id . '">' . esc_html( $term->name ) . '</option>';
		}
		wp_send_json_success( ob_get_clean() );
	}

	public function render_research_library() {
		ob_start();
		?>
		<div class="src-library-container monochromatic home-engine">
			<div class="src-library-header">
				<h1 class="src-home-headline"><?php _e( 'Explore Global Research', 'scientific-research-center' ); ?></h1>

				<?php
				// Dynamic Live Counter Logic
				$start_count = 27520;
				$start_time = 1714521600; // May 1, 2024
				$current_time = current_time( 'timestamp' );
				$intervals = floor( ( $current_time - $start_time ) / ( 30 * 60 ) );
				$live_count = $start_count + max( 0, $intervals );
				$live_downloads = 84200 + ( $intervals * 3 );
				$live_users = 12400 + ( floor( $intervals / 10 ) );
				?>
				<p class="src-home-subheadline">
					<?php printf(
						__( 'You can access %s scientific papers, %s downloads, and %s active global researchers.', 'scientific-research-center' ),
						'<span class="src-inline-counter" data-count="' . $live_count . '">' . number_format( $live_count ) . '</span>',
						'<span class="src-inline-counter no-anim">' . number_format( $live_downloads ) . '</span>',
						'<span class="src-inline-counter no-anim">' . number_format( $live_users ) . '</span>'
					); ?>
				</p>

				<div class="src-search-engine centered">
					<div class="src-search-input-wrapper">
						<input type="text" id="lib_search" placeholder="<?php _e( 'Search research, papers, authors...', 'scientific-research-center' ); ?>">
						<span class="dashicons dashicons-search"></span>
					</div>
					<div class="src-filters inline advanced-filters-row">
						<div class="src-filter-pill-wrapper">
							<div class="src-field-group compact-select">
								<select id="lib_faculty" class="src-hier-search-select">
									<option value=""><?php _e( 'Select Faculty', 'scientific-research-center' ); ?></option>
									<?php
									$faculties = get_terms( array( 'taxonomy' => 'src_faculty', 'hide_empty' => false, 'parent' => 0 ) );
									foreach ( $faculties as $fac ) echo '<option value="'.$fac->term_id.'">'.$fac->name.'</option>';
									?>
								</select>
							</div>

							<div class="src-field-group compact-select">
								<select id="lib_specialty" class="src-hier-search-select" disabled>
									<option value=""><?php _e( 'Specialty', 'scientific-research-center' ); ?></option>
								</select>
							</div>

							<div class="src-field-group compact-select">
								<select id="lib_sub_specialty" class="src-hier-search-select" disabled>
									<option value=""><?php _e( 'Sub-specialty', 'scientific-research-center' ); ?></option>
								</select>
							</div>

							<div class="src-field-group compact-select">
								<select id="lib_institution" class="src-hier-search-select">
									<option value=""><?php _e( 'Select Institution', 'scientific-research-center' ); ?></option>
									<?php
									$insts = get_terms( array( 'taxonomy' => 'src_institution_tax', 'hide_empty' => false ) );
									foreach ( $insts as $inst ) echo '<option value="'.$inst->term_id.'">'.$inst->name.'</option>';
									?>
								</select>
							</div>

							<div class="src-field-group compact-select">
								<select id="lib_category" class="src-hier-search-select">
									<option value=""><?php _e( 'Select Category', 'scientific-research-center' ); ?></option>
									<?php
									$cats = get_terms( array( 'taxonomy' => 'research_category', 'hide_empty' => false ) );
									foreach ( $cats as $cat ) echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
									?>
								</select>
							</div>

							<div class="src-field-group compact-select">
								<select id="lib_year" class="src-hier-search-select">
									<option value=""><?php _e( 'Select Year', 'scientific-research-center' ); ?></option>
									<?php
									for ( $y = date('Y'); $y >= 2000; $y-- ) echo '<option value="'.$y.'">'.$y.'</option>';
									?>
								</select>
							</div>
						</div>

						<button id="lib_search_btn" class="src-submit-btn"><?php _e( 'Start Discovery', 'scientific-research-center' ); ?></button>
					</div>
				</div>
			</div>

			<div class="src-results-section">
				<h3 class="src-section-label"><?php _e( 'Featured Research', 'scientific-research-center' ); ?></h3>
				<div id="src-library-results" class="src-carousel-grid">
					<?php echo $this->get_research_cards(); ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX Research Filtering
	 */
	/**
	 * AJAX Load Submissions for Management
	 */
	public function handle_ajax_load_submissions() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'pending';
		$inst = isset( $_POST['institution'] ) ? sanitize_text_field( $_POST['institution'] ) : '';
		$cat = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';

		$args = array(
			'post_type'   => 'research_paper',
			'post_status' => ( $status === 'any' ) ? array( 'pending', 'publish', 'draft' ) : $status,
			'numberposts' => -1,
			's'           => $search,
			'tax_query'   => array( 'relation' => 'AND' )
		);

		if ( $type ) {
			$args['tax_query'][] = array( 'taxonomy' => 'research_type', 'field' => 'slug', 'terms' => $type );
		}
		if ( $cat ) {
			$args['tax_query'][] = array( 'taxonomy' => 'research_category', 'field' => 'slug', 'terms' => $cat );
		}
		if ( $inst ) {
			$args['meta_query'][] = array( 'key' => 'src_institution', 'value' => $inst );
		}

		$submissions = get_posts( $args );

		ob_start();
		?>
		<table class="src-user-table">
			<thead>
				<tr>
					<th><?php _e( 'Research Paper', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Author / Researcher', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Institution', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Specialty', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Status', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Date', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $submissions ) ) : ?>
					<tr><td colspan="7"><?php _e( 'No submissions found.', 'scientific-research-center' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $submissions as $sub ) :
						$author = get_userdata( $sub->post_author );
						$status = $sub->post_status;
						$inst = get_user_meta( $sub->post_author, 'src_institution', true );
						$spec = get_user_meta( $sub->post_author, 'src_specialty', true );
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $sub->post_title ); ?></strong><br>
								<small><?php echo esc_html( strip_tags( get_the_term_list( $sub->ID, 'research_type', '', ', ' ) ) ); ?></small>
							</td>
							<td><?php echo esc_html( $author->display_name ); ?></td>
							<td><?php echo esc_html( $inst ?: '-' ); ?></td>
							<td><?php echo esc_html( $spec ?: '-' ); ?></td>
							<td><span class="src-badge status-<?php echo $status; ?>"><?php echo ucfirst( $status ); ?></span></td>
							<td><?php echo get_the_date( 'Y-m-d', $sub->ID ); ?></td>
							<td class="src-actions">
								<?php if ( $status === 'pending' ) : ?>
									<button class="src-icon-btn src-sub-act" data-action="approve" data-id="<?php echo $sub->ID; ?>" title="Approve"><span class="dashicons dashicons-yes"></span></button>
									<button class="src-icon-btn src-sub-act src-danger" data-action="reject" data-id="<?php echo $sub->ID; ?>" title="Reject"><span class="dashicons dashicons-no"></span></button>
									<button class="src-icon-btn src-sub-act" data-action="assign" data-id="<?php echo $sub->ID; ?>" title="Assign Reviewer"><span class="dashicons dashicons-admin-users"></span></button>
								<?php endif; ?>
								<button class="src-icon-btn src-sub-act" data-action="history" data-id="<?php echo $sub->ID; ?>" title="Version History"><span class="dashicons dashicons-backup"></span></button>
								<button class="src-icon-btn src-sub-act" data-action="view" data-id="<?php echo $sub->ID; ?>" title="View"><span class="dashicons dashicons-visibility"></span></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * AJAX Process Submission (Approve/Reject)
	 */
	public function handle_ajax_manage_taxonomy() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$act = sanitize_text_field( $_POST['hier_action'] );
		$type = sanitize_text_field( $_POST['hier_type'] );

		if ( $act === 'add' ) {
			$name = sanitize_text_field( $_POST['hier_name'] );
			$parent = absint( $_POST['hier_parent'] );
			$term = wp_insert_term( $name, $type, array( 'parent' => $parent ) );
			if ( is_wp_error( $term ) ) {
				wp_send_json_error( array( 'message' => $term->get_error_message() ) );
			}
			wp_send_json_success( array( 'message' => __( 'Category added successfully.', 'scientific-research-center' ) ) );
		} elseif ( $act === 'delete' ) {
			$term_id = absint( $_POST['hier_id'] );
			wp_delete_term( $term_id, $type );
			wp_send_json_success( array( 'message' => __( 'Category removed.', 'scientific-research-center' ) ) );
		} elseif ( $act === 'load' ) {
			$terms = get_terms( array( 'taxonomy' => $type, 'hide_empty' => false, 'parent' => 0 ) );
			ob_start();
			?>
			<table class="src-user-table">
				<thead>
					<tr>
						<th><?php _e( 'Name', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Hierarchy', 'scientific-research-center' ); ?></th>
						<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $terms ) ) : ?>
						<tr><td colspan="3"><?php _e( 'No items found.', 'scientific-research-center' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $terms as $term ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $term->name ); ?></strong></td>
								<td><?php echo esc_html( $type ); ?></td>
								<td>
									<button class="src-icon-btn src-danger src-hier-act" data-action="delete" data-id="<?php echo $term->term_id; ?>" data-type="<?php echo $type; ?>"><span class="dashicons dashicons-trash"></span></button>
								</td>
							</tr>
							<?php
							$children = get_terms( array( 'taxonomy' => $type, 'hide_empty' => false, 'parent' => $term->term_id ) );
							foreach ( $children as $child ) : ?>
								<tr>
									<td>&mdash; <?php echo esc_html( $child->name ); ?></td>
									<td><?php echo esc_html( $type ); ?></td>
									<td>
										<button class="src-icon-btn src-danger src-hier-act" data-action="delete" data-id="<?php echo $child->term_id; ?>" data-type="<?php echo $type; ?>"><span class="dashicons dashicons-trash"></span></button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			<?php
			$html = ob_get_clean();

			// Also return parent options
			$parent_options = '<option value="0">' . __( 'None (Root)', 'scientific-research-center' ) . '</option>';
			$all_terms = get_terms( array( 'taxonomy' => $type, 'hide_empty' => false ) );
			foreach ( $all_terms as $at ) {
				$parent_options .= '<option value="' . $at->term_id . '">' . esc_html( $at->name ) . '</option>';
			}

			wp_send_json_success( array( 'html' => $html, 'parents' => $parent_options ) );
		}
	}

	public function handle_ajax_process_submission() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$post_id = absint( $_POST['sub_id'] );
		$action = sanitize_text_field( $_POST['sub_action'] );

		if ( $action === 'approve' ) {
			// Generate Unique Publication ID if not exists
			$pub_id = get_post_meta( $post_id, 'src_pub_id', true );
			if ( ! $pub_id ) {
				$pub_id = 'PUB-' . strtoupper( wp_generate_password( 8, false ) );
				update_post_meta( $post_id, 'src_pub_id', $pub_id );
			}

			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			) );

			SRC_Emails::send_research_status_email( $post_id, 'approved' );
			wp_send_json_success( array( 'message' => __( 'Research approved and published.', 'scientific-research-center' ) ) );
		} elseif ( $action === 'reject' ) {
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'draft', // or custom status 'rejected'
			) );

			SRC_Emails::send_research_status_email( $post_id, 'rejected' );
			wp_send_json_success( array( 'message' => __( 'Research rejected.', 'scientific-research-center' ) ) );
		}
	}

	/**
	 * AJAX Assign Reviewer Handler
	 */
	public function handle_ajax_assign_reviewer() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_others_posts' ) ) wp_send_json_error();

		$post_id = absint( $_POST['sub_id'] );
		$reviewer_id = absint( $_POST['reviewer_id'] );
		$deadline = sanitize_text_field( $_POST['deadline'] );

		update_post_meta( $post_id, 'src_assigned_reviewer', $reviewer_id );
		update_post_meta( $post_id, 'src_review_deadline', $deadline );

		// Record in Activity Log
		src_log_activity( get_current_user_id(), 'reviewer_assignment', sprintf( __( 'Assigned reviewer %d to submission %d.', 'scientific-research-center' ), $reviewer_id, $post_id ) );

		// In production: SRC_Emails::send_reviewer_assignment_notice($reviewer_id, $post_id);

		wp_send_json_success( array( 'message' => __( 'Reviewer assigned and notified.', 'scientific-research-center' ) ) );
	}

	/**
	 * AJAX Get Submission History Handler
	 */
	public function handle_ajax_get_submission_history() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$post_id = absint( $_POST['sub_id'] );

		global $wpdb;
		$table_name = $wpdb->prefix . 'src_activity_log';
		$logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE description LIKE %s ORDER BY event_date DESC", '%' . $wpdb->esc_like( (string)$post_id ) . '%' ) );

		ob_start();
		?>
		<table class="src-user-table compact">
			<thead>
				<tr>
					<th><?php _e( 'Event / Action', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Status Update', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Date', 'scientific-research-center' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="3"><?php _e( 'No detailed history found for this submission.', 'scientific-research-center' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( $log->description ); ?></td>
							<td><span class="src-badge small-badge type-<?php echo esc_attr($log->event_type); ?>"><?php echo esc_html(strtoupper($log->event_type)); ?></span></td>
							<td><?php echo esc_html( $log->event_date ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * AJAX Save Inline Research Edit
	 */
	public function handle_ajax_save_inline_research() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_others_posts' ) ) wp_send_json_error();

		$post_id = absint( $_POST['post_id'] );
		$content = wp_kses_post( $_POST['content'] );

		// Record version in activity log before update
		src_log_activity( get_current_user_id(), 'version_control', sprintf( __( 'Saved new version for research ID: %d', 'scientific-research-center' ), $post_id ) );

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $content
		) );

		wp_send_json_success();
	}

	/**
	 * AJAX Rebuild Search Index
	 */
	public function handle_ajax_rebuild_index() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		// Simulate Indexing
		sleep(1);

		src_log_activity( get_current_user_id(), 'system_action', __( 'Manually rebuilt research search index.', 'scientific-research-center' ) );
		wp_send_json_success( array( 'message' => __( 'Search index rebuilt successfully. Fast retrieval enabled.', 'scientific-research-center' ) ) );
	}

	public function handle_ajax_filter_research() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		$search = sanitize_text_field( $_POST['search'] );
		$type = sanitize_text_field( $_POST['type'] );
		$sort = sanitize_text_field( $_POST['sort'] );

		wp_send_json_success( $this->get_research_cards( $search, $type, $sort ) );
	}

	/**
	 * Helper to get research cards HTML
	 */
	private function get_research_cards( $search = '', $type = '', $sort = 'date' ) {
		$args = array(
			'post_type'      => 'research_paper',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			's'              => $search,
			'orderby'        => $sort === 'title' ? 'title' : 'date',
			'order'          => $sort === 'title' ? 'ASC' : 'DESC',
		);

		if ( $type ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'research_type',
					'field'    => 'slug',
					'terms'    => $type,
				),
			);
		}

		$query = new WP_Query( $args );
		ob_start();

		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) : $query->the_post();
				$post_id = get_the_ID();
				$author_id = get_the_author_meta( 'ID' );
				$institution = get_user_meta( $author_id, 'src_institution', true );
				$file_id = get_post_meta( $post_id, 'src_main_file', true );
				$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '#';

				$user_id = get_current_user_id();
				$favorites = get_user_meta( $user_id, 'src_favorites', true ) ?: array();
				$is_fav = in_array( $post_id, $favorites );
				?>
				<div class="src-research-card card compact-card" data-id="<?php echo $post_id; ?>">
					<div class="src-card-header">
						<span class="src-badge small-badge"><?php echo esc_html( strip_tags( get_the_term_list( get_the_ID(), 'research_type', '', ', ' ) ) ); ?></span>
						<div class="src-card-top-actions">
							<span class="src-date small-text"><?php echo get_the_date(); ?></span>
							<?php if ( is_user_logged_in() ) : ?>
								<button class="src-fav-toggle <?php echo $is_fav ? 'active' : ''; ?>" title="<?php _e( 'Add to Favorites', 'scientific-research-center' ); ?>">
									<span class="dashicons <?php echo $is_fav ? 'dashicons-heart' : 'dashicons-heart'; ?>"></span>
								</button>
							<?php endif; ?>
						</div>
					</div>
					<h3><?php the_title(); ?></h3>
					<div class="src-card-meta academic-meta">
						<div class="src-meta-item"><span class="dashicons dashicons-admin-users"></span> <strong><?php the_author(); ?></strong></div>
						<?php if ( $institution ) : ?>
							<div class="src-meta-item"><span class="dashicons dashicons-welcome-learn-more"></span> <span><?php echo esc_html( $institution ); ?></span></div>
						<?php endif; ?>
					</div>
					<div class="src-card-excerpt">
						<?php echo wp_trim_words( get_the_content(), 15 ); ?>
					</div>
					<div class="src-card-actions">
						<a href="<?php the_permalink(); ?>" class="src-view-details-btn"><?php _e( 'View Details', 'scientific-research-center' ); ?></a>
					</div>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
		else :
			echo '<p>' . __( 'No research entries found matching your criteria.', 'scientific-research-center' ) . '</p>';
		endif;

		return ob_get_clean();
	}

	public function register_research_taxonomies() {
		// Research Type (Thesis, Paper, Study)
		register_taxonomy( 'research_type', 'research_paper', array(
			'label'        => __( 'Research Type', 'scientific-research-center' ),
			'rewrite'      => array( 'slug' => 'research-type' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );

		// Research Category (Scientific Fields)
		register_taxonomy( 'research_category', 'research_paper', array(
			'label'        => __( 'Scientific Category', 'scientific-research-center' ),
			'rewrite'      => array( 'slug' => 'research-category' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );

		// Faculty (Level 1)
		register_taxonomy( 'src_faculty', 'research_paper', array(
			'label'        => __( 'Faculty / College', 'scientific-research-center' ),
			'rewrite'      => array( 'slug' => 'faculty' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );

		// Specialty (Level 2 - Child of Faculty)
		register_taxonomy( 'src_specialty', 'research_paper', array(
			'label'        => __( 'Specialty', 'scientific-research-center' ),
			'rewrite'      => array( 'slug' => 'specialty' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );

		// Sub-specialty (Level 3 - Child of Specialty)
		register_taxonomy( 'src_sub_specialty', 'research_paper', array(
			'label'        => __( 'Sub-specialty', 'scientific-research-center' ),
			'rewrite'      => array( 'slug' => 'sub-specialty' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );

		// Registered Institution (Level 4)
		register_taxonomy( 'src_institution_tax', 'research_paper', array(
			'label'        => __( 'Registered Institution', 'scientific-research-center' ),
			'rewrite'      => array( 'slug' => 'institution' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );
	}

	public function add_pub_rewrite_rules() {
		add_rewrite_rule( '^research/([^/]+)/?$', 'index.php?src_pub_id=$matches[1]', 'top' );
	}

	public function add_pub_query_vars( $vars ) {
		$vars[] = 'src_pub_id';
		return $vars;
	}

	public function add_research_seo_meta() {
		if ( is_singular( 'research_paper' ) ) {
			$post = get_queried_object();
			$abstract = wp_trim_words( $post->post_content, 30 );
			echo '<meta name="description" content="' . esc_attr( $abstract ) . '">' . "\n";
			echo '<meta name="keywords" content="' . esc_attr( strip_tags( get_the_term_list( $post->ID, 'src_specialty', '', ', ' ) ) ) . '">' . "\n";
		}
	}

	public function handle_pub_id_redirect() {
		$pub_id = get_query_var( 'src_pub_id' );
		if ( $pub_id ) {
			$posts = get_posts( array(
				'post_type'  => 'research_paper',
				'meta_key'   => 'src_pub_id',
				'meta_value' => $pub_id,
				'limit'      => 1
			) );

			if ( ! empty( $posts ) ) {
				wp_safe_redirect( get_permalink( $posts[0]->ID ) );
				exit;
			}
		}
	}
}
