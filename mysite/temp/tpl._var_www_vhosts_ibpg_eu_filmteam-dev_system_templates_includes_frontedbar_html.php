<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php $data->convertDefault = false; if($caller->admin()) { $data->convertDefault = null; ?>
	<?php $data->convertDefault = false; if((!$data->doObject("_GET_preview") || !$data->doObject("_GET_preview")->bool())) { $data->convertDefault = null; ?>
		<?php echo $caller->INCLUDE_CSS("../css/frontedbar.css"); ?>
		<div id="frontedbar_spacer"></div>
		<div id="frontedbar">
			<div id="frontedbar_inner">
				<div>
					<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>admin/" title="<?php echo lang("administration", "administration"); ?>"><?php echo $caller->lang("manage_website", "manage website"); ?></a>
				</div>
				<?php $data->convertDefault = false; if(($data->doObject("frontedbar") && $data->doObject("frontedbar")->bool())) { $data->convertDefault = null; ?>
					
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->frontedbar();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("frontedbar")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("frontedbar")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("frontedbar")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

						<div>
							<?php $data->convertDefault = false; if(($data->doObject("frontedbar")->doObject("attr_title") && $data->doObject("frontedbar")->doObject("attr_title")->bool())) { $data->convertDefault = null; ?>
								<a href="<?php echo $data["frontedbar"]["url"]; ?>" class="<?php echo $data["frontedbar"]["class"]; ?>" title="<?php echo $data["frontedbar"]["attr_title"]; ?>"><?php echo $data["frontedbar"]["title"]; ?></a>
							<?php } else { ?>
								<a href="<?php echo $data["frontedbar"]["url"]; ?>" class="<?php echo $data["frontedbar"]["class"]; ?>" title="<?php echo $data["frontedbar"]["title"]; ?>"><?php echo $data["frontedbar"]["title"]; ?></a>
							<?php }   $data->convertDefault = null; ?>
						</div>
					
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

				<?php }   $data->convertDefault = null; ?>
				
				<div class="endclear"></div>
			</div>
		</div>
		<div style="clear: both"></div>
	<?php }   $data->convertDefault = null; ?>
<?php }   $data->convertDefault = null; ?>