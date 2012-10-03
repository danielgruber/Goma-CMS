<?php
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;) 
if(!isset($data))
	return false;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
	<head>
		<base href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>" />
		<title><?php echo $caller->lang("install.install_goma", "Install Goma"); ?></title>
		<meta charset="UTF-8" />
		<?php echo $caller->INCLUDE_CSS_MAIN("admin/style.css"); ?>
		<?php echo $caller->INCLUDE_CSS_MAIN("install.css"); ?>
		
		<?php $data->convertDefault = false; if(($data->doObject("firstrun") && $data->doObject("firstrun")->bool())) { $data->convertDefault = null; ?>
			<script type="text/javascript">
				$(function(){
					$("#wrapper_logout").fadeTo(0,0);
					$("body").append('<div id="introAnimation"><img src="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>system/templates/admin/images/logo.png" alt="Logo" /><p>Open Source CMS/Framework</p></div>');
					$("#introAnimation img").css({display: "block",position: "absolute", top: "50%", left: "50%", "margin-left": "-96px", "margin-top": "-30px"});
					$("#introAnimation img").css({top: $("#introAnimation img").offset().top, left: $("#introAnimation img").offset().left, "margin-left": "0", "margin-top": 0});
					$("#introAnimation p").fadeTo(0,0);
					
					// text-position
					setTimeout(function(){
						var textLeftPos = $("#introAnimation img").offset().left + (192 / 2) - $("#introAnimation p").outerWidth() / 2;
						$("#introAnimation p").css({position: "absolute", top: $("#introAnimation img").offset().top + 60 + 5, left: textLeftPos});
						$("#introAnimation p").fadeTo(500, 1);
					}, 100);
					
					
					// animate the logo ;)
					var logoTop = $("#logo").offset().top + (($("#logo").outerHeight() - $("#logo").height()) / 2);
					var logoLeft = $("#logo").offset().left + (($("#logo").outerWidth() - $("#logo").width()) / 2);
					setTimeout(function(){
						$("#introAnimation p").fadeTo(400, 0);
						$("#introAnimation img").animate({top: logoTop, left: logoLeft}, 750, function(){
							$("#wrapper_logout").fadeTo(500, 1, function(){
								$("#introAnimation img").remove();
							});
						});
					}, 2000);
				});
			</script>
		<?php }   $data->convertDefault = null; ?>
		
		<?php echo $caller->HeaderHTML(); ?>
	</head>
	<body>
		<div id="wrapper_logout">
			<div class="wrapper_inner">
				<div class="logo_wrapper">
					<div class="beside_logo">
						<a href="<?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>install/langselect/" title="<?php echo lang("switchlang", "switchlang"); ?>"><img src="images/icons/fatcow-icons/32x32/locate.png" alt="<?php echo lang("switchlang", "switchlang"); ?>" /></a>
					</div>
					<a target="_blank" href="http://goma-cms.org" target="_blank"><img id="logo" src="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?>system/templates/admin/images/logo.png" alt="logo" /></a>
				</div>
				<div class="header">
					<a href="<?php echo defined("BASE_URI") ? constant("BASE_URI") : null; ?><?php echo defined("BASE_SCRIPT") ? constant("BASE_SCRIPT") : null; ?>"><h1>Goma Framework</h1></a>
				</div>
				<div class="content">
					<?php echo $caller->addContent(); ?>
					<?php echo $data["content"]; ?>
				</div>
			</div>
		</div>
	</body>
</html>