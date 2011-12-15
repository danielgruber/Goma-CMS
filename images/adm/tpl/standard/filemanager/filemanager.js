/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 04.11.2010
*/ 

var current_dir = "/";
var ajaxreqfn = [];
function ajaxrequest(action, post, fn)
{
		var a = action;
		var f = fn;
		var data = $.extend({action: action, 'ajax': true}, post);
		var url  = location.href;
		$.ajax({
			type: 'post',
			url: url,
			data: data,
			dataType: 'json',
			cache: false,
			error: function(e)
			{
				alert("error while connecting");
			},
			success: function(json)
			{
				ActionParser(json);
				if(typeof f != "undefined")
				{
						f();
				}
				
				for(i in self.ajaxreqfn)
				{
						self.ajaxreqfn[i](a);
				}
			}
		});
}

var filemanager = 
{
	init: function()
	{
		var hash = location.hash;
		var regexp = /^#!/;
		if(regexp.test(hash))
		{
				var regexp_file = /\/$/;
				if(regexp_file.test(hash))
				{
						var url = hash.substr(2);
						
						self.current_dir = url.replace('//', '/');
						var regexp = /^\//;
						if(regexp.test(self.current_dir))
						{
								self.current_dir = self.current_dir.substr(1);
						}
				} else
				{
						var url = hash.substr(2, hash.lastIndexOf("/") - 1);
						var file = hash.substr(hash.lastIndexOf("/") + 1);
						
						self.current_dir = url.replace('//', '/');
						var regexp = /^\//;
						if(regexp.test(self.current_dir))
						{
								self.current_dir = self.current_dir.substr(1);
						}
				}
				
		} else
		{
				location.href = location.pathname + location.search + "#!/";
				self.current_hash = "#!/";
				var url = "/";
		}
		
		ajaxrequest('init', {dir: url});
	}
}

function register_filemanager_ajaxload(fn)
{
		self.ajaxreqfn.push(fn);
}

$(function() 
{
	$("#filesholder").css("max-height", $(window).height() - 400);
	$(window).resize(function(){
		$("#filesholder").css("max-height", $(window).height() - 400);
	});
});


var current_hash = location.hash;
setInterval(function(){
	if(location.hash != self.current_hash)
	{
		var hash = location.hash;
		var regexp = /^#!/;
		if(regexp.test(hash))
		{
				var regexp_file = /\/$/;
				if(regexp_file.test(hash))
				{
						var url = hash.substr(2);
				} else
				{
						var url = hash.substr(2, hash.lastIndexOf("/") - 1);
						var file = hash.substr(hash.lastIndexOf("/") + 1);
				}
		} else
		{
				return 0;
		}
		loaddir(url);
	}
}, 100);

/**
 * Powered by php.js
*/
function strrpos (haystack, needle, offset) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   input by: saulius
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: strrpos('Kevin van Zonneveld', 'e');
    // *     returns 1: 16
    // *     example 2: strrpos('somepage.com', '.', false);
    // *     returns 2: 8
    // *     example 3: strrpos('baa', 'a', 3);
    // *     returns 3: false
    // *     example 4: strrpos('baa', 'a', 2);
    // *     returns 4: 2

    var i = -1;
    if (offset) {
        i = (haystack+'').slice(offset).lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
        // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
        if (i !== -1) {
            i += offset;
        }
    }
    else {
        i = (haystack+'').lastIndexOf(needle);
    }
    return i >= 0 ? i : false;
}