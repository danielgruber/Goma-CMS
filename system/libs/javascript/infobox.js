/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 08.09.2011
*/

//You need an anonymous function to wrap around your function to avoid conflict
(function($){
 	"use strict";
 	
    $.fn.extend({ 
         
        g_infobox: function(options) {
			var defaults = {
				"text"	: "title",
				"useattr": true,
				"useparent": false,
				"parent": ""
			};
			var o = $.extend(defaults, options);
            return this.each(function() {
             
                //code to be inserted here
             	var element = $("<div></div>").addClass("infobox").css({display: "none", position: "absolute", zIndex: 998}).append('<span class="text"></span>');
             	element.appendTo("body");
             	element.find(" > .text").css("display", "block");
             	
             	if(o.useattr) {
             		element.find(" > .text").html($(this).attr(o.text));
             	} else if(o.useparent) {
             		element.find(" > .text").html($(this).parents(o.parent).find(o.text).text());
             	} else {
             		element.find(" > .text").html($(this).find(o.text).text());
             	}
             	element.css({
             		"background": "url("+root_path+"system/templates/images/triangle_for_information.png) no-repeat 115px 0px",
             		"padding-top": "25px",
             		"z-index": 500,
             		"width": 300
             	});
             	
             	element.find(" > .text").css({
             		"background": "#2d2d2d", 
             		"-webkit-box-shadow": "0 0 20px #2d2d2d",
             		"-moz-box-shadow": "0 0 20px #2d2d2d",
             		"-khtml-box-shadow": "0 0 20px #2d2d2d",
             		"box-shadow": "0 0 20px #2d2d2d",
             		"color": "#ddd",
             		"padding": "10px"
             	});
             	
             	var timeout;
             	var hide = function(){
             		timeout = setTimeout(function(){
             			element.fadeOut(100);
             		}, 500);
             	}
             	var show = function(e) {
             		
             		clearTimeout(timeout);
             		if(element.css("display") == "none") {
             			$(".infobox").css("display", "none");
	             		// make position
	             		var left = $(this).offset().left + ($(this).width() / 2) - 144;
	             		var top = $(this).offset().top + $(this).height() + parseInt($(this).css("padding-top"));
	             		element.css({left: left, top: top}).css("display", "block");
	             		element.fadeIn(100);
	             	}
             	}
             	
             	// bind events
             	$(this).hover(show, hide);
                element.hover(show, hide);
                /*element.mousedown(function(){
                	$(this).css("display", "none");
                });
                element.mouseup(function(){
                	setTimeout(show, 10);
                });*/
            });
        }
    });
     
})(jQuery);