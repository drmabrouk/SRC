<?php
/**
 * Profile Completion Template (Step 2)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$user_id = get_current_user_id();
if ( ! $user_id ) {
	wp_safe_redirect( home_url( '/login-register/' ) );
	exit;
}
?>

<div class="src-profile-completion-container monochromatic compact">
	<?php
	$user = wp_get_current_user();
	$profile_pic_id = get_user_meta( $user->ID, 'src_profile_picture', true );
	$profile_pic_url = $profile_pic_id ? wp_get_attachment_url( $profile_pic_id ) : get_avatar_url( $user->ID );
	?>

	<div class="src-profile-header">
		<div class="src-profile-avatar-wrapper" id="src-trigger-profile-upload">
			<img src="<?php echo esc_url( $profile_pic_url ); ?>" alt="Profile" class="src-profile-avatar">
			<div class="src-avatar-overlay"><span class="dashicons dashicons-camera"></span></div>
			<input type="file" name="profile_picture" id="prof_picture_input" style="display:none;" accept="image/*">
		</div>
		<div class="src-profile-title">
			<h2><?php echo esc_html( $user->display_name ); ?></h2>
			<p><?php _e( 'Edit Your Professional Profile', 'scientific-research-center' ); ?></p>
		</div>
	</div>

	<form id="src-profile-completion-action" class="src-profile-edit-form" enctype="multipart/form-data">
		<div class="src-profile-tabs-nav">
			<button type="button" class="src-prof-tab-btn active" data-tab="basic"><?php _e( 'Basic Information', 'scientific-research-center' ); ?></button>
			<button type="button" class="src-prof-tab-btn" data-tab="contact"><?php _e( 'Contact & Location', 'scientific-research-center' ); ?></button>
			<button type="button" class="src-prof-tab-btn" data-tab="academic"><?php _e( 'Academic Details', 'scientific-research-center' ); ?></button>
			<button type="button" class="src-prof-tab-btn" data-tab="account"><?php _e( 'Account Settings', 'scientific-research-center' ); ?></button>
		</div>

		<!-- Basic Info Tab -->
		<div class="src-prof-tab-content active" id="src-prof-tab-basic">
			<h3><span class="dashicons dashicons-admin-users"></span> <?php _e( 'Basic Information', 'scientific-research-center' ); ?></h3>
			<div class="src-field-row">
				<div class="src-field-group">
					<input type="text" name="first_name" id="prof_fn" placeholder=" " value="<?php echo esc_attr( $user->first_name ); ?>" required>
					<label for="prof_fn"><?php _e( 'First Name', 'scientific-research-center' ); ?></label>
				</div>
				<div class="src-field-group">
					<input type="text" name="last_name" id="prof_ln" placeholder=" " value="<?php echo esc_attr( $user->last_name ); ?>" required>
					<label for="prof_ln"><?php _e( 'Last Name', 'scientific-research-center' ); ?></label>
				</div>
			</div>
			<div class="src-field-group">
				<input type="email" name="user_email" id="prof_email" placeholder=" " value="<?php echo esc_attr( $user->user_email ); ?>" required>
				<label for="prof_email"><?php _e( 'Professional Email', 'scientific-research-center' ); ?></label>
			</div>
		</div>

		<!-- Contact Tab -->
		<div class="src-prof-tab-content" id="src-prof-tab-contact">
			<h3><span class="dashicons dashicons-location"></span> <?php _e( 'Contact & Location', 'scientific-research-center' ); ?></h3>
			<div class="src-field-row">
				<div class="src-field-group">
					<input type="text" name="country" id="prof_country" placeholder=" " value="<?php echo esc_attr( get_user_meta( $user->ID, 'src_country', true ) ); ?>" required>
					<label for="prof_country"><?php _e( 'Country', 'scientific-research-center' ); ?></label>
				</div>
				<div class="src-field-group src-phone-input-group">
					<div class="src-country-code-wrapper">
						<select name="country_code" id="prof_code" class="src-code-select">
							<?php
							$current_code = get_user_meta( $user->ID, 'src_country_code', true ) ?: '+1';
							$codes = array( '+1' => '🇺🇸 +1', '+44' => '🇬🇧 +44', '+966' => '🇸🇦 +966', '+971' => '🇦🇪 +971' );
							foreach ( $codes as $code => $label ) {
								echo '<option value="' . esc_attr( $code ) . '" ' . selected( $current_code, $code, false ) . '>' . esc_html( $label ) . '</option>';
							}
							?>
						</select>
					</div>
					<input type="text" name="mobile" id="prof_mobile" placeholder=" " value="<?php echo esc_attr( get_user_meta( $user->ID, 'src_mobile', true ) ); ?>" required>
					<label for="prof_mobile"><?php _e( 'Phone Number', 'scientific-research-center' ); ?></label>
				</div>
			</div>
		</div>

		<!-- Academic Tab -->
		<div class="src-prof-tab-content" id="src-prof-tab-academic">
			<h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php _e( 'Academic Details', 'scientific-research-center' ); ?></h3>
			<div class="src-field-row">
				<div class="src-field-group">
					<input type="text" name="institution" id="prof_inst" placeholder=" " value="<?php echo esc_attr( get_user_meta( $user->ID, 'src_institution', true ) ); ?>" required>
					<label for="prof_inst"><?php _e( 'Institution', 'scientific-research-center' ); ?></label>
				</div>
				<div class="src-field-group">
					<input type="text" name="academic_degree" id="prof_degree" placeholder=" " value="<?php echo esc_attr( get_user_meta( $user->ID, 'src_academic_degree', true ) ); ?>" required>
					<label for="prof_degree"><?php _e( 'Academic Degree', 'scientific-research-center' ); ?></label>
				</div>
			</div>
			<div class="src-field-group">
				<input type="text" name="specialty" id="prof_spec" placeholder=" " value="<?php echo esc_attr( get_user_meta( $user->ID, 'src_specialty', true ) ); ?>">
				<label for="prof_spec"><?php _e( 'Scientific Specialty (e.g. Molecular Biology)', 'scientific-research-center' ); ?></label>
			</div>
			<div class="src-field-group">
				<select name="gender" id="prof_gender" required>
					<option value="male" <?php selected( get_user_meta( $user->ID, 'src_gender', true ), 'male' ); ?>><?php _e( 'Male', 'scientific-research-center' ); ?></option>
					<option value="female" <?php selected( get_user_meta( $user->ID, 'src_gender', true ), 'female' ); ?>><?php _e( 'Female', 'scientific-research-center' ); ?></option>
				</select>
				<label for="prof_gender" class="select-label"><?php _e( 'Gender', 'scientific-research-center' ); ?></label>
			</div>
		</div>

		<!-- Account Tab -->
		<div class="src-prof-tab-content" id="src-prof-tab-account">
			<div class="src-form-section">
				<h3><span class="dashicons dashicons-lock"></span> <?php _e( 'Security & Password', 'scientific-research-center' ); ?></h3>
				<p class="src-hint"><?php _e( 'Leave blank if you do not want to change your password.', 'scientific-research-center' ); ?></p>
				<div class="src-field-row">
					<div class="src-field-group">
						<input type="password" name="new_password" id="prof_pwd" placeholder=" ">
						<label for="prof_pwd"><?php _e( 'New Password', 'scientific-research-center' ); ?></label>
					</div>
					<div class="src-field-group">
						<input type="password" name="confirm_password" id="prof_pwd_conf" placeholder=" ">
						<label for="prof_pwd_conf"><?php _e( 'Confirm New Password', 'scientific-research-center' ); ?></label>
					</div>
				</div>
			</div>
		</div>

		<div class="src-prof-footer">
			<button type="submit" class="src-submit-btn"><?php _e( 'Save Profile Changes', 'scientific-research-center' ); ?></button>
		</div>
		<div class="src-form-msg"></div>
	</form>
</div>

<?php
get_footer();
