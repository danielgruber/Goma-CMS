<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<tr class="sortable-header">
	
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->Fields();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised[strtolower("Fields")] = $data_loop; 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("Fields")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("Fields")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

		<td name="<?php echo (isset($data["fields"]["name"]) ? $data["fields"]->doObject("name")->text() : ""); ?>">
			<?php echo $data["fields"]["field"]; ?>
		</td>
	
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

</tr>