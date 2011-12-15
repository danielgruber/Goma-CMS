<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php $data->convertDefault = false; if($caller->isMobileAvailable()) { $data->convertDefault = null; ?>
	<div id="mobileswitcher">
		<?php $data->convertDefault = false; if($caller->isMobile()) { $data->convertDefault = null; ?>
			<a href="system/setMobile/0"><?php echo $caller->lang("classic_version", "Classic Version"); ?></a>
		<?php } else { ?>
			<a href="system/setMobile/1"><?php echo $caller->lang("mobile_version", "Mobile Version"); ?></a>
		<?php }   $data->convertDefault = null; ?>
	</div>
<?php }   $data->convertDefault = null; ?>