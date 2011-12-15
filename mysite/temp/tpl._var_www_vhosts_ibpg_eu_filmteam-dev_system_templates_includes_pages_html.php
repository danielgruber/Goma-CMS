<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
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
			<a href="<?php echo defined("URL") ? constant("URL") : null; ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>?pa=<?php echo $data["pages"]["page"]; ?>"><?php echo $data["pages"]["page"]; ?></a>
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