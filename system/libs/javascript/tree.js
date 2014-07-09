/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 29.03.2013
*/

function tree_bind(tree) {
	var node = tree;
	tree = null;
	node.find(".tree").removeClass("tree");
	node.find(".hitarea a").unbind("click");
	node.find(".hitarea a").click(function(){
		var link = $(this);
		if(link.parent().hasClass("disabled")) {
			alert("Please wait a second and try again.");
			return 0;
		}
		var li = link.parent().parent().parent().parent();
		if(link.parent().hasClass("collapsed")) {
			// if ajax
			if(link.parent().hasClass("ajax") && link.parent().find(".load").length == 0) {
				li.addClass("expanded").removeClass("collapsed");
				li.append("<ul><li class=\"load\"><span class=\"a\"><span class=\"b\"><img src=\"images/16x16/loading.gif\" alt=\"\" /> Loading...</span></span></li></ul>");
				
				link.parent().removeClass("ajax");
				$.ajax({
					url: link.attr("href"),
					success: function(html) {
						li.removeClass("expanded").addClass("collapsed");
						li.find(" > ul").remove();
						li.append(html);
						tree_bind(li.find(" > ul"));
						
						setTimeout(function(){
							li.find(" > ul").slideDown(150, function(){
								li.addClass("expanded").removeClass("collapsed");
							});
							link.parent().addClass("expanded").removeClass("collapsed");
						}, 50);
						link.attr("href","treeserver/setCollapsed/"+link.attr("name")+"/"+link.attr("id")+"/?redirect="+ escape(location.pathname + location.search));
						li.find(".tree").removeClass("tree");
						node.trigger("treeupdate", [li]);
					}
				});
				return false;
			} else {
				$.ajax({
					url: 	link.attr("href")
				});
				// expand
				li.find(" > ul").slideDown(150, function(){
					li.addClass("expanded").removeClass("collapsed");
				});
				link.parent().addClass("expanded").removeClass("collapsed");
				link.attr("href","treeserver/setCollapsed/"+link.attr("name")+"/"+link.attr("id")+"/?redirect="+ escape(location.pathname + location.search));
			}
			
		} else {
			$.ajax({
				url: 	link.attr("href")
			});
			// collapse
			li.find(" > ul").slideUp(150, function(){
				li.removeClass("expanded").addClass("collapsed");
			});
			link.parent().removeClass("expanded").addClass("collapsed");
			link.attr("href","treeserver/setExpanded/"+link.attr("name")+"/"+link.attr("id")+"/?redirect="+ escape(location.pathname + location.search));
		}
		
		return false;
	});
}


$(function(){
	$(".tree").each(function(){
		tree_bind($(this) );
	});
});