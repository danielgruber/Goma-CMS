/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 06.12.2012
  * $Version 1.2.1
*/

if(window.top != window) {
	top.location.href = location.href;
}

//preloadLang(["view_site", "view_page"]);

function pages_pushPreviewURL(publish, state, usePublish, title) {
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
			publish = publish;
		}
		show_preview(publish, state, usePublish);
		return false;	
	});
	
	if(typeof title != "undefined") {
		$("#visit_webpage .flex").html(lang("view_page").replace("%s", '<span class="page">' + title + "</span>"));
	} else {
		$("#visit_webpage .flex").html(lang("preview_site"));
	}
}

function pages_unbindPreviewURL() {
	$("#visit_webpage").unbind("click");
	$("#visit_webpage").attr("href", ROOT_PATH);
	$("#visit_webpage").removeClass("preview");
	$("#visit_webpage .flex").html(lang("preview_website"));
}

$(function(){
	goma.ui.bindUnloadEvent(goma.ui.getMainContent(), pages_unbindPreviewURL);
})

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
		zIndex: 975
	});
	
	$("#preview").html('<iframe src="'+state+'" frameBorder="0" name="previewFrame" id="previewFrame" width="100%"></iframe><div id="bottomBarWrapper"><div id="bottomBar"></div></div>');

	$("#bottomBar").html('<a class="edit flatButton" href="#"><i class="fa fa-angle-left fa-2x"></i> '+lang("edit")+'</a><a href="'+state+'" target="_blank" class="new_window">'+lang("open_in_new_tab")+' <i class="fa fa-angle-right fa-2x"></i></a><div class="previewLinks"><a href="'+state+'" target="previewFrame" class="flatButton previewLink state active">'+lang("draft")+'</a><a href="'+publish+'" target="previewFrame" class="flatButton previewLink publish">'+lang("published_site")+'</a></div><div class="clear"></div>');
	
	$("#bottomBar .state, #bottomBar .publish").click(function(){
		$("#bottomBar .state, #bottomBar .publish").removeClass("active");
		$(this).addClass("active");
		$("#bottomBar .new_window").attr("href", $(this).attr("href"));
		changeCount--;
	});
	
	
	if(publish === false) {
		$("#bottomBar .publish").remove();
	} else
	if(typeof usePublish != "undefined" && usePublish === true) {
		$("#bottomBar .publish").click();
		$("#previewFrame").attr("src", publish);
	}
	
	var updateHeight = function() {
		$("#previewFrame").height($(window).height() - $("#bottomBar").height());
	}
	$(window).resize(updateHeight);
	updateHeight();
	
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
		
		clearInterval(interval);
		
		return false;
	});
	
	$("#preview .new_window").click(function(){
		$("body").css({
			height: "",
			overflow: ""
		});
		$("#preview").animate({top: 0 - $(window).height()},300);
		
		clearInterval(interval);
		
		setTimeout(function(){
			$("#preview").remove();
		}, 400);
	});
	
	var current = state;
	var current2 = publish;
	var changeCount = -1;
	$("#previewFrame").get(0).onload = function() {
		interval = setInterval(function(){
			if($("#previewFrame").length == 0) {
				clearInterval(interval);
			}
			
			if($("#previewFrame").get(0).contentDocument) {
				var doc = $("#previewFrame").get(0).contentDocument;
			} else if($("#previewFrame").get(0).contentWindow) {
				var doc = $("#previewFrame").get(0).contentWindow;
			} else {
				return false;
			}
			
			if(current != $("#previewFrame").get(0).contentWindow.location.href && current2 != $("#previewFrame").get(0).contentWindow.location.href) {
				current = $("#previewFrame").get(0).contentWindow.location.href;
				changeCount++;
				if(changeCount > 0 && current.match(/^http\:\/\//)) {
					location.href = current;
					$("#preview .edit").click();
				}
			}
			
		}, 500);
	}
	
	return false;
}