/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
 *@Copyright (C) 2009 - 2012  Goma-Team
 * last modified: 21.01.2013
 */

function update() {
	var headerWidth = $("#header").width();
	var userbarWidth = $("#userbar").outerWidth();
	var naviWidth = $("#navi").outerWidth();
	var naviWidthMax = headerWidth - userbarWidth - 50;
	var curNode;
	var active = $("#navi > ul > li.active").index() + 1;

	update.lastUpdate = headerWidth;

	if (naviWidth <= naviWidthMax) {
		if ($("#navMore-sub li").length > 0) {
			curNode = $("#navi li.nav-inactive").first();
			if (naviWidthMax - naviWidth > curNode.outerWidth(true)) {
				$("#navMore-sub li").first().remove();
				curNode.removeClass("nav-inactive");
			}
		}
		$("#navMore").css("display", "none");
	} else {
		while ($("#navi").outerWidth() > naviWidthMax) {
			curNode = $("#navi").find(" > ul > li").not($("#navMore")).not($("#navi li.active")).not($("#navi li.nav-inactive")).last();
			curNode.clone().prependTo("#navMore-sub");
			curNode.addClass("nav-inactive");
			$("#navMore").css("display", "block");
		}
	}
}


$(document).ready(function() {
	$("#userbar").addClass("userbar-js");

	// Timeout for more smoothness
	var timeoutEnd;
	$(window).resize(function() {
		clearTimeout(timeoutEnd);
		doit = setTimeout(function() {
			update();
		}, 200);
	});

	update();

	$("#navMore").click(function() {
		$(this).toggleClass("open");
		$("#navMore-sub").slideToggle("fast");
	});
})
