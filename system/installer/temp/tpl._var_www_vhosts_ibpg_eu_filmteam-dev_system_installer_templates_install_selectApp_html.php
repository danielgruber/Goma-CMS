<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<div id="apps">
	<?php $data->convertDefault = false; if(($data->doObject("this") && $data->doObject("this")->bool())) { $data->convertDefault = null; ?>
		
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

			<?php $data->convertDefault = false; if(($data->doObject("this")->doObject("first") && $data->doObject("this")->doObject("first")->bool())) { $data->convertDefault = null; ?>
				<div class="app first">
			<?php $data->convertDefault = false; } else if((!$data->doObject("this")->doObject("white") || !$data->doObject("this")->doObject("white")->bool())) {  $data->convertDefault = null; ?>
				<div class="app grey">
			<?php } else { ?>
				<div class="app">
			<?php }   $data->convertDefault = null; ?>
				<div class="icon">
					<?php $data->convertDefault = false; if(($data->doObject("this")->doObject("icon") && $data->doObject("this")->doObject("icon")->bool())) { $data->convertDefault = null; ?>
						<img src="<?php echo $data["this"]["icon"]; ?>" alt="" width="64" />
					<?php }   $data->convertDefault = null; ?>
				</div>
				<div class="button_right">
					<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>install/installApp/<?php echo (isset($data["app"]) ? $data->doObject("app")->url() : ""); ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>" class="button"><?php echo $caller->lang("install.install"); ?></a>
				</div>
				<div class="info">
					<h1><?php echo $data["this"]["title"]; ?></h1>
					<p><?php echo $data["this"]["appinfo"]["autor"]; ?></p>
					<p><?php echo lang("version", "version"); ?> <?php echo $data["this"]["version"]; ?></p>
				</div>
				<div class="clear"></div>

			</div>
		
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

	<?php } else { ?>
		<div class="no_app">
			<?php echo $caller->lang("install.no_app_found"); ?>
		</div>
	<?php }   $data->convertDefault = null; ?>
</div>