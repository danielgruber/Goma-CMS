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
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
	<head>
		<base href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>" />
		<title>Install Goma</title>
		<?php echo $caller->INCLUDE_CSS("admin/style.css"); ?>
		<?php echo $caller->INCLUDE_CSS("install.css"); ?>
		
		<?php echo $caller->INCLUDE_JS_MAIN("jquery"); ?>
		<?php echo $caller->INCLUDE_JS_MAIN("loader"); ?>
	</head>
	<body>
		<div id="wrapper_logout">
			
			<div class="wrapper_inner">
				<div class="logo_wrapper">
					<div class="beside_logo">
						<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>install/langselect/" title="<?php echo lang("switchlang", "switchlang"); ?>"><img src="images/icons/fatcow-icons/32x32/locate.png" alt="<?php echo lang("switchlang", "switchlang"); ?>" /></a>
					</div>
					<a target="_blank" href="http://goma-cms.org" target="_blank"><img id="logo" src="<?php echo $data["BASE_URI"]; ?>system/templates/admin/images/logo.png" alt="logo" /></a>
				</div>
				<div class="header">
					<a href="<?php echo $data["BASE_URI"]; ?>"><h1>Goma Framework</h1></a>
				</div>
				<div class="content">
					<?php echo $data["content"]; ?>
				</div>
			</div>
		</div>
	</body>
</html>