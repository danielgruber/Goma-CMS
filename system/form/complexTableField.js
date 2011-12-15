/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 04.06.2011
*/

var ComplexTableField = function(table, orderby, ordertype) {
	table.css("display", "block");
	this.table = table;
	this.searchtext = "";
	this.searchfield = table.find("thead .search");
	this.orderby = "";
	this.ordertype = "";
	this.addSelected = [];
	var that = this;
	if(this.searchfield.val() == "") {
		this.searchfield.val(lang_search);
		this.searchfield.css("color", "#9f9f9f");
	}
	this.searchfield.focus(function(){
		$(this).addClass("active");
		if($(this).val() == lang_search) {
			$(this).val("");
			$(this).css("color", "#000000");
		}
	});
	
	this.searchfield.blur(function(){
		$(this).removeClass("active");
		if($(this).val() == "") {
			$(this).val(lang_search);
			$(this).css("color", "#9f9f9f");
		}
	});
	
	this.searchfield.change(function(){
		searchtext = that.searchfield.val();
		that.searchtext = searchtext;
		that.reloadBody(searchtext);
		that.updateCancelButton();
		
	});
	
	this.searchfield.keydown(function(){
		that.updateCancelButton();
	});
	
	
	this.table.parents("form").submit(function(){
		return that.form_submit();
	});
	
	
	
	this.table.find(".sortlink").click(function(){
		that.updateSort($(this).attr("name"));
	});
	
	this.table.find(".check").click = function() {
	
		that.checkclick($(this));
	}
	
	return this;
};

ComplexTableField.prototype = {
	form_submit: function() {
		if(this.searchfield.hasClass("active")) {
			this.searchfield.blur();
			this.searchfield.change();
			return false;
		}
		return true;
	},
	
	reloadBody: function(searchtext) {
		this.searchfield.val(searchtext);
		if(searchtext == lang_search) {
			searchtext = "";
		}
		this.setLoading();
		var that = this;
		var name = this.name;
		var key = this.key;
		if(this.order != "" && this.ordertype != "") {
			$.ajax({
				type: "post",
				url: this.searchfield.attr("href") + "/" + searchtext + "?order=" + this.order + "&ordertype=" + this.ordertype,
				error: function() {
					that.reloadBody(that.searchfield.val());				
				},
				success: function(html) {
					that.unsetLoading();
					that.table.find("tbody").remove();
					that.table.append(html);
				}
			});
		} else {
			$.ajax({
				type: "post",
				url: this.searchfield.attr("href") + "/" + searchtext,
				error: function() {
					that.reloadBody(that.searchfield.val());				
				},
				success: function(html) {
					that.unsetLoading();
					that.table.find("tbody").remove();
					that.table.append(html);
				}
			});
		}
	},
	setLoading: function() {
		this.searchfield.parent().find(".loader").remove();
		this.searchfield.parent().append("<img src=\"images/16x16/loading.gif\" class=\"loader\" alt=\"...\" />");
	},
	unsetLoading: function() {
		this.searchfield.parent().find(".loader").remove();
	},
	updateSort: function(name) {
		if(this.order == name) {
			if(this.ordertype == "desc") {
				this.ordertype = "asc"
			} else {
				this.ordertype = "desc";
			}
		} else {
			this.order = name;
			this.ordertype = "desc";
		}
		this.setSortActive(name, this.ordertype);
		this.reloadBody(this.searchfield.val());
	},
	setSortActive: function(name, ordertype) {
		this.table.find(".desc").removeClass("desc");
		this.table.find(".asc").removeClass("asc");
		this.table.find(".sortlink[name="+name+"]").addClass(ordertype);
		
	},
	updateCancelButton: function() {
		var that = this;
		this.table.find(".searchfieldholder .cancel").remove();
		if(this.searchfield.val() != "" && this.searchfield.val() != self.lang_search) {
			this.table.find(".searchfieldholder").append("<span class=\"cancel\"></span>");
			this.table.find(".searchfieldholder .cancel").click(function(){
				that.searchfield.val("");
				that.updateCancelButton();
				that.searchfield.change();
				that.searchfield.focus();
			});
		}
	},
	checkclick: function(box) {
		if(box.attr("type") == "checkbox") {
			if(box.attr("checked")) {
				$.ajax({
					url: this.searchfield.attr("href") + "/" + searchtext + "?order=" + this.order + "&ordertype=" + this.ordertype,
				});
			}
		}
	}
}