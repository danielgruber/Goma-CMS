<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<h3><?php echo (isset($data["subject"]) ? $data->doObject("subject")->text() : ""); ?></h3>
<div id="message_thread">
	
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

		<div  id="message_<?php echo $data["this"]["id"]; ?>"  class="message record">
			<div class="ui-left user">
				
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->this()->from();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("from")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("from")]);  
			unset($data->viewcache["1_" . strtolower("from")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("from")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("from")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

					<?php $data->convertDefault = false; if(($data->doObject("from")->doObject("avatar") && $data->doObject("from")->doObject("avatar")->bool())) { $data->convertDefault = null; ?>
						<a href="member/<?php echo $data["from"]["id"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><img src="images/resampled/50/50/<?php echo (isset($data["from"]["avatar"]) ? $data["from"]->doObject("avatar")->raw() : ""); ?>" /></a>
					<?php } else { ?>
						<a href="member/<?php echo $data["from"]["id"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><img src="images/no_avatar.png" height="50" width="50" /></a>
					<?php }   $data->convertDefault = null; ?>
				
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

			</div>
			<div class="ui-right delete">
				<a title="<?php echo lang("delete", "delete"); ?>" rel="ajaxfy" href="pm/delm/<?php echo $data["this"]["id"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><img src="images/icons/fatcow-icons/16x16/delete.png" name="<?php echo lang("delete", "delete"); ?>" /></a>
			</div>
			<div class="body" style="margin-left: 60px;">
				<div class="info">
					
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->this()->from();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("from")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("from")]);  
			unset($data->viewcache["1_" . strtolower("from")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("from")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("from")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

						<a class="username" href="member/<?php echo $data["from"]["id"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo (isset($data["from"]["nickname"]) ? $data["from"]->doObject("nickname")->text() : ""); ?></a> <span class="date"><?php echo (isset($data["time"]) ? $data->doObject("time")->date() : ""); ?></span>
					
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

				</div>
				<div class="message-body">
					<?php echo (isset($data["this"]["text"]) ? $data["this"]->doObject("text")->bbcode() : ""); ?>
				</div>
			</div>
			<div class="clear"></div>
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
<?php echo $data["form"]; ?>