/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012 Goma-Team
  * last modified: 25.11.2012
*/
(function($, w){
	w.bindHistory = function() {
		$("div.history .older").unbind("click");
		$("div.history .event").unbind("mouseover");
		
		/**
		 * loads new elements based on the older-button
		*/
		var load = function(o){
			
			// define which history is meant, maybe there are more than one on a page
			var history = o.parent();
			var older = o;
			var olderText = older.html();
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
					node.find(".event").each(function(){
						$(this).removeClass("first");
						$(this).appendTo(history);
					});
				
					// append older button if existing
					if(node.find(".older").length > 0) {
						node.find(".older").appendTo(history);
					}
					
					// rebind events
					history.find(".event").unbind("mouseover");
					if(history.find(".older").length > 0) {
						
						// bind click
						history.find(".older").click(function(){
							return load($(this));
						});
						
						// bind mouseover
						var evLength = history.find(".event").length - 10;
						var timeout;
						history.find(".event:gt("+evLength+")").mouseover(function(){
							history.find(".event").unbind("mouseover");
							
							// we need the timeout to prevent from firing multiple times
							clearTimeout(timeout);
							timeout = setTimeout(function(){
								history.find(".older").click();
							}, 80);
						});
					}
				}
			});
			return false;
		};
		
		// bind mouseover events
		$("div.history").each(function(){
			var history = $(this);
			if(history.find(".older").length > 0) {
				var evLength = history.find(".event").length - 10;
				var timeout;
				history.find(".event:gt("+evLength+")").mouseover(function(){
					history.find(".event").unbind("mouseover");
					
					// we need the timeout to prevent from firing multiple times
					clearTimeout(timeout);
					timeout = setTimeout(function(){
						history.find(".older").click();
					}, 80);
				});
			}
		});
		
		// bind click event
		$("div.history .older").click(function(){
			return load($(this));
		});
	};
})(jQuery, window);