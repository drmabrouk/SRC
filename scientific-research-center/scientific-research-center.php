<?php
/**
 * Plugin Name: Scientific Research Center
 * Plugin URI: https://healthedia.org
 * Description: A specialized scientific gateway for publishing and managing research.
 * Version: 1.0.0
 * Author: Healthedia Technical Development Team
 * Author URI: https://healthedia.org
 * Text Domain: scientific-research-center
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
class Scientific_Research_Center {

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies and set the hooks for the admin area and the public-facing side of the site.
	 */
	public function __construct() {
		$this->version = '1.0.0';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-roles.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-post-types.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-activator.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-rewrites.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-security.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-portability.php';
		require_once plugin_dir_path( __FILE__ ) . 'admin/class-src-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'public/class-src-public.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'scientific-research-center',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new SRC_Admin( $this->version );
		add_action( 'admin_menu', array( $plugin_admin, 'add_plugin_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {
		$plugin_public = new SRC_Public( $this->version );
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts' ) );

		// Register shortcodes
		add_shortcode( 'src_research_engine', array( $plugin_public, 'render_research_engine' ) );
		add_shortcode( 'src_search_results', array( $plugin_public, 'render_search_results' ) );
		add_shortcode( 'src_submission_wizard', array( $plugin_public, 'render_submission_wizard' ) );
		add_shortcode( 'src_user_profile', array( $plugin_public, 'render_user_profile' ) );
		add_shortcode( 'src_auth_portal', array( $plugin_public, 'render_auth_portal' ) );

		add_filter( 'single_template', array( $this, 'load_research_paper_template' ) );
	}

	/**
	 * Load custom template for single research paper.
	 */
	public function load_research_paper_template( $template ) {
		if ( is_singular( 'research_paper' ) ) {
			$custom_template = plugin_dir_path( __FILE__ ) . 'templates/single-research_paper.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	}

	/**
	 * Execution of the plugin.
	 */
	public function run() {
		SRC_Portability::init();
		add_action( 'init', array( 'SRC_Post_Types', 'register_research_paper_cpt' ) );
		add_action( 'init', array( 'SRC_Post_Types', 'register_taxonomies' ) );
		add_action( 'init', array( 'SRC_Rewrites', 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( 'SRC_Rewrites', 'add_query_vars' ) );
	}
}

/**
 * The code that runs during plugin activation.
 */
function activate_scientific_research_center() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-activator.php';
	SRC_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_scientific_research_center() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-src-roles.php';
	SRC_Roles::unregister_roles();
}

register_activation_hook( __FILE__, 'activate_scientific_research_center' );
register_deactivation_hook( __FILE__, 'deactivate_scientific_research_center' );

/**
 * Begins execution of the plugin.
 */
function run_scientific_research_center() {
	$plugin = new Scientific_Research_Center();
	$plugin->run();
}

run_scientific_research_center();
