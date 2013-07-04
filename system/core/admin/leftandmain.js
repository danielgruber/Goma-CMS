/**
 * JavaScript for the simple two column admin-panel.
 *
 * @package     Goma\Admin\LeftAndMain
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.2.5
 */

var LaM_current_text = "";
var LaM_type_timeout;

(function($, w){
	$(function(){
		// first modify out view-controller in javascript.
		goma.ui.setMainContent($("#content > .content_inner table td.main > .inner"));
		
		// make it visible.
		$(".leftandmaintable").css("display", "");
		
		// add flex-boxes
		goma.ui.addFlexBox($(".leftandmaintable .LaM_tabs .treewrapper"));
		goma.ui.addFlexBox(".leftandmaintable form .fieldsScroll");
		goma.ui.addFlexBox(".leftandmaintable .main .inner");
		
		//! searchfield bindings
		$(".treesearch form").submit(function(){
			updateWithSearch($(this));
			return false;
		});
		
		$(".treesearch form input[type=text]").change(function(){
			updateWithSearch($(this).parent());
			return false;
		});
		
		updateSidebarToggle();
		
		//! leftbar
		$(document).on("click touchend", ".leftbar_toggle", function(){
			if($(this).hasClass("active")) {
				$(this).removeClass("active");
				$(this).addClass("not_active");
				$(".leftandmaintable .left").addClass("not_active");
				$(".leftandmaintable .left").removeClass("active");
			} else {
				$(this).addClass("active");
				$(this).removeClass("not_active");
				$(".leftandmaintable .left").removeClass("not_active");
				$(".leftandmaintable .left").addClass("active");
			}
			goma.ui.updateFlexBoxes();
			return false;
		});
		
		setTimeout(function(){
			if(!$(".leftbar_toggle").hasClass("active")) {
				$(".leftbar_toggle, .leftandmaintable .left").addClass("not_active");
			} else {
				$(".leftandmaintable .left").addClass("active");
			}
		}, 100);
		
		
		//! history
		if(getInternetExplorerVersion() > 7 || getInternetExplorerVersion() == -1) {
			gloader.load("history");
			HistoryLib.bind(function(url){
				
				if($(".treewrapper a[href='"+url+"']").length > 0) {
					var $this = $(".treewrapper a[href='"+url+"']");
					$this.addClass("loading");
				}
				
				goma.ui.ajax(undefined, {
					url: url,
					data: {"ajaxfy": true}
				}).done(function(html, node, request) {
					$("#content .success, #content .error, #content .notice").hide("fast");
					$(".tree .marked").removeClass("marked");
					$(".left-and-main .LaM_tabs > div.create ul li.active").removeClass("active");
					
					if(typeof $this != "undefined") {
						$this.removeClass("loading");
						$this.parent().parent().addClass("marked");
					}
					
					// find optimal scroll by position of active element
					if($(".treewrapper").find(".marked").length > 0) {
						// switch to tree-tab if necessary
						if(!$(".left-and-main .LaM_tabs > ul > li > a.tree").parent().hasClass("active")) {
							$(".left-and-main .LaM_tabs > ul > li > a.tree").click();
						}
						
						// correct scroll-position
						var oldscroll = $(".treewrapper").scrollTop();
						$(".treewrapper").scrollTop(0);
						var pos = $(".treewrapper").find(".marked").offset().top - $(".treewrapper").position().top - $(".treewrapper").height() / 2 + 20;
						if(pos > 0) {
							$(".treewrapper").scrollTop(oldscroll);
							$(".treewrapper").scrollTop(pos);
						} else
							$(".treewrapper").scrollTop(0);
					}
				});
				
			});
		}
		
		//! tree-events
		$(".treesearch form input[type=text]").keyup(function(){
			self.LaM_current_text = $(this).val();
			clearTimeout(self.LaM_type_timeout);
			self.LaM_type_timeout = setTimeout(function(){
				if(self.LaM_current_text == $(".treesearch form input[type=text]").val()) {
					updateWithSearch($(".treesearch form"),null, null, true);
				}
			}, 400);
			
			// legend-fade
			if($(".treesearch form input[type=text]").val() == "") {
				$(".legend").stop().fadeTo(300, 1);	
			} else {
				$(".legend").stop().fadeTo(300, 0.4);
			}
		});
		
		// bindings
		setTimeout(function(){
			// bind now!
			tree_bind_ajax(sort, $(".left div.tree ul"));
		}, 150);
		
		
		//! sort
		if(	$(".treesearch form input[type=text]").val() == "" || $(".treesearch form input[type=text]").val() == lang("search", "Search...")) {
			var sort = true;
		} else {
			var sort = false;
		}
		
		//! legend
		$(".legend").find(":checkbox").each(function(){
			if(!$(this).prop("disabled")) {
				$(this).click(function(){
					reloadTree();
				});
			}
		});
		
		/**
		 * tab-rendering
		*/
		$(".left-and-main a.create").click(function(){
			var $this = $(this);
			goma.ui.ajax(undefined, {
				url: $this.attr("href"),
				data: {"ajaxfy": true},
				beforeSend: function() {
					if(typeof HistoryLib.push == "function")
						HistoryLib.push($this.attr("href"));
				}
			}).done(function(){
				$("#content .success, #content .error, #content .notice").hide("fast");
				$("div.tree .marked").removeClass("marked");
											
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
			});
			return false;
		});
	});
	
	w.reloadTree = function(fn, openid) {
		$(".treesearch form input[type=text]").val("");
		updateWithSearch($(".treesearch form"), fn, true, undefined, openid);
	}
	
	var active_val = "";
	function updateWithSearch($this, callback, force, notblur, openid) {
		
		var fn = callback;
		var value = $this.find("input[type=text]").val();
		if(value == lang("search", "Search...")) {
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
		var treewrapper = $this.parents(".classtree").find(".treewrapper");
		// if no search
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
			
			if(typeof openid != "undefined")
				params += "&edit_id=" + escape(openid);
			
			$.ajax({
				url: BASE_SCRIPT + adminURI + "/updateTree/"+marked_node+"/?" + params,
				success: function(html, code, jqXHR) {
					
					renderResponseTo(html, treewrapper, jqXHR).done(function(){
						tree_bind(treewrapper.find(".tree"));
						tree_bind_ajax(true, $(".left div.tree ul"));
						
						
						if(fn != null) {
							fn();
						}
						// find optimal scroll by position of active element
						if(treewrapper.find(".marked").length > 0) {
							var pos = treewrapper.find(".marked").position().top - treewrapper.position().top - treewrapper.height() / 2 + 20;
							if(pos > 0)
								treewrapper.scrollTop(pos);
						}
					});
				}
			});
			
			$(".legend").stop().fadeTo(300, 1);	
		} else {
			
			// if search
			$.ajax({
				url: BASE_SCRIPT + adminURI + "/updateTree/"+marked_node+"/" + escape(value),
				success: function(html, code, jqXHR) {
					renderResponseTo(html, $this.parents(".classtree").find(".treewrapper"), jqXHR).done(function(){
						tree_bind($this.parents(".classtree").find(".treewrapper").find(".tree"));
						tree_bind_ajax(false, $(".left div.tree ul"));
						
						if(fn != null) {
							fn();
						}
					});
				}
			});
		}
	}
	
	var last_dragged = 0;
	
	function tree_bind_ajax(sortable, node, findPos) {
		// normally we trigger the javascript to find optimal scroll-position
		if(typeof findPos == "undefined") {
			findPos = true;
		}
		
		// find optimal scroll by position of active element
		if(findPos && node.parents(".treewrapper").find(".marked").length > 0) {
			var oldscroll = $(".treewrapper").scrollTop();
			$(".treewrapper").scrollTop(0);
			var pos = $(".treewrapper").find(".marked").offset().top - $(".treewrapper").position().top - $(".treewrapper").height() / 2 + 20;
			if(pos > 0) {
				$(".treewrapper").scrollTop(oldscroll);
				$(".treewrapper").scrollTop(pos);
			} else
				$(".treewrapper").scrollTop(0);
		}
		
		// bind events to the nodes to load the content then
		node.find(".treelink").click(function(){
			if($(this).attr("nodeid") == 0 || self.last_dragged != $(this).attr("nodeid")) {
				// no ajax in IE
				if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) {
					return true;
				}
				LoadTreeItem($(this).attr("nodeid"));
			}
			return false;
		});
		
		// bind the sort
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
							data: $(s).sortable('serialize', {key: "treenode[]"}),
							type: 'post',
							error: function(e)
							{
								alert(e);
							},
							success: function(html, code, jqXHR)
							{
								renderResponseTo(html, $(".left .treewrapper"), jqXHR).done(function(){;
									tree_bind($(".left .treewrapper").find(".tree"));
									tree_bind_ajax(true, $(".left div.tree ul"));
								});
							}
						});
					},
					tolerance: 'pointer',
				});
			});
		}
		
	}
	
	// bind the treeupdate, when the tree was updated
	$(function(){
		$(".left .treewrapper").bind("treeupdate", function(event, node){
			tree_bind_ajax(true, node, false);
		});
	});
	
	var updateSidebarToggle = function() {
		if($("#content > .content_inner table td.main > .inner .leftbar_toggle").length > 0) {
			$("#content > .content_inner table td.main > .leftbar_toggle").css("display", "none");
		} else {
			$("#content > .content_inner table td.main > .leftbar_toggle").css("display", "");
		}
		
		$(".left-and-main > table td.main > .inner > form > .fields").addClass("fieldsScroll");
		$(".left-and-main > table td.main > .inner > form > .actions").addClass("actionsScroll");
		
		goma.ui.updateFlexBoxes();
	};
	
	// function to load content of a tree-item
	w.LoadTreeItem = function (id) {

		var $this = $("a[nodeid="+id+"]");
		if($this.length == 0) {
			return false;
		}
		
		// Internet Explorer seems not to work correctly with Ajax, maybe we'll fix it later on, but until then, we will just load the whole page ;)
		if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) {
			$this.click();
			return true;
		}
		
		setTimeout(function(){
			goma.ui.ajax(undefined, {
				beforeSend: function() {
					$this.addClass("loading");
					$this.parent().parent().addClass("marked");
					if(typeof HistoryLib.push == "function")
						HistoryLib.push($this.attr("href"));
				},
				url: $this.attr("href"),
				data: {"ajaxfy": true}
			}).done(function(){
				if(id == 0) {
					$("td.left").addClass("active");
				} else {
					$("td.left").removeClass("active");
				}
				
				$("#content .success, #content .error, #content .notice").hide("fast");
				self.marked_node = $this.attr("nodeid");
				$(".tree .marked").removeClass("marked");
				$this.parent().parent().addClass("marked");
				$this.removeClass("loading");
				
				
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
				
				updateSidebarToggle();
			});
			
		}, 100);
		
		return false;
	}
})(jQuery, window);