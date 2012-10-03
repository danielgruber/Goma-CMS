<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>



<div id="page_page">
	<h2><?php echo (isset($data["title"]) ? $data->doObject("title")->text() : ""); ?></h2>
	<?php echo $data["content"]; ?>
</div>

<div class="clear"></div>