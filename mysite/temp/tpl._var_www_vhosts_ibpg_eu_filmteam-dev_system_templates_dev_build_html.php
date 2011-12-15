<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
	<head>
		<title>Creating new Database</title>
		<base href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>" />
		<link rel="stylesheet" href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>system/templates/css/default.css" />
		<link rel="stylesheet" href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>system/templates/admin/style.css" />
	</head>
	<body>
		<div id="wrapper_logout">
			
			<div class="wrapper_inner">
				<a href="http://goma-cms.org" target="_blank"><img id="logo" src="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>system/templates/admin/images/logo.png" alt="logo" /></a>
				<div class="header">
					<h1><?php echo Core::getCMSVar('ptitle'); ?></h1>
				</div>
				<div class="content">
					<h3>Creating new Database</h3>
					<?php echo $data["data"]; ?>
					<div><a href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?><?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/"><?php echo lang("administration", "administration"); ?></a></div>
				</div>
			</div>
		</div>
	</body>
</html>