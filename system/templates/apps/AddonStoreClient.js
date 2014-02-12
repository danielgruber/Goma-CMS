/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 12.02.2014
*/

if(typeof goma.AddOnStore == "undefined") {
	goma.AddOnStore = (function($, w){
		if(typeof window.postMessage === "undefined") {
			// fail
			throw "Could not connect to goma-server. PostMessage not supported";
		} else {
			
			var ext_regexp = /https?\:\/\//;
			var appstore_prefix = "https://goma-cms.org/apps/";
			
			var ajaxRequest = [];
			
			var historyStack = [];
			var historyForwardStack = [];
			var historyBind = [];
			
			var uiAjaxBind = [];
			
			/**
			 * event for reacting to store-requests
			*/
			
			var ReactToMessage = function(e) {
				goma.ui.loadAsync("json").done(function(e){
					
					if(e.origin != "https://goma-cms.org") {
						return false;
					}
					
					try {
						var data = JSON.parse(e.data);
						switch(data.action) {
							case "init":
								if(data.message == "Hello") {
									// it works
									goma.AddOnStore.active = true;
									if(console.log)
										console.log("store available");
									
									for(var i in readyQueue) {
										readyQueue[i]();
									}
								}
							break;
							case "error":
								alert(data.message);
							break;
							case "ajaxResponse":
								if(typeof ajaxRequest[data.id] != "undefined") {
									ajaxRequest[data.id].done = 1;
									ajaxRequest[data.id].callback(data.status, data.textStatus, data.responses, data.headers);
								}
							break;
						}
					} catch(err) {
						console.log(e);
						console.log && console.log(err);
					}
				}.bind(this, e));
			}
			
			/**
			 * this code is for initiating the connection to the store and checking if it works
			*/
			var helloToStore = function() {
				goma.AddOnStore.frame.contentWindow.postMessage('{"action":"init", "version": "'+goma.AddOnStore.version+'", "framework": "'+goma.ENV.framework_version+'"}', "https://goma-cms.org");
				
			};
			
			// init store-client
			$(function(){
				// append transport-frame
				$("body").append('<iframe name="storeFrame" id="store-frame" src="https://goma-cms.org/messageBridge.html" frameborder="0"></iframe>');
				$("#store-frame").css({
					position: "absolute",
					top: -100,
					left: -100,
					height: 1,
					width: 1
				});
				
				goma.AddOnStore.frame = document.getElementById("store-frame");
				
				if(getInternetExplorerVersion() == -1)
				    goma.AddOnStore.frame.onload = helloToStore;
	            else
	                goma.AddOnStore.frame.attachEvent("onload", helloToStore);
	            
	            if(window.addEventListener)   
	            	window.addEventListener('message', ReactToMessage, true);
	            else
	            	window.onmessage = ReactToMessage;
			});
			
			
			$.ajaxTransport('+*', function(options, originalOptions, jqXHR) {
				
				if(goma.AddOnStore.active && (options.url.match(/^https\:\/\/goma\-cms\.org\/apps/i) || (options.url.match(/^https\:\/\/goma\-cms\.org\//) && options.url.match(/\.(css|js|gfs)/i)))) {
					
					var reqID = randomString(10);
					return {
						
						send: function( headers , callback ) {
							
							ajaxRequest[reqID] = {callback: callback, headers: headers, options: options, jqXHR: jqXHR};
							
							if(console.log)
								console.log("trying to send message to Host");
							
							goma.AddOnStore.frame.contentWindow.postMessage(JSON.stringify({action: "ajax", data: {options: options, originalOptions: originalOptions, headers: headers}, id: reqID}), "https://goma-cms.org");
						},
						
						abort: function() {
							ajaxRequest[reqID] = null;
						}
					};
				}
			});
		
			var readyQueue = [];
			
			return {
				"version": "1.0",
				appStoreMainContent: null,
				appStoreInstallUrl: null,
				
				/**
				 * sets the ENV of the app-store
				 *
				 *@name setENV
				*/
				setENV: function(content, url) {
					if($(content).length > 0) {
						goma.AddOnStore.appStoreMainContent = $(content);
						goma.AddOnStore.history.Init();
						
						goma.AddOnStore.history.bind(function(url){
							goma.AddOnStore.uiAjax(null, {
								url: url
							});
						});
					}
					
					if(url)
						goma.AddOnStore.appStoreInstallUrl = url;
				},
				
				/**
				 * gets data via ajax from the goma-app-server
				*/
				ajax: function(url, options) {
					if(typeof options != "undefined") {
						options.url = url;
					} else {
						options = url;
					}
					
					options.url = (typeof options.url == "undefined") ? "" : options.url;
					options.url = appstore_prefix + options.url;
					
					return $.ajax(options);
				},
				
				/**
				 * gets data via ajax and writes it to a given destination
				*/
				uiAjax: function(destination, options, unload, hideLoading) {
					if(typeof hideLoading == "undefined") {
						hideLoading = false;
					}
					
					destination = ($(destination).length > 0) ? $(destination) : $(goma.AddOnStore.appStoreMainContent);
					
					options.url = (typeof options.url == "undefined") ? "" : options.url;
					options.url = appstore_prefix + options.url;
					
					var newOptions;
					for(i in uiAjaxBind) {
						newOptions = uiAjaxBind[i](options);
						if(typeof newOptions == "object") {
							options = newOptions;
						}
					}
					
					destination.html('<span class="loading"><img src="images/16x16/loading.gif" alt="" /> '+lang("loading")+'</span>');
					
					if(!hideLoading) {
						goma.ui.setProgress(5).done(function(){	
							goma.ui.setProgress(15, true)
						});
					}
					
					return goma.ui.ajax(destination, options, unload).done(function(){
						if(typeof goma.ui.progress != "undefined") {
							goma.ui.setProgress(100);
						}
						
						goma.AddOnStore.parse($(destination));
						if(options.type != "post")
							goma.AddOnStore.history.push(options.url);
					});
				},
				
				/**
				 * hooks into UI-Ajax
				 *
				 *@name bindUIAjax
				*/
				bindUIAjax: function(fn) {
					if(typeof fn != "undefined") {
						uiAjaxBind.push(fn);
					}
				},
				
				/**
				 * registers a handler if app-store is ready
				*/
				onReady: function(fn) {
					if(goma.AddOnStore.active == true) {
						fn();
					} else {
						readyQueue.push(fn);
					}
				},
				
				/**
				 * parses appstore-dom
				 *
				 *@name parse
				*/
				parse: function(dom) {
					if($(dom).length > 0) {
						var r = $(dom);
						if(goma.AddOnStore.appStoreInstallUrl)
							r.find("a").each(function(){
								if($(this).attr("href").match(/\.gfs$/)) {
									if(goma.AddOnStore.appStoreInstallUrl.indexOf("?") != -1)
										$(this).attr("href", goma.AddOnStore.appStoreInstallUrl + "&download=" + escape($(this).attr("href")));
									else
										$(this).attr("href", goma.AddOnStore.appStoreInstallUrl + "?download=" + escape($(this).attr("href")));
								} else if((!ext_regexp.test($(this).attr("href")) || $(this).attr("href").substring(0, appstore_prefix.length) == appstore_prefix) && !$(this).attr("href").match(/^(#|javascript\:)/)) {
									$(this).click(function(){
										var url = $(this).attr("href");
										if(url.substring(0, 5) == "apps/")
											url = url.substring(5);
										goma.AddOnStore.uiAjax(null, {
											url: url
										});
										
										return false;
									});
								}
							});
						
						r.find("img").each(function(){
							if(!ext_regexp.test($(this).attr("src"))) {
								$(this).attr("src", "https://goma-cms.org/" + $(this).attr("src"));
							}
						});
						
						r.find("form").each(function(){
							$(this).find("input[type=submit], input[type=image]").click(function(){
								if(!$(this).hasClass("default_submit")) {
									var data = $(this).parents("form").serializeArray();
									data[$(this).attr("name")] = $(this).attr("value");
									
									var action = $(this).parents("form").attr("action");
									if(!ext_regexp.test(action) || action.substring(0, appstore_prefix.length) == appstore_prefix) {
										if(action.substring(0, 5) == "apps/")
											action = action.substring(5);
										
										goma.AddOnStore.uiAjax(null, {
											url: action,
											type: $(this).parents("form").attr("method"),
											data: data
										});
									} else {
										return true;
									}
								}
								return false;
							});
						});
						
						if(goma.AddOnStore.history.isBack()) {
							r.find(".history_back").removeClass("disabled");
						} else {
							r.find(".history_back").addClass("disabled");
						}
						
						if(goma.AddOnStore.history.isForward()) {
							r.find(".history_forward").removeClass("disabled");
						} else {
							r.find(".history_forward").addClass("disabled");
						}
					}
				},
				
				/**
				 * History-Plugin
				*/
				history: {
					interval: 250,
					
					/**
					 * pushes a url to the history
					 *
					 *@name push
					*/
					push: function(url) {
						goma.AddOnStore.history.lastPush = true;
						
						if(url.substring(0, appstore_prefix.length) == appstore_prefix)
							url = url.substring(appstore_prefix.length);
						
						window.location.hash = "!" + url;
						historyStack.push(url);
						historyForwardStack = [];
						
						setTimeout(function() {
							goma.AddOnStore.history.lastPush = false;
						}, goma.AddOnStore.history.interval + 50);
					},
					
					/**
					 * inits the history
					 *
					 *@name Init
					*/
					Init: function() {
						if(typeof window.onhashchange == "object") {
							window.onhashchange = function() {
								if(location.hash.substr(0, 2) == "#!" || location.hash.substr(0, 1) == "!") {
									
									if(goma.AddOnStore.history.lastPush) {
										goma.AddOnStore.history.lastPush = false;
									} else {
										var hash = document.location.hash.substr(2);
									
										// check for history event
										if(historyStack[historyStack.length - 1] == hash) {
											historyForwardStack.push(historyStack.pop());
										} else {
											historyStack.push(hash);
											historyForwardStack = [];
										}
									
										for(i in historyBind) {
											if(!historyBind[i](hash))
												break;
										}
									}
								}
							};
						} else {
							setInterval(function(){
								if(window.___oldHash != location.hash) {
									window.___oldHash = location.hash;
									if(location.hash.substr(0, 2) == "#!" || location.hash.substr(0, 1) == "!") {
										
										if(goma.AddOnStore.history.lastPush) {
											goma.AddOnStore.history.lastPush = false;
										} else {
											var hash = document.location.hash.substr(2);
										
											// check for history event
											if(historyStack[historyStack.length - 1] == hash) {
												historyForwardStack.push(historyStack.pop());
											} else {
												historyStack.push(hash);
												historyForwardStack = [];
											}
											
											for(i in historyBind) {
												if(!historyBind[i](hash))
													break;
											}
										}
									}
								}
							}, goma.AddOnStore.history.interval)
						}
					},
					
					bind: function(fn) {
						historyBind.push(fn);
					},
					
					isBack: function() {
						return (historyStack.length > 1);
					},
					
					isForward: function() {
						return (historyForwardStack.length > 0);
					}
				}
			};
		}
	})(jQuery, window);
}

window.onload = function() {
	setTimeout(function() {
		goma.AddOnStore.history.lastPush = false;
	}, goma.AddOnStore.history.interval + 50);
	
	goma.AddOnStore.history.Init();
};
