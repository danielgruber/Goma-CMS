/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 11.03.2013
  * $Version - 1.0
*/

if(typeof goma == "undefined")
	var goma = {};

goma.form = function(id) {
	this.leave_check = false;
	var that = this;
	
	$("#" + id).bind("formsubmit",function(){
		that.leave_check = true;
	});
	
	var button = false;
	$("#" + id).find("button[type=submit], input[type=submit]").click(function(){
		button = true;
		that.leave_check = false;
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
		
		that.leave_check = false;
		button = false;
	});
	
	$("#" + id).find("select, input, textarea").change(function(){
		that.leave_check = true;
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
		if(that.leave_check) {
			return lang("unload_not_saved").replace('\n', "\n");
		}
		
		return true;
	});
}

goma.form.prototype = {}; 