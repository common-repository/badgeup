<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.badgeup.io/
 * @since      1.0.0
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/admin
 * @author     BadgeUp Support <support@badgeup.io>
 */
class BadgeUp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $BadgeUp    The ID of this plugin.
	 */
	private $BadgeUp;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $BadgeUp       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $BadgeUp, $version ) {

		$this->BadgeUp = $BadgeUp;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue() {

		wp_enqueue_style( $this->BadgeUp, plugin_dir_url( __FILE__ ) . 'css/badgeup-admin.css', array(), $this->version, 'all' );

		wp_enqueue_script( $this->BadgeUp, plugin_dir_url( __FILE__ ) . 'js/badgeup-admin.min.js', array( 'jquery' ), $this->version, false );

	}

	// region Admin area

	public function admin_menu() {
		add_options_page( 'BadgeUp', 'BadgeUp', 'manage_options', $this->BadgeUp, [ $this, 'options_page' ] );
	}

	public function admin_init() {

		register_setting( $this->BadgeUp, $this->BadgeUp . '_' );

		add_settings_section(
			$this->BadgeUp . '_section',
			'',
			'__return_false',
			$this->BadgeUp
		);

		register_setting(
			$this->BadgeUp,
			$this->BadgeUp . '_api_key',
			[ $this, 'setup_complete_event' ]
		);

		register_setting(
			$this->BadgeUp,
			$this->BadgeUp . '_error_reporting_enabled'
		);

		add_settings_field(
			$this->BadgeUp . '_api_key',
			'API Key',
			[ $this, 'field_api_key' ],
			$this->BadgeUp,
			$this->BadgeUp . '_section'
		);

		add_settings_field(
			$this->BadgeUp . '_error_reporting_enabled',
			'Enable Error Reporting',
			[ $this, 'field_error_reporting_enabled' ],
			$this->BadgeUp,
			$this->BadgeUp . '_section'
		);

	}

	public function setup_complete_event( $key ) {
		$api = new \BadgeUp\Client( $key );

		try {
			$api->createEvent( get_current_user_id(), 'wp:setup_complete' );
		} catch ( Exception $e ) {}

		return $key;
	}

	public function field_api_key() {
		$pre = $this->BadgeUp;
		$value = get_option( "{$pre}_api_key", '' );
		echo
			"<input id='{$pre}_api_key' type='password' name='{$pre}_api_key' value='$value' class='regular-text ltr'>" .
			"<span id='{$pre}_api_key_valid'></span>";
	}

	public function field_error_reporting_enabled() {
		$value = get_option( "{$this->BadgeUp}_error_reporting_enabled", '' );
		$checked = checked( $value, 1, false );

		$label = "<label for='{$this->BadgeUp}error_reporting_enabled'>Report plugin errors to BadgeUp</label>";

		echo "<input id='{$this->BadgeUp}error_reporting_enabled' name='{$this->BadgeUp}_error_reporting_enabled' value='1' type='checkbox' $checked>$label";
	}

	public function options_page() {
		include 'partials/badgeup-admin-display.php';
	}

	// endregion

	public function ajax_verify_key() {
		$key = filter_input( INPUT_POST, 'key' );
		$key = $key ? $key : filter_input( INPUT_GET, 'key' );

		if ( ! $key ) {
			die( "<i class='dashicons dashicons-no' title='No Key Provided'><span class='screen-reader-text'>No Key Provided</span></i>" );
		}

		$api = new \BadgeUp\Client( $key );

		try {
			$ping = $api->createEvent( get_current_user_id(), 'wp:ping' )->wait();
			if ( $ping->event ) {
				die( "<i class='dashicons dashicons-yes' title='Valid API Key'><span class='screen-reader-text'>Valid API Key</span></i>" );
			}
		} catch ( Exception $e ) {}

		die( "<i class='dashicons dashicons-no' title='Invalid API Key'><span class='screen-reader-text'>Invalid API Key</span></i>" );
	}


	/**
	 * Admin notice
	 * Checks the notice setup in install(). If it exists, display it then delete the option so it's not displayed again.
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_notices() {
		if ( $notices = get_option( 'badgeup_notices' ) ) {

			foreach ( $notices as $notice ) {
				echo '<div class="notice is-dismissible updated"><p>' . $notice . '</p></div>';
			}

			delete_option( 'badgeup_notices' );
		}
	}

}
