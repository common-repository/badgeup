<?php
/**
 * Achievements admin panel
 *
 * Displays all achievements registered with
 *
 * @link       https://www.badgeup.io/
 * @since      1.0.0
 *
 * @package    BadgeUp
 * @subpackage BadgeUp/admin/partials
 */

$achievements = badgeup_api()->get_achievements();
if ( empty( $_GET['badgeup_reload_cache'] ) ) {
	?>
	<h4>Achievements are cached for 24 hours.
		<a href="?page=badgeup&tab=achievements&badgeup_reload_cache=1" class="button reload-achievements-cache">
			Reload achievements cache</a>
	</h4>
	<?php
} else {
	?>
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
		<p><strong>Achievements cache reloaded.</strong></p>
	</div>
	<?php
}
?>
<div class="achievements">
	<?php foreach ( $achievements as $ach_id => $ach ) {
		?>
		<div class="achievement">
			<img src="<?php echo $ach['icon'] ?>" alt="No image">
			<div class="desc">
				<h3><?php echo $ach['name'] ?></h3>
				<p><?php echo $ach['description'] ?></p>
			</div>
			<div class="actions">
				<a target="_blank" href="https://dashboard.badgeup.io/#/achievements/edit/<?php echo $ach_id ?>?tab=properties">Edit Achievement</a>
			</div>
		</div>
		<?php
	} ?>
</div>