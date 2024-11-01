<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.badgeup.io
 * @since      1.0.0
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    BadgeUp
 * @subpackage BadgeUp/includes
 * @author     BadgeUp Support <support@badgeup.io>
 */
class BadgeUp {

	/** @var self Instance */
	private static $_instance;

	/**
	 * Returns instance of current class
	 * @return self Instance
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
	/** @var BadgeUp_Loader Maintains and registers all hooks for the plugin. */
	protected $loader;

	/** @var string The string used to uniquely identify this plugin. */
	protected $BadgeUp;

	/** @var string The current version of the plugin. */
	protected $version;

	/** @var BadgeUp_API Instance */
	protected $api;

	public function __get( $name ) {
		if ( $name == 'api' ) {
			return $this->api;
		}
	}

	/**
	 * Adds an admin notice
	 * @since   1.0.0
	 * @return bool Success
	 */
	public static function notify( $notice ) {
		$notices = get_option( 'badgeup_notices', array() );

		$notices[] = $notice;

		return update_option( 'badgeup_notices', $notices );
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {
		if ( defined( 'BadgeUp_VERSION' ) ) {
			$this->version = BadgeUp_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->BadgeUp = 'badgeup';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->loader->run();
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - BadgeUp_Loader. Orchestrates the hooks of the plugin.
	 * - BadgeUp_i18n. Defines internationalization functionality.
	 * - BadgeUp_Admin. Defines all hooks for the admin area.
	 * - BadgeUp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/** Orchestrating the actions and filters of the core plugin. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-badgeup-loader.php';

		/** Defining internationalization functionality of the plugin. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-badgeup-i18n.php';

		/** BadgeUp API handler. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-badgeup-api.php';

		/** BadgeUp Earned Achievements widgets. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-badgeup-widget.php';

		/** Defining all actions that occur in the admin area. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-badgeup-admin.php';

		/** Defining all actions that occur in the public-facing side of the site. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-badgeup-public.php';

		$this->loader = new BadgeUp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the BadgeUp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new BadgeUp_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new BadgeUp_Admin( $this->get_BadgeUp(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue' );
		$this->loader->add_action( 'wp_ajax_badgeup_verify_key', $plugin_admin, 'ajax_verify_key' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new BadgeUp_Public( $this->get_BadgeUp(), $this->get_version() );

		$this->api = new BadgeUp_API();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue' );
		// User registers
		$this->loader->add_action( 'user_register', $plugin_public, 'user_register' );
		// User posts
		$this->loader->add_action( 'wp_insert_post', $plugin_public, 'wp_insert_post', 10, 2 );
		// User comments
		$this->loader->add_action( 'wp_insert_comment', $plugin_public, 'wp_insert_comment' );
		// User changes email
		$this->loader->add_action( 'profile_update', $plugin_public, 'profile_update' );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_BadgeUp() {
		return $this->BadgeUp;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    BadgeUp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
