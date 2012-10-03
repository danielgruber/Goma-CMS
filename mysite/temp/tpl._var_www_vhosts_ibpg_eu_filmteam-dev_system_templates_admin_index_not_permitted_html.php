<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<!DOCTYPE html>
<html lang="<?php echo Core::getCMSVar('lang'); ?>">
	<head>
		<base href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>" />
		<title><?php echo Core::getCMSVar('ptitle'); ?> <?php $data->convertDefault = false; if(($data->doObject("title") && $data->doObject("title")->bool())) { $data->convertDefault = null; ?> - <?php echo $data["title"]; ?> <?php } else { ?> - <?php echo lang("administration", "administration"); ?> <?php }   $data->convertDefault = null; ?></title>
		
		<!-- some css -->
		<?php echo $caller->INCLUDE_CSS_MAIN("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS_MAIN("jqueryui/theme.css"); ?>
	
		<?php echo $data["header"]; ?>
	</head>
	<body>
		<div id="wrapper_logout">
			<?php $data->convertDefault = false; if((!$data->doObject("content") || !$data->doObject("content")->bool())) { $data->convertDefault = null; ?>
				<div class="wrapper_inner wrapper_inner_login">
			<?php } else { ?>
				<div class="wrapper_inner">
			<?php }   $data->convertDefault = null; ?>
				<div class="logo_wrapper">
					<div class="beside_logo">
						<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/switchlang/" title="<?php echo lang("switchlang", "switchlang"); ?>"><img src="images/icons/fatcow-icons/32x32/locate.png" alt="<?php echo lang("switchlang", "switchlang"); ?>" /></a>
					</div>
					<a href="http://goma-cms.org" target="_blank"><img id="logo" src="system/templates/admin/images/logo.png" alt="logo" /></a>
				</div>
				<div class="header">
					<?php $data->convertDefault = false; if(($data->doObject("content") && $data->doObject("content")->bool())) { $data->convertDefault = null; ?>
						<h1><?php echo Core::getCMSVar('ptitle'); ?> - <?php echo $caller->lang("administration", "Admin-Panel"); ?></h1>
					<?php } else { ?>
						<h1><?php echo $caller->lang("login", "login"); ?></h1>
					<?php }   $data->convertDefault = null; ?>
				</div>
				<div class="content">
					<?php echo $data["addcontent"]; ?>
					<?php $data->convertDefault = false; if(($data->doObject("content") && $data->doObject("content")->bool())) { $data->convertDefault = null; ?>
						<?php echo $data["content"]; ?>
					<?php } else { ?>
						<script type="text/javascript">
							(function(){
								$(function(){
									if($("#login_name").val() == "" || $("#login_name").val() == $("#login_name").attr("placeholder")) {
										$("#login_name").focus();
									} else {
										$("#login_pwd").focus();
									}
								});
							})(jQuery);
						</script>
						<form class="login_form" action="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>profile/login/?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->url() : ""); ?>" method="post">
							<label for="login_name"><?php echo lang("email_or_username", "email_or_username"); ?></label>
							<input type="text" name="user" title="<?php echo lang("email_or_username", "email_or_username"); ?>" id="login_name" value="<?php echo (isset($data["_POST_user"]) ? $data->doObject("_POST_user")->text() : ""); ?>" />
							<label for="login_pwd"><?php echo lang("password", "password"); ?></label>
							<input type="password" id="login_pwd" title="<?php echo lang("password", "password"); ?>" name="pwd" />
							<div class="_actions">
								<div>
									<input class="button" type="submit" class="submit" value="<?php echo $caller->lang('perform_login', 'Login'); ?>"  />
									<a href="<?php echo defined("ROOT_PATH") ? constant("ROOT_PATH") : null; ?>"><?php echo $caller->lang("back", "back"); ?></a>
								</div>
								<a href="profile/lost_password<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("lost_password", "lost_password"); ?></a>
							</div>
						</form>
						
					<?php }   $data->convertDefault = null; ?>
					
				</div>
			</div>
		</div>
	</body>
</html>