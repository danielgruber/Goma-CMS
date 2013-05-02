/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 05.02.2012
*/

$(function(){
	// just add shadows on desktop-browsers, on mobile devices there is a big performance decrease
	$(".linelink").click(function(){
		loadVersionToView($(this).attr("name").replace("version_", ""));
		return false;
	});
	$(".select").click(function(){
		loadVersionToView($(this).attr("id").replace("version_", ""));
		return false;
	});
	
	$("#version_" + self.active_version).parent().addClass("active");
	$("#version_name").html('' + $("#version_" + self.active_version).find("span").html());
	
	$("#subbar .area").fadeTo(0, 0.9);
	
	$("#subbar .actions .done").click(function(){
		$("#currentversion form .actions input").click();
	});
	
	$("#subbar .actions .restore").click(function(){
		$("#selectedversion form .actions input").click();
	});
	
	// pagination in the timeline
	var page = 1;
	var perPage = 41;
	var pages = Math.ceil($("#timeline > div > div > ul > li").length / perPage);
	
	$("#timeline > div > .left, #timeline > div > .right").css("display", "none");
	if($("#timeline > div > div > ul > li").length > perPage) {
		
		if(page == 1 || page < 1) {
			page = 1;
			$("#timeline > div > .left").css("display", "block");
		} else if(pages > page) {
			$("#timeline > div > .left, #timeline > div > .right").css("display", "block");
		} else if(pages == page || page < pages) {
			page = pages; // correct if things happening wrong
			$("#timeline > div > .right").css("display", "block");
		}
	}
	
	// bind events
	$("#timeline > div > .left").click(function(){
		page++;
		$("#timeline > div > .left, #timeline > div > .right").css("display", "none");
		if(page == 1 || page < 1) {
			page = 1;
			$("#timeline > div > .left").css("display", "block");
		} else if(pages > page) {
			$("#timeline > div > .left, #timeline > div > .right").css("display", "block");
		} else if(pages == page || page < pages) {
			page = pages; // correct if things happening wrong
			$("#timeline > div > .right").css("display", "block");
		}
		var left = 0 - 10020 + 1300 * page;
		$("#timeline > div > .wrapper").stop().animate({left: left}, 300);
	});
	$("#timeline > div > .right").click(function(){
		page--;
		$("#timeline > div > .left, #timeline > div > .right").css("display", "none");
		if(page == 1 || page < 1) {
			page = 1;
			$("#timeline > div > .left").css("display", "block");
		} else if(pages > page) {
			$("#timeline > div > .left, #timeline > div > .right").css("display", "block");
		} else if(pages == page || page < pages) {
			page = pages; // correct if things happening wrong
			$("#timeline > div > .right").css("display", "block");
		}
		var left = 0 - 10020 + 1300 * page;
		$("#timeline > div > .wrapper").stop().animate({left: left}, 300);
	});
	
});

// loads a version from the server
function loadVersionToView(id) {
	if($("#version_" + id).parent().hasClass("currentversion")) {
		return false;
	}
	$("#selectedversion > div").html('<div class="loading" align="center"><img src="images/loading.gif" alt="Loading" /></div>');
	$.ajax({
		url: version_namespace + "/getVersion/" + id,
		dataType: "json",
		success: function(json, code, xhr) {
			
			renderResponseTo(json.form, $("#selectedversion > div"), xhr);
			self.active_version = json.active;
			
			$("#timeline > div > .wrapper > ul > li.active").removeClass("active");
			$("#version_" + self.active_version).parent().addClass("active");
			$("#version_name").html('' + $("#version_" + self.active_version).find("span").html());
		},
		error: function() {
			alert("Error at parsing the data.");
		}
	});
}