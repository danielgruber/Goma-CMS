/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 31.07.2013
  * $Version - 1.0.1
*/

if(typeof goma == "undefined")
	var goma = {};

(function ( $ ) {
	goma.form = function(id) {
		if(!this instanceof goma.form)
			return new goma.form(id);
		
		
		var that = this;
		
		this.form = $("#" + id);
		this.form.removeClass("leave_check");
		
		$("#" + id).bind("formsubmit",function(){
			that.form.addClass("leave_check");
		});
		
		var button = false;
		$("#" + id).find("button[type=submit], input[type=submit]").click(function(){
			button = true;
			that.form.removeClass("leave_check");
		});
		
		$("#" + id).submit(function(){
			if(button == false) {
				button = true;
				setTimeout(function(){
					$("#"+id+" .default_submit").click();
				}, 100);
				return false;
			}
			var eventb = jQuery.Event("beforesubmit");
			$("#"+id).trigger(eventb);
			if ( eventb.result === false ) {
				return false;
			}
			
			var event = jQuery.Event("formsubmit");
			$("#"+id).trigger(event);
			if ( event.result === false ) {
				return false;
			}
			
			that.form.removeClass("leave_check");
			button = false;
		});
		
		$("#" + id).find("select, input, textarea").change(function(){
			that.form.addClass("leave_check");
		});
		
		$("#"+id+" > .default_submit").click(function(){
			$("#"+id+" > .actions  input[type=submit]").each(function(){
				if($(this).attr("name") != "cancel" && !$(this).hasClass("cancel")) {
					$(this).click();
					return false;
				}
			});
			return false;
		});
		
		goma.ui.bindUnloadEvent($("#" + id), function(){
			return that.unloadEvent();
		});
		
		goma.form.list[id.toLowerCase()] = this;
		return this;
	}
	
	goma.form.prototype = {
		setLeaveCheck: function(bool) {
			if(bool)
				this.form.addClass("leave_check");
			else
				this.form.removeClass("leave_check");
		},
		
		unloadEvent: function() {
			if(this.form.hasClass("leave_check")) {
				return lang("unload_not_saved").replace('\n', "\n");
			}
			
			return true;
		}
	};
	
	goma.form.list = [];
	
	$.fn.gForm = function() {
		
		if(this.get(0).tagName.toLowerCase() == "form") {
			if(typeof goma.form.list[this.attr("id").toLowerCase()] != "undefined") {
				return goma.form.list[this.attr("id").toLowerCase()];
			} else {
				return new goma.form(this.attr("id").toLowerCase());
			}
		}
		
		return false;
	};
})(jQuery);
