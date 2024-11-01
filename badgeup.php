<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/badgeup/badgeup-wordpress-plugin/
 * @since             1.0.0
 * @package           BadgeUp
 *
 * @wordpress-plugin
 * Plugin Name:       BadgeUp
 * Plugin URI:        https://github.com/badgeup/badgeup-wordpress-plugin/
 * Description:       Easily add and manage achievements and badges.
 * Version:           {{CURRENT_VERSION}}
 * Author:            BadgeUp
 * Author URI:        https://www.badgeup.io/
 * License:           MIT
 * License URI:       https://github.com/BadgeUp/badgeup-wordpress-plugin/blob/master/LICENSE
 * Text Domain:       badgeup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BadgeUp_VERSION', '{{CURRENT_VERSION}}' );
define( 'BadgeUp_URL', plugin_dir_url( __FILE__ ) );

if ( version_compare( '7', phpversion(), '>' ) ) {
	add_action( 'admin_notices', function() {
		$class = 'notice notice-error';
		$message = __( 'Oops! BadgeUp needs PHP v7.0.0 or higher your php version is ' . phpversion() . '.', 'sample-text-domain' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

	} );
	return;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-badgeup-activator.php
 */
function activate_BadgeUp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-badgeup-activator.php';
	BadgeUp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-badgeup-deactivator.php
 */
function deactivate_BadgeUp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-badgeup-deactivator.php';
	BadgeUp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_BadgeUp' );
register_deactivation_hook( __FILE__, 'deactivate_BadgeUp' );

/** Composer autoload */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-badgeup.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
BadgeUp::instance();

/**
 * Get BadgeUp API object
 * @return BadgeUp_API Instance
 */
function badgeup_api() {
	return BadgeUp::instance()->api;
}
