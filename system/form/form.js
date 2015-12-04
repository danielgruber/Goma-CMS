/**
 * The JS for forms.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0
 */
if(typeof goma == "undefined")
	var goma = {};

(function ( $ ) {
	goma.form = function(id, fields) {
		if(!this instanceof goma.form)
			return new goma.form(id);

        goma.form.garbageCollect();

		var that = this;
		
		this.form = $("#" + id);
		this.form.removeClass("leave_check");
		
		this.form.bind("formsubmit",function(){
			that.form.addClass("leave_check");
		});
		
		var button = false;
		this.form.find("button[type=submit], input[type=submit]").click(function(){
			button = true;
			that.form.removeClass("leave_check");
		});
		
		this.form.submit(function(){
            that.form.find(".err").slideUp();

			if(button == false) {
				setTimeout(function(){
                    that.form.find(".default_submit").click();
				}, 100);
				return false;
			}
			var eventb = jQuery.Event("beforesubmit");
			that.form.trigger(eventb);
			if ( eventb.result === false ) {
				return false;
			}
			
			var event = jQuery.Event("formsubmit");
			that.form.trigger(event);
			if ( event.result === false ) {
				return false;
			}
			
			that.form.removeClass("leave_check");
			button = false;
		});
		
		this.form.find("select, input, textarea").change(function(){
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
		
		goma.ui.bindUnloadEvent(this.form, function(){
			return that.unloadEvent();
		});

        for(var i in fields) {
            if(fields.hasOwnProperty(i)) {
               if(fields[i]["js"]) {
                   eval_global(fields[i]["js"]);
               }
            }
        }
		
		goma.form._list[id.toLowerCase()] = this;
		return this;
	};
	
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
	
	goma.form._list = [];
	goma.form.garbageCollect = function() {
		for(var i in goma.form._list) {
			if($("#" + id).length == 0) {
                delete goma.form._list[i];
            }
		}
	};
	
	$.fn.gForm = function() {
        goma.form.garbageCollect();

		if(this.get(0).tagName.toLowerCase() == "form") {
			if(typeof goma.form._list[this.attr("id").toLowerCase()] != "undefined") {
				return goma.form._list[this.attr("id").toLowerCase()];
			} else {
				return new goma.form(this.attr("id").toLowerCase());
			}
		}
		
		return false;
	};
})(jQuery);
