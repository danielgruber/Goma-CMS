/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 13.11.2013
*/

// put the data into the right namespace
(function($){
    var helpData = [], w = window, shown = false;
    
    w.addHelp = function(data) {
        for(var i in data)  {
            if(typeof data[i] == "string") {
                helpData.push({selector: i, text: data[i]});
            } else {
                helpData.push($.extend({selector: i}, data[i]));
            }
        }
        
        $(renderHelp);
    };
    
    w.renderHelp = function() {
        for(var i in helpData) {
            var data = helpData[i];
            if(!data.rendered) {
                helpData[i].id = randomString(10);
                helpData[i].rendered = true;
                
                $("body").append('<div id="'+helpData[i].id+'" class="help-box"><div class="wrapper"><div class="arrow"></div><div class="content">'+data.text+'</div></div></div>');
            }
            
            var id = helpData[i].id,
                box = $("#" + id),
                element = $(data.selector + ":first"),
                position;

            if(!shown) {
	            box.css("display", "none");
            } else {
	            box.css("display", "block");
            }
            
            if(element.length >= 1) {
            	if(!element.is(":visible") || element.width() < 5) {
	            	box.css("display", "none");
	            	continue;
            	}
                // get position
                
                if(data.position === undefined) {
                    // get position which is logical
                    var elemtop = element.offset().top,
                    elemleft = element.offset().left,
                    elemright = $(document).width() - elemleft;
                
                    if(elemleft < 100 && elemtop > 100) {
                        position = "right";
                    } else if(elemright < 100 && elemtop > 100) {
                        position = "left";
                    } else {
                        if(elemtop > ($(window).height() * 0.7)) {
                            position = "bottom";
                        } else {
                            position = "top";
                        }
                    }
    
                    // validate
                    if(position === "top") {
                        if((elemtop - box.height() - 2) < -10)
                            position = "bottom";
                    }
                } else {
                    position = data.position;
                }
                
                var display = box.css("display");
                box.css("display", "block");
                
                var boxWidth = box.outerWidth(),
                boxHeight = box.outerHeight(),
                elemtop = element.offset().top,
                elemleft = element.offset().left,
                elemWidth = element.outerWidth(true),
                elemHeight = element.outerHeight(true),
                positionTop = "auto", 
                positionLeft = "auto",
                positionRight = "auto",
                positionBottom = "auto",
                _position = "absolute";
                
                
                box.css("display", display);
                
                box.removeClass("position-left").removeClass("position-top").removeClass("position-bottom").removeClass("position-right").addClass("position-" + position);
                
                // reset
                box.find(".arrow").css({left: "", right: "", top: "", bottom: ""});
                
                switch(position) {
                    case "bottom":
                    case "center":
                        positionTop = elemtop + elemHeight;
                        positionLeft = elemleft + elemWidth / 2 - boxWidth / 2 + 4;
                        
                        if(positionLeft < 0) {
	                        positionLeft = 0;
	                        box.find(".arrow").css("left", 5);
                        }
                    break;
                    
                    case "right":
                        positionLeft = elemleft + elemWidth;
                        positionTop = elemtop + elemHeight / 2 - boxHeight / 2;
                    break;
                    
                    case "left":
                        positionLeft = elemleft - boxWidth;
                        positionTop = elemtop + elemHeight / 2 - boxHeight / 2;
                    break;
                    
                    case "top":
                        positionTop = elemtop - boxWidth;
                        positionLeft = elemleft + elemWidth / 2 - boxWidth / 2 - 4;
                    break;
                    case "fixed":
                    	_position = "fixed";
                    	
                    	if(data.top !== undefined)
                    		positionTop = data.top;
                    		
                    	if(data.left !== undefined)
                    		positionLeft = data.left;
                    		
                    	if(data.bottom !== undefined)
                    		positionBottom = data.bottom;
                    	
                    	if(data.Right !== undefined)
                    		positionRight = data.right;
                    break;
                }

                box.css({
                    position: _position,
                    top: positionTop,
                    left: positionLeft,
                    right: positionRight,
                    bottom: positionBottom
                });
            } else {
                box.css("display", "none");
            }
        }
    };
    
    w.showHelp = function() {
        $(".help-box").fadeIn("fast");
        shown = true;
        $(renderHelp);
        
    };
    
    w.hideHelp = function() {
        $(".help-box").fadeOut("fast");
        shown = false;
    };
    
    $(function(){
       $(window).on("resize scroll updatehtml", renderHelp);
    });
})(jQuery);