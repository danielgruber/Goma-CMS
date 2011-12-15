<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php $data->convertDefault = false; if($caller->login()) { $data->convertDefault = null; ?>
	<h4><?php echo $caller->lang("really welcome", "Welcome "); ?>, <?php echo Core::getCMSVar('user'); ?></h4>
		<div class="userinfo">
			<div class="messages"><?php echo lang("new_messages", "new_messages"); ?>: [ <a href="pm<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo $data["new_messages"]; ?></a> ]</div>
			<div class="users"><?php echo lang("user_online", "user_online"); ?> [ <?php echo $data["users_online"]; ?> ]</div>
			<div>&nbsp;</div>
		</div>
		<a style="color:black;text-decoration:underline;" href="profile/edit<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("edit_profile", "edit_profile"); ?></a> | <a style="color:black;text-decoration:underline;" href="profile<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("show_profile", "show_profile"); ?></a><br />
		[ <a style="color:black;text-decoration:underline;" href="profile/logout/?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->url() : ""); ?>"><?php echo lang("logout", "logout"); ?></a> ]
	<br />
<?php } else { ?>
		<form action="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>profile/login/?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->url() : ""); ?>" method="post">
			<table>
				<tr class="hide-on-js">
					<td><label for="admin_name"><?php echo lang("username", "username"); ?></label></td>
				</tr>
				<tr>
					<td><input type="text" placeholder="<?php echo lang("username", "username"); ?>" name="user" title="<?php echo lang("username", "username"); ?>" id="admin_name" size="20" /></td>
				</tr>
				<tr class="hide-on-js">
					<td><label for="admin_pwd"><?php echo lang("password", "password"); ?></label></td>
				</tr>
				<tr>
					<td><input type="password" placeholder="<?php echo lang("password", "password"); ?>" id="admin_pwd" title="<?php echo lang("password", "password"); ?>" name="pwd" size="20" /></td>
				</tr>
				<tr>
					<td>
						<div><input class="button" type="submit" value="<?php echo $caller->lang('perform_login', 'Login'); ?>"  /></div>
						<div style="padding-left: 6px;" class="subline"><a href="profile/register<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("register", "register"); ?></a><br /><a href="profile/lost_password<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("lost_password", "lost_password"); ?></a></div>
					</td>
				</tr>
			</table>
		</form>
<?php }   $data->convertDefault = null; ?>