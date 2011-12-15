<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<table width="100%" class="profile-info">
	<tr>
		<td class="title">
			<?php echo lang("name", "name"); ?>
		</td>
		<td>
			<?php echo (isset($data["name"]) ? $data->doObject("name")->text() : ""); ?>
		</td>
	</tr>
	<tr>
		<td class="title">
			<?php echo lang("signatur", "signatur"); ?>
		</td>
		<td>
			<?php echo (isset($data["signatur"]) ? $data->doObject("signatur")->bbcode() : ""); ?>
		</td>
	</tr>
</table>