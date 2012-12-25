/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 25.12.2012
*/

function DropDown(id, url, multiple) {
	this.url = url;
	this.multiple = multiple;
	this.widget = $("#" + id + "_widget");
	this.input = $("#" + id);
	this.page = 1;
	this.search = "";
	this.timeout = "";
	this.init();
	this.id = id;
	return this;
}

DropDown.prototype = {
	/**
	 * inits the dropdown-events
	 *
	 *@name init
	*/
	init: function() {
		var that = this;
		this.widget.disableSelection();
		this.widget.find(" > .field").css("cursor", "pointer");
		
		this.widget.find(" > .field").click(function(){ 
			that.toggleDropDown();
			return false;
		});
		this.widget.find(" > input").css("display", "none");
		this.widget.find(" > .field").css("margin-top", 0);
		// make document-click to close the dropdown
		CallonDocumentClick(function(){
			that.hideDropDown();
		}, [this.widget.find(" > .dropdown"), this.widget.find(" > .field"), this.widget.parent().parent().find(" > label")]);
		
		
		// pagination
		this.widget.find(" > .dropdown > .header > .pagination > span > a").click(function(){
			if(!$(this).hasClass("disabled")) {
				if($(this).hasClass("left")) {
					if(that.page != 1)
						that.page--;
				} else {
					that.page++;
				}
				that.reloadData();
			}
		});
		
		this.widget.find(" > .dropdown > .header > .search").keyup(function(){
			that.page = 1;
			that.reloadData();
		});
		
		// preload some lang to improve performance
		preloadLang(["loading", "search", "no_result"]);
		
		unbindFromFormSubmit(this.widget.find(" > .dropdown > .header > .search"));
		this.widget.find(" > .dropdown > .header > .cancel").click(function(){
			that.widget.find(" > .dropdown > .header > .search").val("");
			that.widget.find(" > .dropdown > .header > .search").keyup();
			that.widget.find(" > .dropdown > .header > .search").focus();
		});
		
		// register on label
		this.widget.parent().parent().find(" > label").click(function(){
			that.showDropDown();
			return false;
		});
	},
	/**
	 * sets the content of the field, which is clickable and is showing current selection
	 *
	 *@name setField
	 *@param string - content
	*/
	setField: function(content) {
		this.widget.find(" > .field").html(content);
	},
	
	/**
	 * sets the content of the dropdown
	 *
	 *@name setContent
	 *@param string - content
	*/
	setContent: function(content) {
		this.widget.find(" > .dropdown > .content").html('<div class="animationwrapper">' + content + '</div>');
	},
	/**
	 * shows the dropdown
	 *
	 *@name showDropDown
	*/
	showDropDown: function() {
		if(this.widget.find(" > .dropdown").css("display") == "none") {
			this.widget.find(" > .field").addClass("active");
			// set correct position
			this.widget.find(" > .dropdown").css({top: this.widget.find(" > .field").outerHeight() - 2});
			
			if(($.browser.msie && getInternetExplorerVersion() < 9)) {
				var fieldhtml = this.widget.find(" > .field").html();
				//this.widget.find(" > .field").css({height: this.widget.find(" > .field").height()});
				this.widget.find(" > .field").html("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."));
				var $this = this;
				this.reloadData(function(){
					//$this.widget.find(" > .field").css({height: ""});
					$this.widget.find(" > .dropdown").fadeIn(200);
					$this.widget.find(" > .field").html(fieldhtml);
					var width = $this.widget.find(" > .field").width() +  /* padding */10;
					$this.widget.find(" > .dropdown").css({ width: width});
					$this.widget.find(" > .dropdown .search").focus();
				});
			} else {
				var fieldhtml = this.widget.find(" > .field").html();
				//this.widget.find(" > .field").css({height: this.widget.find(" > .field").height()});
				this.widget.find(" > .field").html("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."));
				var $this = this;
				gloader.load("jquery.scale.rotate");
				this.reloadData(function(){
					//$this.widget.find(" > .field").css({height: ""});
					$this.widget.find(" > .dropdown").css("display", "block");
					var destheight = $this.widget.find(" > .dropdown").height();
					$this.widget.find(" > .field").html(fieldhtml);
					var width = $this.widget.find(" > .field").width() +  /* padding */10;
					$this.widget.find(" > .dropdown").css({width: width, height: destheight,"opacity": 0.4});
					$this.widget.find(" > .dropdown").scale(0.1);
					$this.widget.find(" > .dropdown").animate({scale: 1.05, opacity: 0.9}, 150, function(){
						$this.widget.find(" > .dropdown").animate({scale:1, "opacity": 0.95}, 100, function(){
							$this.widget.find(" > .dropdown").css({ height: "", "-moz-transform": ""});
							$this.widget.find(" > .dropdown .search").focus();
						});
					});
				});
			}
		}
	},
	/**
	 * hides the dropdown
	 *
	 *@name showDropDown
	*/
	hideDropDown: function() {	
		clearTimeout(this.timeout);
		this.widget.find(" > .dropdown").fadeOut(200);
		this.widget.find(" > .field").removeClass("active");
	},
	/**
	 * toggles the dropdown
	 *
	 *@name toggleDropDown
	*/
	toggleDropDown: function() {
		if(this.widget.find(" > .dropdown").css("display") == "none") {
			this.showDropDown();
		} else {
			this.hideDropDown();
		}
	},
	/**
	 * gets data from the server to set the content of the dropdown
	 * it uses current pid and search-query
	 *
	 *@name reloadData
	*/
	reloadData: function(fn) {
		var onfinish = fn;
		var that = this;
		var search = this.widget.find(" > .dropdown > .header > .search").val();
		
		
		this.setContent("<div style=\"text-align: center;\"><img src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading...")+"</div>");
		clearTimeout(this.timeout);
		var that = this;
		// we limit the request, so just send if in the last 200 milliseconds no edit in search was done
		if(search != "" && search != lang("search", "search...")) {
			this.widget.find(" > .dropdown > .header > .cancel").fadeIn(100);
			var timeout = 200;
		} else  { // if no search is set, limit is not needed
			this.widget.find(" > .dropdown > .header > .cancel").fadeOut(100);
			var timeout = 0;
		}
		
		var makeAjax = function(){
			$.ajax({
				url: that.url + "/getData/" + that.page + "/",
				type: "post",
				data: {"search": search},
				dataType: "json",
				error: function() {
					that.setContent("Error! Please try again");
					if(onfinish != null) {
						onfinish();
					}
				},
				success: function(data) {
					
					if(!data || data == "")
						that.setContent("No data given, Your Session might be timed out.");
						
					if(data.right) {
						that.widget.find(".dropdown > .header > .pagination > span > .right").removeClass("disabled");
					} else {
						that.widget.find(".dropdown > .header > .pagination > span > .right").addClass("disabled");
					}
					
					if(data.left) {
						that.widget.find(".dropdown > .header > .pagination > span > .left").removeClass("disabled");
					} else {
						that.widget.find(".dropdown > .header > .pagination > span > .left").addClass("disabled");
					}
					
					this.value = data.value;
					var content = "";
					// render data
					if(data.data) {
						content += "<ul>";
						i = -1;
						for(i in data.data) {
							var val = data.data[i];
							if(typeof val == "object") {
								smallText = val[1];
								val = val[0];
							}
							
							content += "<li>";
							
							if(this.value[i] || this.value[i] === 0)
								content += "<a href=\"javascript:;\" class=\"checked\" id=\"dropdown_"+that.id+"_"+i+"\">"+val+"</a>";
							else
								content += "<a href=\"javascript:;\" id=\"dropdown_"+that.id+"_"+i+"\">"+val+"</a>";
								
							if(typeof smallText == "string") {
								content += "<span class=\"record_info\">"+smallText+"</span>";
							}
							
							content += "</li>";
							
							smallText = null;
						}
						
						content += "</ul>";
						if(i == -1) 
							content = '<div class="no_data">' + lang("no_result", "There is no data to show.") + '</div>';
						that.setContent(content);					
						that.bindContentEvents();
					}
					
					if(onfinish != null) {
						onfinish();
					}
					
				}
			});
		};
		
		if(timeout == 0)
			makeAjax();
		else
			this.timeout = setTimeout(makeAjax, timeout);
	},
	/**
	 * binds the clicks on values to set/unset a value
	 *
	 *@name bindContentEvents
	*/
	
	bindContentEvents: function() {
		var that = this;
		this.widget.find(" > .dropdown > .content ul li a").click(function(){
			if(that.multiple) {
				
				if($(this).hasClass("checked")) {
					that.uncheck($(this).attr("id"));
				} else {
					that.check($(this).attr("id"));
				}
			} else {
				that.check($(this).attr("id"));
			}
		});
	},
	
	/**
	 * checks a node
	 *
	 *@name check
	 *@param id
	*/
	check: function(id) {
		var that = this;
		if(this.multiple) {
			
			$("#" + id).addClass("checked");
			var value = id.substring(10 + this.id.length);
			this.widget.find(" > .field").html("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."));
			$.ajax({
				url: this.url + "/checkValue/",
				type: "post",
				data: {"value": value},
				error: function() {
					alert("Failed to check Node. Please check your Internet-Connection");
				}, 
				success: function(html) {
					// everything is fine
					that.widget.find(" > .field").html(html);
				}
			});
		} else {
			this.widget.find(" > .dropdown > .content ul li a.checked").removeClass("checked");
			$("#" + id).addClass("checked");
			var value = id.substring(10 + this.id.length);
			this.input.val(value);
			that.widget.find(" > .field").html("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."));
			$.ajax({
				url: this.url + "/checkValue/",
				type: "post",
				data: {"value": value},
				error: function() {
					alert("Failed to check Node. Please check your Internet-Connection");
				}, 
				success: function(html) {
					// everything is fine
					that.widget.find(" > .field").html(html);
					that.hideDropDown();
					
					that.input.val(value);
				}
			});	
			
			
		}
	},
	/**
	 * unchecks a node
	 *
	 *@name check
	 *@param id
	*/
	uncheck: function(id) {
		var that = this;
		// this is just for dropdowns with multiple values
		if(this.multiple) {
			$("#" + id).removeClass("checked");
			var value = id.substring(10 + this.id.length);
			that.widget.find(" > .field").html("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."));
			$.ajax({
				url: this.url + "/uncheckValue/",
				type: "post",
				data: {"value": value},
				error: function() {
					alert("Failed to uncheck Node. Please check your Internet-Connection");
				}, 
				success: function(html) {
					// everything is fine
					that.widget.find(" > .field").html(html);
				}
			});
		}
	}

}