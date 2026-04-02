<?php
/**
 * Template for displaying a single research paper.
 */

get_header(); ?>

<div id="primary" class="content-area src-single-paper">
	<main id="main" class="site-main">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<div class="src-paper-meta">
					<span class="author-name"><?php _e( 'By:', 'scientific-research-center' ); ?> <?php the_author(); ?></span> |
					<span class="publish-date"><?php echo get_the_date(); ?></span>
				</div>
			</header>

			<div class="src-paper-details">
				<div class="src-abstract">
					<h3><?php _e( 'Abstract', 'scientific-research-center' ); ?></h3>
					<?php the_content(); ?>
				</div>

				<div class="src-sidebar-info">
					<div class="info-item">
						<strong><?php _e( 'DOI:', 'scientific-research-center' ); ?></strong>
						<span><?php echo get_post_meta( get_the_ID(), 'src_doi', true ) ?: 'Pending'; ?></span>
					</div>
					<div class="info-item">
						<strong><?php _e( 'Categories:', 'scientific-research-center' ); ?></strong>
						<span><?php echo get_the_term_list( get_the_ID(), 'scientific_category', '', ', ' ); ?></span>
					</div>
					<div class="info-item">
						<strong><?php _e( 'Citation:', 'scientific-research-center' ); ?></strong>
						<p class="citation-text"><?php echo get_the_author(); ?> (<?php echo get_the_date('Y'); ?>). <?php the_title(); ?>. Healthedia Scientific Research Center.</p>
					</div>

					<?php
					$file_id = get_post_meta( get_the_ID(), 'src_paper_file', true );
					if ( $file_id ) :
						$file_url = wp_get_attachment_url( $file_id );
					?>
						<div class="src-download-action">
							<a href="<?php echo esc_url( $file_url ); ?>" class="button button-primary" target="_blank"><?php _e( 'View Full PDF', 'scientific-research-center' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</article>

	<?php endwhile; ?>

	</main>
</div>

<?php get_footer(); ?>
