/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 14.11.2013
 */


$(function() {
	
	var scroll, scrollLeft;
	
	var hideNavBar = function() {
		if($("#head .dropdown").hasClass("show")) {
			$("#head .dropdown").removeClass("show");
			$("#head").removeClass("show-dropdown");
			
			$(window).scrollTop(scroll);
			$(window).scrollLeft(scrollLeft);
		}
	}
	
	CallonDocumentClick(hideNavBar, [$("#head")]);
	
	goma.ui.setMainContent($("#content > #maincontent > content_inner"));

	$("#navi-toggle").click(function(){
		if(!$("#head .dropdown").hasClass("show")) {
			$("#head .dropdown").addClass("show");
			$("#head").addClass("show-dropdown");
			
			scroll = $(window).scrollTop();
			scrollLeft = $(window).scrollLeft();
			
			$(window).scrollTop(0);
			$(window).scrollLeft(0);
		} else {
			hideNavBar();
		}
	});
	
	var hide = function() {
		$("#userbar-langSelect ul").clearQueue().stop().slideUp(100);
		$("#userbar-langSelect").removeClass("active");
	}
	
	$("#userbar-langSelect > a").click(function() {
		$(this).parent().toggleClass("active");
		$(this).parent().find("ul").clearQueue().stop().slideToggle(100);
		return false;
	});
	
	if(getCookie("help") != 1) {
		$("#help-button").removeClass("active");
		hideHelp();
	} else {
		$("#help-button").addClass("active");
		setTimeout(function(){
			showHelp();
		}, 500);
	}
	
	$("#help-button").click(function(){
		if($(this).hasClass("active")) {
			hideHelp();
			setCookie("help", 2, 365);
		} else {
			showHelp();
			setCookie("help", 1, 365);
		}
		$(this).toggleClass("active");
		
		return false;
	});

	if($("#flush-log-recommend").length == 1) {
		$("#flush-log-recommend").remove();
		
		setTimeout(function(){
			$.ajax({
				url: BASE_SCRIPT + "admin/flushLog/"
			});
		}, 3000);
	}

	CallonDocumentClick(hide, [$("#userbar-langSelect"), $("#userbar-langSelect ul")]);
});
