/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 14.11.2012
  * $Version 1.2
*/

function pages_pushPreviewURL(publish, state, usePublish) {
	if(publish !== false) {
		$("#visit_webpage").attr("href", publish);	
	} else {
		$("#visit_webpage").attr("href", state);
	}
	
	if($("#visit_webpage").hasClass("preview")) {
		$("#visit_webpage").unbind("click");
	} else {
		$("#visit_webpage").addClass("preview");
	}
	
	$("#visit_webpage").click(function(){
		if(publish !== false) {
			publish = publish + "&preview=1";
		}
		show_preview(publish, state + "&preview=1", usePublish);
		return false;	
	});
}

function pages_unbindPreviewURL() {
	$("#visit_webpage").unbind("click");
	$("#visit_webpage").attr("href", ROOT_PATH);
	$("#visit_webpage").removeClass("preview");
}

function show_preview(publish, state, usePublish) {
	$("body").append('<div id="preview"></div>');
	$('html,body').animate({scrollTop: 0, scrollLeft: 0}, 300);
	$("body").css({
		height: "100%",
		overflow: "hidden"
	});
	
	$("#preview").css({
		background: "#fff",
		position: "absolute",
		top: 0 - $(window).height(),
		left: 0,
		width: "100%",
		height: "100%",
		zIndex: 900
	});
	
	$("#preview").html('<iframe src="'+state+'" frameBorder="0" name="previewFrame" id="previewFrame" width="100%" height="100%"></iframe><div id="bottomBarWrapper"><div id="bottomBar"></div></div>');
	
	$("#bottomBarWrapper").css({
		position: "absolute",
		bottom: 0,
		left: 0,
		width: "100%",
	});
	$("#bottomBar").html('<a class="edit flatButton" href="#">&laquo; '+lang("edit")+'</a>&nbsp;<a href="'+state+'" target="_blank" class="new_window">'+lang("open_in_new_tab")+'</a><div class="previewLinks"><a href="'+publish+'" target="previewFrame" class="flatButton previewLink publish">'+lang("published_site")+'</a><a href="'+state+'" target="previewFrame" class="flatButton previewLink state active">'+lang("draft")+'</a></div><div class="clear"></div>');
	
	$("#bottomBar .state, #bottomBar .publish").click(function(){
		$("#bottomBar .state, #bottomBar .publish").removeClass("active");
		$(this).addClass("active");
		$("#bottomBar .new_window").attr("href", $(this).attr("href"));
	});
	
	
	if(publish === false) {
		$("#bottomBar .publish").unbind("click");
		$("#bottomBar .publish").removeAttr("href");
		$("#bottomBar .publish").fadeTo(0, 0.4);
	} else
	if(typeof usePublish != "undefined" && usePublish === true) {
		$("#bottomBar .publish").click();
		$("#previewFrame").attr("src", publish);
	}
	
	
	$("#preview").animate({top: 0}, 150);
	setTimeout(function(){
		$("#preview").css({top: 0});
	}, 200);
	
	
	$("#preview .edit").click(function(){
		$("body").css({
			height: "",
			overflow: ""
		});
		$("#preview").animate({top: 0 - $(window).height()},300);
		
		setTimeout(function(){
			$("#preview").remove();
			
		}, 400);
		return false;
	});
	
	$("#preview .new_window").click(function(){
		$("body").css({
			height: "",
			overflow: ""
		});
		$("#preview").animate({top: 0 - $(window).height()},300);
		
		setTimeout(function(){
			$("#preview").remove();
			
		}, 400);
	});
}