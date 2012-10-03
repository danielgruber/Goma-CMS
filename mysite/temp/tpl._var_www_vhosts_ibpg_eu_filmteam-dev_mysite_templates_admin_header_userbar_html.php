<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<script type="text/javascript">
	$(function(){
		$(".userbar div form .logoutButton").hover(function(){
			$(this).attr("src", "system/templates/admin/images/power_off_hover.png");
		}, function(){
			$(this).attr("src", "system/templates/admin/images/power_off.png");
		});
		
		
		var hide = function(){
			$(".userbar .langSelect").find(".langSelection").stop().slideUp(300);
			$(".userbar .langSelect").find(".langSelection").removeClass("visisble");
			$(".userbar .langSelect > a").removeClass("active");
		}
		
		$(".userbar .langSelect > a").click(function(){
			if(!$(this).parent().find(".langSelection").hasClass("visisble")) {
				$(this).parent().find(".langSelection").stop().slideUp(1).slideDown(100);
				$(this).parent().find(".langSelection").addClass("visisble");
				$(this).addClass("active");
			} else {
				hide();
			}
			
			return false;
		});
		
		CallonDocumentClick(hide, [$(".userbar .langSelect > a"), $(".userbar .langSelect").find(".langSelection")]);
	});
</script>
<div class="userbar">
	<div class="spacer"></div>
	<div class="langSelect">
		
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = array($caller->currentLang());
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("currentLang")] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("currentLang")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("currentLang")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

			<a title="<?php echo lang("switchlang", "switchlang"); ?>" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/switchLang<?php echo defined("URLEND") ? constant("URLEND") : null; ?>?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->url() : ""); ?>">
				<img src="<?php echo $data["currentLang"]["icon"]; ?>" alt="<?php echo $data["currentLang"]["code"]; ?>" />
			</a>
		
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

		<div class="langSelection dropdowncontainer">
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
						<a href="<?php echo $caller->addParamToUrl($data['_SERVER_REDIRECT'], "setlang", $data['lang']['code']); ?>">
							<img src="<?php echo $data["lang"]["icon"]; ?>" alt="<?php echo $data["lang"]["code"]; ?>" />
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
		</div>
	</div>
	<div class="spacer"></div>
	<div>
		<span>
			<a href="profile/"><strong><?php echo Core::getCMSVar('user'); ?></strong></a>
		</span>
	</div>
	<?php echo $data["userbar"]; ?>
	<div class="spacer"></div>
	<div>
		<a href="pm/"><?php echo $caller->lang("pm_inbox", "Inbox"); ?> (<?php echo $caller->PM_Unread(); ?>)</a>
	</div>
	<div class="spacer"></div>
	<div class="logout">
		<form method="post" id="loginFormGlobe" action="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>profile/logout">
			<input type="hidden" name="logout" value="1" />
			<input type="hidden" name="redirect" value="<?php echo (isset($data["_SERVER_REQUEST_URI"]) ? $data->doObject("_SERVER_REQUEST_URI")->text() : ""); ?>" />
			<input title="<?php echo lang("logout", "logout"); ?>" value="<?php echo lang("logout", "logout"); ?>" class="logoutButton" type="image" src="system/templates/admin/images/power_off.png" />
		</form>
	</div>
</div>