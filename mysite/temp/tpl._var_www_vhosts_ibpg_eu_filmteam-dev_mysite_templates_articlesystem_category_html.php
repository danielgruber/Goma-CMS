<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<?php echo $caller->INCLUDE_CSS("article.css"); ?>

<h1><?php echo $data["Title"]; ?></h1>

<?php echo $data["data"]; ?>

<?php $data->convertDefault = false; if(($data->doObject("categories") && $data->doObject("categories")->bool())) { $data->convertDefault = null; ?>
	<div id="articlecategories">
		<ul>
			
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->categories();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["category"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("category")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("category")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

				<li><h3><a href="<?php echo $data["category"]["url"]; ?>"><?php echo $data["category"]["Title"]; ?></a></h3><div><?php echo $data["category"]["data"]; ?></div></li>
			
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

		</ul>
	</div>
<?php }   $data->convertDefault = null; ?>

<?php $data->convertDefault = false; if(($data->doObject("articles") && $data->doObject("articles")->bool())) { $data->convertDefault = null; ?>
	<div id="articles">
		<?php echo $caller->articles()->activatepages($data['_GET_articlepage']); ?>
		
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->articles();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["article"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("article")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("article")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

			<div class="article">
				<h2><a href="<?php echo $data["article"]["url"]; ?>"><?php echo $data["article"]["Title"]; ?></a></h2>
				<div class="info">
					<?php echo lang("ar_written_by", "ar_written_by"); ?> <?php echo (isset($data["article"]["autor"]["nickname"]) ? $data["article"]["autor"]->doObject("nickname")->text() : ""); ?> | <?php echo $data["article"]["comments"]["count"]; ?> <?php echo lang("co_comments", "co_comments"); ?>
				</div>
				<div class="description"><?php echo $data["article"]["description"]; ?></div>
				<div class="read_more"><a href="<?php echo $data["article"]["url"]; ?>"><?php echo lang("ar_read_more", "ar_read_more"); ?></a></div>
			</div>
		
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

		<div class="pages">
			<?php echo lang("page", "page"); ?>: 
			
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->articles()->pages();
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

				<?php $data->convertDefault = false; if(($data->doObject("pages")->doObject("black") && $data->doObject("pages")->doObject("black")->bool())&&($data->doObject("pages")->doObject("active") && $data->doObject("pages")->doObject("active")->bool())) { $data->convertDefault = null; ?>
					<span class="active"><?php echo $data["pages"]["page"]; ?></span>
				<?php $data->convertDefault = false; } else if(($data->doObject("pages")->doObject("black") && $data->doObject("pages")->doObject("black")->bool())) {  $data->convertDefault = null; ?>
					<?php echo $data["pages"]["page"]; ?>
				<?php } else { ?>
					<a href="<?php echo defined("URL") ? constant("URL") : null; ?>?pa=<?php echo $data["pages"]["page"]; ?>" class="link"><?php echo $data["pages"]["page"]; ?></a>
				<?php }   $data->convertDefault = null; ?>
			
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

		</div>
	</div>
<?php }   $data->convertDefault = null; ?>