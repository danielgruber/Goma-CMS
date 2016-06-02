/**
 * The JS for dropdowns.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1
 */
var DropDown = function(form, field, id, url, multiple, sortable) {
	this.url = url;
	this.multiple = multiple;
	this.widget = $("#" + id + "_widget");
	this.input = $("#" + id);
	this.page = 1;
	this.search = "";
	this.timeout = "";
	this.sortable = !!sortable;
	this.id = id;
	this.form = form;
	this.field = field;

	field.dropdown = this;

	field.getValue = this.getValue.bind(this);
	field.getValueTitle = this.getValueTitle.bind(this);
	field.setValue = this.setValue.bind(this);
	field.getPossibleValuesAsync = this.getPossibleValuesAsync.bind(this);

	this.init();
	return this;
};

DropDown.prototype = {

	currentSearchResult: "",
	currentRequest: null,
	inSort: false,

	getValue: function() {
		return this.input.val();
	},

	getValueTitle: function() {
		return this.widget.find(" > .field .value-title").eq(0).text();
	},

	setValue: function(ids) {
		if(this.multiple) {
			if(typeof ids == "object") {
				for(var i in ids) {
					if(ids.hasOwnProperty(i)) {
						this.check(ids[i]);
					}
				}
			} else {
				this.check(ids)
			}
		} else {
			this.check(ids);
		}
	},

	getPossibleValuesAsync: function(page) {
		page = page ? page : 1;
		var deferred = $.Deferred();
		$.ajax({
			url: this.url + "/getData/" + page + "/",
			type: "post",
			dataType: "json"
		}).fail(deferred.reject).done(function(data){
			var records = [];

			for(var i in data.data) {
				if(data.data.hasOwnProperty(i)) {
					records.push(data.data[i].key);
				}
			}

			if(data.right) {
				records.push(undefined);
			}

			deferred.resolve(records);
		});

		return deferred.promise();
	},

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
			if(!that.inSort) {
				that.toggleDropDown();
			}
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
			if ((code < 37 || code > 40) && code != 13 && code != 91) {
				that.page = 1;
				that.reloadData();
			} else {
				if (code == 37) {
					that.widget.find(" > .dropdown > .header > .pagination > span > a.left").click();
					return false;
				} else if(code == 39) {
					that.widget.find(" > .dropdown > .header > .pagination > span > a.right").click();
					return false;
				} else if(code == 38 && !that.multiple) {
					// check for marked
					if(that.widget.find(" > .dropdown > .content a.checked").length > 0) {
						if(that.widget.find(" > .dropdown > .content a.checked").parent().prev("li").length > 0) {
							var id = that.widget.find(" > .dropdown > .content a.checked").parent().prev("li").find("a").attr("id").substring(10 + that.id.length);
							that.check(id, false);
						}
					} else {
						var id = that.widget.find(" > .dropdown > .content a:last-child").attr("id").substring(10 + that.id.length);
						that.check(id, false);
					}
				} else if(code == 40 && !that.multiple) {
					if(that.widget.find(" > .dropdown > .content a.checked").length > 0) {
						if(that.widget.find(" > .dropdown > .content a.checked").parent().next("li").length > 0) {
							var id = that.widget.find(" > .dropdown > .content a.checked").parent().next("li").find("a").attr("id").substring(10 + that.id.length);
							that.check(id, false);
						}
					} else {
						var id = that.widget.find(" > .dropdown > .content a:first-child").attr("id").substring(10 + that.id.length);
						that.check(id, false);
					}
				}
			}
		});

		// preload some lang to improve performance
		preloadLang(["loading", "search", "no_result"]);

		unbindFromFormSubmit(this.widget.find(" > .dropdown > .header > .search"));
		this.widget.find(" > .dropdown > .header > .cancel").click(function(){
			that.widget.find(" > .dropdown > .header > .search").val("");
			that.page = 1;
			that.reloadData();
			that.widget.find(" > .dropdown > .header > .search").focus();
		});

		// register on label
		this.widget.parent().parent().find(" > label").click(function(){
			if(!that.inSort) {
				that.showDropDown();
			}
			return false;
		});

		goma.ui.bindESCAction($("body"), function() {
			that.hideDropDown();
		});

		this.bindFieldEvents();
	},
	/**
	 * sets the content of the field, which is clickable and is showing current selection
	 *
	 *@name setField
	 *@param string - content
	 */
	setField: function(content, setHeight) {
		if(setHeight === true) {
			this.widget.find(" > .field").css("height", this.widget.find(" > .field").height());
		} else {
			this.widget.find(" > .field").css("height", "");
		}

		this.widget.find(" > .field").html(content);
		this.bindFieldEvents();
		this.updateDropdownPosition();
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
			this.updateDropdownPosition();

			var oldFieldHTML = this.widget.find(" > .field").html();

			// show loading
			this.setFieldLoading();
			var $this = this;

			// load data
			this.reloadData(function(){
				//$this.widget.find(" > .field").css({height: ""});
				$this.widget.find(" > .dropdown").fadeIn(200);
				$this.setField(oldFieldHTML);
				var width = $this.widget.find(" > .field").outerWidth(false) - ($this.widget.find(" > .dropdown").outerWidth(false) - $this.widget.find(" > .dropdown").width());
				$this.widget.find(" > .dropdown").css({ width: width});
				$this.widget.find(" > .dropdown .search").focus();

				$this.updateDropdownPosition();
			});
		}
	},

	updateDropdownPosition: function() {
		this.widget.find(" > .dropdown").css({top: this.widget.find(" > .field").outerHeight() - 2});
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

		// we limit the request, so just send if in the last 200 milliseconds no edit in search was done
		if(search != "" && search != lang("search", "search...")) {
			this.widget.find(" > .dropdown > .header > .cancel").fadeIn(100);
			var timeout = 300;
		} else  { // if no search is set, limit is not needed
			this.widget.find(" > .dropdown > .header > .cancel").fadeOut(100);
			var timeout = 0;
		}

		var makeAjax = function(){
			if(that.currentRequest !== null) {
				that.currentRequest.abort();
			}

			that.currentRequest = $.ajax({
				url: that.url + "/getData/" + that.page + "/",
				type: "post",
				data: {"search": search},
				dataType: "json",
				error: function(jqXHR, status, text) {
					if(status != "abort") {
						that.setContent("Error! Please try again");
						if (onfinish != null) {
							onfinish();
						}

						setTimeout(that.reloadData.bind(that, fn), 1000);
					}
				},
				success: function(data) {
					var inputVal = that.widget.find(" > .dropdown > .header > .search").val();
					if(this != inputVal && that.currentSearchResult == inputVal) {
						return;
					}

					that.currentSearchResult = this;

					if(!data || data == "")
						that.setContent("No data given, Your Session might have timed out.");

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

					var content = "";
					// render data
					if(data.data) {
						content += "<ul>";
						for(var i in data.data) {
							if(data.data.hasOwnProperty(i)) {
								var val = data.data[i];

								content += "<li>";

								if (data.value[val.key] || data.value[val.key] === 0)
									content += "<a href=\"javascript:;\" class=\"checked\" id=\"dropdown_" + that.id + "_" + val.key + "\"><span title=\"" + val.value.replace('"', '\\"') + "\">" + val.value + "</span></a>";
								else
									content += "<a href=\"javascript:;\" id=\"dropdown_" + that.id + "_" + val.key + "\"><span title=\"" + val.value.replace('"', '\\"') + "\">" + val.value + "</span></a>";

								if (typeof val.smallText == "string") {
									content += "<span class=\"record_info\">" + val.smallText + "</span>";
								}

								content += "</li>";
							}
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

				}.bind(search)
			});
		};

		if(timeout == 0)
			makeAjax();
		else
			this.timeout = setTimeout(makeAjax, timeout);
	},

	/**
	 * binds the clicks on values to set/unset a value.
	 *
	 * @access public
	 */
	bindContentEvents: function() {
		var that = this;
		this.widget.find(" > .dropdown > .content ul li a").click(function(){
			var id = $(this).attr("id").substring(10 + that.id.length);
			if(that.multiple) {
				if($(this).hasClass("checked")) {
					that.uncheck(id);
				} else {
					that.check(id);
				}
			} else {
				that.check(id);
			}
			return false;
		});
	},

	/**
	 * binds the events to the displayed values in the field-area.
	 *
	 * @access public
	 */
	bindFieldEvents: function() {
		var $this = this;
		this.widget.find(" > .field").find(".value-holder .value .value-remove").click(function(){
			$this.uncheck($(this).attr("data-id"));
			return false;
		});

		if(this.sortable) {
			console.log && console.log("init sortable");
			this.widget.find(" > .field > .value-holder").addClass("sortable");
			this.widget.find(" > .field > .value-holder").sortable({
				opacity: 0.75,
				revert: true,
				tolerance: 'pointer',
				containment: "parent",
				start: function(event, ui) {
					$this.inSort = true;
				},
				update: function(event, ui) {
					var data  = $(this).sortable("serialize", {key: "sorted[]"});
					// save order
					$.ajax({
						url: $this.url + "/saveSort/",
						data: data,
						type: "post",
						dataType: "html"
					});
				},
				stop: function() {
					setTimeout(function(){
						$this.inSort = false;
					}, 33);
				}
			});
		} else {
			this.widget.find(" > .field > .value-holder").removeClass("sortable");
		}
	},

	/**
	 * checks a node
	 *
	 *@name check
	 *@param id
	 */
	check: function(id, hide) {
		var _this = this;
		var shouldHide = hide;
		if(!this.multiple) {
			this.widget.find(" > .dropdown > .content ul li a.checked").removeClass("checked");
			this.input.val(id);
		}

		// we use document.getElementById, cause of possible dots in the id https://github.com/danielgruber/Goma-CMS/issues/120
		$(document.getElementById("dropdown_" + this.id + "_" + id)).addClass("checked");

		// id contains id of form-field and value
		this.setFieldLoading();
		$.ajax({
			url: this.url + "/checkValue/",
			type: "post",
			data: {"value": id},
			error: function() {
				alert("Failed to check Node. Please check your Internet-Connection");
			},
			success: function(html) {
				// everything is fine
				_this.setField(html);

				if(!_this.multiple) {
					if(typeof shouldHide == "undefined" || shouldHide == true)
						_this.hideDropDown();

					_this.input.val(id);
				}
			}
		});
	},
	/**
	 * unchecks a node
	 *
	 *@name check
	 *@param id
	 */
	uncheck: function(id) {
		// we use document.getElementById, cause of possible dots in the id https://github.com/danielgruber/Goma-CMS/issues/120
		$(document.getElementById("dropdown_" + this.id + "_" + id)).removeClass("checked");

		this.setFieldLoading();
		$.ajax({
			url: this.url + "/uncheckValue/",
			type: "post",
			data: {"value": id},
			error: function() {
				alert("Failed to uncheck Node. Please check your Internet-Connection");
			},
			success: function(html) {
				// everything is fine
				this.setField(html);

				if(!this.multiple) {
					if(this.input.val() == id) {
						this.input.val(0);
						this.showDropDown();
					}
				}
			}.bind(this)
		});
	},

	/**
	 * sets field loading.
	 */
	setFieldLoading: function(){
		this.setField("<img height=\"12\" width=\"12\" src=\"images/16x16/loading.gif\" alt=\"loading\" /> "+lang("loading", "loading..."), true);
	}
}
