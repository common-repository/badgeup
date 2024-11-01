<?php

// Register the widget
add_action( 'widgets_init', 'Badgup_Earned_Achievements_Widget::register_widget' );

/**
 * Class Badgup_Earned_Achievements_Widget
 */
class Badgup_Earned_Achievements_Widget extends WP_Widget {
	/** Basic Widget Settings */
	const WIDGET_NAME = "BadgeUp Achievements";
	const WIDGET_DESCRIPTION = "Displays achievements the logged-in user has earned.";

	var $textdomain;
	var $fields;

	/**
	 * Construct the widget
	 */
	function __construct() {
		//We're going to use $this->textdomain as both the translation domain and the widget class name and ID
		$this->textdomain = strtolower( get_class( $this ) );

		//Figure out your textdomain for translations via this handy debug print
		//var_dump($this->textdomain);

		//Add fields
		$this->add_field( 'title', 'Widget title', 'Earned Achievements', 'text' );
		$this->add_field( 'cols', 'Columns', '2', 'number' );

		//Translations
		load_plugin_textdomain( $this->textdomain, false, basename( dirname( __FILE__ ) ) . '/languages' );

		//Init the widget
		parent::__construct( $this->textdomain, __( self::WIDGET_NAME, $this->textdomain ), array(
			'description' => __( self::WIDGET_DESCRIPTION, $this->textdomain ),
			'classname'   => $this->textdomain
		) );
	}

	/**
	 * Adds a text field to the widget
	 *
	 * @param $field_name
	 * @param string $field_description
	 * @param string $field_default_value
	 * @param string $field_type
	 */
	private function add_field( $field_name, $field_description = '', $field_default_value = '', $field_type = 'text' ) {
		if ( ! is_array( $this->fields ) ) {
			$this->fields = array();
		}

		$this->fields[ $field_name ] = array(
			'name'          => $field_name,
			'description'   => $field_description,
			'default_value' => $field_default_value,
			'type'          => $field_type
		);
	}

	/**
	 * Registers widget
	 * @action widgets_init
	 */
	public static function register_widget() {
		register_widget( "Badgup_Earned_Achievements_Widget" );
	}

	/**
	 * Widget frontend
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$title = apply_filters( 'widget_title', $instance['title'] );

		/* Before and after widget arguments are usually modified by themes */
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		/* Widget output here */
		$this->widget_output( $instance );

		/* After widget */
		echo $args['after_widget'];
	}

	/**
	 * This function will execute the widget frontend logic.
	 * Everything you want in the widget should be output here.
	 */
	private function widget_output( $instance ) {
		extract( $instance );

		$cols = empty( $cols ) ? 2 : $cols;

		$achievements_data = badgeup_api()->get_achievements();
		$achievements      = $this->get_achievements( get_current_user_id() );

		?>
		<div class="badgeup-achievements">
			<?php

			if ( is_array( $achievements ) ) {
				foreach ( $achievements as $achievement ) {
					$ach = $achievements_data[ $achievement->achievementId ];
					?>
					<div class="achievement" style="width:<?php echo (99.9-5*$cols) / $cols ?>%">
						<img src="<?php echo $ach['icon'] ?>"></img>
						<h4><?php echo $ach['name'] ?></h4>
					</div>
					<?php
				}
			}
			?>
		</div>
		<div class="badgeup-credit-wrap"><a class="badgeup-credit" href="https://www.badgeup.io">Powered by BadgeUp</a></div>
		<?php
	}

	/**
	 * Gets achievements for the user
	 *
	 * @param Int $user_id
	 * @return Exception|mixed
	 */
	private function get_achievements( $user_id ) {
		return badgeup_api()->api( 'getEarnedAchievements', [ $user_id ] );
	}

	/**
	 * Widget backend
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		/* Generate admin for fields */
		foreach ( $this->fields as $field_name => $field_data ) {
			if ( $field_data['type'] === 'text' ):
				?>
				<p>
					<label
						for="<?php echo $this->get_field_id( $field_name ); ?>"><?php _e( $field_data['description'], $this->textdomain ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( $field_name ); ?>"
								 name="<?php echo $this->get_field_name( $field_name ); ?>" type="text"
								 value="<?php echo esc_attr( isset( $instance[ $field_name ] ) ? $instance[ $field_name ] : $field_data['default_value'] ); ?>"/>
				</p>
				<?php
			elseif ($field_data['type'] == 'number'):
				?>
				<p>
					<label
						for="<?php echo $this->get_field_id( $field_name ); ?>"><?php _e( $field_data['description'], $this->textdomain ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( $field_name ); ?>"
								 name="<?php echo $this->get_field_name( $field_name ); ?>" type="number" min="1" max="4"
								 value="<?php echo esc_attr( isset( $instance[ $field_name ] ) ? $instance[ $field_name ] : $field_data['default_value'] ); ?>"/>
				</p>
				<?php
			else:
				echo __( 'Error - Field type not supported', $this->textdomain ) . ': ' . $field_data['type'];
			endif;
		}
	}

	/**
	 * Updating widget by replacing the old instance with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}
