<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->lang("welcome_content_area", "Welcome to the CMS"); ?>
<div id="dragndrop_info" class="field info">
	<img src="<?php echo defined("APPLICATION") ? constant("APPLICATION") : null; ?>/templates/admin/images/dragndropinfo.jpg" alt="Picture" />
	<p><?php echo $caller->lang("dragndrop_info", "Drag the Elements to reorder them."); ?></p>
</div>
