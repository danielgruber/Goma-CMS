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
	<form action="<?php echo $data["adminURI"]; ?>/deletemany" method="post">
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
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->datafields();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised["fields"] = $data_loop; 
			unset($data->viewcache["_" . strtolower("fields")]);  
			unset($data->viewcache["1_" . strtolower("fields")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("fields")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("fields")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

					
						<?php $data->convertDefault = false; if(($data->doObject("fields")->doObject("orderdesc") && $data->doObject("fields")->doObject("orderdesc")->bool())) { $data->convertDefault = null; ?>
							<td><a href="<?php echo $data["adminURI"]; ?>/?order=<?php echo (isset($data["fields"]["name"]) ? $data["fields"]->doObject("name")->url() : ""); ?>" class="orderdesc"><?php echo $data["fields"]["title"]; ?></a></td>
						<?php $data->convertDefault = false; } else if(($data->doObject("fields")->doObject("order") && $data->doObject("fields")->doObject("order")->bool())) {  $data->convertDefault = null; ?>
							<td><a href="<?php echo $data["adminURI"]; ?>/?order=<?php echo (isset($data["fields"]["name"]) ? $data["fields"]->doObject("name")->url() : ""); ?>&ordertype=desc" class="orderasc"><?php echo $data["fields"]["title"]; ?></a></td>
						<?php } else { ?>
							<td><a href="<?php echo $data["adminURI"]; ?>/?order=<?php echo (isset($data["fields"]["name"]) ? $data["fields"]->doObject("name")->url() : ""); ?>"><?php echo $data["fields"]["title"]; ?></a></td>
						<?php }   $data->convertDefault = null; ?>
						
					
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

					<td style="padding: 0;">
						<?php $data->convertDefault = false; if(($data->doObject("deletable") && $data->doObject("deletable")->bool())) { $data->convertDefault = null; ?>
							<input class="button" type="submit" name="delete" value="<?php echo lang("delete_selected", "delete_selected"); ?>" />
						<?php }   $data->convertDefault = null; ?>
					</td>
				</tr>
			</thead>
			<tbody>
				
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

					<?php $data->convertDefault = false; if(($data->doObject("this")->doObject("white") && $data->doObject("this")->doObject("white")->bool())) { $data->convertDefault = null; ?> 
						<tr>
					<?php } else { ?>
						<tr class="grey">
					<?php }   $data->convertDefault = null; ?>
						<td class="first">
							<?php $data->convertDefault = false; if(($data->doObject("deletable") && $data->doObject("deletable")->bool())) { $data->convertDefault = null; ?>
								<input type="checkbox" name="data[<?php echo $data["this"]["id"]; ?>]" />
							<?php }   $data->convertDefault = null; ?>
						</td>
						
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->datafields();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised["fields"] = $data_loop; 
			unset($data->viewcache["_" . strtolower("fields")]);  
			unset($data->viewcache["1_" . strtolower("fields")]); 
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
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

						<td class="actions">
							
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->Action();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised[strtolower("Action")] = $data_loop; 
			unset($data->viewcache["_" . strtolower("Action")]);  
			unset($data->viewcache["1_" . strtolower("Action")]); 
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
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

						</td>
					</tr>
				
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

			</tbody>
		</table>
		<?php echo $caller->INCLUDE("pages.html"); ?>
		
<?php 
// begin control
if(PROFILE) 
	Profiler::mark("loops"); 
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->GlobalAction();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && ClassInfo::hasInterface($value, "traversable"))) 
		foreach($value as $data_loop) { 
			$data->customised["action"] = $data_loop; 
			unset($data->viewcache["_" . strtolower("action")]);  
			unset($data->viewcache["1_" . strtolower("action")]); 
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
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

		
	</form>
</div>