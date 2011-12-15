<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php $data->convertDefault = false; if($caller->Permission("BOXES_ALL")) { $data->convertDefault = null; ?>
	<?php echo $caller->INCLUDE_CSS("../css/adminbar.css"); ?>
	<div id="header_admin_bar">
		<div>
			<?php $data->convertDefault = false; if($caller->admin()) { $data->convertDefault = null; ?>
				<?php echo lang("userstatus_admin", "userstatus_admin"); ?>
			<?php }   $data->convertDefault = null; ?>
			<?php echo lang("ansicht_page", "ansicht_page"); ?> <strong>
				<?php $data->convertDefault = false; if($caller->adminAsUser()) { $data->convertDefault = null; ?>
					<?php echo lang("user", "user"); ?>
				<?php } else { ?>
					<?php echo lang("admin", "admin"); ?>
				<?php }   $data->convertDefault = null; ?>
			</strong>
			&nbsp;&nbsp;
			<a href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?><?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>system/switchView/?redirect=<?php echo (isset($data["_SERVER_REQUEST_URI"]) ? $data->doObject("_SERVER_REQUEST_URI")->URL() : ""); ?>"><?php echo lang("switch_ansicht", "switch_ansicht"); ?></a>
			<?php $data->convertDefault = false; if($caller->admin()) { $data->convertDefault = null; ?>
				 &nbsp;&nbsp; <a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("administration", "administration"); ?></a> 
			<?php }   $data->convertDefault = null; ?>
		</div>
	</div>
<?php }   $data->convertDefault = null; ?>
<div style="clear: both"></div>