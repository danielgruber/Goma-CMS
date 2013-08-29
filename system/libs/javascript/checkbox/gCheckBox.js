/**
 * provides a bit nicer Checkboxes in Javascript.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\JS-Framework
 * @version 1.0.4
*/
 
(function( $ ) {
 
 	'use strict';
 
    // Plugin definition.
    $.fn.gCheckBox = function( options ) {
    	
    	
        var settings = $.extend( true, {}, $.fn.gCheckBox.defaults, options );
        
        goma.ui.load("draggable");
        goma.ui.load("jquery-color");
        
        return this.each(function() {
        	
        	 var $wrapper,
        	 Init = function(elem) {
	        	
	        	// init the wrapper
				$wrapper = $( "<div />" )
			    	.attr( settings.wrapper.attrs )
			    	.css( settings.wrapper.css );
			    
			    // labels
			    var labelOn = $("<label />")
			    	.attr( settings.labelOn.attrs )
			    	.css( settings.labelOn.css )
			    	.text( settings.labelOn.text ),
			    
			    labelOff = $("<label />")
			    	.attr( settings.labelOff.attrs )
			    	.css( settings.labelOff.css )
			    	.text( settings.labelOff.text ),
			    	
			    // handle
			    handle = $("<div />")
			    	.attr( settings.handle.attrs )
			    	.css( settings.handle.css );
			    	
				// bring everything together
			    elem.wrap($wrapper);
			    
			    console.log("Init. Wrap.");
				$wrapper = elem.parent();
			    
				labelOn.appendTo($wrapper);
				labelOff.appendTo($wrapper);
			    handle.appendTo($wrapper);
			    
			    return $wrapper;
			},
			
        
        	$this = $(this);
        	
        	if ($this.get(0).tagName.toLowerCase() == "input" && $this.attr("type").toLowerCase() == "checkbox") {
        		Init($this);
        		
				// get colors
				var borderColor = $wrapper.css("border-top-color"),
				bgColor = $wrapper.css("background-color");
				
				$wrapper.removeClass(settings.activeClass);
				
				var borderColorActive = $wrapper.css("border-top-color"),
				bgColorActive = $wrapper.css("background-color");
				
				// some classes
				$wrapper.addClass(settings.activeClass);
				
				if ($this.prop("checked")) {
					$wrapper.addClass(settings.activeClass);
				}
				
				// functions, who need the wrapper.
	        	var currentlyClicking = false,
	        	acurrentlyClicking = function() {
			        currentlyClicking = true;
			        setTimeout(function() {
			        	currentlyClicking = false;
			        }, 20);
		        },
		        
				switchValueToOff = function() {
					// run drag-animation.
					$wrapper.find("div").stop().css({"left": $wrapper.find("div").position().left, "right": "auto"}).animate({"left": 0}, 300, function() {
						$wrapper.removeClass(settings.activeClass);
						$wrapper.css({backgroundColor: "", borderColor: ""});
					});
					
					// animate color
					$wrapper.stop().animate({backgroundColor: bgColor, borderColor: borderColor}, 290);
					
					// set value
					$wrapper.find("input").prop("checked", false).change();
				},
				
				switchValueToOn = function() {
					// calculate and run drag-animation
					var left = $wrapper.width() - $wrapper.find("div").width();
					$wrapper.find("div").stop().animate({left: left}, 300, function() {
						$wrapper.addClass(settings.activeClass);
						$wrapper.find("div").css({left: "auto", right: 0});
						$wrapper.css({backgroundColor: "", borderColor: ""});
					});
					
					// animate color
					$wrapper.stop().animate({backgroundColor: bgColorActive, borderColor: borderColorActive}, 290);
					
					// set value
					$wrapper.find("input").prop("checked", true).change();
				},
				
				// called when switch is clicked.
				switchValue = function() {
					if (currentlyClicking) {
						return ;
					}
					
					acurrentlyClicking();
					var $wrapper = $(this);
					// if checked.
					if ($wrapper.find("input").prop("checked")) {
						switchValueToOff();
					} else {
						switchValueToOn();
					}
				},
		        
		        // inits drag-functionallity.
		        InitDrag = function() {
		        	var x2 = $wrapper.offset().left + $wrapper.outerWidth() - $wrapper.find("div").width() - 4;
			        $wrapper.find("div").draggable({
				        axis: "x",
				        appendTo: $wrapper,
				        iframeFix: true,
				        containment: [$wrapper.offset().left, $wrapper.offset().top, x2, $wrapper.offset().top],
				        start: function() {
				        	x2 = $wrapper.offset().left + $wrapper.outerWidth() - $wrapper.find("div").width() - 4;
					        $wrapper.find("div").draggable("option", "containment", [$wrapper.offset().left, $wrapper.offset().top, x2, $wrapper.offset().top]);
					        $wrapper.find("div").addClass("in-drag");
				        },
				        // shows the current status while dragging.
				        drag: function() {
					        var maxLeft = $wrapper.width() - $wrapper.find("div").width(),
				        	left = $wrapper.find("div").position().left;
				        	
				        	// the borders are different if checked or not.
				        	if ($wrapper.find("input").prop("checked")) {
				        		if (left < maxLeft / 1.3) {
				        			$wrapper.removeClass(settings.activeClass);
				        			$wrapper.stop().animate({backgroundColor: bgColor, borderColor: borderColor}, 200);
				        		} else {
				        			$wrapper.addClass(settings.activeClass);
					        		$wrapper.stop().animate({backgroundColor: bgColorActive, borderColor: borderColorActive}, 200);
				        		}
				        	} else {
					        	if (left < maxLeft / 3) {
					        		$wrapper.removeClass(settings.activeClass);
						        	$wrapper.stop().animate({backgroundColor: bgColor, borderColor: borderColor}, 200);
					        	} else {
					        		$wrapper.addClass(settings.activeClass);
						        	$wrapper.stop().animate({backgroundColor: bgColorActive, borderColor: borderColorActive}, 200);
					        	}
				        	}
				        	
				        },
				        
				        // stops and sets the correct status after drag is complete.
				        stop: function() {
				        	var maxLeft = $wrapper.width() - $wrapper.find("div").width(),
				        	left = $wrapper.find("div").position().left;
				        	
				        	$wrapper.find("div").removeClass("in-drag");
				        	
				        	// the borders are different if checked or not.
				        	if ($wrapper.find("input").prop("checked")) {
					        	if (left < maxLeft / 1.3) {
						        	switchValueToOff();
					        	} else {
						        	switchValueToOn();
					        	}
					        } else {
						        if (left < maxLeft / 3) {
						        	switchValueToOff();
					        	} else {
						        	switchValueToOn();
					        	}
					        }
				        }
			        });
			        
					$wrapper.hover(function() {
			        	x2 = $wrapper.offset().left + $wrapper.outerWidth() - $wrapper.find("div").width() - 4;
					    $wrapper.find("div").draggable("option", "containment", [$wrapper.offset().left, $wrapper.offset().top, x2, $wrapper.offset().top]);
			        });
		        };
				
				if (!$this.prop("disabled")) {
				    $wrapper.click(switchValue);
						
					InitDrag();
				} else {
					$wrapper.addClass("disabled");
				}
				
				
				$wrapper.disableSelection();
				
				$this.on("click change", function() {
					if (currentlyClicking) {
						return ;
					}
					
					acurrentlyClicking();
					if ($this.prop("checked")) {
						switchValueToOn($wrapper);
					} else {
						switchValueToOff($wrapper);
					}
				});
			}
	        
        });
    };
    
	/**
	 * defaults
	*/
    $.fn.gCheckBox.defaults = {
    	wrapper: {
	    	attrs: {
		    	"class": "g-checkbox"
	    	},
	    	css: {}
    	},
    	labelOn: {
	    	attrs: {
		    	"class": "g-check-label-on"
	    	},
	    	text: "On",
	    	css: {}
    	},
    	labelOff: {
	    	attrs: {
		    	"class": "g-check-label-off"
	    	},
	    	text: "Off",
	    	css: {}
    	},
    	handle: {
	    	attrs: {
		    	"class": "g-check-handle"
	    	},
	    	css: {}
    	},
    	activeClass: "value-active"
	};
	
	$(function() {
		$.fn.gCheckBox.defaults.labelOn.text = lang("on", "On");
		$.fn.gCheckBox.defaults.labelOff.text = lang("off", "Off");
	});

 
})( jQuery );