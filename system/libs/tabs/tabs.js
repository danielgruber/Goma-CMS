/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 05.06.2011
*/
(function($){
	$.fn.gtabs = function(options) {
		var defaults = {"animation":true, "cookiename":"tabs_whole"};
		var o = $.extend(defaults, options);
		this.each(function(){
			var tabs = $(this);
			tabs.find(" > ul").find("a, input, button").click(function(){
				var oldtab = tabs.find(" > div.active");
				var id = $(this).attr("id");
				id = id.substring(0, id.lastIndexOf("_"));
				
				var newtab = $("#" + id)
				
				oldtab.removeClass("active");
				oldtab.css("height", "");
				var oldheight = oldtab.height();
				
				tabs.find(" > ul .active").removeClass("active");
				$(this).addClass("active");
				newtab.addClass("active");
				//setCookie(o.cookiename, $("#" + newtab.attr("id") + "_tab").attr("name").substr(5));
				var newheight = newtab.height();
				
				if($(this).hasClass("ajax")) {
					$.ajax({
						url: $(this).attr("href"),
						dataType: "json",
						success: function(obj) {
							if(typeof obj != "object") {
								newtab.html(obj);
							} else {
								newtab.html(obj.content);
								$("#" + newtab.attr("id") + "_tab").html(obj.title);
							}
						}
					});
				}
				
				return false;
			});
		});
	}
})(jQuery);
