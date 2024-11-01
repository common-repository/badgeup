<?php
/**
 * API handler class
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/includes
 */

/**
 * Handles API calls
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/includes
 * @author     Shramee <shramee.srivastav@gmail.com>
 */
class BadgeUp_API {

	/** @var array Promises */
	protected $promises = [];

	/** @var \BadgeUp\Client BadgeUp API Instance */
	protected $api;

	/** @var \Raven_Client Instance */
	protected $raven;

  /** @var Int User Id of current user (subject in API) */
	protected $subject;

	/**
	 * Handles exceptions
	 * @param Exception $e
	 * @return Exception
	 */
	protected function handle_exception( Exception $e ) {

		if ( get_option( 'badgeup_error_reporting_enabled' ) ) {

			$this->raven->captureException($e);

		}

		return $e;
	}

	/**
	 * Does some stuff on achievement completion
	 * @param object $achievement Achievement data
	 */
	protected function achievement_complete( $achievement ) {
		$user = wp_get_current_user();
		$achievements = badgeup_api()::get_achievements();
		BadgeUp::notify( "User $user->display_name($user->ID) achieved new achievement " . $achievements[ $achievement->achievementId ]['name'] );
	}

	/**
	 * Call methods on BadgeUp API client instance
	 * @param 'getAchievement'|'getAchievements'|'getEarnedAchievements'|'createEvent' $method Method to call
	 * @param array $args Arguments for method
	 * @param bool $wait Whether or not to wait
	 *
	 * @return Exception|mixed
	 */
	public function api( $method, $args = [], $wait = true ) {
		if ( $this->subject && $this->api ) {
			$promise = call_user_func_array( [ $this->api, $method ], $args );

			if ( $wait ) {
				try {
					$promise = $promise->wait(); // Unwrap promise
				} catch ( Exception $e ) {
					return $this->handle_exception( $e );
				}
			}
			return $promise;
		}

		return false;
	}

	/**
	 * BadgeUp_API constructor.
	 */
	public function __construct() {

		// Setup Sentry
		$sentryClient = new Raven_Client('https://d5111a77e2c04d6680b52aa06f1d7608:220d819937a945bf88645faac51219c2@sentry.io/235164');
		$this->raven = new Raven_ErrorHandler($sentryClient);

		// Set API on init
		add_action( 'init', [ $this, 'setup_api', ] );

		add_action( 'shutdown', [ $this, 'resolve_promises', ] );

	}

	/**
	 * Get achievements array with name, description and icon of each achievement.
	 * With achievement ID as key in the array
	 * @return array|mixed
	 */
	public function get_achievements() {

		$achievements = get_transient( 'badgeup_achievements' );

		if ( ! $achievements || isset( $_GET['badgeup_reload_cache'] ) ) {
			$achievements = [];
			$achs = $this->api( 'getAchievements' );
			if ( is_array( $achs ) ) {
				foreach ( $achs as $ach ) {
					$achievements[ $ach->id ] = [
						'name' => $ach->name,
						'description' => $ach->description,
						'icon' => $ach->meta->icon,
					];
				}

				set_transient( 'badgeup_achievements', $achievements, DAY_IN_SECONDS );
			}
		}

		return $achievements;

	}

	/**
	 * Creates a new event
	 * @param string $event Event key to create event with
	 * @param callable $done Called promise is fulfilled or rejected
	 *
	 * @return bool|Exception|\GuzzleHttp\Promise\PromiseInterface
	 */
	public function create_event( $event, $done = null ) {

		if ( $this->subject && $this->api ) {
			$promise = $this->api->createEvent( $this->subject, $event )->then(
				function ( $response ) use ( $done ) {
					if ( $done ) {
						call_user_func( $done, $response );
					}
					if ( $response && $response->progress ) {
						foreach ( $response->progress as $progress ) {
							if ( $progress->isComplete && $progress->isNew ) {
								$this->achievement_complete( $progress );
							}
						}
					}
				}
			);

			$this->promises[] = $promise; // We resolve on shutdown

			return $promise;

		}

		return false;
	}

	/**
	 * Sets API
	 *
	 * @param string $key BadgeUp application API key
	 * @param string $default_subject User id for creating events
	 *
	 * @return \BadgeUp\Client
	 */
	public function setup_api( $key = '', $default_subject = '' ) {
		if ( ! $key ) {
			$key = get_option( 'badgeup_api_key' );
		}

		if ( ! $default_subject ) {
			$default_subject = get_current_user_id();
		}

		$this->subject = $default_subject;

		if ( $key && ! $this->api ) {
			$this->api = new \BadgeUp\Client( $key );
		}

		return $this->api;
	}

	/**
	 * Resolves all promises at shutdown
	 * @uses \GuzzleHttp\Promise\all()
	 * @action shutdown
	 */
	public function resolve_promises() {
		if ( $this->promises ) {
			try {
				\GuzzleHttp\Promise\settle( $this->promises )->wait();
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
		}
	}

}
