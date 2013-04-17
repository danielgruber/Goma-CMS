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
			
			function sleep(milliseconds) {
				  var start = new Date().getTime();
				  	for (var i = 0; i < 1e7; i++) {
				    	if ((new Date().getTime() - start) > milliseconds){
					    	break;
					    }
				  }
			}
			
			
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
				uiAjax: function(destination, options, unload) {
					if(typeof options != "undefined") {
						options.url = url;
					} else {
						options = url;
					}
					
					options.url = (typeof options.url == "undefined") ? "" : options.url;
					options.url = "https://goma-cms.org/apps/" + options.url;
					
					return goma.ui.ajax(options);
				},
				onReady: function(fn) {
					if(goma.AddOnStore.active == true) {
						fn();
					} else {
						readyQueue.push(fn);
					}
				}
			};
		}
	})(jQuery, window);
}