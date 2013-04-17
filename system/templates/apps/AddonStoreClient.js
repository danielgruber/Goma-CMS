/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013 Goma-Team
  * last modified: 11.04.2013
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
			
			/**
			 * event for reacting to store-requests
			*/
			
			var ReactToMessage = function(e) {
				gloader.load("json");
				
				try {
					var data = JSON.parse(e.data);
					switch(data.action) {
						case "init":
							// it works
							goma.AddOnStore.active = true;
							if(console.log)
								console.log("store available");
							
							for(var i in readyQueue) {
								readyQueue[i]();
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
				} catch(e) {
					alert(e);
				}
			}
			
			/**
			 * this code is for initiating the connection to the store and checking if it works
			*/
			var helloToStore = function() {
				goma.AddOnStore.frame.contentWindow.postMessage('{"action":"init", "version": "'+goma.AddOnStore.version+'"}', "https://goma-cms.org");
				
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
	               
	            window.addEventListener('message', ReactToMessage, true);
			});
			
			
			$.ajaxTransport('+*', function(options, originalOptions, jqXHR) {
				if(goma.AddOnStore.active && (options.url.match(/^https\:\/\/goma\-cms\.org\/apps/i) || (options.url.match(/^https\:\/\/goma\-cms\.org\//) && options.url.match(/\.(css|js|gfs)/i)))) {

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
					options.url = "https://goma-cms.org/apps/" + options.url;
					
					return $.ajax(options);
				},
				
				/**
				 * gets data via ajax and writes it to a given destination
				*/
				uiAjax: function(destination, options, unload) {
					destination = ($(destination).length > 0) ? $(destination) : $(goma.AddOnStore.appStoreMainContent);
					
					options.url = (typeof options.url == "undefined") ? "" : options.url;
					options.url = appstore_prefix + options.url;
					
					return goma.ui.ajax(destination, options, unload).done(function(){
						goma.AddOnStore.parse($(destination));
					});
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
									if(goma.AddOnStore.appStoreInstallUrl.indexOf("?"))
										$(this).attr("href", goma.AddOnStore.appStoreInstallUrl + "&download=" + escape($(this).attr("href")));
									else
										$(this).attr("href", goma.AddOnStore.appStoreInstallUrl + "?download=" + escape($(this).attr("href")));
								} else if(!ext_regexp.test($(this).attr("href")) || $(this).attr("href").substring(0, appstore_prefix.length) == appstore_prefix) {
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
					}
				}
			};
		}
	})(jQuery, window);
}