/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 1.11.2011
*/


var LaM_current_text = "";
var LaM_type_timeout;

(function($, w){
	$(function(){
		// searchfield bindings
		$(".treesearch form").submit(function(){
			updateWithSearch($(this));
			return false;
		});
		
		$(".treesearch form input[type=text]").change(function(){
			updateWithSearch($(this).parent());
			return false;
		});	
		
		// create-form-binding
		$(".left .create form").submit(function(){
			// no ajax in IE
			if(getInternetExplorerVersion() <= 8 && getInternetExplorerVersion() != -1) {
				return true;
			}
			$this = $(this);
			if(self.leave_check ===  false && !confirm(self.unload_lang)) {
				return false;
			}
			self.leave_check = true;
			$this.append("<img class=\"loading\" src=\"images/16x16/ajax-loader.gif\" alt=\"Loading...\" />");
			$.ajax({
				url: $this.attr("action") + "?" + $this.serialize(),
				success: function(html, code, request) {
					renderResponseTo(html, $this.parents(".leftandmaintable").find(".main"), request);
					$this.find(".loading").remove();
					self.marked_node = $this.attr("nodeid");
					$(".tree .marked").removeClass("marked");
					$("a[nodeid=0]").parent().parent().addClass("marked");
					
				}
			});
			return false;
		});
		
		// addon for searching while typing
		$(".treesearch form input[type=text]").keyup(function(){
			self.LaM_current_text = $(this).val();
			clearTimeout(self.LaM_type_timeout);
			self.LaM_type_timeout = setTimeout(function(){
				if(self.LaM_current_text == $(".treesearch form input[type=text]").val()) {
					updateWithSearch($(".treesearch form"),null, null, true);
				}
			}, 400);
		});
		
		if(	$(".treesearch form input[type=text]").val() == "" || $(".treesearch form input[type=text]").val() == lang_search) {
			var sort = true;
		} else {
			var sort = false;
		}
		
		$(".legend").find(":checkbox").each(function(){
			if(!$(this).prop("disabled")) {
				$(this).click(function(){
					reloadTree();
				});
			}
		});
		
		tree_bind_ajax(sort, $(".left .tree"));
	});
	
	w.reloadTree = function(fn) {
		$(".treesearch form input[type=text]").val("");
		updateWithSearch($(".treesearch form"), fn, true);
	}
	
	var active_val = "";
	function updateWithSearch($this, callback, force, notblur) {
		
		var fn = callback;
		var value = $this.find("input[type=text]").val();
		if(value == lang_search) {
			value = "";
		}
		
		if(force != null || value != active_val) {
			active_val = value;
		} else {
			return false;
		}
		
		if(value != "") {
			$this.find("input[type=text]").addClass("active");
		} else {
			$this.find("input[type=text]").removeClass("active");
		}
		if(notblur == null) {
			$this.find("input[type=text]").blur();
		}
		
		
		
		$this.parents(".classtree").find(".treewrapper").html("&nbsp;<img src=\"images/16x16/ajax-loader.gif\" alt=\"\" /> Loading...");
		if(value == "") {
			
			var params = "";
			$(".legend").find(":checkbox").each(function(){
				if(!$(this).prop("disabled")) {
					if($(this).prop("checked")) {
						params += "&tree_params["+$(this).attr("name")+"]=1";
					} else {
						params += "&tree_params["+$(this).attr("name")+"]=0";
					}
				}
			});
			
			$.ajax({
				url: BASE_SCRIPT + adminURI + "/updateTree/"+marked_node+"/?" + params,
				success: function(html, code, jqXHR) {
					
					renderResponseTo(html, $this.parents(".classtree").find(".treewrapper"), jqXHR);
					tree_bind($this.parents(".classtree").find(".treewrapper").find(".tree"));
					tree_bind_ajax(true, $(".left .tree"));
					
					
					
					
					if(fn != null) {
						fn();
					}
					// find optimal scroll by position of active element
					if($this.parents(".classtree").find(".treewrapper").find(".marked").length > 0) {
						var pos = $this.parents(".classtree").find(".treewrapper").find(".marked").position().top - $this.parents(".classtree").find(".treewrapper").position().top - $this.parents(".classtree").find(".treewrapper").height() / 2 + 20;
						if(pos > 0)
							$this.parents(".classtree").find(".treewrapper").scrollTop(pos);
					}
				}
			});
		} else {
			
			$.ajax({
				url: BASE_SCRIPT + adminURI + "/updateTree/"+marked_node+"/" + escape(value),
				success: function(html, code, jqXHR) {
					renderResponseTo(html, $this.parents(".classtree").find(".treewrapper"), jqXHR);
					tree_bind($this.parents(".classtree").find(".treewrapper").find(".tree"));
					tree_bind_ajax(false, $(".left .tree"));
					if(fn != null) {
						fn();
					}
				}
			});
		}
	}
	
	var last_dragged = 0;
	
	function tree_bind_ajax(sortable, node) {
		// find optimal scroll by position of active element
		if(node.parents(".treewrapper").find(".marked").length > 0) {
			var oldscroll = $(".treewrapper").scrollTop();
			$(".treewrapper").scrollTop(0);
			var pos = $(".treewrapper").find(".marked").offset().top - $(".treewrapper").position().top - $(".treewrapper").height() / 2 + 20;
			if(pos > 0) {
				$(".treewrapper").scrollTop(oldscroll);
				$(".treewrapper").scrollTop(pos);
			} else
				$(".treewrapper").scrollTop(0);
		}
		
		node.find(".treelink").click(function(){
			if($(this).attr("nodeid") == 0 || self.last_dragged != $(this).attr("nodeid")) {
				// no ajax in IE
				if(getInternetExplorerVersion() <= 8 && getInternetExplorerVersion() != -1) {
					return true;
				}
				LoadTreeItem($(this).attr("nodeid"));
			}
			return false;
		});
		
		if(sortable && self.LaMsort) {
			gloader.load("sortable");
			node.find("ul").each(function(){
				var s = this;
				$(this).find( " > li " ).css("cursor", "move");
				$(this).sortable({
					helper: 'clone',
					items: ' > li',
					cursor: "move",
					update: function(event, ui)
					{
						$(ui.item).find(" > .a a").addClass("loading");
						// rerender
						$(s).find(" > li.last").removeClass("last");
						$(s).find(" > li:last").addClass("last");
						$.ajax({
							url: BASE_SCRIPT + adminURI + "/savesort/" + marked_node + "/",
							data: $(s).sortable('serialize'),
							type: 'post',
							error: function(e)
							{
								alert(e);
							},
							success: function(html, code, jqXHR)
							{
								renderResponseTo(html, $(".left .treewrapper"), jqXHR);
								tree_bind($(".left .treewrapper").find(".tree"));
								tree_bind_ajax(true, $(".left .tree"));
							}
						});
					},
					tolerance: 'pointer',
				});
			});
		}
		
	}
	
	$(function(){
		$(".left .treewrapper").bind("treeupdate", function(event, node){
			tree_bind_ajax(true, node);
		});
	});
	
	w.LoadTreeItem = function (id) {
		if(self.leave_check ===  false && !confirm(self.unload_lang)) {
			return false;
		}
		self.leave_check = true;
		var $this = $("a[nodeid="+id+"]");
		if($this.length == 0) {
			return false;
		}
		$this.addClass("loading");
		$this.parent().parent().addClass("marked");
		$.ajax({
			url: $this.attr("href"),
			data: {"ajaxfy": true},
			success: function(html, code, request) {
				renderResponseTo(html, $this.parents(".leftandmaintable").find(".main"), request);
				$this.removeClass("loading");
				self.marked_node = $this.attr("nodeid");
				$(".tree .marked").removeClass("marked");
				$this.parent().parent().addClass("marked");
				// find optimal scroll by position of active element
				if($(".treewrapper").find(".marked").length > 0) {
					var oldscroll = $(".treewrapper").scrollTop();
					$(".treewrapper").scrollTop(0);
					var pos = $(".treewrapper").find(".marked").offset().top - $(".treewrapper").position().top - $(".treewrapper").height() / 2 + 20;
					if(pos > 0) {
						$(".treewrapper").scrollTop(oldscroll);
						$(".treewrapper").scrollTop(pos);
					} else
						$(".treewrapper").scrollTop(0);
				}
			}
		});
		return false;
	}
})(jQuery, window);


window.onbeforeunload = function(){

	if(self.leave_check ===  false) {
		return self.lang_unload_not_saved;
	}
	return;
}