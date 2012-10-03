<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->INCLUDE_CSS("dialog.css"); ?>
<div class="dialog">
	<div class="title">
		<?php echo (isset($data["title"]) ? $data->doObject("title")->text() : ""); ?>
	</div>
	<div class="content">
		<?php echo $data["content"]; ?>
	</div>
</div>