<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.badgeup.io/
 * @since      1.0.0
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/public
 * @author     BadgeUp Support <support@badgeup.io>
 */
class BadgeUp_Public {

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
	 * @param      string    $BadgeUp       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $BadgeUp, $version ) {

		$this->BadgeUp = $BadgeUp;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue() {

		wp_enqueue_style( $this->BadgeUp, plugin_dir_url( __FILE__ ) . 'css/badgeup-public.css', array(), $this->version, 'all' );

		wp_enqueue_script( $this->BadgeUp, plugin_dir_url( __FILE__ ) . 'js/badgeup-public.min.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Creates BadgeUp event on user registration
	 */
	public function user_register() {
		badgeup_api()->create_event( 'wp:user_register' );
	}

	/**
	 * Creates BadgeUp event on inserting post
	 * @param Int $post_id
	 * @param WP_Post $post
	 */
	public function wp_insert_post( $post_id, $post ) {
		$post_format = get_post_format( $post );
		if ( $post_format ) {
			$post_format .= ':';
		}
		badgeup_api()->create_event( "wp:wp_insert_post:$post->post_type:{$post_format}$post_id" );
	}

	/**
	 * Creates BadgeUp event on inserting post
	 * @param Int $comment_id
	 */
	public function wp_insert_comment( $comment_id ) {
		badgeup_api()->create_event( "wp:wp_insert_comment:$comment_id" );
	}

	/**
	 * Creates BadgeUp event on profile updation
	 */
	public function profile_update() {
		badgeup_api()->create_event( "wp:wp_update_user" );
	}
}
