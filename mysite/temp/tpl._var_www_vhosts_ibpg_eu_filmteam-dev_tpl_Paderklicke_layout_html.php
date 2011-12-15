<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php if(isset($required_areas)) { 
								$available_areas = array (
  'mainbar' => true,
  'content' => true,
  'sidebar' => true,
); 
								foreach($required_areas as $area) { 
									if(!isset($available_areas[$area])) {
										throwError("6", "PHP-Error", "Error in Template-File ".$tpl.". Area ".$area." not found! Please add <code>&lt;garea name=\"".$area."\"&gt;...&lt;/garea&gt;");
									}
								}
							} ?>	<body>
		
		<?php echo $caller->INCLUDE("adminbar.html"); ?>
	   	<div id="wrapper">
			<div id="header">
				<div id="header_inner">
					<div id="header_text">
						<a href="./"><?php echo Core::getCMSVar('ptitle'); ?></a>
					</div>
					<div id="header_search">
						<form method="get" action="search<?php echo defined("URLEND") ? constant("URLEND") : null; ?>">
							<input type="text" name="q" class="field" value="<?php echo lang("search", "search"); ?>" onfocus="if(this.value == '<?php echo lang("search", "search"); ?>'){this.value = '';}" onblur="if(this.value == ''){this.value = '<?php echo lang("search", "search"); ?>';}" />
						</form>
					</div>
					<div class="clear"></div>
				</div>
				
			</div>
			
			<!-- begin page -->
			<div id="page">
			
				
				<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?>
					<!-- begin navi -->
					<div class="area" id="<?php echo $data->class; ?>_mainbar"><?php if(isset($data[$data->class . "_mainbar"])) { echo $data[$data->class . "_mainbar"]; } else { ?>
			
						<ul id="nav">
							
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("mainbar")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("mainbar")]);  
			unset($data->viewcache["1_" . strtolower("mainbar")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("mainbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("mainbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

								<li>
									<a href="<?php echo $data["mainbar"]["url"]; ?>" class="<?php echo $data["mainbar"]["LinkClass"]; ?>"><span><?php echo (isset($data["mainbar"]["mainbartitle"]) ? $data["mainbar"]->doObject("mainbartitle")->text() : ""); ?></span></a>
								</li>
							
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

						</ul>
					
			<?php } ?></div>
					<!-- end navi -->
					<div style="clear: both;"></div>
					
					
					<div id="page_wrapper"> <!-- start page_wrapper -->
						<div id="left">
							<div class="area" id="<?php echo $data->class; ?>_sidebar"><?php if(isset($data[$data->class . "_sidebar"])) { echo $data[$data->class . "_sidebar"]; } else { ?>
			
								<?php $data->convertDefault = false; if($caller->level(2)) { $data->convertDefault = null; ?>
									<div class="box_with_title box_new subnav" style="width: 200px;  float: left;">			
										<div class="header">
											<strong class="title"><?php echo $caller->active_mainbar_title(); ?></strong>
										</div>
										<div class="content" >
											<ul class="subbar">
												
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar(2);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised["subbar"] = $data_loop; 
			unset($data->viewcache["_" . strtolower("subbar")]);  
			unset($data->viewcache["1_" . strtolower("subbar")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("subbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("subbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

													<li class="<?php echo $data["subbar"]["LinkClass"]; ?>">
														<a href="<?php echo $data["subbar"]["url"]; ?>">
															<span><?php echo (isset($data["subbar"]["mainbartitle"]) ? $data["subbar"]->doObject("mainbartitle")->text() : ""); ?></span>
														</a>
														<?php $data->convertDefault = false; if(($data->doObject("subbar")->doObject("active") && $data->doObject("subbar")->doObject("active")->bool())) { $data->convertDefault = null; ?>
															<?php $data->convertDefault = false; if($caller->level(3)) { $data->convertDefault = null; ?>
																<ul class="subbar subsubbar">
																	
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar(3);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("mainbar")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("mainbar")]);  
			unset($data->viewcache["1_" . strtolower("mainbar")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("mainbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("mainbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

																		<li>
																			<a href="<?php echo $data["mainbar"]["url"]; ?>" class="<?php echo $data["mainbar"]["LinkClass"]; ?>"><span><?php echo (isset($data["mainbar"]["mainbartitle"]) ? $data["mainbar"]->doObject("mainbartitle")->text() : ""); ?></span></a>
																		</li>
																	
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

																</ul>
															<?php }   $data->convertDefault = null; ?>
														<?php }   $data->convertDefault = null; ?>
													</li>
												
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

											</ul>
										</div>
									</div>
								<?php }   $data->convertDefault = null; ?>
								
								<!-- boxes in the sidebar -->
								<?php echo $caller->boxes("sidebar", 200); ?>
								<div class="clear"></div>
							
			<?php } ?></div>
						</div>
					<?php } else { ?>
						<ul id="mobilenavi">
							<div class="area" id="<?php echo $data->class; ?>_mainbar"><?php if(isset($data[$data->class . "_mainbar"])) { echo $data[$data->class . "_mainbar"]; } else { ?>
			
								
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("mainbar")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("mainbar")]);  
			unset($data->viewcache["1_" . strtolower("mainbar")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("mainbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("mainbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

									<li>
										<a href="<?php echo $data["mainbar"]["url"]; ?>" class="<?php echo $data["mainbar"]["LinkClass"]; ?>">
											<span><?php echo (isset($data["mainbar"]["mainbartitle"]) ? $data["mainbar"]->doObject("mainbartitle")->text() : ""); ?></span>
										</a>
										<?php $data->convertDefault = false; if(($data->doObject("mainbar")->doObject("active") && $data->doObject("mainbar")->doObject("active")->bool())) { $data->convertDefault = null; ?>
											<?php $data->convertDefault = false; if($caller->level(2)) { $data->convertDefault = null; ?>
												<ul>
													
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar(2);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("mainbar")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("mainbar")]);  
			unset($data->viewcache["1_" . strtolower("mainbar")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("mainbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("mainbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

														<li>
															<a href="<?php echo $data["mainbar"]["url"]; ?>" class="<?php echo $data["mainbar"]["LinkClass"]; ?>">
																<span><?php echo (isset($data["mainbar"]["mainbartitle"]) ? $data["mainbar"]->doObject("mainbartitle")->text() : ""); ?></span>
															</a>
															<?php $data->convertDefault = false; if(($data->doObject("mainbar")->doObject("active") && $data->doObject("mainbar")->doObject("active")->bool())) { $data->convertDefault = null; ?>
																<?php $data->convertDefault = false; if($caller->level(3)) { $data->convertDefault = null; ?>
																	<ul>
																		
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->mainbar(3);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("mainbar")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("mainbar")]);  
			unset($data->viewcache["1_" . strtolower("mainbar")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("mainbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("mainbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

																			<li>
																				<a href="<?php echo $data["mainbar"]["url"]; ?>" class="<?php echo $data["mainbar"]["LinkClass"]; ?>">
																					<span><?php echo (isset($data["mainbar"]["mainbartitle"]) ? $data["mainbar"]->doObject("mainbartitle")->text() : ""); ?></span>
																				</a>																		
																			</li>
																		
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>
 
																	</ul>
																<?php }   $data->convertDefault = null; ?>
															<?php }   $data->convertDefault = null; ?>
														</li>
													
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

												</ul>
											<?php }   $data->convertDefault = null; ?>
										<?php }   $data->convertDefault = null; ?>
									</li>
								
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

							
			<?php } ?></div>
						</ul>
					<?php }   $data->convertDefault = null; ?>
				
					<div id="content">
						<div id="content_inner">
							<!-- breadcrumbs -->
								<div id="breadcrumbs">
									<?php echo lang("you_are_here", "you_are_here"); ?>: <a href="./"><?php echo lang("homepage", "homepage"); ?></a>
									<?php $data->convertDefault = false; if($caller->breadcrumbs()) { $data->convertDefault = null; ?>
										
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->breadcrumbs();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("breadcrumbs")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("breadcrumbs")]);  
			unset($data->viewcache["1_" . strtolower("breadcrumbs")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("breadcrumbs")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("breadcrumbs")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

											 <?php echo defined("BREADCRUMB_SEPERATOR") ? constant("BREADCRUMB_SEPERATOR") : null; ?> <a href="<?php echo $data["breadcrumbs"]["link"]; ?>"><?php echo $data["breadcrumbs"]["title"]; ?></a>
										
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

									<?php }   $data->convertDefault = null; ?>
								</div>
							<!-- end breadcrumbs -->
							<?php echo $data["addcontent"]; ?>
							<div class="area" id="<?php echo $data->class; ?>_content"><?php if(isset($data[$data->class . "_content"])) { echo $data[$data->class . "_content"]; } else { ?>
			
								<h1>404 - Not found!</h1>
							
			<?php } ?></div>
							
							<div class="clear"></div>
						</div>
					</div>
					<div style="clear: both"></div>
				<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?>
					</div> <!-- end page_wrapper -->
				<?php }   $data->convertDefault = null; ?>
			</div> 
			<!-- end page -->
			<!-- footer -->
			<div  id="footer">
				<div id="footer_inner">
					<?php $data->convertDefault = false; if(! $caller->isMobile()) { $data->convertDefault = null; ?>
						<div id="footer_left">  Powered by <a target="_blank" href="http://goma-cms.org">Goma</a>       </div>
						<div id="footer_right">
							<a href="impressum<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("imprint", "imprint"); ?></a>&nbsp;&middot;&nbsp;<a href="kontakt<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("contact", "contact"); ?></a>  
						</div>
						<div class="clear"></div>
					<?php } else { ?>
						<div id="footer_right">
							<a href="impressum<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("imprint", "imprint"); ?></a>&nbsp;&middot;&nbsp;<a href="kontakt<?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo lang("contact", "contact"); ?></a>  
							<?php $data->convertDefault = false; if($caller->login()) { $data->convertDefault = null; ?>
								&middot;&nbsp;<a href="pm/"><?php echo lang("pm_inbox", "pm_inbox"); ?></a>
								&middot;&nbsp;<a href="profile/logout/"><?php echo lang("logout", "logout"); ?></a>
							<?php } else { ?>
							
								&middot;&nbsp;<a href="profile/login/"><?php echo lang("login", "login"); ?></a> 
							<?php }   $data->convertDefault = null; ?>
						</div>
						<div id="footer_left">  Powered by <a target="_blank" href="http://goma-cms.org">Goma</a>       </div>
						
					<?php }   $data->convertDefault = null; ?>
					<?php echo $caller->INCLUDE("mobile.html"); ?>
					<div style="clear: both;"></div>
				</div>
			</div>
		</div>
	</body>