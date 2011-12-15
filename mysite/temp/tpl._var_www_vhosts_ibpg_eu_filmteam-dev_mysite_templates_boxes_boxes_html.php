<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->INCLUDE_CSS("boxes.css"); ?>
<?php $data->convertDefault = false; if($caller->permission("boxes_all")&&! $caller->adminAsUser()) { $data->convertDefault = null; ?>
	<?php echo $caller->GLOAD("sortable"); ?>
	<?php echo $caller->GLOAD("resizable"); ?>
	<?php echo $caller->GLOAD("dropdownDialog"); ?>
	<script type="text/javascript">
		// <![CDATA[
			(function($){
				$(function(){
					$("#boxes_new_<?php echo $data["id"]; ?> .box_new").resizable({
						autoHide: true,
						handles: 'e',
						minWidth: 100,
						grid: [10, 10],
						stop: function(event, ui) {
							$.ajax({
								url: root_path + "boxes_new/<?php echo $data["id"]; ?>/saveBoxWidth/" + ui.element.attr("id").replace("box_new_", ""),
								type: "post",
								data: {width: ui.element.width()},
								dataType: "html"
							});
						},
						resize: function(event, ui){
							ui.element.css('height','auto');
						}
					});
					$("#boxes_new_<?php echo $data["id"]; ?>").sortable({
						opacity: 0.6,
						handle: '.adminhead',
						helper: 'clone',
						placeholder: 'placeholder',
						revert: true,
						tolerance: 'pointer',
						start: function(event, ui) {
							$(".placeholder").css({'width' : ui.item.width(), 'height': ui.item.height()});
							$(".placeholder").attr("class", ui.item.attr("class") + " placeholder");
							
						},
						update: function(event, ui) {
							var data  = $(this).sortable("serialize");
							// save order
							$.ajax({
								url: root_path + "boxes_new/<?php echo $data["id"]; ?>/saveBoxOrder",
								data: data,
								type: "post",
								dataType: "html"
							});
						},
						distance: 10,
						items: " > .box_new"
					});
					$("#boxes_new_<?php echo $data["id"]; ?> > .box_new .adminhead").css("cursor", "move");
				});
			})(jQuery);
		// ]]>
	</script>
<?php }   $data->convertDefault = null; ?>

<div class="boxes_new" id="boxes_new_<?php echo $data["id"]; ?>">
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
			$data->customised["box"] = $data_loop; 
			unset($data->viewcache["_" . strtolower("box")]);  
			unset($data->viewcache["1_" . strtolower("box")]); 
			if(is_object($data_loop)) 
				$caller->callers[strtolower("box")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("box")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

			<?php $data->convertDefault = false; if($caller->permission("boxes_all")&&! $caller->adminAsUser()&&($data->doObject("box")->doObject("title") && $data->doObject("box")->doObject("title")->bool())) { $data->convertDefault = null; ?>
				<div class="box_new adminview <?php echo $data["box"]["class_name"]; ?> box_with_title <?php echo $data["box"]["border_class"]; ?>" style="width: <?php echo $data["box"]["width"]; ?>px;" id="box_new_<?php echo $data["box"]["id"]; ?>">
			<?php $data->convertDefault = false; } else if($caller->permission("boxes_all")&&! $caller->adminAsUser()) {  $data->convertDefault = null; ?>
				<div class="box_new adminview <?php echo $data["box"]["class_name"]; ?> <?php echo $data["box"]["border_class"]; ?>" style="width: <?php echo $data["box"]["width"]; ?>px;" id="box_new_<?php echo $data["box"]["id"]; ?>">
			<?php $data->convertDefault = false; } else if(($data->doObject("box")->doObject("title") && $data->doObject("box")->doObject("title")->bool())) {  $data->convertDefault = null; ?>
				<div class="box_new <?php echo $data["box"]["class_name"]; ?> box_with_title <?php echo $data["box"]["border_class"]; ?>" style="width: <?php echo $data["box"]["width"]; ?>px;" id="box_new_<?php echo $data["box"]["id"]; ?>">
			<?php } else { ?>
				<div class="box_new <?php echo $data["box"]["class_name"]; ?> <?php echo $data["box"]["border_class"]; ?>" style="width: <?php echo $data["box"]["width"]; ?>px;" id="box_new_<?php echo $data["box"]["id"]; ?>">
			<?php }   $data->convertDefault = null; ?>
				<?php $data->convertDefault = false; if($caller->permission("boxes_all")&&! $caller->adminAsUser()) { $data->convertDefault = null; ?>
					<div class="adminhead">
						<div class="actions">
							<a href="boxes_new/<?php echo $data["id"]; ?>/add?insertafter=<?php echo $data["box"]["sort"]; ?>&redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->URL() : ""); ?>" title="<?php echo lang("new_box", "new_box"); ?>" rel="dropdownDialog">
								<img src="images/16x16/add.png" alt="<?php echo lang("new_box", "new_box"); ?>" />
							</a>
							
							<a class="noAutoHide" href="boxes_new/<?php echo $data["id"]; ?>/edit/<?php echo $data["box"]["id"]; ?>?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->URL() : ""); ?>" title="<?php echo lang("edit_box", "edit_box"); ?>" rel="dropdownDialog">
								<img src="images/16x16/edit.png" alt="<?php echo lang("edit_box", "edit_box"); ?>" />
							</a>
							
							<a class="" href="boxes_new/<?php echo $data["id"]; ?>/delete/<?php echo $data["box"]["id"]; ?>?redirect=<?php echo (isset($data["_SERVER_REDIRECT"]) ? $data->doObject("_SERVER_REDIRECT")->URL() : ""); ?>" title="<?php echo lang("del_box", "del_box"); ?>" rel="dropdownDialog">
								<img src="images/16x16/del.png" alt="<?php echo lang("del_box", "del_box"); ?>" />
							</a>
						</div>
					</div>
				<?php }   $data->convertDefault = null; ?>
				
				<?php $data->convertDefault = false; if(($data->doObject("box")->doObject("title") && $data->doObject("box")->doObject("title")->bool())) { $data->convertDefault = null; ?>
					<div class="header">
						<span class="title"><?php echo $data["box"]["title"]; ?></span>
					</div>
				<?php }   $data->convertDefault = null; ?>
				
				<div class="content">
					<?php echo $data["box"]["content"]; ?>
				</div>
			</div>
		
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
if(PROFILE) 
	Profiler::unmark("loops"); 
?>

	<?php } else { ?>
		<?php $data->convertDefault = false; if($caller->permission("boxes_all")&&! $caller->adminAsUser()) { $data->convertDefault = null; ?>
			<div class="no_box">
				<a rel="dropdownDialog" href="boxes_new/<?php echo $data["id"]; ?>/add?insertafter=1"><?php echo lang("new_box", "new_box"); ?></a>
			</div>
		<?php }   $data->convertDefault = null; ?>
	<?php }   $data->convertDefault = null; ?>
</div>