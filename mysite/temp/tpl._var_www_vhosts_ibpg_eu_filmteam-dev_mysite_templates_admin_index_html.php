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
		<title><?php echo Core::getCMSVar('ptitle'); ?> <?php $data->convertDefault = false; if(($data->doObject("title") && $data->doObject("title")->bool())) { $data->convertDefault = null; ?> - <?php echo $data["title"]; ?> <?php } else { ?> - <?php echo lang("administration", "administration"); ?> <?php }   $data->convertDefault = null; ?></title>
		
		<?php echo $caller->INCLUDE_CSS("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS("box.css"); ?>
		<?php echo $caller->INCLUDE_CSS("jqueryui/theme.css"); ?>
		<script type="text/javascript">
			// <![CDATA[
				
				$(function(){
					$(window).resize(function(){
						$("#content > .header").css("min-width",0);
						$("#content > .header").css("min-width", $(document).width() - 220);
					});
					setInterval(function(){
						$("#content > .header").css("min-width",0);
						$("#content > .header").css("min-width", $(document).width() - 220);
					}, 2000);
					$("#content > .header").css("min-width",0);
					$("#content > .header").css("min-width", $(document).width() - 220);
					
				});
			// ]]>
		</script>
		<?php echo $data["header"]; ?>
	</head>
	<body>
		<!--<div id="viewport">-->
			<div id="wrapper">
			
				<div id="leftbar">
					<div id="leftbar_inner">
						<img src="system/templates/admin/images/logo.png" alt="logo" />
						<div class="container">
							<span class="pagetitle"><?php echo Core::getCMSVar('ptitle'); ?></span>
						</div>
						<div class="container">
							<?php echo $caller->lang("really welcome", "Welcome"); ?>, <strong><?php echo Core::getCMSVar('user'); ?></strong>
							<div>[ <a href="profile/edit<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("edit_profile", "edit_profile"); ?></a> | <a href="pm/"><?php echo $caller->lang("pm_inbox", "Inbox"); ?></a> ] </div><div> [ <a href="<?php echo defined("ROOT_PATH") ? constant("ROOT_PATH") : null; ?>profile/logout/?redirect=<?php echo $data["_SERVER_REQUEST_URI"]; ?>"><?php echo $caller->lang("logout", "logout"); ?></a> ]</div>
						</div>
						<div class="container">
							<span class="weblink"><a href="<?php echo defined("ROOT_PATH") ? constant("ROOT_PATH") : null; ?>"><?php echo $caller->lang("view_website", "Browse Website"); ?></a></span>
						</div>
						<div id="navi">
							<ul>
								<li>
									<?php $data->convertDefault = false; if(URL == "admin") { $data->convertDefault = null; ?>
										<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/" class="active">
											<span><?php echo $caller->lang("dashboard", "Dashboard"); ?></span>
										</a>
									<?php } else { ?>
										<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/">
											<span><?php echo $caller->lang("dashboard", "Dashboard"); ?></span>
										</a>
									<?php }   $data->convertDefault = null; ?>
									
								</li>
								<li>
									
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->this();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("this")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("this")]);  
			unset($data->viewcache["1_" . strtolower("this")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("this")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("this")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

										<?php $data->convertDefault = false; if(($data->doObject("this")->doObject("active") && $data->doObject("this")->doObject("active")->bool())) { $data->convertDefault = null; ?>
											<a class="active"  title="<?php echo $data["this"]["text"]; ?>" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/<?php echo $data["this"]["uname"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>">
												<span><?php echo $data["this"]["text"]; ?></span>
											</a>
										<?php } else { ?>
											<a title="<?php echo $data["this"]["text"]; ?>" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/<?php echo $data["this"]["uname"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>">
												<span><?php echo $data["this"]["text"]; ?></span>
											</a>
										<?php }   $data->convertDefault = null; ?>
									
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

		
								</li>
							</ul>
						</div>
						<div id="langselect" class="container">
							<form method="get" action="">
								<select onchange="$(this).parent().submit();" id="langselect_page" name="setlang">
									
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->languages();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised["lang"] = $data_loop; 
			unset($data->viewcache["_" . strtolower("lang")]);  
			unset($data->viewcache["1_" . strtolower("lang")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("lang")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("lang")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

										<?php $data->convertDefault = false; if(($data->doObject("lang")->doObject("active") && $data->doObject("lang")->doObject("active")->bool())) { $data->convertDefault = null; ?>
											<option value="<?php echo $data["lang"]["name"]; ?>" selected="selected"><?php echo $data["lang"]["title"]; ?></option>
										<?php } else { ?>
											<option value="<?php echo $data["lang"]["name"]; ?>"><?php echo $data["lang"]["title"]; ?></option>
										<?php }   $data->convertDefault = null; ?>
										
									
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

								</select>
							</form>
						</div>
						<div id="footer">
							Powered by <a href="http://goma-cms.org" target="_blank">Goma</a>
						</div>
					</div>
				</div>
				<div class="area" id="<?php echo $data->class; ?>_content"><?php if(isset($data[$data->class . "_content"])) { echo $data[$data->class . "_content"]; } else { ?>
			
					<div id="content">
						<?php $data->convertDefault = false; if(($data->doObject("content") && $data->doObject("content")->bool())) { $data->convertDefault = null; ?>
							<div class="header">
								<h1><?php echo (isset($data["title"]) ? $data->doObject("title")->text() : ""); ?></h1>
							</div>	
							<div class="content_inner">
								<?php echo $data["addcontent"]; ?>
								<?php echo $data["content"]; ?>
							</div>
						<?php } else { ?>
							<div class="header">
								<h1><?php echo $caller->lang("dashboard", "Dashboard"); ?></h1>
							</div>	
							<div class="content_inner">
								<div id="addcontent" style="width: 900px;max-height: 150px;overflow: auto;">
									<?php echo $data["addcontent"]; ?>
								</div>
								<div id="statistics">
									<div class="title">
										<?php echo $caller->lang("visitors", "Visitors"); ?>
									</div>
									<div class="controls">
										<span style="color: #24acb8;">&middot;</span> <a style="color: #24acb8;" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo $caller->lang("visitors_by_day", "By Day"); ?></a> <span style="color: #da097a;">&middot; </span> <a style="color: #da097a;" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?month=1"><?php echo $caller->lang("visitors_by_month", "By Month"); ?></a>
									</div>
									<?php $data->convertDefault = false; if(($data->doObject("_GET_month") && $data->doObject("_GET_month")->bool())) { $data->convertDefault = null; ?>
										
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->statistics();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("statistics")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("statistics")]);  
			unset($data->viewcache["1_" . strtolower("statistics")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("statistics")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("statistics")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

											<div class="stat month">
												<div class="data">
													<div style="height:<?php echo $data['statistics']['percent']*2; ?>px">
													</div>
												</div>
												<div><?php echo $data["statistics"]["count"]; ?></div>
												<div><?php echo (isset($data["statistics"]["timestamp"]) ? $data["statistics"]->doObject("timestamp")->date("F") : ""); ?></div>
											</div>
										
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

									<?php } else { ?>
										
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->statistics(false);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("statistics")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("statistics")]);  
			unset($data->viewcache["1_" . strtolower("statistics")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("statistics")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("statistics")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

											<div class="stat">
												<div class="data">
													<div style="height:<?php echo $data['statistics']['percent']*2; ?>px">
													</div>
												</div>
												<div><?php echo $data["statistics"]["count"]; ?></div>
												<div><?php echo (isset($data["statistics"]["timestamp"]) ? $data["statistics"]->doObject("timestamp")->date("j M") : ""); ?></div>
											</div>
										
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

									<?php }   $data->convertDefault = null; ?>
									<form action="" method="get">
										
									</form>
									<div class="clear"></div>
								</div>
								<script type="text/javascript">
									var update_url = "http://download.goma-cms.org/api/v1/gomadownloads.json?version=<?php echo defined("GOMA_VERSION") ? constant("GOMA_VERSION") : null; ?>-<?php echo defined("BUILD_VERSION") ? constant("BUILD_VERSION") : null; ?>&add_fields=UpdateFile,UpdateVersion";
									$(function(){
										$("#loading").css("display", "block");
										$.ajax({
											dataType: "jsonp",
											url: update_url,
											success: function(data)
											{
												if(data.data[0] == null)
												{
													$("#update_response").html('<span style="color: #00aa00;"><?php echo lang("version_current", "version_current"); ?></span>');
												} else
												{
														if(data.data[0].UpdateFile === false)
														{
															$("#update_response").html('<span style="color: #00aa00;"><?php echo lang("version_current", "version_current"); ?></span>');
														} else
														{
															$("#update_response").html('<div style="color: #cc0000;"><?php echo lang("version_available", "version_available"); ?></div> <a href="http://download.goma-cms.org/download_goma/update/<?php echo defined("GOMA_VERSION") ? constant("GOMA_VERSION") : null; ?>-<?php echo defined("BUILD_VERSION") ? constant("BUILD_VERSION") : null; ?>"><?php echo lang("upgrade_to_next", "upgrade_to_next"); ?></a>');
														}
												}
												$("#loading").css("display", "none");
											}
										});
									});
								</script>
								<div id="home-container">
									
									<div id="left">
										<div id="version" class="content_container">
											<h2>Goma <?php echo lang("version", "version"); ?></h2>
											<div>
												<div><?php echo lang("version", "version"); ?>: <?php echo defined("GOMA_VERSION") ? constant("GOMA_VERSION") : null; ?> - <?php echo defined("BUILD_VERSION") ? constant("BUILD_VERSION") : null; ?></div>
												<div id="loading" style="display: none;">Checking updates ...</div>
												<div>
													<div id="update_response"></div>
												</div>
											</div>
										</div>
										<div id="cache" class="content_container">
											<h2><?php echo lang("del_cache", "del_cache"); ?></h2>
											<div>
												<div class="info"><?php echo lang("cache_del_info", "cache_del_info"); ?></div>
												<a href="admin/?flush=1" class="button"><?php echo lang("del_cache", "del_cache"); ?></a>
											</div>
										</div>
									</div>
									<div id="right">
									
										<div id="database" class="content_container">
											<h2><?php echo lang("database", "database"); ?></h2>
											<div>
												<p class="info"><?php echo lang("db_update_info", "db_update_info"); ?></p>
												<a href="dev"><?php echo lang("db_update", "db_update"); ?></a>
											</div>
										</div>
									</div>
									
									<div class="clear"></div>
									
									
								</div>
							</div>
						<?php }   $data->convertDefault = null; ?>
					
				
					</div>
				
			<?php } ?></div>
			
			</div>
		<!--</div>-->
	</body>
</html>