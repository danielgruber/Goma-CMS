<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $data["prependedContent"]; ?>

<?php echo $data["boxes"]; ?>

<div class="clear"></div>

<?php echo $data["appendedContent"]; ?>

<?php echo $data["ratingbar"]; ?>

<?php $data->convertDefault = false; if(($data->doObject("showcomments") && $data->doObject("showcomments")->bool())) { $data->convertDefault = null; ?>
	<?php echo $data["comments"]; ?>
<?php }   $data->convertDefault = null; ?>