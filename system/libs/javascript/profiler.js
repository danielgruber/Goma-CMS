/**
  * goma-javascript-profiler
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 03.05.2012
  * $Version 1.1
*/

var profiler = {
	version: "1.0",
	/**
	 * here we save profiles
	*/
	profiles: [],
	/**
	 * here we save active profiles
	*/
	currentProfiles: [],
	
	/**
	 * use this to start profiling
	*/
	mark: function(mark){
		if(typeof this.currentProfiles[mark] == "undefined") {
			this.currentProfiles[mark] = microtime(true);
		}
		
		this.registerShutDown();
	},
	/**
	 * use this to stop profiling
	*/
	unmark: function(mark) {
		if(typeof this.currentProfiles[mark] != "undefined") {
			var length = microtime(true) - this.currentProfiles[mark];
			if(typeof this.profiles[mark] == "undefined") {
				this.profiles[mark] = {
					count: 1,
					time: length
				};
			} else {
				this.profiles[mark]["count"]++;
				this.profiles[mark]["time"] += length;
			}
			
			this.currentProfiles[mark] = undefined;
		}
	},
	
	/**
	 * call this to get result as string
	*/
	draw: function() {
		var str = "Profiler V. "+this.version+"\nUser-Agent: "+navigator.userAgent+"\nURL: "+location.href+"\n\n";
		str += "Calls  Execution Time     Name\n";
		var a = 0;
		for(i in this.profiles) {
			str += this.profiles[i].count + str_repeat(" ", 8 - this.profiles[i].count.toString().length);
			
			var time = Math.round(this.profiles[i].time * 10000) / 10;
			
			str += time + "ms" + str_repeat(" ",20  - time.toString().length);
			
			str += i + "\n";
			a++;
		}
		
		if(a == 0)
			return "";
		
		return str;
	},
	flush: function() {
		this.profiles = [];
		this.currentProfiles = [];
	},
	
	/**
	 * send to server
	 * data is available in LOG_FOLDER/jsprofile/date/time.log
	*/
	sendToServer: function(async) {
		if(typeof async == "undefined")
			async = true;
		
		var data = {"profiles": [], "user-agent": navigator.userAgent, "url": location.href};
		var a = 0;
		for(i in this.profiles) {
			data.profiles.push({"name": i, "count": this.profiles[i].count, "time": this.profiles[i].time});
			a++;
		}
		if(a != 0) {
			jQuery.ajax({
				url: root_path + "system/logJSProfile",
				async: async,
				type: "post",
				data: {"JSProfile": data},
				timeout: 1000
			});
		}
		
		this.flush();
	},
	registerShutDown: function() {
		var that = this;
		if(typeof this.registered == "undefined") {
			jQuery(window).unload(function(){
				that.sendToServer(false);
			});
			this.registered = true;
		}
	}
};