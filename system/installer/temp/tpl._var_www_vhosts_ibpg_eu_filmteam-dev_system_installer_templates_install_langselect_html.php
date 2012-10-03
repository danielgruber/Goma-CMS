<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<script type="text/javascript">
	var welcome = [
		"Herzlich Willkommen",
		"welcome",
		"benvenuto",
		"welkom",
		"velkommen",
		"bienvenue",
		"w&euml;llkom",
		"f&agrave;ilte",
		"ben&egrave;nnidu",
		"tonga soa",
		"haere mai",
		"dobredojde",
		"Sean bienvenidos"
	];
	
	welcome = array_shuffle(welcome);
	
	$(function(){
		var i = 0;
		var a = 0;
		var last;
		var beforeLast;
		$("#welcome_animation").css("display", "block");
		var intro = function(init) {
			if(i == welcome.length) {
				i = 0;
				welcome = array_shuffle(welcome);
			} else {
				i++;
			}
			$("#welcome_animation").append('<span class="welcome_lang"></span>');
			$("#welcome_animation span:last").html(welcome[i]);
			$("#welcome_animation span:last").css({"left": $("#wrapper_logout .content").width(), "top": a * 30 + 13});
			if(typeof init != "undefined") {
				$("#welcome_animation span:last").css({"left": Math.round(Math.random() * $("#wrapper_logout .content").width() / 2)});
				var duration = 10000;
			} else {
				var duration = 14000;
			}
			$("#welcome_animation span:last").animate({left: "-400"}, duration, function(){
				$(this).remove();
			});
			a++;
			if(a == 3) {
				a = 0;
			}
		}
		
		// init some
		intro(true);
		intro(true);
		intro(true);
		intro();
		intro();
		setInterval(intro, 1200);
	});
</script>
<div id="welcome_animation">
	
</div>
<h3>Please select your Language:</h3>
<div id="langSelect">
	<div class="selectBox">
		<ul>
			
<?php 
	// begin control
	array_push($callerStack, clone $caller); 
	array_push($dataStack, clone $data); 
	$value = $caller->languages();
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customised["lang"] = $data_loop;
			if(is_object($data_loop)) 
				$caller->callers[strtolower("lang")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("lang")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>

				<li>
					<a href="<?php echo $caller->addParamToURL($data['_SERVER_REDIRECT'], "setlang", $data['lang']['code']); ?>">
						<img src="<?php echo $data["lang"]["icon"]; ?>" alt="<?php echo $data["currentLang"]["code"]; ?>" />
						<span><?php echo $data["lang"]["title"]; ?></span>
					</a>
				</li>
			
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>

		</ul>
		<div class="clear"></div>
	</div>
</div>