<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<div id="login_page">
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
	<div class="left">
		<h3><?php echo lang("login", "login"); ?></h3>
		<form class="login_form" action="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>profile/login/?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->url() : ""); ?>" method="post">
			<label for="login_name"><?php echo lang("email_or_username", "email_or_username"); ?></label>
			<input type="text" name="user" title="<?php echo lang("email_or_username", "email_or_username"); ?>" id="login_name" value="<?php echo (isset($data["_POST_user"]) ? $data->doObject("_POST_user")->text() : ""); ?>" />
			<label for="login_pwd"><?php echo lang("password", "password"); ?></label>
			<input type="password" id="login_pwd" title="<?php echo lang("password", "password"); ?>" name="pwd" />
			<div class="_actions">
				<div>
					<input class="button" type="submit" class="submit" value="<?php echo $caller->lang('perform_login', 'Login'); ?>"  />
					<a href="profile/register<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("register", "register"); ?></a>
				</div>
				<a href="profile/lost_password<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("lost_password", "lost_password"); ?></a>
			</div>
		</form>
	</div>
	<div class="right">
		<div id="langSelect">
			<div class="back">
				<a href="<?php echo (isset($data["_SERVER_REDIRECT_PARENT"]) ? $data->doObject("_SERVER_REDIRECT_PARENT")->url() : ""); ?>"><?php echo lang("back", "back"); ?></a>
			</div>
			<h3><?php echo lang("select_lang", "select_lang"); ?></h2>
			<div class="selectBox">
				<ul>
					
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->languages();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["lang"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("lang")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("lang")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

						<?php $data->convertDefault = false; if($data['lang']['code'] == Core::getCMSVar('lang')) { $data->convertDefault = null; ?>
							<li class="active">
						<?php } else { ?>
							<li>
						<?php }   $data->convertDefault = null; ?>
							<a href="<?php echo $caller->addParamToURL($data['_SERVER_REQUEST_URI'], "setlang", $data['lang']['code']); ?>">
								<img src="<?php echo $data["lang"]["icon"]; ?>" alt="<?php echo $data["currentLang"]["code"]; ?>" />
								<span><?php echo $data["lang"]["title"]; ?></span>
							</a>
						</li>
					
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

				</ul>
				<div class="clear"></div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>