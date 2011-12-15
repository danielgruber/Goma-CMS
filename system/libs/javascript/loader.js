/**
  * some basic functionality for goma, e.g. loaders for javascript and some global functions
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 14.12.2011
*/

// prevent from being executed twice
if(typeof self.loader == "undefined") {
	
	self.loader = true;
	
	// some regular expressions
	var json_regexp = /^\(?\{/;
	var html_regexp = new RegExp("<body");
	var external_regexp = /https?\:\/\/|ftp\:\/\//;
	
	/*bluebox*/
	var boxcount = 0;
	var blueboxes = [];
	
	// the gloader
	var gloader = {
		load: function(component, fn)
		{
			if(gloader.loaded[component] == null)
			{
				if(self.gloader_data[component] != null)
				{
					var i;
					if(self.gloader_data[component]["required"])
						for(i in self.gloader_data[component]["required"])
						{
							gloader.load(self.gloader_data[component]["required"][i]);
						}
					$("body").css("cursor", "wait");
					$.ajax({
						cache: true,
						noRequestTrack: true,
						url: self.gloader_data[component]["file"],
						dataType: "script",
						async: false
					});
					$("body").css("cursor", "auto");
					if(fn != null)
							fn();
					
				}
				gloader.loaded[component] = true;
			}
		},
		loaded: []
	};
	
	
	// put methods into the right namespace
	(function($, w){
		
		// some browsers don't like this =D
		//"use strict";
		
		$.fn.inlineOffset = function() {
			var el = $('<i/>').css('display','inline').insertBefore(this[0]);
			var pos = el.offset();
			el.remove();
			return pos;
		};
		
		$(function(){
			// ajax handlers
			$("a[rel=ajaxfy]").live("click", function()
			{
				var $this = $(this);
				var _html = $this.html();
				$this.html("<img src=\"images/16x16/ajax-loader.gif\" alt=\"loading...\" />");
				var $container = $this.parents(".record").attr("id");
				$.ajax({
					url: $this.attr("href"),
					data: {ajaxfy: true, "ajaxcontent": true, "container": $container},
					dataType: "html",
					success: function(html, code, ajaxreq) {
						eval_script(html, ajaxreq);
						$this.html(_html);
					},
					error: function(ajaxreq) {
						eval_script(ajaxreq.responseText, ajaxreq);
						$this.html(_html);
					}
				});
				return false;
			});
			$('a[rel*=orangebox]').live('click',function(){	
				gloader.load("orangebox");
				$(this).orangebox();
				$(this).removeAttr("rel");
				$(this).click();
			});
			
			// containers
			
			$(".con_open").live('click',function(){
				con_open($(this).attr('name'), $(this).attr('title'),true);
				return false;
			});
	
			$("a[rel*=bluebox], a[rel*=facebox]").live('click',function(){
				gloader.load("dialog");
				if($(this).hasClass("nodrag"))
				{
					new bluebox($(this).attr('href'), $(this).attr('title'), $(this).attr('name'), false);
				} else
				{
					new bluebox($(this).attr('href'), $(this).attr('title'), $(this).attr('name'));
				}
				return false;
			});
			
			// shadowbox
			$("a[rel*=shadow],a[rel*=light]").live("click", function () {
				gloader.load("shadowbox", function(){
					Shadowbox.init();
					return false;
				});
		        Shadowbox.open({
		            content: $(this).attr("href"),
		            rel: $(this).attr("rel"),
		            title: $(this).attr("title")
		        });
		        return false
		    });
		    
		    $("a[rel*=dropdownDialog]").live("click", function()
			{
				gloader.load("dropdownDialog");
				var options = {
					uri: $(this).attr("href")
				};
				if($(this).attr("rel") == "dropdownDialog[left]")
					options.position = "left";
				else if($(this).attr("rel") == "dropdownDialog[center]")
					options.position = "center";
				else if($(this).attr("rel") == "dropdownDialog[right]")
					options.position = "right";
				
				$(this).dropdownDialog(options);
				return false;
			});
		    
		    /**
			 * addon for z-index
			 * every element with class="windowzindex" is with this plugin
			 * it makes the clicked one on top
			*/
			$(".windowzindex").live('click', function(){
				$(".windowzindex").parent().css('z-index', 900);
				$(this).parent().css("z-index", 901);
			});
			
			// hide and show if js
			$(".hide-on-js").css("display", "none");
			$(".show-on-js").css("display", "block");
			
			// html5 placeholder
			$("input").each(
				function(){
					
					if(($(this).attr("type") == "text" || $(this).attr("type") == "password" || $(this).attr("type") == "search") && ($(this).val()=="" || $(this).val() == $(this).attr("placeholder")) && $(this).attr("placeholder")!="") {
						gloader.load("modernizr");
						if(!Modernizr.input.placeholder) {
							$(this).val($(this).attr("placeholder"));
							$(this).css("color", "#999");
							
							$(this).focus(function(){
								if($(this).val()==$(this).attr("placeholder")) {
									$(this).val("");
								} 
								$(this).css("color", "");
							});
							$(this).blur(function(){
								if($(this).val()=="") {
									 $(this).val($(this).attr("placeholder"));	
									 $(this).css("color", "#999");
									
								}
							});
						}
					}
				}
			);
			
			// checkbox-ajax-save
			$("input[type=checkbox]").live("click",function(){
				if($(this).attr("href")) {
					// make loader
					var id = $(this).attr("id")+'_c';
					var position = $(this).position();
					$("body").append('<img src="images/16x16/loader.gif" style="position: absolute;left: '+position.left+'px; top: '+position.top+'px;z-index: 999;" alt="…" class="checkbox_loader" id="'+id+'" />');
					if($(this).attr("checked")) {
						$.ajax({
							url: $(this).attr("href") + "/" + $(this).attr("value") + "/1",
							complete: function(){
								$("#" + id).remove();
							}
						});
					} else {
						$.ajax({
							url: $(this).attr("href") + "/0",
							complete: function(){
								$("#" + id).remove();
							}
						});
					}
				}
			});
			
			// radio-box-ajax-save
			$("input[type=radio]").live("click",function(){
				if($(this).attr("href")) {
					// make loader
					var id = $(this).attr("id")+'_c';
					var position = $(this).position();
					$("body").append('<img src="images/16x16/loader.gif" style="position: absolute;left: '+position.left+'px; top: '+position.top+'px;z-index: 999;" alt="…" class="checkbox_loader" id="'+id+'" />');
					$.ajax({
						url: $(this).attr("href") + "/" + $(this).attr("value"),
						complete: function(){
							$("#" + id).remove();
						}
					});
				}
			});
		});
		
		// SOME GLOBAL METHODS
		
		// containers
		w.con_open = function(url, title, mouse)
		{
			gloader.load("con");
			_con_open(url, title, mouse);
		}
		
		
		
		// some response handlers
		w.eval_script = function(html, ajaxreq, object) {
			LoadAjaxResources(ajaxreq);
			var content_type = ajaxreq.getResponseHeader("content-type");
			if(content_type == "text/javascript") {
				if(typeof object != "undefined") {
					var method = eval_global('(function(){' + html + '});');
					method.call(object);
				} else {
					eval_global(html);
				}
			} else if(content_type == "text/x-json") {
				var object = eval_global("("+html+")");
				var _class = object["class"];
				var i;
				for(i in object["areas"]) {
					$("#"+_class+"_"+i+"").html(object["areas"][i]);
				}
			} else {
				gloader.load("orangebox");
				var id = randomString(5);
				if(html_regexp.test(html)) {
					self[id + "_html"] = html;
					$("body").append('<div id="'+id+'_div" style="display: none;width: 800px;hieght: 300px;"><iframe src="javascript:document.write(top.'+id+'_html);" height="500" width="100%" name="'+id+'" frameborder="0" id="'+id+'"></iframe></div>');
					
					$("body").append('<a style="display: none;" href="#'+id+'_div" rel="orangebox" id="'+id+'_link"></a>');
					$("#" + id + "_link").click();
				} else{
					$("body").append('<div id="'+id+'_div" style="display: none;">'+html+'</div>');
					$("body").append('<a style="display: none;" href="#'+id+'_div" rel="orangebox" id="'+id+'_link"></a>');
					$("#" + id + "_link").click();
				}
			}
			RunAjaxResources(ajaxreq);
		}
		
		w.renderResponseTo = function(html, node, ajaxreq, object) {
			LoadAjaxResources(ajaxreq);
			if(ajaxreq != null) {
				var content_type = ajaxreq.getResponseHeader("content-type");
				if(content_type == "text/javascript") {
					if(typeof object != "undefined") {
						var method = eval_global('(function(){' + html + '});');
						method.call(object);
					} else {
						eval_global(html);
					}
					return true;
				} else if(content_type == "text/x-json" && json_regexp.test(html)) {
					var object = eval_global("("+html+")");
					var _class = object["class"];
					var i;
					for(i in object["areas"]) {
						$("#"+_class+"_"+i+"").html(object["areas"][i]);
					}
				}
			}
			var regexp = new RegExp("<body");
			if(regexp.test(html)) {
				var id = randomString(5);
				top[id + "_html"] = html;
				node.html('<iframe src="javascript:document.write(top.'+id+'_html);" height="500" width="100%" name="'+id+'" frameborder="0"></iframe>');
			} else {
				node.html(html);
			}
			RunAjaxResources(ajaxreq);
		}
		
		w.ajax_submit = function(obj)
		{
			var $this = $(obj);
			$form = $this.parents("form");
			var data = $form.serialize();
			var url = $form.attr("action");
			var method = $form.attr("method");
			$this.before('<img src="images/16x16/loading.gif" class="loader" alt="loading..." />');
			$.ajax({
				url: url,
				type: method,
				data: data,
				dataType: "script",
				complete: function()
				{
					$form.find(".loader").remove();
				}
			});
			return false;
		}
		
		if(typeof w.JSLoadedResources == "undefined")
			w.JSLoadedResources = [];
		
		if(typeof w.CSSLoadedResources == "undefined")
			w.CSSLoadedResources = [];
		
		if(typeof w.CSSIncludedResources == "undefined")
			w.CSSIncludedResources = [];
		
		
		
		w.LoadAjaxResources = function(request) {
			var css = request.getResponseHeader("X-CSS-Load");
			var js = request.getResponseHeader("X-JavaScript-Load");
			if(css != null) {
				var cssfiles = css.split(";");
				var i;
				for(i in cssfiles) {
					var file = cssfiles[i];
					if(!external_regexp.test(file)) {
						
						if(typeof w.CSSLoadedResources[file] == "undefined") {
							$.ajax({
								cache: true,
								url: file,
								noRequestTrack: true,
								async: false,
								dataType: "html",
								success: function(css) {
									// patch uris
									var base = file.substring(0, file.lastIndexOf("/"));
									css = css.replace(/url\(("|')?(.*)("|')?\)/gi, 'url(' + root_path + base + '/$2)');
									
									w.CSSLoadedResources[file] = css;
								}
							});
						}
						
						if(typeof w.CSSIncludedResources[file] == "undefined") {
							$("head").prepend('<style type="text/css" id="css_'+file.replace(/[^a-zA-Z0-9_\-]/, "_")+'">'+CSSLoadedResources[file]+'</style>');
							w.CSSIncludedResources[file] = true;
						}
					} else {
						w.CSSLoadedResources[file] = css;
						if($("head").html().indexOf(file) != -1) {
							$("head").prepend('<link rel="stylesheet" href="'+file+'" type="text/css" />');
						}
					}
				}
			}
			if(js != null) {
				var jsfiles = js.split(";");
				var i;
				for(i in jsfiles) {
					var file = jsfiles[i];
					if(file != "") {
						var regexp = /\/[^\/]+(script|raw)[^\/]+\.js/;
						if(!regexp.test(file) && w.JSLoadedResources[file] !== true) {
							w.JSLoadedResources[file] = true;
							$.ajax({
								cache: true,
								url: file,
								noRequestTrack: true,
								async: false,
								dataType: "script"
							});
						}
						regexp = null;
						
					}
				}
			}
			
		}
		w.RunAjaxResources = function(request) {
			var js = request.getResponseHeader("X-JavaScript-Load");
			if(js != null) {
				var jsfiles = js.split(";");
				var i;
				for(i in jsfiles) {
					
					var file = jsfiles[i];
					if(file != "") {
						var regexp = /\/[^\/]+(script|raw)[^\/]+\.js/;
						if(regexp.test(file)) {
							$.ajax({
								cache: true,
								url: file,
								noRequestTrack: true,
								async: false,
								dataType: "html",
								success: function(js) {
									eval_global(js);
								}
							});
						}
						regexp = null;	
					}
				}
			}
		}
	
	
		/**
		 * if you have a search-field in a widget in a form, users should can press enter without submitting the form
		 * use this method to make this happen ;)
		 *
		 *@name unbindFormFormSubmit
		 *@param node
		*/
		w.unbindFromFormSubmit = function(node) {
			// first make sure it works!
			var active = false;
			$(node).focus(function(){
				active = true;
			});
			
			$(node).blur(function(){
				active = false;
			});
			
			$(node).parents("form").bind("formsubmit", function(){
				if(active)
					return false;
			});
			
			$(node).parents("form").bind("submit", function(){
				if(active)
					return false;
			});
			
			// second use a better method, just if the browser support it
			$(node).keydown(function(e){
				if (e.keyCode == 13) {
		       	 	return false;
		    	}
			});
		}
		
		
		/**
		 * if you have a dropdown and you want to close it on click on the document, but not on the dropdown, use this function
		 *
		 *@name CallonDocumentClick
		 *@param fn
		 *@param array - areas, which aren't calling this function (css-selectors)
		*/
		w.CallonDocumentClick = function(call, exceptions) {
			var fn = call;
			var mouseover = false;
			var timeout;
			var i;
			if(exceptions) {
				var i;
				for(i in exceptions) {
					$(exceptions[i]).mousedown(function(){
						clearTimeout(timeout);
						mouseover = true;
						timeout = setTimeout(function(){
							mouseover = false;
						}, 300);
					});
					$(exceptions[i]).mouseup(function(){
						clearTimeout(timeout);
						mouseover = true;
						timeout = setTimeout(function(){
							mouseover = false;
						}, 300);
					});
				}
			}
			// init mouseover-events
			$(document).mouseup(function(){
				setTimeout(function(){		
					if(mouseover === false) {
						fn();
					}
				}, 10);
			});
			$("iframe").each(function(){
				var w = $(this).get(0).contentWindow;
				if(w)
					$(w).mouseup(function(){
						setTimeout(function(){
							if(mouseover === false) {
								fn();
							}
						}, 10);
						
					})
			});
		}
		
		// jQuery Extensions
		
		// @url http://stackoverflow.com/questions/955030/remove-css-from-a-div-using-jquery
		//this parse style & remove style & rebuild style. I like the first one.. but anyway exploring..
		$.fn.extend
		({
		    removeCSS: function(cssName) {
		        return this.each(function() {
		
		            return $(this).attr('style',
		
		            $.grep($(this).attr('style').split(";"),
		                    function(curCssName) {
		                        if (curCssName.toUpperCase().indexOf(cssName.toUpperCase() + ':') <= 0)
		                            return curCssName;
		                    }).join(";"));
		        });
		    }
		});
	
		
		// save settings of last ajax request
		w.request_history = [];
		
		$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
	 		 w.request_history.push(originalOptions);
		});
		
	})(jQuery, window);
	
	
	
	function getblueboxbyid(id)
	{
			return self.blueboxes[id];
	}
	
	// trim
	// thanks to @url http://www.somacon.com/p355.php
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	}
	String.prototype.ltrim = function() {
		return this.replace(/^\s+/,"");
	}
	String.prototype.rtrim = function() {
		return this.replace(/\s+$/,"");
	}
	
	function randomString(string_length) {
		var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.substring(rnum,rnum+1);
		}
		return randomstring;
	}
	
	function is_string(input) {
	    return (typeof(input) == 'string');
	}
	
	
	
	function getLastRequest() {
		return self.request_history[self.request_history.length -1];
	}
	function getPreRequest(i) {
		return self.request_history[self.request_history.length - parseInt(i)];
	}
	
	
	/**
	 *@link http://msdn.microsoft.com/en-us/library/ms537509(v=vs.85).aspx
	*/
	function getInternetExplorerVersion()
	// Returns the version of Internet Explorer or a -1
	// (indicating the use of another browser).
	{
	  var rv = -1; // Return value assumes failure.
	  if (navigator.appName == 'Microsoft Internet Explorer')
	  {
	    var ua = navigator.userAgent;
	    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
	    if (re.exec(ua) != null)
	      rv = parseFloat( RegExp.$1 );
	  }
	  return rv;
	}
	
	function getFirefoxVersion()
	{
		var rv = -1; // if not found
		var ua = navigator.userAgent;
		var regexp_firefox = /Firefox/i;
		if(regexp_firefox.test(ua)) {
			var re  = new RegExp("Firefox/([0-9]{1,}[\.0-9]{0,})");
	    	if (re.exec(ua) != null)
	      		rv = parseFloat( RegExp.$1 );
		}
		return rv;
	}
	
	
	/**
	 * cookies, thanks to @url http://www.w3schools.com/JS/js_cookies.asp
	*/
	function setCookie(c_name,value,exdays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + "; path=/";
		document.cookie=c_name + "=" + c_value;
	}
	
	function getCookie(c_name)
	{
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++)
		{
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x=x.replace(/^\s+|\s+$/g,"");
			if (x==c_name)
			{
				return unescape(y);
			}
		}
	}
	
	function isIDevice() {
		return /(iPad|iPhone|iPod)/.test(navigator.userAgent);
	}
	
	function isiOS5() {
		return isIDevice() && navigator.userAgent.match(/AppleWebKit\/(\d*)/)[1]>=534;
	}
	
	function isJSON(content) {
		return json_regexp.test(content);
	}
	
	// patch for IE eval
	var code_evaled;
	function eval_global(codetoeval) {
	    if (window.execScript)
	        window.execScript('code_evaled = ' + '(' + codetoeval + ')',''); // execScript doesn’t return anything
	    else
	        code_evaled = eval(codetoeval);
	    return code_evaled;
	}
}