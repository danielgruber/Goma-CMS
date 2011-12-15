<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>
<?php if(isset($required_areas)) { 
								$available_areas = array (
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
		<title><?php echo $data["title"]; ?></title>
		

		
		<style type="text/css">
			html, body
			{
				background: none;
				color: none;
			}
		</style>
		
		<?php echo $caller->INCLUDE_JS_MAIN("jquery"); ?>
		<?php echo $caller->INCLUDE_JS_MAIN("js/loader.js"); ?>
		
		<?php echo $caller->INCLUDE_CSS("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS("jqueryui/theme.css"); ?>
	</head>
	<body>
		
		<?php echo $data["content"]; ?>
		
	</body>
</html>