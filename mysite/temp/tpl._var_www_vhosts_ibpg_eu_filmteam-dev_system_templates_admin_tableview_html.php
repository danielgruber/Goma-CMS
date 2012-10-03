<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<script type="text/javascript">
	// <![CDATA[
		$(function(){
			$(".tableview_wrapper .allcheckbox").click(function(){
				if($(this).prop("checked")) {
					$(this).parent().parent().parent().parent().find("input[type=checkbox]").prop("checked", "checked");
				} else {
					$(this).parent().parent().parent().parent().find("input[type=checkbox]").prop("checked", false);
				}
			});
		});
	// ]]>
</script>
<div class="tableview_wrapper">
	<form action="" method="post">
		<input type="submit" style="position: absolute;top: -500px;" />
		<input type="hidden" name="deletekey" value="<?php echo $data["deletekey"]; ?>" />
		<table width="100%" class="tableview">
			<thead>
				<tr>
					<td class="first">
						<?php $data->convertDefault = false; if(($data->doObject("deletable") && $data->doObject("deletable")->bool())) { $data->convertDefault = null; ?>
							<input type="checkbox" class="allcheckbox" name="data[all]" />
						<?php }   $data->convertDefault = null; ?>
					</td>
					
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->datafields();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["field"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("field")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("field")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

						<?php $data->convertDefault = false; if(($data->doObject("field")->doObject("sortable") && $data->doObject("field")->doObject("sortable")->bool())) { $data->convertDefault = null; ?>
							<?php $data->convertDefault = false; if(($data->doObject("field")->doObject("orderdesc") && $data->doObject("field")->doObject("orderdesc")->bool())) { $data->convertDefault = null; ?>
								<td>
									<a href="<?php echo $data["adminURI"]; ?>/?order=<?php echo (isset($data["field"]["name"]) ? $data["field"]->doObject("name")->url() : ""); ?>" class="orderdesc">
										<span></span>
										<?php echo $data["field"]["title"]; ?>
									</a>
								</td>
							<?php $data->convertDefault = false; } else if(($data->doObject("field")->doObject("order") && $data->doObject("field")->doObject("order")->bool())) {  $data->convertDefault = null; ?>
								<td>
									<a href="<?php echo $data["adminURI"]; ?>/?order=<?php echo (isset($data["field"]["name"]) ? $data["field"]->doObject("name")->url() : ""); ?>&ordertype=desc" class="orderasc">
										<span></span>
										<?php echo $data["field"]["title"]; ?>
									</a>
								</td>
							<?php } else { ?>
								<td>
									<a href="<?php echo $data["adminURI"]; ?>/?order=<?php echo (isset($data["field"]["name"]) ? $data["field"]->doObject("name")->url() : ""); ?>">
									<span></span>
									<?php echo $data["field"]["title"]; ?>
									</a>
								</td>
							<?php }   $data->convertDefault = null; ?>
						<?php } else { ?>
							<td><?php echo $data["field"]["title"]; ?></td>
						<?php }   $data->convertDefault = null; ?>
					
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

					<td class="actions">
						<?php $data->convertDefault = false; if(($data->doObject("deletable") && $data->doObject("deletable")->bool())) { $data->convertDefault = null; ?>
							<input class="button" type="submit" name="delete_many" value="<?php echo lang("delete_selected", "delete_selected"); ?>" />
						<?php }   $data->convertDefault = null; ?>
					</td>
				</tr>
				<?php $data->convertDefault = false; if(($data->doObject("search") && $data->doObject("search")->bool())) { $data->convertDefault = null; ?>
					<tr class="search">
						<td></td>
						
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->datafields();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["field"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("field")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("field")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

							<td class="field">
								<div>
								<?php $data->convertDefault = false; if(($data->doObject("field")->doObject("searchable") && $data->doObject("field")->doObject("searchable")->bool())) { $data->convertDefault = null; ?>
									<input type="search" name="search_<?php echo $data["field"]["name"]; ?>" value="<?php echo (isset($data["field"]["searchval"]) ? $data["field"]->doObject("searchval")->text() : ""); ?>" />
									<?php $data->convertDefault = false; if(($data->doObject("field")->doObject("searchval") && $data->doObject("field")->doObject("searchval")->bool())) { $data->convertDefault = null; ?>
										<button type="submit" class="cancel" name="search_<?php echo $data["field"]["name"]; ?>_cancel">&times;</button>
									<?php }   $data->convertDefault = null; ?>
									<input type="image" value="" src="system/templates/admin/images/loope.png" />
								<?php }   $data->convertDefault = null; ?>
								</div>
							</td>
						
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

						<td></td>
					</tr>
				<?php }   $data->convertDefault = null; ?>
			</thead>
			<tbody>
				<?php $data->convertDefault = false; if(($data->doObject("this") && $data->doObject("this")->bool())) { $data->convertDefault = null; ?>
					<?php echo $caller->activatePagination($data['_GET_bp'], $data['perPage']); ?>
					
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

						<?php $data->convertDefault = false; if(($data->doObject("this")->doObject("white") && $data->doObject("this")->doObject("white")->bool())) { $data->convertDefault = null; ?> 
							<tr id="tablenode_<?php echo $data["this"]["id"]; ?>">
						<?php } else { ?>
							<tr id="tablenode_<?php echo $data["this"]["id"]; ?>" class="grey">
						<?php }   $data->convertDefault = null; ?>
							<td class="first">
								<?php $data->convertDefault = false; if(($data->doObject("deletable") && $data->doObject("deletable")->bool())) { $data->convertDefault = null; ?>
									<input type="checkbox" name="data[<?php echo $data["this"]["id"]; ?>]" />
								<?php }   $data->convertDefault = null; ?>
							</td>
							
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->datafields();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["fields"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("fields")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("fields")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

								<td>
									<?php echo $caller->this()->getvar($data['fields']['name']); ?>
								</td>
							
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

							<td class="actions">
								
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->Action();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("Action")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("Action")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("Action")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

									<a href="<?php echo $data["action"]["url"]; ?>/<?php echo $data["this"]["id"]; ?>?redirect=<?php echo (isset($data["_SERVER_REQUEST_URI"]) ? $data->doObject("_SERVER_REQUEST_URI")->url() : ""); ?>"><?php echo $data["action"]["title"]; ?></a>
								
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

							</td>
						</tr>
					
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

				<?php } else { ?>
					<tr>
						<th colspan="20" class="no_data">
							<?php echo $caller->lang("no_result", "There is no data to show."); ?>
						</th>
					</tr>
				<?php }   $data->convertDefault = null; ?>
			</tbody>
		</table>
		<div class="foot">
			<div class="pages">
				<?php echo lang("page", "page"); ?>:
				
				
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->pages();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("pages")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("pages")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("pages")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

					<?php $data->convertDefault = false; if(($data->doObject("pages")->doObject("black") && $data->doObject("pages")->doObject("black")->bool())) { $data->convertDefault = null; ?>
						<span class="black"><?php echo $data["pages"]["page"]; ?></span>
					<?php } else { ?>
						<a href="<?php echo $caller->addParamToURL($data['_SERVER_REDIRECT'], "bp", $data['pages']['page']); ?>"><?php echo $data["pages"]["page"]; ?></a>
					<?php }   $data->convertDefault = null; ?>
				
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

			</div>
			
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->GlobalAction();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["action"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("action")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("action")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

				<a href="<?php echo $data["action"]["url"]; ?>?redirect=<?php echo $data["_SERVER_REQUEST_URI"]; ?>" class="button"><?php echo $data["action"]["title"]; ?></a>
			
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

			<div class="clear"></div>
		</div>
	</form>
</div>