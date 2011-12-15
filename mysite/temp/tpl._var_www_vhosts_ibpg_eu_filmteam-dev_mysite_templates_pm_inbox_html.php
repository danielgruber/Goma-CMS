<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<h4><?php echo lang("pm_inbox", "pm_inbox"); ?></h4>
<div id="inbox">
	<?php $data->convertDefault = false; if(($data->doObject("this") && $data->doObject("this")->bool())) { $data->convertDefault = null; ?>
		
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

			<div class="ui-message record" id="message_<?php echo $data["this"]["tid"]; ?>">
				<div class="ui-left">
					
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->from();
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
							<a href="member/<?php echo $data["from"]["id"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo (isset($data["from"]["avatar"]) ? $data["from"]->doObject("avatar")->setSize(50,50) : ""); ?></a>
						<?php } else { ?>
							<a href="member/<?php echo $data["from"]["id"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><img height="50" width="50" src="images/no_avatar.png" /></a>
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
					<a title="<?php echo lang("delete", "delete"); ?>" rel="ajaxfy" href="pm/del/<?php echo $data["this"]["tid"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><img src="images/icons/fatcow-icons/16x16/delete.png" name="<?php echo lang("delete", "delete"); ?>" /></a>
				</div>
				<div onclick="location.href = '<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>pm/<?php echo $data["this"]["tid"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>';" class="ui-body" style="cursor: pointer;margin-left: 70px;">
					<div class="subject">
						<a href="pm/<?php echo $data["this"]["tid"]; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>"><?php echo (isset($data["this"]["subject"]) ? $data["this"]->doObject("subject")->text() : ""); ?></a>
						<div class="preview">
							<?php echo (isset($data["this"]["preview"]) ? $data["this"]->doObject("preview")->bbcode() : ""); ?>
						</div>
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

		<div class="pages">
		<?php echo $caller->lang("page", "Page"); ?>
		
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->pages();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("pages")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("pages")]);  
			unset($data->viewcache["1_" . strtolower("pages")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("pages")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("pages")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

			<?php $data->convertDefault = false; if(($data->doObject("pages")->doObject("black") && $data->doObject("pages")->doObject("black")->bool())) { $data->convertDefault = null; ?>
				<?php echo $data["pages"]["page"]; ?>
			<?php } else { ?>
				<a href="pm<?php echo defined("URLEND") ? constant("URLEND") : null; ?>?pa=<?php echo $data["pages"]["page"]; ?>"><?php echo $data["pages"]["page"]; ?></a>
			<?php }  $data->convertDefault = null;?>
		
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

		</div>
	<?php } else { ?>
		<div style="text-align: center; color: #555; padding: 10px; border-bottom: 3px double #efefef;">
			<?php echo $caller->lang("pm_no_message", "You don't have any message."); ?>
		</div>
	<?php }   $data->convertDefault = null; ?>
</div>