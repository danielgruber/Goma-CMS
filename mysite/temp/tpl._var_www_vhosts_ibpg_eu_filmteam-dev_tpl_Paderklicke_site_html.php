<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php if(isset($required_areas)) { 
								$available_areas = array (
  'mainbar' => true,
  'content' => true,
  'sidebar' => true,
); 
								foreach($required_areas as $area) { 
									if(!isset($available_areas[$area])) {
										throwError("6", "PHP-Error", "Error in Template-File ".$tpl.". Area ".$area." not found! Please add <code>&lt;garea name=\"".$area."\"&gt;...&lt;/garea&gt;");
									}
								}
							} ?><!DOCTYPE html>
<html lang="<?php echo Core::getCMSVar('lang'); ?>">
	<head>
		
		<base href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>" />
		<title><?php echo $data["Title"]; ?></title>
		
		<?php echo $caller->headerHTML(); ?>

		<?php echo $caller->INCLUDE_CSS("jqueryui/theme.css"); ?>
		<?php echo $caller->INCLUDE_CSS("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS("typography.css"); ?>
		<?php $data->convertDefault = false; if($caller->isMobile()) { $data->convertDefault = null; ?>
			<?php echo $caller->INCLUDE_CSS("mobile.css"); ?>
			<meta name="viewport" content="width=device-width, initial-scale=1" />
		<?php }   $data->convertDefault = null; ?>

		<style type="text/css">
			<?php echo $data["own_css"]; ?>
			
		</style>
	</head>
<?php echo $caller->INCLUDE("layout.html"); ?>
</html>