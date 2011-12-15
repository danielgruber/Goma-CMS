<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->INCLUDE_CSS("profile.css"); ?>

<div>
	<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?>
		<div class="profile-left">
	<?php }   $data->convertDefault = null; ?>
		<div>
		<div class="profile-photo">
			<?php $data->convertDefault = false; if(($data->doObject("avatar") && $data->doObject("avatar")->bool())) { $data->convertDefault = null; ?>
				<a href="<?php echo (isset($data["avatar"]) ? $data->doObject("avatar")->raw() : ""); ?>" rel="dropdownDialog"><?php echo (isset($data["avatar"]) ? $data->doObject("avatar")->setSize(150, 150) : ""); ?></a>
			<?php } else { ?>
				<img src="images/no_avatar.png" alt="<?php echo $data["avatar"]; ?>" />
			<?php }   $data->convertDefault = null; ?>
		</div>
		</div>
	<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?>
		</div>
		
		<div class="profile-content">
	<?php }   $data->convertDefault = null; ?>
	<div>
		<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?> 
			<div class="profile-actions ui-right">
				<?php echo $data["profile_actions"]; ?>
			</div>
		<?php } else { ?>
			<div class="profile-actions" style="margin-top: 8px;">
				<?php echo $data["profile_actions"]; ?>
				<div class="clear"></div>
			</div>
		<?php }   $data->convertDefault = null; ?>
		<h1><?php echo (isset($data["nickname"]) ? $data->doObject("nickname")->text() : ""); ?></h1>
		<?php echo $data["tabs"]; ?>
	</div>
	<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?>
		</div>
	<?php }   $data->convertDefault = null; ?>
	<div class="clear"></div>
</div>