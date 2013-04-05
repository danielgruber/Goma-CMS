/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 31.03.2013
*/

var DropDown = function(id, url, multiple) {
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
};

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
		
		this.widget.find(" > .dropdown").click(function(){
			that.widget.find(" > .dropdown > .header > .search").focus();
		});
		
		this.widget.find(" > .dropdown > .header > .search").keyup(function(e){
			var code = e.keyCode ? e.keyCode : e.which;
			if ((code < 37 || code > 40) && code != 13) {
				that.page = 1;
				that.reloadData();
			} else {
				if (code == 37) {
	       	 		that.widget.find(" > .dropdown > .header > .pagination > span > a.left").click();
	       	 		return false;
	       	 	} else if(code == 39) {
		       	 	that.widget.find(" > .dropdown > .header > .pagination > span > a.right").click();
		       	 	return false;
	       	 	} else if(code == 38) {
		       	 	// check for marked
		       	 	if(that.widget.find(" > .dropdown > .content a.checked").length > 0) {
			       	 	if(that.widget.find(" > .dropdown > .content a.checked").parent().prev("li").length > 0) {
				       	 	that.check(that.widget.find(" > .dropdown > .content a.checked").parent().prev("li").find("a").attr("id"), false);
			       	 	}
		       	 	} else {
			       	 	that.check(that.widget.find(" > .dropdown > .content a:last-child").attr("id"), false);
		       	 	}
	       	 	} else if(code == 40) {
		       	 	if(that.widget.find(" > .dropdown > .content a.checked").length > 0) {
			       	 	if(that.widget.find(" > .dropdown > .content a.checked").parent().next("li").length > 0) {
				       	 	that.check(that.widget.find(" > .dropdown > .content a.checked").parent().next("li").find("a").attr("id"), false);
			       	 	}
		       	 	} else {
			       	 	that.check(that.widget.find(" > .dropdown > .content a:first-child").attr("id"), false);
		       	 	}
	       	 	}
			}
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
		
		goma.ui.bindESCAction($("body"), function() {
			that.hideDropDown();
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
			
			var fieldhtml = this.widget.find(" > .field").html();
			//this.widget.find(" > .field").css({height: this.widget.find(" > .field").height()});
			
			// show loading
			this.widget.find(" > .field").html("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."));
			var $this = this;
			
			// load data
			this.reloadData(function(){
				//$this.widget.find(" > .field").css({height: ""});
				$this.widget.find(" > .dropdown").fadeIn(200);
				$this.widget.find(" > .field").html(fieldhtml);
				var width = $this.widget.find(" > .field").width() +  /* padding */10;
				$this.widget.find(" > .dropdown").css({ width: width});
				$this.widget.find(" > .dropdown .search").focus();
			});
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
		
		
		this.setContent("<div class=\"loading\" style=\"text-align: center;\"><img src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading...")+"</div>");
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
							
							content += "<li>";
							
							if(this.value[val.key] || this.value[val.key] === 0)
								content += "<a href=\"javascript:;\" class=\"checked\" id=\"dropdown_"+that.id+"_"+val.key+"\">"+val.value+"</a>";
							else
								content += "<a href=\"javascript:;\" id=\"dropdown_"+that.id+"_"+val.key+"\">"+val.value+"</a>";
								
							if(typeof val.smallText == "string") {
								content += "<span class=\"record_info\">"+val.smallText+"</span>";
							}
							
							content += "</li>";
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
			return false;
		});
	},
	
	/**
	 * checks a node
	 *
	 *@name check
	 *@param id
	*/
	check: function(id, hide) {
		var that = this;
		var h = hide;
		if(this.multiple) {
			
			// we use document.getElementById, cause of possible dots in the id https://github.com/danielgruber/Goma-CMS/issues/120
			$(document.getElementById(id)).addClass("checked");
			
			// id contains id of form-field and value
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
			
			// we use document.getElementById, cause of possible dots in the id https://github.com/danielgruber/Goma-CMS/issues/120
			$(document.getElementById(id)).addClass("checked");
			
			// id contains id of form-field and value
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
					if(typeof h == "undefined" || h == true)
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
			// we use document.getElementById, cause of possible dots in the id https://github.com/danielgruber/Goma-CMS/issues/120
			$(document.getElementById(id)).removeClass("checked");
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