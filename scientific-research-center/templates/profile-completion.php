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
	<div class="src-welcome-msg">
		<h2><?php _e( 'Complete Your Profile', 'scientific-research-center' ); ?></h2>
		<p><?php _e( 'Please provide additional details to finalize your account.', 'scientific-research-center' ); ?></p>
	</div>

	<form id="src-profile-completion-action" enctype="multipart/form-data">
		<div class="src-field-row">
			<div class="src-field-group">
				<input type="text" name="country" id="prof_country" placeholder=" " required>
				<label for="prof_country"><?php _e( 'Country', 'scientific-research-center' ); ?></label>
			</div>
			<div class="src-field-group">
				<input type="text" name="mobile" id="prof_mobile" placeholder=" " required>
				<label for="prof_mobile"><?php _e( 'Mobile Number', 'scientific-research-center' ); ?></label>
			</div>
		</div>

		<div class="src-field-group">
			<input type="email" name="alt_email" id="prof_alt_email" placeholder=" ">
			<label for="prof_alt_email"><?php _e( 'Alternate Email (Optional)', 'scientific-research-center' ); ?></label>
		</div>

		<div class="src-field-row">
			<div class="src-field-group">
				<select name="gender" id="prof_gender" required>
					<option value="male"><?php _e( 'Male', 'scientific-research-center' ); ?></option>
					<option value="female"><?php _e( 'Female', 'scientific-research-center' ); ?></option>
					<option value="other"><?php _e( 'Other', 'scientific-research-center' ); ?></option>
				</select>
				<label for="prof_gender" class="select-label"><?php _e( 'Gender', 'scientific-research-center' ); ?></label>
			</div>
			<div class="src-field-group">
				<input type="text" name="academic_degree" id="prof_degree" placeholder=" " required>
				<label for="prof_degree"><?php _e( 'Academic Degree', 'scientific-research-center' ); ?></label>
			</div>
		</div>

		<div class="src-field-group">
			<input type="file" name="profile_picture" id="prof_picture" accept="image/*">
			<label for="prof_picture" class="file-label"><?php _e( 'Profile Picture', 'scientific-research-center' ); ?></label>
		</div>

		<button type="submit" class="src-submit-btn"><?php _e( 'Complete Profile', 'scientific-research-center' ); ?></button>
		<div class="src-form-msg"></div>
	</form>
</div>

<style>
.src-profile-completion-container { max-width: 600px; margin: 40px auto; padding: 30px; background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; }
.file-label { top: -10px !important; font-size: 12px !important; color: #000 !important; }
</style>

<?php
get_footer();
