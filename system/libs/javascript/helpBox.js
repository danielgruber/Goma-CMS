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
        for(i in data)  {
            if(typeof data[i] == "string") {
                helpData.push({selector: i, text: data[i]});
            } else {
                helpData.push($.extend({selector: i}, data[i]));
            }
        }
        
        renderHelp();
    };
    
    w.renderHelp = function() {
        for(i in helpData) {
            var data = helpData[i];
            if(!data.rendered) {
                helpData[i].id = randomString(10);
                helpData[i].rendered = true;
                
                $("body").append('<div id="'+helpData[i].id+'" class="help-box"><div class="wrapper"><div class="arrow"></div><div class="content">'+data.text+'</div></div></div>');
            }
            
            var id = helpData[i].id, box = $("#" + id), s = $(data.selector), position;
            
            if(!shown) {
	            box.css("display", "none");
            }
            
            if(s.length == 1) {
                // get position
                
                if(data.position === undefined) {
                    // get position which is logical
                    var elemtop = s.offset().top,
                    elemleft = s.offset().left,
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
                    if(position == "bottom") {
                        if((elemtop - this.dropdown.height() - 2) < -10)
                            position = "top";
                    }
                } else {
                    position = data.position;
                }
                var display = box.css("display");
                box.css("display", "block");
                
                var boxWidth = box.outerWidth(),
                boxHeight = box.outerHeight(),
                elemtop = s.offset().top,
                elemleft = s.offset().left,
                elemWidth = s.outerWidth(true),
                elemHeight = s.outerHeight(true),
                positionTop = 0, 
                positionLeft = 0;
                
                box.css("display", display);
                
                box.removeClass("position-left").removeClass("position-top").removeClass("position-bottom").removeClass("position-right").addClass("position-" + position);
                
                switch(position) {
                    case "top":
                    case "center":
                        positionTop = elemtop + elemHeight;
                        positionLeft = elemleft + elemWidth / 2 - boxWidth / 2 + 4;
                    break;
                    
                    case "left":
                        positionLeft = elemleft + elemWidth;
                        positionTop = elemtop + elemHeight / 2 - boxHeight / 2;
                    break;
                    
                    case "right":
                        positionLeft = elemleft - boxWidth;
                        positionTop = elemtop + elemHeight / 2 - boxHeight / 2;
                    break;
                    
                    case "bottom":
                        positionTop = elemtop - boxWidth;
                        positionLeft = elemleft + elemWidth / 2 - boxWidth / 2 - 4;
                    break;
                }
                
                box.css({
                    position: "absolute",
                    top: positionTop,
                    left: positionLeft
                });
                
                if(data.autoHide !== false) {
	                s.click(function(){
		               	 box.fadeOut("fast");
	                });
                }
            }
        }
    };
    
    w.showHelp = function() {
    	renderHelp();
        $(".help-box").fadeIn("fast");
        shown = true;
    };
    
    w.hideHelp = function() {
        $(".help-box").fadeOut("fast");
        shown = false;
    };
    
    $(function(){
       $(window).resize(function(){
           renderHelp();
       });
       
       $(window).scroll(function(){
           renderHelp();
       });
    });
})(jQuery);