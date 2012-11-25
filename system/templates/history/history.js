/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012 Goma-Team
  * last modified: 25.11.2012
*/
(function($, w){
	w.bindHistory = function() {
		var load = function(o){
			var history = o.parent();
			var older = o;
			var olderText = older.html();
			older.html('<img src="images/16x16/loading.gif" alt="loading" />');
			$.ajax({
				url: older.attr("href"),
				success: function(html) {
					var node = $("<div></div>");
					node.append(html);
					older.remove();
					node.find(".event").each(function(){
						$(this).removeClass("first");
						$(this).appendTo(history);
					});
					if(node.find(".older").length > 0) {
						node.find(".older").appendTo(history);
					}
					$(".history .older").click(function(){
						return load($(this));
					});
				}
			});
			return false;
		};
		$(".history .older").click(function(){
			return load($(this));
		});
	};
})(jQuery, window);