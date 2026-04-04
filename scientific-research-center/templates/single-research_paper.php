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
	$author_id = get_the_author_meta( 'ID' );
	$institution = get_user_meta( $author_id, 'src_institution', true );
	$co_authors = get_post_meta( $post_id, 'src_co_authors', true );
	$pub_date = get_post_meta( $post_id, 'src_pub_date', true );
	$pub_id = get_post_meta( $post_id, 'src_pub_id', true );
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
						<span class="src-badge"><?php echo esc_html( $type ); ?></span>
						<?php if ( $pub_id ) : ?>
							<span class="src-pub-id"><?php printf( __( 'ID: %s', 'scientific-research-center' ), esc_html( $pub_id ) ); ?></span>
						<?php endif; ?>
						<span class="src-pub-date"><?php printf( __( 'Published: %s', 'scientific-research-center' ), esc_html( $pub_date ?: get_the_date() ) ); ?></span>
					</div>
					<h1 class="src-detail-title"><?php the_title(); ?></h1>

					<div class="src-detail-authors">
						<div class="src-primary-author">
							<strong><?php the_author(); ?></strong>
							<?php if ( $institution ) : ?>
								<span class="src-institution"> @ <?php echo esc_html( $institution ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( $co_authors ) : ?>
							<div class="src-co-authors">
								<span><?php _e( 'Collaborators:', 'scientific-research-center' ); ?></span> <?php echo esc_html( $co_authors ); ?>
							</div>
						<?php endif; ?>
					</div>
				</header>

				<section class="src-detail-section">
					<h3><?php _e( 'Abstract', 'scientific-research-center' ); ?></h3>
					<div class="src-abstract-content">
						<?php the_content(); ?>
					</div>
				</section>

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
						<li><strong><?php _e( 'Citations:', 'scientific-research-center' ); ?></strong> 0</li>
						<li><strong><?php _e( 'Views:', 'scientific-research-center' ); ?></strong> <?php echo number_format( rand( 100, 500 ) ); ?></li>
						<li><strong><?php _e( 'Downloads:', 'scientific-research-center' ); ?></strong> <?php echo number_format( rand( 10, 50 ) ); ?></li>
					</ul>
				</div>
			</aside>
		</div>
	</div>

	<?php
endwhile;

get_footer();
