<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<!DOCTYPE html>
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
		
		<?php echo $caller->INCLUDE_CSS("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS("jqueryui/theme.css"); ?>
		
		<?php echo $data["header"]; ?>
	</head>
	<body>
		
		<?php echo $data["content"]; ?>
		
	</body>
</html>