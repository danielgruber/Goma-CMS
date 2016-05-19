/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 07.04.2013
 */
(function($, w){
	w.bindHistory = function(div, filter) {
		var f = filter;
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
				silence: true,
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
						};
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
				};
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

		if(div.find("a.newer").length == 0) {
			goma.Pusher.subscribe("presence-goma", function(){
				this.bind("history-update", function(data) {
					if(data.rendering && (!f || !data.class_name || in_array(data.class_name, f))) {
						div.prepend(data.rendering);
						div.find(".event.first").removeClass("first");
						div.find(".event:first-child").addClass("first").css("display", "none").slideDown("fast");
					}
				});
			});

		}

		function in_array(item,arr) {
			for(var p=0;p<arr.length;p++)
				if (item == arr[p])
					return true;

			return false;
		}
	};
})(jQuery, window);
