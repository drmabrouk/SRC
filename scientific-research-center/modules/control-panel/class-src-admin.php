<?php
/**
 * SRC_Admin Class
 * Handles the WordPress admin panel for the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
	}

	/**
	 * Register the plugin control panel
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Scientific Research', 'scientific-research-center' ),
			__( 'Scientific Research', 'scientific-research-center' ),
			'manage_options',
			'src-admin',
			array( $this, 'render_admin_page' ),
			'dashicons-analytics',
			25
		);

		add_submenu_page(
			'src-admin',
			__( 'User Management', 'scientific-research-center' ),
			__( 'User Management', 'scientific-research-center' ),
			'manage_options',
			'src-users',
			array( $this, 'render_user_management_page' )
		);
	}

	/**
	 * Render the main settings page
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Scientific Research Center Settings', 'scientific-research-center' ); ?></h1>
			<p><?php _e( 'Manage your platform configurations here.', 'scientific-research-center' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render the User Management page
	 */
	public function render_user_management_page() {
		if ( isset( $_GET['src_imported'] ) ) {
			echo '<div class="updated"><p>' . sprintf( __( '%d users imported successfully!', 'scientific-research-center' ), absint( $_GET['src_imported'] ) ) . '</p></div>';
		}
		if ( isset( $_GET['src_error'] ) ) {
			echo '<div class="error"><p>' . esc_html( $_GET['src_error'] ) . '</p></div>';
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'User Management', 'scientific-research-center' ); ?></h1>
			<div class="card">
				<h2><?php _e( 'Import / Export Users', 'scientific-research-center' ); ?></h2>
				<p><?php _e( 'Seamlessly import and export user accounts between the plugin and WordPress core.', 'scientific-research-center' ); ?></p>

				<form method="post" style="display:inline-block; margin-right: 10px;">
					<?php wp_nonce_field( 'src_user_export', 'src_export_nonce' ); ?>
					<input type="submit" name="src_export_users" class="button button-primary" value="<?php _e( 'Export Users to JSON', 'scientific-research-center' ); ?>">
				</form>

				<form method="post" style="display:inline-block;">
					<?php wp_nonce_field( 'src_user_export', 'src_export_nonce' ); ?>
					<input type="submit" name="src_export_research" class="button button-primary" value="<?php _e( 'Export Research to JSON', 'scientific-research-center' ); ?>">
				</form>

				<hr>

				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'src_user_import', 'src_import_nonce' ); ?>
					<input type="file" name="src_import_file" accept=".json">
					<input type="submit" name="src_import_users" class="button" value="<?php _e( 'Import Users from JSON', 'scientific-research-center' ); ?>">
				</form>
			</div>
		</div>
		<?php
	}
}
