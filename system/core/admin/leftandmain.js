/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 16.12.2012
*/


var LaM_current_text = "";
var LaM_type_timeout;

(function($, w){
	$(function(){
		$(".leftandmaintable").css("display", "");
		
		// searchfield bindings
		$(".treesearch form").submit(function(){
			updateWithSearch($(this));
			return false;
		});
		
		$(".treesearch form input[type=text]").change(function(){
			updateWithSearch($(this).parent());
			return false;
		});
		
		if(getInternetExplorerVersion() > 7 || getInternetExplorerVersion() == -1) {
			gloader.load("history");
			HistoryLib.bind(function(url){
				
				if(self.leave_check ===  false && !confirm(lang("unload_lang").replace('\n', "\n"))) {
					return false;
				}
				self.leave_check = true;
				
				if($(".treewrapper a[href='"+url+"']").length > 0) {
					var $this = $(".treewrapper a[href='"+url+"']");
					$this.addClass("loading");
					
				}
				
				$.ajax({
					url: url,
					data: {"ajaxfy": true},
					success: function(html, code, request) {
						$("#content .success, #content .error, #content .notice").hide("fast");
						renderResponseTo(html, $(".leftandmaintable").find("td.main > .inner"), request);
						renderSideBar();
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
					}
				});
				
			});
		}
		
		// create-form-binding
		$(".left .create form").submit(function(){
			// no ajax in IE
			if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) {
				return true;
			}
			$this = $(this);
			if(self.leave_check ===  false && !confirm(lang("unload_lang"))) {
				return false;
			}
			self.leave_check = true;
			$this.append("<img class=\"loading\" src=\"images/16x16/ajax-loader.gif\" alt=\"Loading...\" />");
			$.ajax({
				url: $this.attr("action") + "?" + $this.serialize(),
				success: function(html, code, request) {
					renderResponseTo(html, $this.parents(".leftandmaintable").find(".main"), request);
					renderSideBar();
					$this.find(".loading").remove();
					self.marked_node = $this.attr("nodeid");
					$(".tree .marked").removeClass("marked");
					$("a[nodeid=0]").parent().parent().addClass("marked");
					
				}
			});
			return false;
		});
		
		/**
		 * tree-events
		*/
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
		
		// sort
		if(	$(".treesearch form input[type=text]").val() == "" || $(".treesearch form input[type=text]").val() == lang("search", "Search...")) {
			var sort = true;
		} else {
			var sort = false;
		}
		
		// legend
		$(".legend").find(":checkbox").each(function(){
			if(!$(this).prop("disabled")) {
				$(this).click(function(){
					reloadTree();
				});
			}
		});
		
		setTimeout(function(){
			// bind now!
			tree_bind_ajax(sort, $(".left div.tree ul"));
		}, 150);
		
		/**
		 * rendering of the whole page via javascript
		*/
		var renderSideBar = function() {
			var tableHeight = $(window).height() - $("#content > .header").outerHeight() - $("#content > .addcontent").outerHeight() - $("#head").outerHeight();
			if(tableHeight < 405)
				tableHeight = 405;
			
			$(".left-and-main").css("min-height", tableHeight);
			$(".left-and-main > table").css("height", tableHeight);
			
			// set height for main-area
			OuterDiff = $(".left-and-main > table td.main > .inner").outerHeight() - $(".left-and-main > table td.main > .inner").height();
			$(".left-and-main > table td.main > .inner").css("height", tableHeight - OuterDiff);
			
			// render fixed actions
			if($(".left-and-main > table td.main > .inner > form > .fields").length > 0 && $(".left-and-main > table td.main > .inner > form > .actions").length > 0) {
				$(".left-and-main > table td.main > .inner > form > .fields").addClass("fieldsScroll");
				$(".left-and-main > table td.main > .inner > form > .fields").outerHeight(tableHeight - OuterDiff - $(".left-and-main > table td.main > .inner > form > .actions").outerHeight(true) - $(".left-and-main > table td.main > .inner > form > .error").outerHeight(true));
				$(".left-and-main > table td.main > .inner > form > .actions").addClass("actionsScroll");
			}
			
			// set height for sidebar
			var otherSideBar = $(".leftandmaintable tr > .left > .LaM_tabs > ul").outerHeight() + $(".leftandmaintable tr > .left > .LaM_tabs > .tree > .treesearch").outerHeight() + $(".leftandmaintable tr > .left > .LaM_tabs > .tree .legend").outerHeight() + 15;
			$(".leftandmaintable tr > .left > .LaM_tabs > .tree > .classtree > .treewrapper").css("height", tableHeight - otherSideBar - 30);
		}
		
		window.renderLeftSideBar = renderSideBar;
		
		$(window).resize(renderSideBar);
		renderSideBar();
		
		$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
			jqXHR.done(function(){
				setTimeout(renderSideBar, 1);
			});
		});
		
		/**
		 * tab-rendering
		*/
		$(".left-and-main .LaM_tabs > ul.tabArea > li > a").click(function(){
			if($(".left-and-main .LaM_tabs").find("." + $(this).attr("class")).length > 0) {
				if(self.leave_check ===  false && !confirm(lang("unload_lang").replace('\n', "\n"))) {
					return false;
				}
				self.leave_check = true;
				$(".left-and-main .LaM_tabs > ul > li.active").removeClass("active");
				$(".left-and-main .LaM_tabs > div").css("display", "none");
				$(".left-and-main .LaM_tabs").find("div." + $(this).attr("class")).css("display", "block");
				$(this).parent().addClass("active");
				if(typeof HistoryLib.push == "function")
					HistoryLib.push($(this).attr("href"));
			
				renderSideBar();
				
				$.ajax({
					url: $(this).attr("href"),
					data: {"ajaxfy": true},
					success: function(html, code, request) {
						$("#content .success, #content .error, #content .notice").hide("fast");
						renderResponseTo(html, $(".leftandmaintable").find("td.main > .inner"), request);
						renderSideBar();
						$("div.tree .marked").removeClass("marked");
						$(".left-and-main .LaM_tabs > div.create ul li.active").removeClass("active");
													
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
				
			}
			return false;
		});
	});
	
	w.reloadTree = function(fn) {
		$(".treesearch form input[type=text]").val("");
		updateWithSearch($(".treesearch form"), fn, true);
	}
	
	var active_val = "";
	function updateWithSearch($this, callback, force, notblur) {
		
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
			
			$.ajax({
				url: adminURI + "/updateTree/"+marked_node+"/?" + params,
				success: function(html, code, jqXHR) {
					
					renderResponseTo(html, treewrapper, jqXHR);
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
				}
			});
			
			$(".legend").stop().fadeTo(300, 1);	
		} else {
			
			// if search
			$.ajax({
				url: adminURI + "/updateTree/"+marked_node+"/" + escape(value),
				success: function(html, code, jqXHR) {
					renderResponseTo(html, $this.parents(".classtree").find(".treewrapper"), jqXHR);
					tree_bind($this.parents(".classtree").find(".treewrapper").find(".tree"));
					tree_bind_ajax(false, $(".left div.tree ul"));
					
					if(fn != null) {
						fn();
					}
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
							url: adminURI + "/savesort/" + marked_node + "/",
							data: $(s).sortable('serialize', {key: "treenode[]"}),
							type: 'post',
							error: function(e)
							{
								alert(e);
							},
							success: function(html, code, jqXHR)
							{
								renderResponseTo(html, $(".left .treewrapper"), jqXHR);
								tree_bind($(".left .treewrapper").find(".tree"));
								tree_bind_ajax(true, $(".left div.tree ul"));
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
	
	// function to load content of a tree-item
	w.LoadTreeItem = function (id) {
		
		// check if we are on a current unsaved page
		if(self.leave_check ===  false && !confirm(lang("unload_lang").replace('\n', "\n"))) {
			return false;
		}
		self.leave_check = true;
		
		
		var $this = $("a[nodeid="+id+"]");
		if($this.length == 0) {
			return false;
		}
		
		// Internet Explorer seems not to work correctly with Ajax, maybe we'll fix it later on, but until then, we will just load the whole page ;)
		if(getInternetExplorerVersion() <= 7 && getInternetExplorerVersion() != -1) {
			$this.click();
			return true;
		}
		
		// switch to tree-tab if necessary
		if(!$(".left-and-main .LaM_tabs > ul > li > a.tree").parent().hasClass("active")) {
			$(".left-and-main .LaM_tabs > ul > li.active").removeClass("active");
			$(".left-and-main .LaM_tabs > div").css("display", "none");
			$(".left-and-main .LaM_tabs").find("div.tree").css("display", "block");
			$(".left-and-main .LaM_tabs").find("a.tree").parent().addClass("active");
		}
		
		$this.addClass("loading");
		$this.parent().parent().addClass("marked");
		if(typeof HistoryLib.push == "function")
			HistoryLib.push($this.attr("href"));
		
		// delay a bit to have enough resources for UI to draw
		setTimeout(function(){
			$.ajax({
				url: $this.attr("href"),
				data: {"ajaxfy": true},
				success: function(html, code, request) {
					$("#content .success, #content .error, #content .notice").hide("fast");
					renderResponseTo(html, $this.parents(".leftandmaintable").find("td.main > .inner"), request);
					renderLeftSideBar();
					self.marked_node = $this.attr("nodeid");
					$(".tree .marked").removeClass("marked");
					$this.parent().parent().addClass("marked");
					$this.removeClass("loading");
					$(".left-and-main .LaM_tabs > div.create ul li.active").removeClass("active");
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
		}, 100);
		return false;
	}
})(jQuery, window);


window.onbeforeunload = function(){

	if(self.leave_check ===  false) {
		return lang("unload_not_saved").replace('\n', "\n");
	}
	return;
}