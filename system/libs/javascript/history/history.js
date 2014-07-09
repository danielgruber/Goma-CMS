/**
 * With the history-lib you can easily create javascript-applications, which use ajax to get data from the server and edit the location of the site.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\JS-History-Lib
 * @version	1.1
 */

window.__oldHash = location.hash;

var HistoryLib = {
	/**
	 * binded
	 *
	 *@name binded
	*/
	binded: [],
	
	/**
	 * binded to all
	 *
	 *@name bindedAll
	*/
	bindedAll: [],
	
	/**
	 * mode how we handle it
	 * two options:
	 * - hash
	 * - history
	*/	
	mode: null,
	
	/**
	 * last push
	*/
	lastPush: true,
	
	/**
	 * update-interval for fallback
	*/
	interval: 250,
	
	/**
	 * bind
	*/
	bind: function(fn, all) {
		if(typeof fn == "function") {
			if(all)
				HistoryLib.bindedAll.push(fn);
			else
				HistoryLib.binded.push(fn);
		}
		
		if(this.mode == "hash") {
			if((location.hash.substr(0, 2) == "#!" || location.hash.substr(0, 1) == "!") && document.location.hash.substr(2) != document.location.pathname) {
				fn(document.location.hash.substr(2));
			}
		}
	},
	
	push: function(url) {
		
		HistoryLib.lastPush = true;
		if(HistoryLib.mode == "history") {
			window.history.pushState({}, null, url);
			for(i in HistoryLib.bindedAll) {
				HistoryLib.bindedAll[i](url);
			}
		} else {
			var scroll = $(window).scrollTop();
			if(url.substr(0,1) == "#")
				url = url.substr(1);
			
			//alert(url);
			location.hash = "!" + url;
			$(window).scrollTop(scroll);
		}
		
		setTimeout(function() {
			HistoryLib.lastPush = false;
		}, HistoryLib.interval + 50);
	},
	
	Init: function(mode) {
		if(typeof window.history.pushState == "function" && mode != "hash") {
			window.onpopstate = function(event) {
				if(HistoryLib.lastPush) {
					HistoryLib.lastPush = false;
				} else {
					var path = document.location.pathname;
					// now strip path with root_path
					if(path.substring(0, ROOT_PATH.length) == ROOT_PATH) {
						path = path.substr(ROOT_PATH.length);
					}
					for(i in HistoryLib.binded) {
						HistoryLib.binded[i](path);
					}
					for(i in HistoryLib.bindedAll) {
						HistoryLib.binded[i](path);
					}
				}
			};
			HistoryLib.mode = "history";
		} else {
			HistoryLib.mode = "hash";
			
			if(location.hash.substr(0, 2) != "#!" && location.hash.substr(0, 1) != "!")
				HistoryLib.push(location.pathname);
				
			if(typeof window.onhashchange == "object") {
				window.onhashchange = function() {
					if(location.hash.substr(0, 2) == "#!" || location.hash.substr(0, 1) == "!") {
						if(HistoryLib.lastPush) {
							HistoryLib.lastPush = false;
						} else {
							for(i in HistoryLib.binded) {
								HistoryLib.binded[i](document.location.hash.substr(2));
							}
						}
						
						for(i in HistoryLib.bindedAll) {
							HistoryLib.binded[i](document.location.hash.substr(2));
						}
					}
				};
			} else {
				setInterval(function(){
					if(window.__oldHash != location.hash) {
						window.__oldHash = location.hash;
						if(location.hash.substr(0, 2) == "#!" || location.hash.substr(0, 1) == "!") {
							if(HistoryLib.lastPush) {
								HistoryLib.lastPush = false;
							} else {
								for(i in HistoryLib.binded) {
									HistoryLib.binded[i](document.location.hash.substr(2));
								}
							}
							
							for(i in HistoryLib.bindedAll) {
								HistoryLib.bindedAll[i](document.location.hash.substr(2));
							}
						}
					}
				}, HistoryLib.interval)
			}
		}
	}
};

window.onload = function() {
	setTimeout(function() {
		HistoryLib.lastPush = false;
	}, HistoryLib.interval + 50);
};
HistoryLib.Init();