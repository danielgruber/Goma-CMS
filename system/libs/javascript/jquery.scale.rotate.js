(function ($) {
    // Monkey patch jQuery 1.3.1+ to add support for setting or animating CSS
    // scale and rotation independently.
    // 2009-2010 Zachary Johnson www.zachstronaut.com
    // Updated 2010.11.06
    // edited by Daniel Gruber, added support for scale in msie, 2011.09.01
    var rotateUnits = 'deg';
    
    $.fn.rotate = function (val)
    {
        var style = $(this).css('transform') || 'none';
        
        if (typeof val == 'undefined')
        {
            if (style)
            {
                var m = style.match(/rotate\(([^)]+)\)/);
                if (m && m[1])
                {
                    return m[1];
                }
            }
            
            return 0;
        }
        
        var m = val.toString().match(/^(-?\d+(\.\d+)?)(.+)?$/);
        if (m)
        {
            if (m[3])
            {
                rotateUnits = m[3];
            }
            
            $(this).css(
                'transform',
                style.replace(/none|rotate\([^)]*\)/, '') + 'rotate(' + m[1] + rotateUnits + ')'
            );
        }
        
        return this;
    }
    
    // Note that scale is unitless.
    $.fn.scale = function (val, duration, options)
    {
    	if($.browser.msie && getInternetExplorerVersion() < 9) {
    		var style = $(this).css('zoom');
    		var width =  parseInt($(this).css("width"));
    		var height = parseInt($(this).css("height"));
    		
    		var marginLeft;
    		var marginTop;
    		
    		// make margins
    		if(val < 1) {
    			marginTop = height / 2 * val;
    			marginLeft = width / 2 * val;
    		} else if (val > 1) {
    			marginTop = 0 - height / 2 * val;
    			marginLeft = 0 - width / 2 * val;
    		} else {
    			marginTop = "";
    			marginLeft = "";
    		}
    		$(this).css("margin-top", marginTop);
    		$(this).css("margin-left", marginLeft);
    		
	        
	        if (typeof val == 'undefined')
	        {
	            if (style)
	            {
	               return style;
	            }
	            
	            return 1;
	        }
	        
	        $(this).css(
	            'zoom',
	            val
	        );
	        
	        return this;
    	} else {
	        var style = $(this).css('transform');
	        
	        if (typeof val == 'undefined')
	        {
	            if (style)
	            {
	                var m = style.match(/scale\(([^)]+)\)/);
	                if (m && m[1])
	                {
	                    return m[1];
	                }
	            }
	            
	            return 1;
	        }
	        
	        $(this).css(
	            'transform',
	            style.replace(/none|scale\([^)]*\)/, '') + 'scale(' + val + ')'
	        );
	        
	        return this;
		}
    }

    // fx.cur() must be monkey patched because otherwise it would always
    // return 0 for current rotate and scale values
    var curProxied = $.fx.prototype.cur;
    $.fx.prototype.cur = function ()
    {
        if (this.prop == 'rotate')
        {
            return parseFloat($(this.elem).rotate());
        }
        else if (this.prop == 'scale')
        {
            return parseFloat($(this.elem).scale());
        }
        
        return curProxied.apply(this, arguments);
    }
    
    $.fx.step.rotate = function (fx)
    {
        $(fx.elem).rotate(fx.now + rotateUnits);
    }
    
    $.fx.step.scale = function (fx)
    {
        $(fx.elem).scale(fx.now);
    }
    
    /*
    
    Starting on line 3905 of jquery-1.3.2.js we have this code:
    
    // We need to compute starting value
    if ( unit != "px" ) {
        self.style[ name ] = (end || 1) + unit;
        start = ((end || 1) / e.cur(true)) * start;
        self.style[ name ] = start + unit;
    }
    
    This creates a problem where we cannot give units to our custom animation
    because if we do then this code will execute and because self.style[name]
    does not exist where name is our custom animation's name then e.cur(true)
    will likely return zero and create a divide by zero bug which will set
    start to NaN.
    
    The following monkey patch for animate() gets around this by storing the
    units used in the rotation definition and then stripping the units off.
    
    */
    
    var animateProxied = $.fn.animate;
    $.fn.animate = function (prop)
    {
        if (typeof prop['rotate'] != 'undefined')
        {
            var m = prop['rotate'].toString().match(/^(([+-]=)?(-?\d+(\.\d+)?))(.+)?$/);
            if (m && m[5])
            {
                rotateUnits = m[5];
            }
            
            prop['rotate'] = m[1];
        }
        
        
        return animateProxied.apply(this, arguments);
    }
})(jQuery);

(function ($) {
    // Monkey patch jQuery 1.3.1+ css() method to support CSS 'transform'
    // property uniformly across Safari/Chrome/Webkit, Firefox 3.5+, IE 9+, and Opera 11+.
    // 2009-2011 Zachary Johnson www.zachstronaut.com
    // Updated 2011.05.04 (May the fourth be with you!)
    function getTransformProperty(element)
    {
        // Try transform first for forward compatibility
        // In some versions of IE9, it is critical for msTransform to be in
        // this list before MozTranform.
        var properties = ['transform', 'WebkitTransform', 'msTransform', 'MozTransform', 'OTransform'];
        var p;
        while (p = properties.shift())
        {
            if (typeof element.style[p] != 'undefined')
            {
                return p;
            }
        }
        
        // Default to transform also
        return 'transform';
    }
    
    var _propsObj = null;
    
    var proxied = $.fn.css;
    $.fn.css = function (arg, val)
    {
        // Temporary solution for current 1.6.x incompatibility, while
        // preserving 1.3.x compatibility, until I can rewrite using CSS Hooks
        if (_propsObj === null)
        {
            if (typeof $.cssProps != 'undefined')
            {
                _propsObj = $.cssProps;
            }
            else if (typeof $.props != 'undefined')
            {
                _propsObj = $.props;
            }
            else
            {
                _propsObj = {}
            }
        }
        
        // Find the correct browser specific property and setup the mapping using
        // $.props which is used internally by jQuery.attr() when setting CSS
        // properties via either the css(name, value) or css(properties) method.
        // The problem with doing this once outside of css() method is that you
        // need a DOM node to find the right CSS property, and there is some risk
        // that somebody would call the css() method before body has loaded or any
        // DOM-is-ready events have fired.
        if
        (
            typeof _propsObj['transform'] == 'undefined'
            &&
            (
                arg == 'transform'
                ||
                (
                    typeof arg == 'object'
                    && typeof arg['transform'] != 'undefined'
                )
            )
        )
        {
            _propsObj['transform'] = getTransformProperty(this.get(0));
        }
        
        // We force the property mapping here because jQuery.attr() does
        // property mapping with jQuery.props when setting a CSS property,
        // but curCSS() does *not* do property mapping when *getting* a
        // CSS property.  (It probably should since it manually does it
        // for 'float' now anyway... but that'd require more testing.)
        //
        // But, only do the forced mapping if the correct CSS property
        // is not 'transform' and is something else.
        if (_propsObj['transform'] != 'transform')
        {
            // Call in form of css('transform' ...)
            if (arg == 'transform')
            {
                arg = _propsObj['transform'];
                
                // User wants to GET the transform CSS, and in jQuery 1.4.3
                // calls to css() for transforms return a matrix rather than
                // the actual string specified by the user... avoid that
                // behavior and return the string by calling jQuery.style()
                // directly
                if (typeof val == 'undefined' && jQuery.style)
                {
                    return jQuery.style(this.get(0), arg);
                }
            }

            // Call in form of css({'transform': ...})
            else if
            (
                typeof arg == 'object'
                && typeof arg['transform'] != 'undefined'
            )
            {
                arg[_propsObj['transform']] = arg['transform'];
                delete arg['transform'];
            }
        }
        
        return proxied.apply(this, arguments);
    };
})(jQuery);