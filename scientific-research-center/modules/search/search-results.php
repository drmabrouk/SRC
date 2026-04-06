<?php
/**
 * Research Search Results Template
 * Advanced filtering and results display.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_query = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$faculty = isset( $_GET['faculty'] ) ? absint( $_GET['faculty'] ) : 0;
$specialty = isset( $_GET['specialty'] ) ? absint( $_GET['specialty'] ) : 0;
$subspecialty = isset( $_GET['subspecialty'] ) ? absint( $_GET['subspecialty'] ) : 0;
$institution = isset( $_GET['institution'] ) ? absint( $_GET['institution'] ) : 0;
$category = isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
$pub_year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : 0;
$type_filter = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
$sort_by = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'relevance';

// Keyword highlighting helper
function src_highlight_keywords( $text, $query ) {
	if ( empty( $query ) ) return $text;
	$words = explode( ' ', $query );
	foreach ( $words as $word ) {
		$word = preg_quote( $word, '/' );
		$text = preg_replace( "/($word)/i", '<mark class="src-highlight">$1</mark>', $text );
	}
	return $text;
}
?>

<div class="src-results-page monochromatic">
	<div class="src-results-header">
		<div class="src-container">
			<h1><?php printf( __( 'Search Results for: "%s"', 'scientific-research-center' ), esc_html( $search_query ) ); ?></h1>
			<p><?php _e( 'Refine your discovery with advanced filters.', 'scientific-research-center' ); ?></p>
		</div>
	</div>

	<div class="src-results-layout src-container">
		<!-- Left Sidebar Filters -->
		<aside class="src-results-sidebar">
			<form id="src-advanced-filters" method="GET" action="<?php echo esc_url( home_url( '/research-results/' ) ); ?>">
				<input type="hidden" name="s" value="<?php echo esc_attr( $search_query ); ?>">

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Faculty / College', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<select name="faculty" id="res_faculty" class="src-filter-select">
							<option value="0"><?php _e( 'All Faculties', 'scientific-research-center' ); ?></option>
							<?php
							$facs = get_terms( array( 'taxonomy' => 'src_faculty', 'hide_empty' => false, 'parent' => 0 ) );
							foreach ( $facs as $fac ) echo '<option value="'.$fac->term_id.'" '.selected($faculty, $fac->term_id, false).'>'.$fac->name.'</option>';
							?>
						</select>
					</div>
				</div>

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Specialty', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<select name="specialty" id="res_specialty" class="src-filter-select" <?php echo ! $faculty ? 'disabled' : ''; ?>>
							<option value="0"><?php _e( 'All Specialties', 'scientific-research-center' ); ?></option>
							<?php
							if ( $faculty ) {
								$specs = get_terms( array( 'taxonomy' => 'src_specialty', 'hide_empty' => false, 'parent' => $faculty ) );
								foreach ( $specs as $spec ) echo '<option value="'.$spec->term_id.'" '.selected($specialty, $spec->term_id, false).'>'.$spec->name.'</option>';
							}
							?>
						</select>
					</div>
				</div>

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Scientific Category', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<select name="category" class="src-filter-select">
							<option value="0"><?php _e( 'All Categories', 'scientific-research-center' ); ?></option>
							<?php
							$cats = get_terms( array( 'taxonomy' => 'research_category', 'hide_empty' => false ) );
							foreach ( $cats as $cat ) echo '<option value="'.$cat->term_id.'" '.selected($category, $cat->term_id, false).'>'.$cat->name.'</option>';
							?>
						</select>
					</div>
				</div>

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Institution', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<select name="institution" class="src-filter-select">
							<option value="0"><?php _e( 'All Institutions', 'scientific-research-center' ); ?></option>
							<?php
							$insts = get_terms( array( 'taxonomy' => 'src_institution_tax', 'hide_empty' => false ) );
							foreach ( $insts as $inst ) echo '<option value="'.$inst->term_id.'" '.selected($institution, $inst->term_id, false).'>'.$inst->name.'</option>';
							?>
						</select>
					</div>
				</div>

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Publication Year', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<select name="year" class="src-filter-select">
							<option value="0"><?php _e( 'All Years', 'scientific-research-center' ); ?></option>
							<?php
							$current_year = date('Y');
							for ($y = $current_year; $y >= 2000; $y--) {
								echo '<option value="'.$y.'" '.selected($pub_year, $y, false).'>'.$y.'</option>';
							}
							?>
						</select>
					</div>
				</div>

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Research Type', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<label><input type="radio" name="type" value="" <?php checked( $type_filter, '' ); ?>> <?php _e( 'All Types', 'scientific-research-center' ); ?></label>
						<label><input type="radio" name="type" value="thesis" <?php checked( $type_filter, 'thesis' ); ?>> <?php _e( 'Theses', 'scientific-research-center' ); ?></label>
						<label><input type="radio" name="type" value="paper" <?php checked( $type_filter, 'paper' ); ?>> <?php _e( 'Scientific Papers', 'scientific-research-center' ); ?></label>
						<label><input type="radio" name="type" value="study" <?php checked( $type_filter, 'study' ); ?>> <?php _e( 'Case Studies', 'scientific-research-center' ); ?></label>
					</div>
				</div>

				<div class="src-filter-group collapsible expanded">
					<h3 class="src-filter-toggle"><?php _e( 'Sort Results', 'scientific-research-center' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="src-filter-content">
						<select name="sort" class="src-filter-select">
							<option value="relevance" <?php selected( $sort_by, 'relevance' ); ?>><?php _e( 'Relevance', 'scientific-research-center' ); ?></option>
							<option value="date_desc" <?php selected( $sort_by, 'date_desc' ); ?>><?php _e( 'Latest First', 'scientific-research-center' ); ?></option>
							<option value="date_asc" <?php selected( $sort_by, 'date_asc' ); ?>><?php _e( 'Oldest First', 'scientific-research-center' ); ?></option>
							<option value="title_asc" <?php selected( $sort_by, 'title_asc' ); ?>><?php _e( 'Title (A-Z)', 'scientific-research-center' ); ?></option>
							<option value="author_asc" <?php selected( $sort_by, 'author_asc' ); ?>><?php _e( 'Author (A-Z)', 'scientific-research-center' ); ?></option>
							<option value="inst_asc" <?php selected( $sort_by, 'inst_asc' ); ?>><?php _e( 'Institution (A-Z)', 'scientific-research-center' ); ?></option>
							<option value="views_desc" <?php selected( $sort_by, 'views_desc' ); ?>><?php _e( 'Most Viewed', 'scientific-research-center' ); ?></option>
						</select>
					</div>
				</div>

				<button type="submit" class="src-submit-btn full-width"><?php _e( 'Apply Filters', 'scientific-research-center' ); ?></button>
			</form>
		</aside>

		<!-- Main Results Content -->
		<main class="src-results-main">
			<div class="src-results-grid">
				<?php
				$args = array(
					'post_type'      => 'research_paper',
					'post_status'    => 'publish',
					's'              => $search_query,
					'posts_per_page' => 10,
					'paged'          => max( 1, get_query_var( 'paged' ) ),
				);

				// Apply Sorting
				switch ( $sort_by ) {
					case 'date_desc': $args['orderby'] = 'date'; $args['order'] = 'DESC'; break;
					case 'date_asc':  $args['orderby'] = 'date'; $args['order'] = 'ASC'; break;
					case 'title_asc': $args['orderby'] = 'title'; $args['order'] = 'ASC'; break;
					case 'author_asc': $args['orderby'] = 'author'; $args['order'] = 'ASC'; break;
					case 'inst_asc':
						$args['meta_key'] = 'src_institution';
						$args['orderby'] = 'meta_value';
						$args['order'] = 'ASC';
						break;
					case 'views_desc':
						$args['meta_key'] = 'src_views';
						$args['orderby'] = 'meta_value_num';
						$args['order'] = 'DESC';
						break;
				}

				// Apply Taxonomy Filters
				$tax_query = array( 'relation' => 'AND' );
				if ( $type_filter ) {
					$tax_query[] = array( 'taxonomy' => 'research_type', 'field' => 'slug', 'terms' => $type_filter );
				}
				if ( $faculty ) {
					$tax_query[] = array( 'taxonomy' => 'src_faculty', 'field' => 'term_id', 'terms' => $faculty );
				}
				if ( $specialty ) {
					$tax_query[] = array( 'taxonomy' => 'src_specialty', 'field' => 'term_id', 'terms' => $specialty );
				}
				if ( $subspecialty ) {
					$tax_query[] = array( 'taxonomy' => 'src_sub_specialty', 'field' => 'term_id', 'terms' => $subspecialty );
				}
				if ( $institution ) {
					$tax_query[] = array( 'taxonomy' => 'src_institution_tax', 'field' => 'term_id', 'terms' => $institution );
				}
				if ( $category ) {
					$tax_query[] = array( 'taxonomy' => 'research_category', 'field' => 'term_id', 'terms' => $category );
				}

				if ( count( $tax_query ) > 1 ) {
					$args['tax_query'] = $tax_query;
				}

				// Year Filter Logic
				if ( $pub_year ) {
					$args['date_query'] = array(
						array(
							'year' => $pub_year,
						),
					);
				}

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) : $query->the_post();
						$post_id = get_the_ID();
						$author_id = get_the_author_meta( 'ID' );
						$institution = get_user_meta( $author_id, 'src_institution', true );
						$type = strip_tags( get_the_term_list( $post_id, 'research_type', '', ', ' ) );

						$user_id = get_current_user_id();
						$favorites = get_user_meta( $user_id, 'src_favorites', true ) ?: array();
						$is_fav = in_array( $post_id, $favorites );
						?>
						<div class="src-research-card slide-entry card compact-card" data-id="<?php echo $post_id; ?>">
							<div class="src-card-header">
								<span class="src-badge small-badge"><?php echo esc_html( $type ); ?></span>
								<div class="src-card-top-actions">
									<span class="src-date small-text"><?php echo get_the_date(); ?></span>
									<?php if ( is_user_logged_in() ) : ?>
										<button class="src-fav-toggle <?php echo $is_fav ? 'active' : ''; ?>" title="<?php _e( 'Add to Favorites', 'scientific-research-center' ); ?>">
											<span class="dashicons <?php echo $is_fav ? 'dashicons-heart' : 'dashicons-heart'; ?>"></span>
										</button>
									<?php endif; ?>
								</div>
							</div>
							<h3><?php echo src_highlight_keywords( get_the_title(), $search_query ); ?></h3>
							<div class="src-card-meta academic-meta">
								<a href="<?php echo esc_url( home_url( '/researcher/' . get_the_author_meta( 'user_login' ) . '/' ) ); ?>" class="src-meta-item src-link-item"><span class="dashicons dashicons-admin-users"></span> <strong><?php the_author(); ?></strong></a>
								<?php if ( $institution ) : ?>
									<a href="<?php echo esc_url( add_query_arg( 'institution', urlencode( $institution ), home_url( '/research-results/' ) ) ); ?>" class="src-meta-item src-link-item"><span class="dashicons dashicons-welcome-learn-more"></span> <span><?php echo esc_html( $institution ); ?></span></a>
								<?php endif; ?>
							</div>
							<div class="src-card-excerpt">
								<?php echo src_highlight_keywords( wp_trim_words( get_the_content(), 20 ), $search_query ); ?>
							</div>
							<div class="src-card-actions">
								<a href="<?php the_permalink(); ?>" class="src-view-details-btn"><?php _e( 'View Details', 'scientific-research-center' ); ?></a>
							</div>
						</div>
						<?php
					endwhile;

					// Pagination
					echo '<div class="src-pagination">';
					echo paginate_links( array(
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'total'     => $query->max_num_pages,
						'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
						'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>',
					) );
					echo '</div>';

					wp_reset_postdata();
				else :
					echo '<div class="src-no-results">';
					echo '<h3>' . __( 'No research found matching your criteria.', 'scientific-research-center' ) . '</h3>';
					echo '<p>' . __( 'Try broadening your search or using different filters.', 'scientific-research-center' ) . '</p>';
					echo '</div>';
				endif;
				?>
			</div>
		</main>
	</div>
</div>

<?php
get_footer();
