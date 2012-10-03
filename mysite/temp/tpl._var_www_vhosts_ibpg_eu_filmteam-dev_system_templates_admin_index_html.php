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
<html id="adminPanel" lang="<?php echo Core::getCMSVar('lang'); ?>">
	<head>
		<base href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>" />
		<title><?php echo Core::getCMSVar('ptitle'); ?> <?php $data->convertDefault = false; if(($data->doObject("title") && $data->doObject("title")->bool())) { $data->convertDefault = null; ?> - <?php echo $data["title"]; ?> <?php } else { ?> - <?php echo lang("administration", "administration"); ?> <?php }   $data->convertDefault = null; ?></title>
		
		<?php echo $caller->INCLUDE_CSS_MAIN("style.css"); ?>
		<?php echo $caller->INCLUDE_CSS_MAIN("jqueryui/theme.css"); ?>
		
		<?php echo $caller->INCLUDE_JS_MAIN("admin.js"); ?>
		
		<?php echo $data["header"]; ?>
	</head>
	<body id="adminPanel">
		<!--<div id="viewport">-->
			<div id="wrapper">
			
				<div class="area" id="<?php echo $data->class; ?>_content"><?php if(isset($data[$data->class . "_content"])) { echo $data[$data->class . "_content"]; } else { ?>
			
					<div id="content">
						<!-- top header bar in black -->
						<div class="header">
							<?php echo $caller->INCLUDE("admin/header_userbar.html"); ?>
							<div id="navi" class="clearfix">
								<ul>
									<?php $data->convertDefault = false; if((!$data->doObject("content") || !$data->doObject("content")->bool())) { $data->convertDefault = null; ?>
										<li class="active">
									<?php } else { ?>
										<li>
									<?php }   $data->convertDefault = null; ?>
										<?php $data->convertDefault = false; if((!$data->doObject("content") || !$data->doObject("content")->bool())) { $data->convertDefault = null; ?>
											<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/" class="active">
												<span><?php echo $caller->lang("dashboard", "Dashboard"); ?></span>
											</a>
										<?php } else { ?>
											<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/">
												<span><?php echo $caller->lang("dashboard", "Dashboard"); ?></span>
											</a>
										<?php }   $data->convertDefault = null; ?>
										
									</li>
									
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->this();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("this")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("this")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("this")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

										<?php $data->convertDefault = false; if(($data->doObject("this")->doObject("active") && $data->doObject("this")->doObject("active")->bool())) { $data->convertDefault = null; ?>
											<li class="active">
												<a class="active"  title="<?php echo $data["this"]["text"]; ?>" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/<?php echo $data["this"]["uname"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>">
													<span><?php echo $data["this"]["text"]; ?></span>
												</a>
											</li>
										<?php } else { ?>
											<li>
												<a title="<?php echo $data["this"]["text"]; ?>" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/<?php echo $data["this"]["uname"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>">
													<span><?php echo $data["this"]["text"]; ?></span>
												</a>
											</li>
										<?php }   $data->convertDefault = null; ?>
									
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

									<li id="navMore" style="display: none">
										<a href="javascript:;">
											<span>&raquo;</span>
										</a>
										<ul id="navMore-sub" style="display: none"></ul>
									</li>
								</ul>
							</div>
							<div class="clear"></div>
						</div>
						
						<!-- header subbar in grey -->
						<div id="head">
							<div id="head_inner">
								<a href="http://goma-cms.org" target="_blank">
									<img src="system/templates/admin/images/logo.png" id="logo" alt="logo" />
								</a>
								<span class="weblink"> <strong><?php echo Core::getCMSVar('ptitle'); ?></strong> <a class="button" id="visit_webpage" href="<?php echo defined("ROOT_PATH") ? constant("ROOT_PATH") : null; ?>"><?php echo $caller->lang("view_website", "Browse Website"); ?> &raquo;</a></span>
								<p>
									<?php $data->convertDefault = false; if(($data->doObject("title") && $data->doObject("title")->bool())) { $data->convertDefault = null; ?>
										<span class="title"><?php echo $data["Title"]; ?></span>
									<?php } else { ?>
										<span class="title"><?php echo lang("overview", "overview"); ?></span>
									<?php }   $data->convertDefault = null; ?>
								</p>
									
								<div class="clear"></div>
							</div>
						</div>
						
						<!-- content -->
						<?php $data->convertDefault = false; if(($data->doObject("content") && $data->doObject("content")->bool())) { $data->convertDefault = null; ?>
							<div class="addcontent">
								<?php echo $data["addcontent"]; ?>
							</div>
							<div class="content_inner <?php echo $data["content_class"]; ?>">
								<?php echo $data["content"]; ?>
							</div>
						<?php } else { ?>
							<div class="content_inner overview">
								<div id="addcontent" style="max-width: 1000px;max-height: 150px;overflow: auto;">
									<?php echo $data["addcontent"]; ?>
								</div>
								<?php $data->convertDefault = false; if($caller->isMobile()) { $data->convertDefault = null; ?>
									<div id="statistics" class="mobile">
								<?php } else { ?>
									<div id="statistics">
								<?php }   $data->convertDefault = null; ?>
									<div class="title">
										<?php echo $caller->lang("visitors", "Visitors"); ?>
									</div>
									<div class="controls">
										<span style="color: #24acb8;">&middot;</span> <a style="color: #24acb8;" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo $caller->lang("visitors_by_day", "By Day"); ?></a> <span style="color: #da097a;">&middot; </span> <a style="color: #da097a;" href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?month=1"><?php echo $caller->lang("visitors_by_month", "By Month"); ?></a>
									</div>
									<div id="buttonLeft">
										<?php $data->convertDefault = false; if(($data->doObject("_GET_stat_page") && $data->doObject("_GET_stat_page")->bool())) { $data->convertDefault = null; ?>
											<?php $data->convertDefault = false; if(($data->doObject("_GET_month") && $data->doObject("_GET_month")->bool())) { $data->convertDefault = null; ?>
												<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?month=1&amp;stat_page=<?php echo $data['_GET_stat_page'] + 1; ?>" class="left"></a>
											<?php } else { ?>
												<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?stat_page=<?php echo $data['_GET_stat_page'] + 1; ?>" class="left"></a>
											<?php }   $data->convertDefault = null; ?>
										<?php } else { ?>
											<?php $data->convertDefault = false; if(($data->doObject("_GET_month") && $data->doObject("_GET_month")->bool())) { $data->convertDefault = null; ?>
												<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?month=1&amp;stat_page=2" class="left"></a>
											<?php } else { ?>
												<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?stat_page=2" class="left"></a>
											<?php }   $data->convertDefault = null; ?>
										<?php }   $data->convertDefault = null; ?>
									</div>
									<div id="buttonRight">
										<?php $data->convertDefault = false; if(($data->doObject("_GET_stat_page") && $data->doObject("_GET_stat_page")->bool())&&$data['_GET_stat_page'] > 1) { $data->convertDefault = null; ?>
											<?php $data->convertDefault = false; if(($data->doObject("_GET_month") && $data->doObject("_GET_month")->bool())) { $data->convertDefault = null; ?>
												<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?month=1&amp;stat_page=<?php echo $data['_GET_stat_page'] - 1; ?>" class="left"></a>
											<?php } else { ?>
												<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?stat_page=<?php echo $data['_GET_stat_page'] - 1; ?>" class="left"></a>
											<?php }   $data->convertDefault = null; ?>
										<?php }   $data->convertDefault = null; ?>
									</div>
									<?php $data->convertDefault = false; if(($data->doObject("_GET_month") && $data->doObject("_GET_month")->bool())) { $data->convertDefault = null; ?>
										
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->statistics(true, $data['_GET_stat_page']);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("statistics")] = $data_loop; 
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
												<?php $data->convertDefault = false; if($caller->statistics()->timestamp()->date("F Y") == $caller->date("F Y")) { $data->convertDefault = null; ?>
													<div class="date today"><?php echo (isset($data["statistics"]["timestamp"]) ? $data["statistics"]->doObject("timestamp")->date("F") : ""); ?></div>
												<?php } else { ?>
													<div class="date"><?php echo (isset($data["statistics"]["timestamp"]) ? $data["statistics"]->doObject("timestamp")->date("F") : ""); ?></div>
												<?php }   $data->convertDefault = null; ?>
											</div>
										
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

									<?php } else { ?>
										
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->statistics(false, $data['_GET_stat_page']);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("statistics")] = $data_loop; 
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
												<?php $data->convertDefault = false; if($caller->statistics()->timestamp()->date("j M Y") == $caller->date("j M Y")) { $data->convertDefault = null; ?>
													<div class="today date"><?php echo (isset($data["statistics"]["timestamp"]) ? $data["statistics"]->doObject("timestamp")->date("D j M") : ""); ?></div>
												<?php } else { ?>
													<div class="date"><?php echo (isset($data["statistics"]["timestamp"]) ? $data["statistics"]->doObject("timestamp")->date("D j M") : ""); ?></div>
												<?php }   $data->convertDefault = null; ?>
											</div>
										
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

									<?php }   $data->convertDefault = null; ?>
									<div class="clear"></div>
								</div>
								
								<div id="home-container">
									
									<div id="left">
										<div id="version" class="content_container">
											<h2>Goma <?php echo lang("version", "version"); ?></h2>
											<div>
												<table class="versionTable" width="100%">
													
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->Software();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("Software")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("Software")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("Software")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

														<?php $data->convertDefault = false; if((!$data->doObject("software")->doObject("white") || !$data->doObject("software")->doObject("white")->bool())) { $data->convertDefault = null; ?>
															<tr class="grey">
														<?php } else { ?>
															<tr class="white">
														<?php }   $data->convertDefault = null; ?>
															<td class="icon">
																<?php $data->convertDefault = false; if(($data->doObject("software")->doObject("icon") && $data->doObject("software")->doObject("icon")->bool())) { $data->convertDefault = null; ?>
																	<img src="<?php echo $data["software"]["icon"]; ?>" alt="" />
																<?php }   $data->convertDefault = null; ?>
															</td>
															<td class="name">
																<?php echo $data["software"]["title"]; ?>
															</td>
															<td class="version">
																<?php echo $data["software"]["version"]; ?>
															</td>
														</tr>
													
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

												</table>
												<a href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?><?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/update/" class="button"><?php echo lang("update_install", "update_install"); ?></a>
												<?php $data->convertDefault = false; if(DEV_MODE) { $data->convertDefault = null; ?>
													<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>dev/buildDistro" class="button"><?php echo $caller->lang("distro_build", "build a version"); ?></a>
												<?php }   $data->convertDefault = null; ?>
											</div>
										</div>
									</div>
									<div id="right">
										<div id="cache" class="content_container">
											<h2><?php echo lang("del_cache", "del_cache"); ?></h2>
											<div>
												<div class="info"><?php echo lang("cache_del_info", "cache_del_info"); ?></div>
												<a href="admin/?flush=1" class="button"><?php echo lang("del_cache", "del_cache"); ?></a>
											</div>
										</div>
										<?php $data->convertDefault = false; if(DEV_MODE) { $data->convertDefault = null; ?>
											<div id="database" class="content_container">
												<h2><?php echo lang("database", "database"); ?></h2>
												<div>
													<p class="info"><?php echo lang("db_update_info", "db_update_info"); ?></p>
													<a class="button" href="dev"><?php echo lang("db_update", "db_update"); ?></a>
												</div>
											</div>
										<?php }   $data->convertDefault = null; ?>
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