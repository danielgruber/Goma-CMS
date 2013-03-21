/**
  * goma javascript framework
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 12.03.2013
  * $Version 2.0
*/

// goma-framework
if(typeof goma == "undefined")
	var goma = {};


// some regular expressions
var json_regexp = /^\(?\{/;
var html_regexp = new RegExp("<body");

if(typeof goma.ui == "undefined") {
	goma.ui = (function($){
		
		var external_regexp = /https?\:\/\/|ftp\:\/\//;
	
		var run_regexp = /\/[^\/]*(script|raw)[^\/]+\.js/;
		var load_alwaysLoad = /\/[^\/]*(data)[^\/]+\.js/;
		
		/**
		 * this code loads external plugins on demand, when it is needed, just call gloader.load("pluginName"); before you need it
		 * you must register the plugin in PHP
		 * we stop execution of JavaScript while loading
		*/
		var gloaded = [];
		var loadScript = function(comp, fn) {
			if(gloaded[comp] == null)
			{
				$("body").css("cursor", "wait");
				$.ajax({
					cache: true,
					noRequestTrack: true,
					url: BASE_SCRIPT + "gloader/" + comp + ".js",
					dataType: "script",
					async: false
				});
				$("body").css("cursor", "auto");
				
				gloaded[comp] = true;
				
				if(fn != null)
					fn();
			}
		};
		
		var CSSLoaded = [];
		var CSSIncluded = [];
		var JSLoaded = [];
		
		// retina support
		var RetinaReplace = function() {
			$("img").each(function(){ //.on("load", "img", function(){
				var $this = $(this);
				if($this.attr("data-retined") != "complete" && $this.attr("data-retina") && $this.width() != 0 && $this.height() != 0) {
					if(goma.ui.IsImageOk($(this).get(0))) {
						var img = new Image();
						img.onload = function(){
							$this.css("width", $this.width());
							$this.css("height", $this.height());
							$this.attr("src", $this.attr("data-retina"));
							img.src = null;
						}
						img.src = $this.attr("data-retina");
						$this.attr("data-retined", "complete");
					}
				}
			});
			
		}
		
		$(function() {	
			$.extend(goma.ui, {
				/**
				 * this area is by default used to place content loaded via Ajax
				*/
				mainContent: $("#content").length ? $("#content") : $("body"),
				
				/**
				 * this area is by default used to place containers from javascript
				*/
				DocRoot: ($(".documentRoot").length == 1) ? $(".documentRoot") : $("body")
			});
			
			if(goma.ui.getDevicePixelRatio() > 1.5) {
				RetinaReplace();
				// add retina-updae-event
				document.addEventListener && document.addEventListener("DOMContentLoaded", RetinaReplace, !1);
			    	if (/WebKit/i.test(navigator.userAgent)) var t = setInterval(function () {
			     	   /loaded|complete/.test(document.readyState) && RetinaReplace();
			   	}, 10);
			}
			
			window.onbeforeunload = goma.ui.fireUnloadEvents;
		});
		
		// build module
		return {
			
			/**
			 * defines if we are in backend
			*/
			is_backend: false,
			
			/**
			 * sets the main-content where to put by default content from ajax-requests
			 *
			 *@name setMainContent
			 *@param jQuery-Object | string (CSS-Path)
			*/
			setMainContent: function(node) {
				if($(node).length > 0)
					goma.ui.mainContent = $(node);
			},
			
			/**
			 * returns the main-content as jQuery-Object
			*/
			getMainContent: function() {
				return goma.ui.mainContent;
			},
			
			ajax: function(destination, options, unload) {
				var node = ($(destination).length > 0) ? $(destination) : goma.ui.getMainContent();
				
				
				if(unload !== false) {
					var data = goma.ui.fireUnloadEvents(node);
					if(typeof data == "string") {
						if(!confirm(lang("unload_lang_start") + data + lang("unload_lang_end")))
							return false;
					}
				}
				
				return $.ajax(options).done(function(r, c, a){	
					goma.ui.renderResponse(r, a, node, undefined, false);
				}).fail(function(a){
					// try find out why it has failed
					if(jqXHR.textStatus == "timeout") {
						destination.prepend('<div class="error">Error while fetching data from the server: <br /> The response timed out.</div>');
					} else if(jqXHR.textStatus == "abort") {
						destination.prepend('<div class="error">Error while fetching data from the server: <br /> The request was aborted.</div>');
					} else {
						destination.prepend('<div class="error">Error while fetching data from the server: <br /> Failed to fetch data from the server.</div>');
					}
				});
			},
			
			/**
			 * updates page and replaces all normal images with retina-images if defined in attribute data-retina of img-tag
			 *
			 *@name updateRetina
			*/
			updateRetina: function() {
				if(goma.ui.getDevicePixelRatio() > 1.5)
					RetinaReplace();	
			},
			
			/**
			 * fires unload events and returns perfect result for onbeforeunload event
			 *
			 *@name fireUnloadEvents
			*/
			fireUnloadEvents: function(node) {
				node = ($(node).length > 0) ? $(node) : goma.ui.getContentRoot();
				var event = jQuery.Event("onbeforeunload");
				var r = true;
				
				$(".g-unload-handler").each(function(){
					if($(this).parents(node)) {
						$(this).trigger(event);
						if(typeof event.result == "string")
							r = event.result;
					}
				});
				
				if(r !== true)
					return r;
			},
			
			/**
			 * binds unload-event on specfic html-node
			 *
			 *@name bindUnloadEvent
			 *@param string - selector for event-binding
			 *@param object - data //optional
			 *@param function - handler
			*/
			bindUnloadEvent: function(select, data, handler) {
				$(select).addClass("g-unload-handler");
				$(select).on("onbeforeunload", data, handler);
			},
			
			/**
			 * removes unbind-handler from specific object
			 *
			 *@name removeUnloadHandler
			 *@param string - selector
			 *@param function - handler to remove - optional
			*/
			unbindUnloadEvent: function(select, handler) {
				$(select).off("onbeforeunload", handler);
			},
			
			/**
			 * for loading data
			 * sets data loaded
			*/
			setLoaded: function(mod) {
				gloaded[mod] = true;
			},
			
			/**
			 * loading-script
			 *
			 *@name load
			 *@param string - mod
			 *@param function - fn
			*/
			load: loadScript,
			
			/**
			 * some base-roots in DOM
			*/
			getContentRoot: function() {
				return goma.ui.mainContent;
			},
			getDocRoot: function() {
				return goma.ui.DocRoot;
			},
			
			/**
			 * global ajax renderer
			 *
			 *@name renderResponse
			 *@access public
			*/
			renderResponse: function(html, xhr, node, object, checkUnload) {
				node = ($(node).length > 0) ? $(node) : goma.ui.getContentRoot();
				
				if(checkUnload !== false) {
					var data = goma.ui.fireUnloadEvents(node);
					if(typeof data == "string") {
						if(!confirm(lang("unload_lang_start") + data + lang("unload_lang_end")))
							return false;
					}
				}
				
				LoadAjaxResources(xhr);
			
				if(xhr != null) {
					var content_type = xhr.getResponseHeader("content-type");
					if(content_type == "text/javascript") {
						if(typeof object != "undefined") {
							var method;
							if (window.execScript)
							  	window.execScript('method = ' + 'function(' + html + ')',''); // execScript doesn’t return anything
							else
						  		method = eval('(function(){' + html + '});');
						  	
							method.call(object);
						} else {
							eval_global(html);
						}
						RunAjaxResources(xhr);
						return true;
					} else if(content_type == "text/x-json" && json_regexp.test(html)) {
						
						RunAjaxResources(xhr);
						return false;
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
				
				RunAjaxResources(xhr);
			},
			
			/**
			 * css and javascript-management
			*/
			
			/**
			 * register a resource loaded
			 *
			 *@name registerResource
			 *@access public
			*/
			registerResource: function(type, file) {
				goma.ui.registerResources(type, [file]);
			},
			
			/**
			 * register resources loaded
			 *
			 *@name registerResources
			 *@access public
			*/
			registerResources: function(type, files) {
				switch(type) {
					case "css":
						
						var i;
						for(i in files) {
							CSSLoaded[files[i]] = "";
							CSSIncluded[files[i]] = true;
						}
					break;
					case "js":
						
						var i;
						for(i in files) {
							JSLoaded[files[i]] = true;
						}
					break;
				}
			},
			
			loadResources: function(request) {
				var css = request.getResponseHeader("X-CSS-Load");
				var js = request.getResponseHeader("X-JavaScript-Load");
				
				if(css != null) {
					var cssfiles = css.split(";");
					var i;
					
					for(i in cssfiles) {
						var file = cssfiles[i];
						if(!external_regexp.test(file) && file != "") {
							
							if(typeof CSSLoaded[file] == "undefined") {
								$.ajax({
									cache: true,
									url: file,
									noRequestTrack: true,
									async: false,
									dataType: "html",
									success: function(css){
										// patch uris
										var base = file.substring(0, file.lastIndexOf("/"));
										//css = css.replace(/url\(([^'"]+)\)/gi, 'url(' + root_path + base + '/$2)');
										css = css.replace(/url\(['"]?([^'"#\>\!\s]+)['"]?\)/gi, 'url(' + root_path + base + '/$1)');
										
										CSSLoaded[file] = css;
									}
								});
							}
							
							if(typeof CSSIncluded[file] == "undefined") {
								$("head").prepend('<style type="text/css" id="css_'+file.replace(/[^a-zA-Z0-9_\-]/g, "_")+'">'+CSSLoaded[file]+'</style>');
								CSSIncluded[file] = true;
							}
						} else {
							CSSLoaded[file] = css;
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
							if((!run_regexp.test(file) && JSLoaded[file] !== true) || load_alwaysLoad.test(file)) {
								JSLoaded[file] = true;
								$.ajax({
									cache: true,
									url: file,
									noRequestTrack: true,
									async: false,
									dataType: "html",
									success: function(js){
										eval_global(js);
									}
								});
							}
							regexp = null;
							
						}
					}
				}
				
			},
			
			runResources: function(request) {
				var js = request.getResponseHeader("X-JavaScript-Load");
				if(js != null) {
					var jsfiles = js.split(";");
					var i;
					for(i in jsfiles) {
						
						var file = jsfiles[i];
						if(file != "") {
							if(run_regexp.test(file)) {
								$.ajax({
									cache: true,
									url: file,
									noRequestTrack: true,
									async: false,
									dataType: "html",
									success: function(js){
										eval_global(js);
									}
								});
							}
							regexp = null;	
						}
					}
				}
			},
			
			// Helper Functions
			getDevicePixelRatio: function() {
		        if (window.devicePixelRatio === undefined) { return 1; }
		        return window.devicePixelRatio;
		    },
		    
		    /**
		     * checks if a img were loaded correctly
		     *
		     *@name isImageOK
		    */
			IsImageOk: function(img) {
			    // During the onload event, IE correctly identifies any images that
			    // weren’t downloaded as not complete. Others should too. Gecko-based
			    // browsers act like NS4 in that they report this incorrectly.
			    if (!img.complete) {
			        return false;
			    }
			
			    // However, they do have two very useful properties: naturalWidth and
			    // naturalHeight. These give the true size of the image. If it failed
			    // to load, either of these should be zero.
			
			    if (typeof img.naturalWidth != "undefined" && img.naturalWidth == 0) {
			        return false;
			    }
			
			    // No other way of checking: assume it’s ok.
			    return true;
			}
	
		};
	})(jQuery);
	
	var gloader = {load: goma.ui.load};
}

if(typeof goma.ENV == "undefined") {
	goma.ENV = (function(){
		return {
			"jsversion": "2.0"
		};
	})();
}

// prevent from being executed twice
if(typeof self.loader == "undefined") {
	
	self.loader = true;
	
	// shuffle
	array_shuffle = function(array){
	  var tmp, rand;
	  for(var i =0; i < array.length; i++){
	    rand = Math.floor(Math.random() * array.length);
	    tmp = array[i]; 
	    array[i] = array[rand]; 
	    array[rand] =tmp;
	  }
	  return array;
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
			
			/**
			 * ajaxfy is a pretty basic and mostly by PHP-handled Ajax-Request, we get back mostly javascript, which can be executed
			*/
			$(document).on("click", "a[rel=ajaxfy], a.ajaxfy", function()
			{
				var $this = $(this);
				var _html = $this.html();
				$this.html("<img src=\"images/16x16/ajax-loader.gif\" alt=\"loading...\" />");
				var $container = $this.parents(".record").attr("id");
				$.ajax({
					url: $this.attr("href"),
					data: {ajaxfy: true, "ajaxcontent": true, "container": $container},
					dataType: "html"
				}).done(function(html, textStatus, jqXHR){
					eval_script(html, jqXHR);
					$this.html(_html);
				}).fail(function(jqXHR){
					eval_script(jqXHR.responseText, jqXHR);
					$this.html(_html);
				});
				return false;
			});
		    
		    // new dropdownDialog, which is very dynamic and greate
		    $(document).on("click", "a[rel*=dropdownDialog], a.dropdownDialog, a.dropdownDialog-left, a.dropdownDialog-right, a.dropdownDialog-center, a.dropdownDialog-bottom", function()
			{
				gloader.load("dropdownDialog");
				
				var options = {
					uri: $(this).attr("href")
				};
				if($(this).attr("rel") == "dropdownDialog[left]" || $(this).hasClass("dropdownDialog-left"))
					options.position = "left";
				else if($(this).attr("rel") == "dropdownDialog[center]" || $(this).hasClass("dropdownDialog-center"))
					options.position = "center";
				else if($(this).attr("rel") == "dropdownDialog[right]" || $(this).hasClass("dropdownDialog-right"))
					options.position = "right";
				else if($(this).attr("rel") == "dropdownDialog[bottom]" || $(this).hasClass("dropdownDialog-bottom"))
					options.position = "bottom";
				
				$(this).dropdownDialog(options);
				return false;
			});
		    
		    /**
			 * addon for z-index
			 * every element with class="windowzindex" is with this plugin
			 * it makes the clicked one on top
			*/
			$(document).on('click', ".windowzindex", function(){
				$(".windowzindex").parent().css('z-index', 900);
				$(this).parent().css("z-index", 901);
			});
			
			// html5 placeholder
			$("input").each(
				function(){
					if(($(this).attr("type") == "text" || $(this).attr("type") == "search") && ($(this).val()=="" || $(this).val() == $(this).attr("placeholder")) && $(this).attr("placeholder")!="") {
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
			
			// scroll fix
			$(document).on("click", "a", function(){
				if($(this).attr("href").substr(0,1) == "#") {
					scrollToHash($(this).attr("href").substr(1));
					return false;
				} else if(typeof $(this).attr("data-anchor") == "string" && $(this).attr("data-anchor") != "") {
					scrollToHash($(this).attr("data-anchor"));
					return false;
				}
			});
			
			// scroll to right position
			if($("#frontedbar").length == 1) {
				if(location.hash != "") {
					scrollToHash(location.hash.substr(1));
				}
			}
			
		});
		
		// SOME GLOBAL METHODS
		
		// language
		var lang = [];
		
		/**
		 * load language for name from PHP with default value as second argument
		 * e.g. lang("loading", "loading..."); should return for german: "Laden..."
		 *
		 *@name lang
		*/
		w.lang = function(name, _default) {
			
			if(typeof lang[name] == "undefined") {
				var jqXHR = $.ajax({
					async: false,
					cache: true,
					url: ROOT_PATH + BASE_SCRIPT + "system/getLang/" + escape(name),
					dataType: "json",
					noRequestTrack: true
				});
				
				try {
					var data = parseJSON(jqXHR.responseText);
					for(i in data) {
						lang[i] = data[i];
					}
				} catch(e) {
					lang[name] = null;
				}
			}
			
			if(lang[name] == null) {
				return _default;
			} else {
				return lang[name];
			}
		}
		
		/**
		 * returns the root of the document
		*/
		w.getDocRoot = function() {
			return goma.ui.getDocRoot();
		}
		
		/**
		 * reloads lang that if you need it javascript does not have to make an ajax-request to get it, which can freeze the browser in very performance-exzessive-operations
		 * do this if you know the names before
		 *
		 *@name preloadLang
		 *@param array - names
		 *@param bool - async request or not, default: true
		*/
		w.preloadLang = function(_names, async) {
			
			if(typeof async == "undefined")
				async = true;
			
			var names = [];
			// check names
			for(i in _names) {
				if(typeof lang[_names[i]] == "undefined")
					names.push(_names[i]);
			}
			
			if(names.length == 0)
				return true;
			
			var jqXHR = $.ajax({
				async: async,
				cache: true,
				data: {"lang": names},
				url: ROOT_PATH + "system/getLang/",
				dataType: "json",
				noRequestTrack: true
			});
			
			try {
				var data = parseJSON(jqXHR.responseText);
				for(i in data) {
					lang[i] = data[i];
				}
			} catch(e) { }
		}
			
		// some response handlers
		w.eval_script = function(html, ajaxreq, object) {
			return goma.ui.renderResponse(html, ajaxreq, undefined, object);
		}
		
		w.renderResponseTo = function(html, node, ajaxreq, object) {
			return goma.ui.renderResponse(html, ajaxreq, node, object);
		}
		
		w.LoadAjaxResources = function(request) {
			return goma.ui.loadResources(request);
		}
		
		w.RunAjaxResources = function(request) {
			return goma.ui.runResources(request);
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
				if(active) {
					return false;
				}
			});
			
			$(node).parents("form").bind("submit", function(){
				if(active) {
					return false;
				}
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
			
			
			// function if we click or tap on an exception
			var exceptionFunc = function(){
				clearTimeout(timeout);
				mouseover = true;
				timeout = setTimeout(function(){
					mouseover = false;
				}, 300);
			}
			
			// function if we click anywhere
			mouseDownFunc = function(e){
				setTimeout(function(){		
					if(mouseover === false) {
						fn(e);
					}
				}, 10);
			}
			
			if(exceptions) {
				var i;
				for(i in exceptions) {
					$(exceptions[i]).on("mouseup", exceptionFunc);
					$(exceptions[i]).on("mousedown", exceptionFunc);
					$(exceptions[i]).on("touchend", exceptionFunc);
					$(exceptions[i]).on("touchstart", exceptionFunc);
				}
			}
			// init mouseover-events
			$(window).on("mouseup", mouseDownFunc);
			$(window).on("mousedown", mouseDownFunc);
			$(window).on("touchend", mouseDownFunc);
			$(window).on("touchstart", mouseDownFunc);
			$("iframe").each(function(){
				var w = $(this).get(0).contentWindow;
				if(w) {
					$(w).on("mouseup", mouseDownFunc);
					$(w).on("touchend", mouseDownFunc);
				}
			});
		}
		w.callOnDocumentClick = w.CallonDocumentClick;
		
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
		w.event_history = [];
		
		$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
			if(originalOptions.noRequestTrack == null) {
				var data = originalOptions;
				jqXHR.always(function(){
					w.request_history.push(data);
				});
				
				if(originalOptions.type == "post" && originalOptions.async != false) {
					jqXHR.fail(function(){
						if(jqXHR.textStatus == "timeout") {
							alert('Error while saving data to the server: \nThe response timed out.\n\n' + originalOptions.url);
						} else if(jqXHR.textStatus == "abort") {
							alert('Error while saving data to the server: \nThe request was aborted.\n\n' + originalOptions.url);
						} else {
							alert('Error while saving data to the server: \nFailed to save data on the server.\n\n' + originalOptions.url);
						}
					});
				} else {
					jqXHR.fail(function(){
						
						if(jqXHR.textStatus == "timeout") {
							alert('Error while fetching data from the server: \nThe response timed out.\n\n' + originalOptions.url);
						} else if(jqXHR.textStatus == "abort") {
							alert('Error while fetching data from the server: \nThe request was aborted.\n\n' + originalOptions.url);
						} else {
							alert('Error while fetching data from the server: \nFailed to fetch data from the server.\n\n' + originalOptions.url);
						}
					});
				}
			}
				
	 		jqXHR.setRequestHeader("X-Referer", location.href);
	 		jqXHR.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	 		if(goma.ENV.is_backend)
	 			jqXHR.setRequestHeader("X-Is-Backend", 1);
		});
		
		w.event_history = [];
		$.orgajax = $.ajax;
		$.ajax = function(url, options) {
			
			var w = window;
			
			var jqXHR = $.orgajax.apply(this, [url, options]);
			
			if(typeof options != "undefined" && options.noRequestTrack == null || url.noRequestTrack == null) {
				var i = w.event_history.length;
				w.event_history[i] = {done: [], fail: [], always: []};
				
				jqXHR._done = jqXHR.done;
				jqXHR.done = function(fn) {
					w.event_history[i]["done"].push(fn);
					return jqXHR._done(fn);
				}
				
				jqXHR._fail = jqXHR.fail;
				jqXHR.fail = function(fn) {
					w.event_history[i]["fail"].push(fn);
					return jqXHR._fail(fn);
				}
				
				jqXHR._always = jqXHR.always;
				jqXHR.always = function(fn) {
					w.event_history[i]["always"].push(fn);
					return jqXHR._always(fn);
				}
			}
				
			return jqXHR;
		};
		
		/* API to run earlier Requests with a bit different options */
		w.runLastRequest = function(data) {
			return w.runPreRequest(0, data);
		}
		w.runPreRequest = function(i, data) {
			var a = self.request_history.length - 1 - parseInt(i);
			var options = $.extend(self.request_history[a], data);
			if(self.request_history[a].data != null && typeof self.request_history[a].data != "string" && typeof data.data == "object") {
				options.data = $.extend(self.request_history[a].data, data.data);
			}
			var jqXHR = $.ajax(options);
			for(i in w.event_history[a]["done"]) {
				jqXHR.done(w.event_history[a]["done"][i]);
			}
			for(i in w.event_history[a]["always"]) {
				jqXHR.always(w.event_history[a]["always"][i]);
			}
			for(i in w.event_history[a]["fail"]) {
				jqXHR.fail(w.event_history[a]["fail"][i]);
			}
			return jqXHR;
		}
		
	})(jQuery, window);
	
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
	function eval_global(codetoeval) {
		try {
		    if (window.execScript)
		        window.execScript(codetoeval); // execScript doesn’t return anything
		    else
		        window.eval(codetoeval);
		} catch(e) {
			alert(e);
			throw e;
		}
	}
	
	// parse JSON
	function parseJSON(str) {
		if(str.substring(0, 1) == "(") {
			str = str.substr(1);
		}
		
		if(str.substr(str.length - 1) == ")") {
			str = str.substr(0, str.length -1);
		}
		
		return $.parseJSON(str);
	}
	
	function microtime (get_as_float) {
	    // Returns either a string or a float containing the current time in seconds and microseconds  
	    // 
	    // version: 1109.2015
	    // discuss at: http://phpjs.org/functions/microtime
	    // +   original by: Paulo Freitas
	    // *     example 1: timeStamp = microtime(true);
	    // *     results 1: timeStamp > 1000000000 && timeStamp < 2000000000
	    var now = new Date().getTime() / 1000;
	    var s = parseInt(now, 10);
	 
	    return (get_as_float) ? now : (Math.round((now - s) * 1000) / 1000) + ' ' + s;
	}
	
	function str_repeat (input, multiplier) {
	    // Returns the input string repeat mult times  
	    // 
	    // version: 1109.2015
	    // discuss at: http://phpjs.org/functions/str_repeat
	    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	    // +   improved by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	    // *     example 1: str_repeat('-=', 10);
	    // *     returns 1: '-=-=-=-=-=-=-=-=-=-='
	    return new Array(multiplier + 1).join(input);
	}
	
	var scrollToHash = function(hash) {
		if($("#" + hash).length > 0) {
			var scrollPosition = $("#" + hash).offset().top;
		} else if($("a[name="+hash+"]").length > 0) {
			var scrollPosition = $("a[name="+hash+"]").offset().top;
		} else {
			var scrollPosition = 0;
		}
		
		scrollPosition = Math.round(scrollPosition);
		
		if(scrollPosition != 0 && $("#frontedbar").length == 1) {
			scrollPosition -= $("#frontedbar").height();
		}
		
		var scroll = $(window).scrollTop();
		window.location.hash = hash;
		$(window).scrollTop(scroll);
		
		$("html, body").animate({
			"scrollTop": scrollPosition
		}, 200);
	}
}
