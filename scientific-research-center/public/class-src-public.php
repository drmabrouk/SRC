<?php
/**
 * Frontend functionality for the plugin.
 */
class SRC_Public {

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
	 * Enqueue frontend styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'src-public-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/src-public.css', array(), $this->version, 'all' );

		if ( is_rtl() ) {
			wp_enqueue_style( 'src-rtl-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/src-rtl.css', array( 'src-public-style' ), $this->version, 'all' );
		}
	}

	/**
	 * Enqueue frontend scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'src-public-script', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/src-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Render the Research Engine shortcode.
	 */
	public function render_research_engine() {
		ob_start();
		?>
		<div class="src-research-engine">
			<div class="src-hero-stats">
				<div class="stat-item">
					<span class="stat-label"><?php _e( 'Total Papers', 'scientific-research-center' ); ?></span>
					<span class="stat-value"><?php echo wp_count_posts( 'research_paper' )->publish; ?></span>
				</div>
				<div class="stat-item">
					<span class="stat-label"><?php _e( 'Citations', 'scientific-research-center' ); ?></span>
					<span class="stat-value"><?php echo self::get_total_citations(); ?></span>
				</div>
				<div class="stat-item">
					<span class="stat-label"><?php _e( 'Active Researchers', 'scientific-research-center' ); ?></span>
					<span class="stat-value"><?php echo count_users()['avail_roles']['src_author'] ?? 0; ?></span>
				</div>
			</div>
			<div class="src-search-bar">
				<form action="<?php echo get_permalink( get_option( 'src_search_page_id' ) ); ?>" method="get">
					<input type="text" name="src_query" placeholder="<?php _e( 'Search research papers...', 'scientific-research-center' ); ?>" required>
					<button type="submit"><?php _e( 'Search', 'scientific-research-center' ); ?></button>
				</form>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Helper function to get total citations.
	 */
	private static function get_total_citations() {
		// Mock citation count for now.
		return 1250;
	}

	/**
	 * Render the Search Results shortcode.
	 */
	public function render_search_results() {
		$search_query = isset( $_GET['src_query'] ) ? sanitize_text_field( $_GET['src_query'] ) : '';
		$category_filter = isset( $_GET['src_category'] ) ? sanitize_text_field( $_GET['src_category'] ) : '';
		$author_filter = isset( $_GET['src_author'] ) ? sanitize_text_field( $_GET['src_author'] ) : '';

		$args = array(
			'post_type'      => 'research_paper',
			'posts_per_page' => 10,
			's'              => $search_query,
			'tax_query'      => array(),
			'meta_query'     => array(),
		);

		if ( ! empty( $category_filter ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'scientific_category',
				'field'    => 'slug',
				'terms'    => $category_filter,
			);
		}

		if ( ! empty( $author_filter ) ) {
			$args['author_name'] = $author_filter;
		}

		$query = new WP_Query( $args );

		ob_start();
		?>
		<div class="src-search-container">
			<aside class="src-sidebar-filter">
				<h3><?php _e( 'Filter Results', 'scientific-research-center' ); ?></h3>
				<form action="" method="get">
					<input type="hidden" name="src_query" value="<?php echo esc_attr( $search_query ); ?>">

					<label for="src_category"><?php _e( 'Category:', 'scientific-research-center' ); ?></label>
					<select name="src_category" id="src_category">
						<option value=""><?php _e( 'All Categories', 'scientific-research-center' ); ?></option>
						<?php
						$categories = get_terms( array( 'taxonomy' => 'scientific_category', 'hide_empty' => false ) );
						foreach ( $categories as $cat ) {
							echo '<option value="' . esc_attr( $cat->slug ) . '" ' . selected( $category_filter, $cat->slug, false ) . '>' . esc_html( $cat->name ) . '</option>';
						}
						?>
					</select>

					<label for="src_date_range"><?php _e( 'Date Range:', 'scientific-research-center' ); ?></label>
					<select name="src_date_range" id="src_date_range">
						<option value=""><?php _e( 'All Time', 'scientific-research-center' ); ?></option>
						<option value="last_year"><?php _e( 'Last Year', 'scientific-research-center' ); ?></option>
					</select>

					<button type="submit"><?php _e( 'Apply Filters', 'scientific-research-center' ); ?></button>
				</form>
			</aside>

			<main class="src-results-list">
				<?php if ( $query->have_posts() ) : ?>
					<div class="src-grid-view">
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<article class="src-paper-item">
								<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
								<p class="src-paper-meta">
									<?php _e( 'By:', 'scientific-research-center' ); ?> <?php the_author(); ?> |
									<?php the_date(); ?>
								</p>
								<div class="src-paper-excerpt">
									<?php the_excerpt(); ?>
								</div>
							</article>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				<?php else : ?>
					<p><?php _e( 'No research papers found matching your criteria.', 'scientific-research-center' ); ?></p>
				<?php endif; ?>
			</main>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the Submission Wizard shortcode.
	 */
	public function render_submission_wizard() {
		if ( isset( $_POST['paper_title'] ) && isset( $_POST['src_submission_nonce'] ) ) {
			if ( wp_verify_nonce( $_POST['src_submission_nonce'], 'src_submit_paper' ) ) {
				$this->handle_submission();
			} else {
				echo '<div class="error"><p>' . __( 'Security check failed.', 'scientific-research-center' ) . '</p></div>';
			}
		}

		if ( ! is_user_logged_in() ) {
			$login_url = get_permalink( get_option( 'src_auth_page_id' ) );
			return sprintf(
				'<div class="src-cta-message">%s <a href="%s">%s</a></div>',
				__( 'To submit your research, please', 'scientific-research-center' ),
				esc_url( $login_url ),
				__( 'Login or Register.', 'scientific-research-center' )
			);
		}

		ob_start();
		?>
		<div class="src-submission-wizard">
			<ul class="src-wizard-steps">
				<li class="active"><?php _e( '1. Details', 'scientific-research-center' ); ?></li>
				<li><?php _e( '2. Upload', 'scientific-research-center' ); ?></li>
				<li><?php _e( '3. Review', 'scientific-research-center' ); ?></li>
			</ul>
			<form id="src-submission-form" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'src_submit_paper', 'src_submission_nonce' ); ?>
				<div class="src-step-content" id="step-1">
					<label for="paper_title"><?php _e( 'Paper Title:', 'scientific-research-center' ); ?></label>
					<input type="text" name="paper_title" required>

					<label for="paper_abstract"><?php _e( 'Abstract:', 'scientific-research-center' ); ?></label>
					<textarea name="paper_abstract" required></textarea>
				</div>
				<button type="button" id="src-next-step"><?php _e( 'Next', 'scientific-research-center' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle the backend submission logic.
	 */
	private function handle_submission() {
		if ( ! is_user_logged_in() || ! current_user_can( 'upload_src_papers' ) || ! isset( $_FILES['paper_file'] ) ) {
			return;
		}

		$validation = SRC_Security::validate_upload( $_FILES['paper_file'] );
		if ( is_wp_error( $validation ) ) {
			echo '<div class="error"><p>' . $validation->get_error_message() . '</p></div>';
			return;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'research_paper',
			'post_title'   => sanitize_text_field( $_POST['paper_title'] ),
			'post_content' => wp_kses_post( $_POST['paper_abstract'] ),
			'post_status'  => 'pending',
			'post_author'  => get_current_user_id(),
		) );

		if ( $post_id ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$attachment_id = media_handle_upload( 'paper_file', $post_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_post_meta( $post_id, 'src_paper_file', $attachment_id );
			}
			echo '<div class="updated"><p>' . __( 'Your research has been submitted for review.', 'scientific-research-center' ) . '</p></div>';
		}
	}

	/**
	 * Render the Authentication Portal shortcode.
	 */
	public function render_auth_portal() {
		if ( is_user_logged_in() ) {
			return '<p>' . __( 'You are already logged in.', 'scientific-research-center' ) . '</p>';
		}

		ob_start();
		?>
		<div class="src-auth-portal">
			<div class="src-login-form">
				<h3><?php _e( 'Login', 'scientific-research-center' ); ?></h3>
				<?php wp_login_form(); ?>
			</div>
			<div class="src-register-cta">
				<h3><?php _e( 'New Researcher?', 'scientific-research-center' ); ?></h3>
				<p><?php _e( 'Join the Healthedia scientific community to publish your research.', 'scientific-research-center' ); ?></p>
				<a href="<?php echo wp_registration_url(); ?>" class="button"><?php _e( 'Register Now', 'scientific-research-center' ); ?></a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the User Profile shortcode.
	 */
	public function render_user_profile() {
		$username = get_query_var( 'src_username' );
		$user = $username ? get_user_by( 'slug', $username ) : wp_get_current_user();

		if ( ! $user || ! $user->exists() ) {
			return '<p>' . __( 'User not found.', 'scientific-research-center' ) . '</p>';
		}

		ob_start();
		?>
		<div class="src-user-profile">
			<div class="src-profile-header">
				<?php echo get_avatar( $user->ID, 128 ); ?>
				<h2><?php echo esc_html( $user->display_name ); ?></h2>
				<p class="src-user-bio"><?php echo wp_kses_post( $user->description ); ?></p>
			</div>

			<div class="src-user-publications">
				<h3><?php _e( 'Publications', 'scientific-research-center' ); ?></h3>
				<?php
				$papers = new WP_Query( array(
					'post_type'      => 'research_paper',
					'author'         => $user->ID,
					'posts_per_page' => -1,
				) );

				if ( $papers->have_posts() ) :
					echo '<ul>';
					while ( $papers->have_posts() ) : $papers->the_post();
						echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a> (' . get_the_date() . ')</li>';
					endwhile;
					echo '</ul>';
					wp_reset_postdata();
				else :
					echo '<p>' . __( 'No publications found.', 'scientific-research-center' ) . '</p>';
				endif;
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
