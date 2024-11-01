<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.badgeup.io/
 * @since      1.0.0
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/admin/partials
 */

$active_tab = filter_input( INPUT_GET, 'tab' );

?>
<header class="badgeup-admin-header">
	<div class="heading">
		<img src="<?php echo BadgeUp_URL . 'admin/img/logo-white.svg' ?>" alt="BadgeUp" class="logo">
		<span>BadgeUp</span>
	</div>
	<div class="nav-tab-wrapper">
		<a href="?page=badgeup" class="nav-tab <?php echo $active_tab != 'achievements' ? 'nav-tab-active' : ''; ?>">Settings</a>
		<a href="?page=badgeup&tab=achievements"
			 class="nav-tab <?php echo $active_tab == 'achievements' ? 'nav-tab-active' : ''; ?>">Achievements</a>
	</div>
</header>

<div class="wrap">

	<!-- For notices, appended after h2 -->
	<h2 class="badgeup-notices-magnet"></h2>

	<?php
	if ( $active_tab == 'achievements' ) {
		?>
		<div class="admin-achievements-wrap">
			<?php
			require 'badgeup-admin-achievement.php';
			?>
		</div>
		<?php
	} else {
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( $this->BadgeUp );
			do_settings_sections( $this->BadgeUp );
			submit_button();
			?>
		</form>
		<?php
	} // end if/else

	?>

</div>
