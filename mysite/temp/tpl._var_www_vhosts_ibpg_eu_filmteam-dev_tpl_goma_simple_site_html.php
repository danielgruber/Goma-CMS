<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php if(isset($required_areas)) { 
								$available_areas = array (
  'content' => true,
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
		<title><?php echo $caller->title(); ?> <?php echo defined("TITLE_SEPERATOR") ? constant("TITLE_SEPERATOR") : null; ?> <?php echo Core::getCMSVar('ptitle'); ?></title>
		<?php echo $caller->headerHTML(); ?>
		
		<?php echo $caller->INCLUDE_CSS_MAIN("jqueryui/theme.css"); ?>
		<?php echo $caller->INCLUDE_CSS_MAIN("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS_MAIN("typography.css"); ?>
		
		<style type="text/css">
			<?php echo $data["own_css"]; ?>
		</style>
	</head>
	<body>
		<div id="document">
			<?php echo $caller->INCLUDE("frontedbar.html"); ?>
			<div id="topline"><div></div></div>
			
			<div id="header">
				<div class="content_wrapper">
					<div class="quickLinks">
						<?php $data->convertDefault = false; if($caller->login()) { $data->convertDefault = null; ?>
							<form method="post" id="loginFormGlobe" action="profile/logout">
								<input type="hidden" name="logout" value="1" />
								<input type="hidden" name="redirect" value="<?php echo $data["_SERVER_REQUEST_URI"]; ?>" />
								<noscript>
									<input class="logoutButton" type="submit" class="button" value="<?php echo lang("logout", "logout"); ?>" style="margin: 2px 0;" />
								</noscript>
							</form>
							
							<a href="#" onclick="$('#loginFormGlobe').submit();return false;"><?php echo lang("logout", "logout"); ?></a>
							|
							<a href="profile/"><?php echo lang("profile", "profile"); ?></a>
							|
							<a href="pm/"><?php echo lang("pm_inbox", "pm_inbox"); ?> (<?php echo $caller->PM_Unread(); ?>)</a>
						<?php } else { ?>
							<a href="profile/login/?redirect=<?php echo (isset($data["_SERVER_REQUEST_URI"]) ? $data->doObject("_SERVER_REQUEST_URI")->URL() : ""); ?>"><?php echo lang("login", "login"); ?></a>
							|
							<a href="profile/register"><?php echo lang("register", "register"); ?></a>
						<?php }   $data->convertDefault = null; ?>
						
					</div>
					<div class="clear"></div>
					<form id="search" method="get" action="search/">
						<input class="input" type="search" name="q" placeholder="<?php echo $caller->lang('search'); ?>" />
						<input type="image" src="tpl/<?php echo Core::getCMSVar('tpl'); ?>/images/loope.png" value="<?php echo lang("search.search", "search.search"); ?>" />
					</form>
					<h3><a href="./"><?php echo Core::getCMSVar('title'); ?></a></h3>
					<div id="navi">
						<ul>
							
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar(1);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("mainbar")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("mainbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("mainbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

								<li>
									<a href="<?php echo $data["mainbar"]["url"]; ?>" class="<?php echo $data["mainbar"]["LinkClass"]; ?>"><?php echo (isset($data["mainbar"]["mainbartitle"]) ? $data["mainbar"]->doObject("mainbartitle")->text() : ""); ?></a>
								</li>
							
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>
	
						</ul>
					</div>
					<div class="clear"></div>
				</div>
				
			</div>
			
			
			<div id="content">
				<div class="content_wrapper">
					
					
					<div id="breadcrumb">
						<div>
							<?php echo lang("you_are_here", "you_are_here"); ?>: <a href="./"><?php echo lang("homepage", "homepage"); ?> </a>
							
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->breadcrumbs();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["breadcrumb"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("breadcrumb")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("breadcrumb")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

								<?php echo defined("BREADCRUMB_SEPERATOR") ? constant("BREADCRUMB_SEPERATOR") : null; ?> <a href="<?php echo $data["breadcrumb"]["link"]; ?>"><?php echo $data["breadcrumb"]["title"]; ?></a>
							
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

						</div>
					</div>
					
					
					<?php echo $data["addcontent"]; ?>
					
					<div id="prependedContent">
						<?php echo $caller->PrependedContent(); ?>
					</div>
					
					<div class="area" id="<?php echo $data->class; ?>_content"><?php if(isset($data[$data->class . "_content"])) { echo $data[$data->class . "_content"]; } else { ?>
			
						<h1>404 - Not found!</h1>
					
			<?php } ?></div>
					
					<div id="appendedContent">
						<?php echo $caller->AppendedContent(); ?>
					</div>
					
					<div class="clear"></div>
					
				</div>
			</div>
			
			<div id="footer">
				<div class="content_wrapper">
					<div class="branding">
						Powered by <a target="_blank" href="http://goma-cms.org">Goma</a>
					</div>
					<div class="quickLinks">
						<a href="impressum/"><?php echo lang("imprint", "imprint"); ?></a>
						<a href="kontakt/"><?php echo lang("contact", "contact"); ?></a>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>