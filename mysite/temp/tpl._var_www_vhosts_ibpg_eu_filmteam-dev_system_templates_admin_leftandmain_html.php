<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->INCLUDE_CSS("leftandmain.css"); ?>
<table width="100%" class="leftandmaintable">
	<tr>
		<td class="td main">
			<?php echo $data["CONTENT"]; ?>
		</td>
		<td class="td left">
			<div id="leftwrapper">
				<div class="topwrapper">
					<div class="create content_container">
						<h2><?php echo $caller->lang("Create", "Create"); ?></h2>
						<div>
							<form method="get" action="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?><?php echo $data["AdminURI"]; ?>/add/">
								<select name="model">
									
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->types();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("types")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("types")]);  
			unset($data->viewcache["1_" . strtolower("types")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("types")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("types")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

										<option value="<?php echo $data["types"]["value"]; ?>"><?php echo (isset($data["types"]["title"]) ? $data["types"]->doObject("title")->text() : ""); ?></option>
									
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

								</select>
								<input class="button" type="submit" value="<?php echo $caller->lang("Create", "Create"); ?>" />
							</form>
						</div>
					</div>
				</div>
				<div class="content_container">
					<h2><?php echo $caller->lang("tree", "Tree"); ?></h2>
					<div>
						<div class="classtree">
							<div class="treesearch">
								<form method="get" action="">
									<input type="text" placeholder="<?php echo lang("search", "search"); ?>" name="searchtree" value="<?php echo $data["searchtree"]; ?>" />
								</form>
							</div>
							<div class="treewrapper">
								<?php echo $data["SITETREE"]; ?>
							</div>
						</div>
					</div>
				</div>
				
				<div class="legend content_container">
					<h2><?php echo lang("legend", "legend"); ?></h2>
					<div>
						
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->legend();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("legend")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("legend")]);  
			unset($data->viewcache["1_" . strtolower("legend")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("legend")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("legend")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

							<div class="<?php echo $data["legend"]["class"]; ?> legendpoint">
								<span>
									<?php $data->convertDefault = false; if(($data->doObject("legend")->doObject("checkbox") && $data->doObject("legend")->doObject("checkbox")->bool())&&($data->doObject("legend")->doObject("checked") && $data->doObject("legend")->doObject("checked")->bool())) { $data->convertDefault = null; ?> 
										<input name="<?php echo $data["legend"]["class"]; ?>" type="checkbox" checked="checked" /> 
									<?php $data->convertDefault = false; } else if(($data->doObject("legend")->doObject("checkbox") && $data->doObject("legend")->doObject("checkbox")->bool())) {  $data->convertDefault = null; ?> 
										<input name="<?php echo $data["legend"]["class"]; ?>" type="checkbox" /> 
									<?php } else { ?> 
										<input name="<?php echo $data["legend"]["class"]; ?>" type="checkbox" checked="checked" disabled="disabled" />
									<?php }   $data->convertDefault = null; ?>
									<?php echo $data["legend"]["title"]; ?>
								</span>
							</div>
						
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

					</div>
				</div>
			</div>
		</td>
	</tr>
</table>