<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<h3><?php echo lang("info", "info"); ?></h3>
<table  class="updateInfo">
	<tr class="first">
		<td class="left">
			<?php echo $caller->lang("files.filename"); ?>
		</td>
		<td class="value">
			<?php echo $data["filename"]; ?>
		</td>
	</tr>
	<?php $data->convertDefault = false; if((!$data->doObject("installable") || !$data->doObject("installable")->bool())&&($data->doObject("error") && $data->doObject("error")->bool())) { $data->convertDefault = null; ?>
		<tr>
			<td class="left">
				<?php echo lang("error", "error"); ?>
			</td>
			<td class="value">
				<?php echo $data["error"]; ?>
			</td>
		</tr>
	<?php }   $data->convertDefault = null; ?>
	<tr>
		<td class="left">
			<?php echo $caller->lang("restore_destination"); ?>
		</td>
		<td class="value">
			<?php echo (isset($data["installFolders"]["destination"]) ? $data["installFolders"]->doObject("destination")->text() : ""); ?>
		</td>
	</tr>
	<tr>
		<td class="left">
			<?php echo lang("installable", "installable"); ?>
		</td>
		<td class="value">
			<?php $data->convertDefault = false; if(($data->doObject("installable") && $data->doObject("installable")->bool())) { $data->convertDefault = null; ?>
				<?php echo lang("yes", "yes"); ?>
			<?php } else { ?>
				<?php echo lang("no", "no"); ?>
			<?php }   $data->convertDefault = null; ?>
		</td>
	</tr>
</table>

<?php $data->convertDefault = false; if(($data->doObject("installable") && $data->doObject("installable")->bool())) { $data->convertDefault = null; ?>
	<div class="updateActions">
		<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>install/execInstall/<?php echo $data["rand"]; ?>" class="button"><?php echo lang("restore", "restore"); ?></a>
	</div>
<?php }   $data->convertDefault = null; ?>