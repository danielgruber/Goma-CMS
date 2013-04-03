/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013 Goma-Team
  * last modified: 02.04.2013
*/
(function($, w){
	w.bindHistory = function(div) {
		/**
		 * loads new elements based on the older-button
		*/
		var load = function(o) {
			// define which history is meant, maybe there are more than one on a page
			var history = o.parent();
			var older = o;
			var olderText = older.html();
			var id = o.attr("id").replace("_link", "");
			older.html('<img src="images/16x16/loading.gif" alt="loading" />');
			
			// load data from server
			$.ajax({
				url: older.attr("href"),
				success: function(html) {
					
					// prase data
					var node = $("<div></div>");
					node.append(html);
					
					// remove the button
					older.remove();
					
					// append all events
					node.find("#"+id+" .event").each(function(){
						$(this).removeClass("first");
						$(this).appendTo(history);
					});
				
					// append older button if existing
					if(node.find("#"+id+" .older").length > 0) {
						node.find("#"+id+" .older").appendTo(history);
					}
					
					// rebind events
					history.find(".event").off(".history");
					if(history.find(".older").length > 0) {
						
						// bind click
						history.find(".older").click(function(){
							return load($(this));
						});
						
						// bind mouseover
						var evLength = history.find(".event").length - 10;
						var timeout;
						var func = function(){
							history.find(".event").off(".history");
							
							// we need the timeout to prevent from firing multiple times
							clearTimeout(timeout);
							timeout = setTimeout(function(){
								history.find(".older").click();
							}, 80);
						}
						history.find(".event:gt("+evLength+")").on("mouseover.history", func);
						history.find(".event:gt("+evLength+")").on("touchmove.history", func);
						
						history.on("scoll.history", func);
					}
				}
			});
			return false;
		};
		
		// bind mouseover events
		div.each(function(){
			var history = $(this);
			if(history.find(".older").length > 0) {
				var evLength = history.find(".event").length - 10;
				var timeout;
				var func = function(){
					history.find(".event").off(".history");
					
					// we need the timeout to prevent from firing multiple times
					clearTimeout(timeout);
					timeout = setTimeout(function(){
						history.find(".older").click();
					}, 80);
				}
				history.find(".event:gt("+evLength+")").on("mouseover.history", func);
				history.find(".event:gt("+evLength+")").on("touchmove.history", func);
				history.on("scoll.history", func);
				
				if(history.find(".older").is(":visible")) {
					func();
				}
			}
		});
		
		// bind click event
		div.find(".older").click(function(){
			return load($(this));
		});
	};
})(jQuery, window);