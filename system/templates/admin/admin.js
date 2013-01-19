/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
 *@Copyright (C) 2009 - 2012  Goma-Team
 * last modified: 19.01.2013
 */

function update() {
	if ( typeof update.lastUpdate == 'undefined') {
		update.lastUpdate = 0;
	}

	var headerWidth = $("#header").width();
	var userbarWidth = $("#userbar").outerWidth();
	var naviWidth = $("#navi").outerWidth();
	var naviWidthMax = headerWidth - userbarWidth - 50;
	var curNode;
	var active = $("#navi > ul > li.active").index() + 1;

	// Performance :)
	if (Math.abs(headerWidth - update.lastUpdate) < 25) {
		return;
	}

	update.lastUpdate = headerWidth;

	if (naviWidth <= naviWidthMax) {
		if ($("#navMore-sub li").length > 0) {
			curNode = $("#navi li.nav-inactive").first();
			if (naviWidthMax - naviWidth > curNode.outerWidth(true)) {
				$("#navMore-sub li").first().remove();
				curNode.removeClass("nav-inactive");
			}
		} else {
			$("#navMore").css("display", "none");
		}
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
	$(window).resize(update);

	update();

	$("#navMore").click(function() {
		$(this).toggleClass("open");
		$("#navMore-sub").slideToggle("fast");
	});
})
