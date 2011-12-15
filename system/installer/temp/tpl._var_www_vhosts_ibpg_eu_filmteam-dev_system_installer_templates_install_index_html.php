<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>
<?php if(isset($required_areas)) { 
								$available_areas = array (
); 
								foreach($required_areas as $area) { 
									if(!isset($available_areas[$area])) {
										throwError("6", "PHP-Error", "Error in Template-File ".$tpl.". Area ".$area." not found! Please add <code>&lt;garea name=\"".$area."\"&gt;...&lt;/garea&gt;");
									}
								}
							} ?>
<strong><?php echo $caller->lang("install.welcome"); ?></strong>
<div id="installitems">
	<div class="item install">
		<a href="install/install/">
			<img src="images/icons/fatcow-icons/32x32/world_add.png" alt="<?php echo $caller->lang('install.install_goma', 'Install Goma'); ?>" />
			<?php echo $caller->lang("install.install_goma", "Install Goma"); ?>
		</a>
	</div>
	<div class="item restore">
		<a href="install/restore/">
			<img src="images/icons/fatcow-icons/32x32/site_backup_and_restore.png" alt="<?php echo $caller->lang('install.restore_app', 'Restore a Page'); ?>" />
			<?php echo $caller->lang("install.restore_app", "Restore a Page"); ?>
		</a>
	</div>
	<!--<div class="item browse">
	<a href="install/browse/">
	<img src="images/icons/fatcow-icons/32x32/plugin.png" alt="<?php echo $caller->lang('install.browse_apps', 'Browse Goma-Apps'); ?>" />
	<?php echo $caller->lang("install.browse_apps", "Browse Goma-Apps"); ?>
	</a>
	</div>-->
</div>
<div class="clear"></div>
