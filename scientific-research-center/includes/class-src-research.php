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
		add_shortcode( 'src_submit_research', array( $this, 'render_submission_form' ) );
		add_shortcode( 'src_research_library', array( $this, 'render_research_library' ) );
		add_action( 'wp_ajax_src_submit_research', array( $this, 'handle_ajax_submission' ) );
		add_action( 'wp_ajax_src_filter_research', array( $this, 'handle_ajax_filter_research' ) );
		add_action( 'wp_ajax_src_load_submissions', array( $this, 'handle_ajax_load_submissions' ) );
		add_action( 'wp_ajax_src_process_submission', array( $this, 'handle_ajax_process_submission' ) );
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
			return sprintf( '<p>%s <a href="%s">%s</a></p>',
				__( 'Please login to submit research.', 'scientific-research-center' ),
				home_url( '/login-register/' ),
				__( 'Login here', 'scientific-research-center' )
			);
		}

		$user = wp_get_current_user();
		$institution = get_user_meta( $user->ID, 'src_institution', true );

		ob_start();
		?>
		<div class="src-submission-container monochromatic">
			<div class="src-welcome-msg">
				<h2><?php _e( 'Submit Research', 'scientific-research-center' ); ?></h2>
				<p><?php _e( 'Share your scientific work with the global research community.', 'scientific-research-center' ); ?></p>
			</div>

			<form id="src-research-submission-action" enctype="multipart/form-data">
				<div class="src-field-group">
					<input type="text" name="title" id="res_title" placeholder=" " required>
					<label for="res_title"><?php _e( 'Research Title', 'scientific-research-center' ); ?></label>
				</div>

				<div class="src-field-group">
					<textarea name="abstract" id="res_abstract" placeholder=" " required style="height:120px;"></textarea>
					<label for="res_abstract"><?php _e( 'Abstract', 'scientific-research-center' ); ?></label>
				</div>

				<div class="src-field-row">
					<div class="src-field-group">
						<select name="type" id="res_type" required>
							<option value="thesis"><?php _e( 'Thesis', 'scientific-research-center' ); ?></option>
							<option value="paper"><?php _e( 'Paper', 'scientific-research-center' ); ?></option>
							<option value="study"><?php _e( 'Study', 'scientific-research-center' ); ?></option>
						</select>
						<label for="res_type" class="select-label"><?php _e( 'Research Type', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<input type="text" name="keywords" id="res_keywords" placeholder=" ">
						<label for="res_keywords"><?php _e( 'Keywords (Comma separated)', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<div class="src-field-row">
					<div class="src-field-group">
						<input type="text" value="<?php echo esc_attr( $user->display_name ); ?>" disabled>
						<label><?php _e( 'Author Name', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<input type="text" value="<?php echo esc_attr( $institution ); ?>" disabled>
						<label><?php _e( 'Institution', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<div class="src-field-group">
					<input type="file" name="research_file" id="res_file" accept=".pdf,.doc,.docx" required>
					<label for="res_file" class="file-label"><?php _e( 'Full Research File (PDF/DOCX)', 'scientific-research-center' ); ?></label>
				</div>

				<div class="src-field-group">
					<input type="file" name="supporting_files[]" id="res_supporting" multiple>
					<label for="res_supporting" class="file-label"><?php _e( 'Supporting Files (Optional)', 'scientific-research-center' ); ?></label>
				</div>

				<button type="submit" class="src-submit-btn"><?php _e( 'Submit for Review', 'scientific-research-center' ); ?></button>
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

		// Handle Taxonomies
		wp_set_object_terms( $post_id, $type, 'research_type' );
		if ( ! empty( $keywords ) ) {
			wp_set_object_terms( $post_id, explode( ',', $keywords ), 'post_tag' );
		}

		// Handle Main File
		if ( ! empty( $_FILES['research_file'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			$file_id = media_handle_upload( 'research_file', $post_id );
			if ( ! is_wp_error( $file_id ) ) {
				update_post_meta( $post_id, 'src_main_file', $file_id );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Research submitted successfully! It is now pending review.', 'scientific-research-center' ) ) );
	}

	/**
	 * Render Research Library (Public Search Engine)
	 */
	public function render_research_library() {
		ob_start();
		?>
		<div class="src-library-container monochromatic home-engine">
			<div class="src-library-header">
				<h2><?php _e( 'Global Scientific Research Engine', 'scientific-research-center' ); ?></h2>
				<div class="src-search-engine centered">
					<input type="text" id="lib_search" placeholder="<?php _e( 'Search for research, papers, or authors...', 'scientific-research-center' ); ?>">
					<div class="src-filters inline">
						<select id="lib_type">
							<option value=""><?php _e( 'All Research Types', 'scientific-research-center' ); ?></option>
							<option value="thesis"><?php _e( 'Theses', 'scientific-research-center' ); ?></option>
							<option value="paper"><?php _e( 'Scientific Papers', 'scientific-research-center' ); ?></option>
							<option value="study"><?php _e( 'Case Studies', 'scientific-research-center' ); ?></option>
						</select>
						<select id="lib_sort">
							<option value="date"><?php _e( 'Latest First', 'scientific-research-center' ); ?></option>
							<option value="title"><?php _e( 'Alphabetical (A-Z)', 'scientific-research-center' ); ?></option>
						</select>
						<button id="lib_filter_btn" class="src-submit-btn"><?php _e( 'Search Platform', 'scientific-research-center' ); ?></button>
					</div>
				</div>
			</div>

			<div id="src-library-results" class="src-card-grid">
				<?php echo $this->get_research_cards(); ?>
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

		$submissions = get_posts( array(
			'post_type'   => 'research_paper',
			'post_status' => array( 'pending', 'publish', 'draft' ),
			'numberposts' => -1,
		) );

		ob_start();
		?>
		<table class="src-user-table">
			<thead>
				<tr>
					<th><?php _e( 'Research', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Author', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Status', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Date', 'scientific-research-center' ); ?></th>
					<th><?php _e( 'Actions', 'scientific-research-center' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $submissions ) ) : ?>
					<tr><td colspan="5"><?php _e( 'No submissions found.', 'scientific-research-center' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $submissions as $sub ) :
						$author = get_userdata( $sub->post_author );
						$status = $sub->post_status;
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $sub->post_title ); ?></strong><br>
								<small><?php echo esc_html( strip_tags( get_the_term_list( $sub->ID, 'research_type', '', ', ' ) ) ); ?></small>
							</td>
							<td><?php echo esc_html( $author->display_name ); ?></td>
							<td><span class="src-badge status-<?php echo $status; ?>"><?php echo ucfirst( $status ); ?></span></td>
							<td><?php echo get_the_date( '', $sub->ID ); ?></td>
							<td class="src-actions">
								<?php if ( $status === 'pending' ) : ?>
									<button class="src-icon-btn src-sub-act" data-action="approve" data-id="<?php echo $sub->ID; ?>" title="Approve"><span class="dashicons dashicons-yes"></span></button>
									<button class="src-icon-btn src-sub-act src-danger" data-action="reject" data-id="<?php echo $sub->ID; ?>" title="Reject"><span class="dashicons dashicons-no"></span></button>
								<?php endif; ?>
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
	public function handle_ajax_process_submission() {
		check_ajax_referer( 'src_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied.', 'scientific-research-center' ) ) );
		}

		$post_id = absint( $_POST['sub_id'] );
		$action = sanitize_text_field( $_POST['sub_action'] );

		if ( $action === 'approve' ) {
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			) );
			wp_send_json_success( array( 'message' => __( 'Research approved and published.', 'scientific-research-center' ) ) );
		} elseif ( $action === 'reject' ) {
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'draft', // or custom status 'rejected'
			) );
			wp_send_json_success( array( 'message' => __( 'Research rejected.', 'scientific-research-center' ) ) );
		}
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
				$author_id = get_the_author_meta( 'ID' );
				$institution = get_user_meta( $author_id, 'src_institution', true );
				$file_id = get_post_meta( get_the_ID(), 'src_main_file', true );
				$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '#';
				?>
				<div class="src-research-card card">
					<div class="src-card-header">
						<span class="src-badge"><?php echo esc_html( strip_tags( get_the_term_list( get_the_ID(), 'research_type', '', ', ' ) ) ); ?></span>
						<span class="src-date"><?php echo get_the_date(); ?></span>
					</div>
					<h3><?php the_title(); ?></h3>
					<div class="src-card-meta">
						<strong><?php the_author(); ?></strong>
						<?php if ( $institution ) : ?>
							<span> @ <?php echo esc_html( $institution ); ?></span>
						<?php endif; ?>
					</div>
					<div class="src-card-excerpt">
						<?php echo wp_trim_words( get_the_content(), 20 ); ?>
					</div>
					<div class="src-card-actions">
						<a href="<?php the_permalink(); ?>" class="src-btn-outline"><?php _e( 'View Details', 'scientific-research-center' ); ?></a>
						<?php if ( $file_id ) : ?>
							<a href="<?php echo esc_url( $file_url ); ?>" class="src-submit-btn" download><?php _e( 'Download', 'scientific-research-center' ); ?></a>
						<?php endif; ?>
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
	}
}
