<?php
/**
 * Plugin Name: Scientific Research Center
 * Plugin URI: https://healthedia.org
 * Description: Advanced User Registration & Login System for a Global Scientific Research Platform.
 * Version: 1.0.0
 * Author: Jules
 * Author URI: https://healthedia.org
 * Text Domain: scientific-research-center
 * Domain Path: /languages
 * Requires PHP: 8.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'SRC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SRC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SRC_VERSION', '1.0.0' );

// Include Core Utilities
require_once SRC_PLUGIN_DIR . 'includes/utils.php';
require_once SRC_PLUGIN_DIR . 'includes/class-src-activator.php';
require_once SRC_PLUGIN_DIR . 'includes/class-src-deactivator.php';
require_once SRC_PLUGIN_DIR . 'includes/class-src-roles.php';
require_once SRC_PLUGIN_DIR . 'includes/class-src-emails.php';
require_once SRC_PLUGIN_DIR . 'includes/class-src-frontend.php';

// Include Modular Modules
require_once SRC_PLUGIN_DIR . 'modules/auth/class-src-auth.php';
require_once SRC_PLUGIN_DIR . 'modules/research/class-src-research.php';
require_once SRC_PLUGIN_DIR . 'modules/control-panel/class-src-admin.php';
require_once SRC_PLUGIN_DIR . 'modules/control-panel/class-src-user-management.php';

/**
 * Main Plugin Class
 */
class Scientific_Research_Center {

	public function __construct() {
		$this->init();
	}

	private function init() {
		// Initialize components
		new SRC_Roles();
		new SRC_Auth();
		new SRC_Admin();
		new SRC_User_Management();
		new SRC_Frontend();
		new SRC_Emails();
		new SRC_Research();
	}

	public static function activate() {
		SRC_Activator::activate();
	}

	public static function deactivate() {
		SRC_Deactivator::deactivate();
	}
}

// Register activation and deactivation hooks
register_activation_hook( __FILE__, array( 'Scientific_Research_Center', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Scientific_Research_Center', 'deactivate' ) );

// Start the plugin
new Scientific_Research_Center();
