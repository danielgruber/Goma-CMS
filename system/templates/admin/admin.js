/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 01.07.2013
 */


$(document).ready(function() {
	
	var hideNavBar = function() {
		$("#navigation").stop().slideUp("fast");
	}
	
	CallonDocumentClick(hideNavBar, [$("#navigation"), $("#navi-toggle")]);
	
	goma.ui.setMainContent($("#contnet > #maincontent > content_inner"));

	$("#navi-toggle").click(function(){
		if($("#navigation").css("display") == "none") {
			$("#navigation").stop().slideDown("fast");
		} else {
			$("#navigation").stop().slideUp("fast");
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
