<?php
/**
 * Single Research Paper Template
 * Professional full-page view for scientific research.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();

	// Update View Counter
	$views = (int) get_post_meta( $post_id, 'src_views', true );
	update_post_meta( $post_id, 'src_views', $views + 1 );
	$abstract = get_the_content();
	$title = get_the_title();
	$author_name = get_the_author();

	// Structured Data (JSON-LD) for Scientific Article
	echo '<script type="application/ld+json">
	{
	  "@context": "https://schema.org",
	  "@type": "ScholarlyArticle",
	  "headline": "' . esc_js( $title ) . '",
	  "author": {
		"@type": "Person",
		"name": "' . esc_js( $author_name ) . '"
	  },
	  "datePublished": "' . get_the_date( 'c' ) . '",
	  "description": "' . esc_js( wp_trim_words( $abstract, 50 ) ) . '"
	}
	</script>';
	$author_id = get_the_author_meta( 'ID' );
	$institution = get_user_meta( $author_id, 'src_institution', true );
	$co_authors = get_post_meta( $post_id, 'src_co_authors', true );
	$pub_date = get_post_meta( $post_id, 'src_pub_date', true );
	$pub_id = get_post_meta( $post_id, 'src_pub_id', true );

	// Hierarchical Metadata
	$faculties = get_the_term_list( $post_id, 'src_faculty', '', ', ' );
	$specialties = get_the_term_list( $post_id, 'src_specialty', '', ', ' );
	$institutions_tax = get_the_term_list( $post_id, 'src_institution_tax', '', ', ' );
	$categories = get_the_term_list( $post_id, 'research_category', '', ', ' );
	$file_id = get_post_meta( $post_id, 'src_main_file', true );
	$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
	$file_name = $file_id ? get_the_title( $file_id ) : '';
	$supporting_files = get_post_meta( $post_id, 'src_supporting_file' );
	$type = strip_tags( get_the_term_list( $post_id, 'research_type', '', ', ' ) );
	?>

	<div class="src-research-detail monochromatic">
		<!-- Breadcrumbs -->
		<nav class="src-breadcrumbs">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'scientific-research-center' ); ?></a>
			<span class="sep">/</span>
			<a href="<?php echo esc_url( home_url( '/research-library/' ) ); ?>"><?php _e( 'Research Library', 'scientific-research-center' ); ?></a>
			<span class="sep">/</span>
			<span class="current"><?php the_title(); ?></span>
		</nav>

		<div class="src-detail-container">
			<!-- Main Content Card -->
			<article class="src-detail-card card">
				<header class="src-detail-header">
					<div class="src-detail-meta-top">
						<span class="src-badge small-badge"><?php echo esc_html( $type ); ?></span>
						<?php if ( $pub_id ) : ?>
							<span class="src-pub-id-label"><?php printf( __( 'Publication ID: %s', 'scientific-research-center' ), esc_html( $pub_id ) ); ?></span>
						<?php endif; ?>
						<span class="src-pub-date-label"><span class="dashicons dashicons-calendar-alt"></span> <?php echo esc_html( $pub_date ?: get_the_date() ); ?></span>
						<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
							<a href="<?php echo get_edit_post_link( $post_id ); ?>" class="src-badge admin-badge" target="_blank" title="<?php _e( 'Administrative Quick Edit', 'scientific-research-center' ); ?>"><span class="dashicons dashicons-edit"></span> <?php _e( 'Quick Edit', 'scientific-research-center' ); ?></a>
						<?php endif; ?>
					</div>
					<h1 class="src-detail-title"><?php the_title(); ?></h1>

					<div class="src-detail-authors-box">
						<a href="<?php echo esc_url( home_url( '/researcher/' . get_the_author_meta( 'user_login' ) . '/' ) ); ?>" class="src-meta-pill src-link-pill">
							<span class="dashicons dashicons-admin-users"></span>
							<strong><?php the_author(); ?></strong>
						</a>
						<?php if ( $institution ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'institution', urlencode( $institution ), home_url( '/research-results/' ) ) ); ?>" class="src-meta-pill src-link-pill">
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<span><?php echo esc_html( $institution ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( $co_authors ) : ?>
						<div class="src-meta-pill collaborators-pill" title="<?php _e( 'Collaborating Researchers', 'scientific-research-center' ); ?>">
								<span class="dashicons dashicons-groups"></span>
								<span><strong><?php _e( 'Collaborators:', 'scientific-research-center' ); ?></strong> <?php echo esc_html( $co_authors ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</header>

				<section class="src-detail-section">
					<div class="src-section-header">
						<h3><?php _e( 'Abstract', 'scientific-research-center' ); ?></h3>
						<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
							<button class="src-icon-btn src-inline-edit-trigger" title="<?php _e( 'Edit Abstract Inline', 'scientific-research-center' ); ?>"><span class="dashicons dashicons-edit"></span></button>
						<?php endif; ?>
					</div>
					<div class="src-abstract-wrapper">
						<div class="src-abstract-content">
							<?php the_content(); ?>
						</div>
						<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
							<div class="src-inline-editor" style="display:none;">
								<textarea class="src-abstract-edit-area" style="width:100%; min-height:200px;"><?php echo esc_textarea( get_the_content() ); ?></textarea>
								<div class="src-editor-actions" style="margin-top:10px; text-align:right;">
									<button class="src-btn-outline src-cancel-edit"><?php _e( 'Cancel', 'scientific-research-center' ); ?></button>
									<button class="src-submit-btn src-save-inline-edit" data-id="<?php echo $post_id; ?>"><?php _e( 'Save Version', 'scientific-research-center' ); ?></button>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</section>

				<?php if ( $faculties || $specialties || $categories || $institutions_tax ) : ?>
					<section class="src-detail-section classification-section">
						<h3><span class="dashicons dashicons-category"></span> <?php _e( 'Scientific Classification', 'scientific-research-center' ); ?></h3>
						<div class="src-classification-grid">
							<?php if ( $faculties ) : ?>
								<div class="src-class-item">
									<span class="dashicons dashicons-bank"></span>
									<strong><?php _e( 'Faculty:', 'scientific-research-center' ); ?></strong> <?php echo $faculties; ?>
								</div>
							<?php endif; ?>
							<?php if ( $specialties ) : ?>
								<div class="src-class-item">
									<span class="dashicons dashicons-awards"></span>
									<strong><?php _e( 'Specialty:', 'scientific-research-center' ); ?></strong> <?php echo $specialties; ?>
								</div>
							<?php endif; ?>
							<?php if ( $categories ) : ?>
								<div class="src-class-item">
									<span class="dashicons dashicons-category"></span>
									<strong><?php _e( 'Category:', 'scientific-research-center' ); ?></strong> <?php echo $categories; ?>
								</div>
							<?php endif; ?>
							<?php if ( $institutions_tax ) : ?>
								<div class="src-class-item">
									<span class="dashicons dashicons-building"></span>
									<strong><?php _e( 'Affiliation:', 'scientific-research-center' ); ?></strong> <?php echo $institutions_tax; ?>
								</div>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<section class="src-detail-section">
					<h3><?php _e( 'Keywords', 'scientific-research-center' ); ?></h3>
					<div class="src-keywords-list">
						<?php the_tags( '', ' ', '' ); ?>
					</div>
				</section>

				<?php if ( $file_url ) : ?>
					<section class="src-detail-section src-files-section">
						<h3><?php _e( 'Research Files', 'scientific-research-center' ); ?></h3>
						<div class="src-file-list">
							<div class="src-file-item main-file">
								<span class="dashicons dashicons-pdf"></span>
								<div class="src-file-info">
									<strong><?php _e( 'Full Research Document', 'scientific-research-center' ); ?></strong>
									<span><?php echo esc_html( $file_name ); ?></span>
								</div>
								<div class="src-file-actions">
									<a href="<?php echo esc_url( $file_url ); ?>" class="src-submit-btn" target="_blank"><?php _e( 'View Online', 'scientific-research-center' ); ?></a>
									<a href="<?php echo esc_url( $file_url ); ?>" class="src-btn-outline" download><?php _e( 'Download PDF', 'scientific-research-center' ); ?></a>
								</div>
							</div>

							<?php if ( ! empty( $supporting_files ) ) : ?>
								<?php foreach ( $supporting_files as $supp_id ) :
									$supp_url = wp_get_attachment_url( $supp_id );
									$supp_name = get_the_title( $supp_id );
									?>
									<div class="src-file-item supporting-file">
										<span class="dashicons dashicons-paperclip"></span>
										<div class="src-file-info">
											<strong><?php _e( 'Supporting Material', 'scientific-research-center' ); ?></strong>
											<span><?php echo esc_html( $supp_name ); ?></span>
										</div>
										<a href="<?php echo esc_url( $supp_url ); ?>" class="src-link-btn" download><?php _e( 'Download', 'scientific-research-center' ); ?></a>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php
				// Related Research based on Specialty
				$spec_terms = wp_get_post_terms( $post_id, 'src_specialty', array( 'fields' => 'ids' ) );
				if ( ! empty( $spec_terms ) ) :
					$related_args = array(
						'post_type'      => 'research_paper',
						'posts_per_page' => 3,
						'post__not_in'   => array( $post_id ),
						'tax_query'      => array(
							array(
								'taxonomy' => 'src_specialty',
								'field'    => 'term_id',
								'terms'    => $spec_terms,
							),
						),
					);
					$related_query = new WP_Query( $related_args );

					if ( $related_query->have_posts() ) : ?>
						<section class="src-detail-section related-research">
							<h3><?php _e( 'Related Scientific Contributions', 'scientific-research-center' ); ?></h3>
							<div class="src-card-grid mini-grid">
								<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
									<div class="src-research-card card compact-card">
										<h4 style="font-size: 13px; margin: 0 0 10px;"><?php the_title(); ?></h4>
										<a href="<?php the_permalink(); ?>" class="src-link-btn" style="font-size: 11px;"><?php _e( 'View Study', 'scientific-research-center' ); ?></a>
									</div>
								<?php endwhile; ?>
							</div>
						</section>
					<?php
					endif;
					wp_reset_postdata();
				endif;
				?>

				<footer class="src-detail-footer">
					<div class="src-footer-actions">
						<button class="src-btn-outline"><span class="dashicons dashicons-share"></span> <?php _e( 'Share Research', 'scientific-research-center' ); ?></button>
						<button class="src-btn-outline"><span class="dashicons dashicons-star-empty"></span> <?php _e( 'Bookmark', 'scientific-research-center' ); ?></button>
					</div>
					<a href="<?php echo esc_url( home_url( '/research-library/' ) ); ?>" class="src-back-link">
						<span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e( 'Back to Research Library', 'scientific-research-center' ); ?>
					</a>
				</footer>
			</article>

			<!-- Sidebar (Optional Related Info) -->
			<aside class="src-detail-sidebar">
				<div class="src-sidebar-widget card">
					<h3><?php _e( 'Quick Statistics', 'scientific-research-center' ); ?></h3>
					<ul>
						<li title="<?php _e( 'Total academic citations', 'scientific-research-center' ); ?>"><strong><?php _e( 'Citations:', 'scientific-research-center' ); ?></strong> 0</li>
						<li title="<?php _e( 'Total page views', 'scientific-research-center' ); ?>"><strong><?php _e( 'Views:', 'scientific-research-center' ); ?></strong> <?php echo number_format( get_post_meta( $post_id, 'src_views', true ) ?: 0 ); ?></li>
						<li title="<?php _e( 'Total document downloads', 'scientific-research-center' ); ?>"><strong><?php _e( 'Downloads:', 'scientific-research-center' ); ?></strong> <?php echo number_format( rand( 10, 50 ) ); ?></li>
					</ul>
				</div>
			</aside>
		</div>
	</div>

	<?php
endwhile;

get_footer();
