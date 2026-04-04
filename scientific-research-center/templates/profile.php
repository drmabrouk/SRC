<?php
/**
 * Professional Public Profile Template
 * Custom view for Researchers, Reviewers, and Institutions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$username = get_query_var( 'src_profile' );
$user = get_user_by( 'slug', $username );

if ( ! $user ) {
	$user = get_user_by( 'login', $username );
}

if ( ! $user ) :
	?>
	<div class="src-container monochromatic" style="padding: 100px 0; text-align: center;">
		<h1><?php _e( 'Profile Not Found', 'scientific-research-center' ); ?></h1>
		<p><?php _e( 'The requested researcher profile does not exist or has been removed.', 'scientific-research-center' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="src-submit-btn"><?php _e( 'Back to Home', 'scientific-research-center' ); ?></a>
	</div>
	<?php
else :
	$user_id = $user->ID;
	$display_name = $user->display_name;
	$role = ! empty( $user->roles ) ? $user->roles[0] : '';
	$role_definitions = SRC_Roles::get_roles_definition();
	$role_name = isset( $role_definitions[ $role ] ) ? $role_definitions[ $role ]['name'] : __( 'Member', 'scientific-research-center' );

	$profile_picture_id = get_user_meta( $user_id, 'src_profile_picture', true );
	$profile_picture_url = $profile_picture_id ? wp_get_attachment_url( $profile_picture_id ) : get_avatar_url( $user_id );

	$institution = get_user_meta( $user_id, 'src_institution', true );
	$country = get_user_meta( $user_id, 'src_country', true );
	$degree = get_user_meta( $user_id, 'src_academic_degree', true );
	?>

	<div class="src-public-profile monochromatic">
		<div class="src-profile-hero">
			<div class="src-container grid-2">
				<div class="src-profile-id-card">
					<div class="src-profile-picture">
						<img src="<?php echo esc_url( $profile_picture_url ); ?>" alt="<?php echo esc_attr( $display_name ); ?>">
					</div>
					<div class="src-profile-summary">
						<h1><?php echo esc_html( $display_name ); ?></h1>
						<span class="src-profile-role-badge"><?php echo esc_html( $role_name ); ?></span>
						<div class="src-profile-quick-meta">
							<?php if ( $institution ) : ?>
								<div class="src-meta-item"><span class="dashicons dashicons-welcome-learn-more"></span> <?php echo esc_html( $institution ); ?></div>
							<?php endif; ?>
							<?php if ( $country ) : ?>
								<div class="src-meta-item"><span class="dashicons dashicons-location"></span> <?php echo esc_html( $country ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="src-profile-stats-row">
					<?php
					$research_count = count_user_posts( $user_id, 'research_paper' );
					?>
					<div class="src-stat-box">
						<span class="src-stat-val"><?php echo number_format( $research_count ); ?></span>
						<span class="src-stat-label"><?php _e( 'Publications', 'scientific-research-center' ); ?></span>
					</div>
					<div class="src-stat-box">
						<span class="src-stat-val"><?php echo number_format( rand( 50, 500 ) ); ?></span>
						<span class="src-stat-label"><?php _e( 'Total Reads', 'scientific-research-center' ); ?></span>
					</div>
					<div class="src-stat-box">
						<span class="src-stat-val">0</span>
						<span class="src-stat-label"><?php _e( 'Citations', 'scientific-research-center' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<div class="src-container src-profile-content-layout">
			<main class="src-profile-main">
				<section class="src-profile-section">
					<h3><?php _e( 'Academic Profile', 'scientific-research-center' ); ?></h3>
					<div class="src-academic-info-card card">
						<div class="src-info-grid">
							<?php if ( $degree ) : ?>
								<div class="src-info-item">
									<label><?php _e( 'Highest Degree', 'scientific-research-center' ); ?></label>
									<span><?php echo esc_html( $degree ); ?></span>
								</div>
							<?php endif; ?>
							<div class="src-info-item">
								<label><?php _e( 'Member Since', 'scientific-research-center' ); ?></label>
								<span><?php echo date( 'F Y', strtotime( $user->user_registered ) ); ?></span>
							</div>
						</div>
					</div>
				</section>

				<section class="src-profile-section">
					<h3><?php _e( 'Scientific Contributions', 'scientific-research-center' ); ?></h3>
					<div class="src-research-list">
						<?php
						$research_query = new WP_Query( array(
							'post_type'      => 'research_paper',
							'post_status'    => 'publish',
							'author'         => $user_id,
							'posts_per_page' => 10
						) );

						if ( $research_query->have_posts() ) :
							while ( $research_query->have_posts() ) : $research_query->the_post();
								$post_id = get_the_ID();
								$type = strip_tags( get_the_term_list( $post_id, 'research_type', '', ', ' ) );
								?>
								<div class="src-research-card slide-entry card compact-card" data-id="<?php echo $post_id; ?>">
									<div class="src-card-header">
										<span class="src-badge small-badge"><?php echo esc_html( $type ); ?></span>
										<span class="src-date small-text"><?php echo get_the_date(); ?></span>
									</div>
									<h3><?php the_title(); ?></h3>
									<div class="src-card-actions">
										<a href="<?php the_permalink(); ?>" class="src-view-details-btn"><?php _e( 'View Details', 'scientific-research-center' ); ?></a>
									</div>
								</div>
								<?php
							endwhile;
							wp_reset_postdata();
						else :
							echo '<p>' . __( 'No research contributions published yet.', 'scientific-research-center' ) . '</p>';
						endif;
						?>
					</div>
				</section>
			</main>

			<aside class="src-profile-sidebar">
				<div class="src-sidebar-widget card">
					<h3><?php _e( 'Connect', 'scientific-research-center' ); ?></h3>
					<button class="src-submit-btn full-width"><?php _e( 'Contact Researcher', 'scientific-research-center' ); ?></button>
					<button class="src-btn-outline full-width" style="margin-top: 15px;"><?php _e( 'Follow Updates', 'scientific-research-center' ); ?></button>
				</div>
			</aside>
		</div>
	</div>
	<?php
endif;

get_footer();
