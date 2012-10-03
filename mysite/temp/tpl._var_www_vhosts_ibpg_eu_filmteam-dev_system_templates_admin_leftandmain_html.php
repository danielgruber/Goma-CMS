<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->INCLUDE_CSS("leftandmain.css"); ?>
<?php echo $caller->gload("history"); ?>
<noscript class="lam_noscript">
	<p><?php echo lang("noscript", "noscript"); ?></p>
</noscript>
<?php echo $caller->gload("sortable"); ?>
<table width="100%" style="display: none;" class="leftandmaintable">
	<tr>
		<td class="td left">
			<div class="LaM_tabs">
				<ul>
					<?php $data->convertDefault = false; if((!$data->doObject("addmode") || !$data->doObject("addmode")->bool())) { $data->convertDefault = null; ?>
						<li class="active">
					<?php } else { ?>
						<li>
					<?php }   $data->convertDefault = null; ?>
						<a class="tree" href="<?php echo $data["adminuri"]; ?>">
							<?php echo $caller->lang("tree", "Tree"); ?>
						</a>
					</li>
				<?php $data->convertDefault = false; if(($data->doObject("addmode") && $data->doObject("addmode")->bool())) { $data->convertDefault = null; ?>
						<li class="active">
					<?php } else { ?>
						<li>
					<?php }   $data->convertDefault = null; ?>
						<a class="create" href="<?php echo $data["adminuri"]; ?>add/">
							<?php echo $caller->lang("Create", "Create"); ?>
						</a>
					</li>
				</ul>
				<?php $data->convertDefault = false; if((!$data->doObject("addmode") || !$data->doObject("addmode")->bool())) { $data->convertDefault = null; ?>
					<div class="create" style="display: none;">
				<?php } else { ?>
					<div class="create">
				<?php }   $data->convertDefault = null; ?>
					<ul class="create">
						
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->types();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("types")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("types")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("types")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

							<?php $data->convertDefault = false; if($data['types']['value'] == $data['activeAdd']) { $data->convertDefault = null; ?>
								<li class="active">
							<?php } else { ?>
								<li>
							<?php }   $data->convertDefault = null; ?>
								<a href="<?php echo $data["adminuri"]; ?>add/<?php echo (isset($data["types"]["value"]) ? $data["types"]->doObject("value")->url() : ""); ?><?php echo defined("URLEND") ? constant("URLEND") : null; ?>" class="node <?php echo $data["types"]["value"]; ?>">
									<span><?php echo (isset($data["types"]["title"]) ? $data["types"]->doObject("title")->text() : ""); ?></span>
								</a>
							</li>
						
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

					</ul>
				</div>
				<?php $data->convertDefault = false; if((!$data->doObject("addmode") || !$data->doObject("addmode")->bool())) { $data->convertDefault = null; ?>
					<div class="tree">
				<?php } else { ?>
					<div class="tree" style="display: none;">
				<?php }   $data->convertDefault = null; ?>
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
					<div class="legend">
						<div>
							<h2><?php echo lang("legend", "legend"); ?></h2>
							
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->legend();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("legend")] = $data_loop; 
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
?>

						</div>
					</div>
				</div>
			</div>
		</td>
		<td class="td main">
			<div class="inner">
				<?php echo $data["CONTENT"]; ?>
			</div>
		</td>
	</tr>
</table>