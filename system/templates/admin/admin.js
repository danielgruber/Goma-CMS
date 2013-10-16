/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 01.07.2013
 */


$(document).ready(function() {
	
	var scroll;
	
	var hideNavBar = function() {
		if($("#head .dropdown").hasClass("show")) {
			$("#head .dropdown").removeClass("show");
			$("#head").removeClass("show-dropdown");
			
			$(window).scrollTop(scroll);
		}
	}
	
	CallonDocumentClick(hideNavBar, [$("#head")]);
	
	goma.ui.setMainContent($("#content > #maincontent > content_inner"));

	$("#navi-toggle").click(function(){
		if(!$("#head .dropdown").hasClass("show")) {
			$("#head .dropdown").addClass("show");
			$("#head").addClass("show-dropdown");
			
			scroll = $(window).scrollTop();
			
			$(window).scrollTop(0);
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

	CallonDocumentClick(hide, [$("#userbar-langSelect"), $("#userbar-langSelect ul")]);
})
