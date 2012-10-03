<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<div class="fields">
	<?php echo $data["fields"]; ?>
</div>
<div class="actions">
	<?php echo $data["actions"]; ?>
</div>