/**
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 19.05.2013
 */

self.dropdownDialogs = [];
// put the data into the right namespace
(function($){

	"use strict";

	var counter = 0;
	var elems = [];
	var dropdowns = [];

	/**
	 * the constructor
	 */
	window.dropdownDialog = function(uri, elem, position, options) {

		this.elem = $(elem);
		if(this.elem.length === 0) {
			if(position != "fixed") {
				throw new Error("element for dropdown-dialog must exist.");
			} else {
				this.elem = $("body");
			}
		}

		this.setPosition(position);
		this.uri = uri;
		this.triangle_position = "center";
		this.closeButton = true;
		this.html = "";

		// set options
		if(typeof options !== "undefined") {
			for (var i in options) {
				this[i] = options[i];
			}
		}


		this.id = randomString(20);
		this.Init();

		this.show(uri);

		return this;
	};

	dropdownDialog.prototype = {
		subDialogs: [],
		updateTimeout: null,
		currentPos: null,
		copyElement: true,

		checkEdit: function() {
			if(this.dropdown.find("> div > .content > div").html() != this.html) {
				this.html = this.dropdown.find("> div > .content > div").html();
				this.definePosition(this.position);
			}
		},

		/**
		 * inits the dropdown
		 * creates info for dropdown on related element
		 * creates dropdown
		 * initates the events
		 * registers all other things
		 * oh, and before I forget it, it set's loading state ;)
		 *
		 *@name Init
		 */
		Init: function() {

			//if(typeof profiler != "undefined") profiler.mark("dropdownDialog.Init");

			var loading;

			// first validate id
			if(this.elem.attr("id") === undefined) {
				this.elem.attr("id", "link_dropdown_dialog_" + counter);
				counter++;
			}

			// second check if an dialog for this element doesnt exist, and if not, create the element
			if($("#dropdownDialog_" + this.elem.attr("id")).length == 0) {
				getDocRoot().append('<div id="dropdownDialog_'+this.elem.attr("id")+'" class="dropdownDialog windowindex"></div>');
				this.dropdown = $("#dropdownDialog_" + this.elem.attr("id"));
				this.dropdown.append('<div><div class="content"></div></div>');
				this.dropdown.css({
					display: "none",
					"position": "absolute"
				});

				var definePositionClosure = function(){
					if(this.dropdown != null && this.updateTimeout == null) {
						this.updateTimeout = setTimeout(function(){
							this.updateTimeout = null;
						}.bind(this), 500);
						this.definePosition();
					}
				}.bind(this);

				this.dropdown.find(" > div > .content").resize(definePositionClosure);
				$(window).resize(definePositionClosure);

				$(window).scroll(function(){
					if(this.dropdown != null) {
						this.moveDropdown(this.currentPos);
					}
				}.bind(this));

				loading = true;
			} else {
				this.dropdown = $("#dropdownDialog_" + this.elem.attr("id"));
				loading = false;
			}

			dropdowns[this.dropdown.attr("id")] = this;
			elems[this.elem.attr("id")] = this;
			self.dropdownDialogs[this.id] = this;

			// autohide
			if(!this.elem.hasClass("noAutoHide") && this.autohide != false) {
				CallonDocumentClick(function(){
					this.hide();
				}.bind(this), [this.dropdown, this.elem]);

				goma.ui.bindESCAction($("body"), function(){
					this.hide();
				}.bind(this));
			}

			if(this.elem.hasClass("noIEAjax")) {
				if($.browser.msie && getInternetExplorerVersion() < 9) {
					location.href = this.uri;
				}
			}

			if(loading) {
				this.setLoading();
			}

			//if(typeof profiler != "undefined") profiler.unmark("dropdownDialog.Init");
		},

		/**
		 * defines the position of the dropdown
		 *
		 * @param position: string if to set this.position
		 */
		definePosition: function(position) {
			//if(typeof profiler != "undefined") profiler.mark("dropdownDialog.definePosition");

			if(position !== null && position !== undefined) {
				this.setPosition(position);
			}

			if(this.elem.css("display") == "none") {
				throw new Error("dropdownDialog does not support elements with display:none yet");
			}

			// get position which is logical
			var elemtop = this.elem.offset().top;
			var elemleft = this.elem.offset().left;
			var elemright = $(document).width() - elemleft;

			if(this.position == "auto") {
				if(elemleft < 100 && elemtop > 100) {
					position = "right";
				} else if(elemright < 100 && elemtop > 100) {
					position = "left";
				} else {
					if(elemtop > ($(window).height() * 0.7)) {
						position = "bottom";
					} else {
						position = "top";
					}
				}
			} else {
				position = this.position;
			}

			// validate
			if(position === "bottom") {
				elemtop = this.elem.offset().top;
				if((elemtop - this.dropdown.height() - 2) < -10) {
					position = "top";
				}
			}

			// add position as class
			this.dropdown.find(" > div").attr("class", "");
			this.dropdown.find(" > div").addClass("position_" + position);

			this.currentPos = position;
			// now move dropdown
			this.moveDropdown(position);
		},

		/**
		 * validates and sets the position
		 *
		 *@name setPosition
		 */
		setPosition: function(position) {
			switch(position) {
				case "left":
					this.position = "left";
					break;
				case "center":
				case "top":
					this.position = "top";
					break;
				case "right":
					this.position = "right";
					break;
				case "bottom":
					this.position = "bottom";
					break;
				case "fixed":
					this.position = "fixed";
					break;
				default:
					this.position = "auto";
			}

		},

		/**
		 * moves the dropdown to the right place cause of position (top, bottom, left, right)
		 *
		 *@name moveDropdown
		 */
		moveDropdown: function(position) {
			if(typeof profiler !== "undefined") profiler.mark("dropdownDialog.moveDropdown");

			this.triangle_position = "center";

			// first get position of element
			var elemtop = this.elem.offset().top;
			var elemleft = this.elem.offset().left;

			var elemheight = this.elem.outerHeight();
			var elemwidth = this.elem.outerWidth();


			this.dropdown.find(" > div > .triangle").remove();

			// preserve scroll
			var scrollElements = [];
			this.dropdown.find('.scrollable').each(function(){
				scrollElements.push({
					element: $(this),
					top: $(this).scrollTop(),
					left: $(this).scrollLeft()
				});
			});

			// preserve display
			var display = (this.dropdown.css("display") == "block");
			//this.dropdown.css({"display": "block", top: "-1000px"});
			var triangle_margin_top, positionTop, positionLeft, positionRight;

			switch(position) {
				case "bottom":
					positionTop = elemtop - this.dropdown.height() - 2;

				case "top":
				case "center":


					if(typeof positionTop === "undefined") {
						positionTop = elemtop + elemheight - 2;
					}

					this.dropdown.find(" > div > .content").css("width", "");
					positionLeft = elemleft - (this.dropdown.find(" > div > .content").width() / 2) + (elemwidth / 2) - 3;

					this.dropdown.find(" > div > .content").css("width", this.dropdown.find(" > div > .content").width()); // force width

					var contentWidth = this.getContentWidth();
					// check if this is logical
					if(contentWidth + positionLeft + 100 > $(document).width()) {
						this.triangle_position = "right";
						positionLeft = elemleft + elemwidth - contentWidth + 14;
					}


					if(positionLeft < 0) {
						this.triangle_position = "left";
						positionLeft = elemleft - 18;
					}

					this.dropdown.css({
						top: positionTop,
						left: positionLeft,
						right: "auto",
						bottom: "auto",
						position: "absolute"
					});
					break;
				case "left":
					var positionTop = elemtop - (this.dropdown.find(" > div > .content").height() / 2) + (elemheight / 2);

					// fix if dropdown is not in document
					if(positionTop < 0) {
						triangle_margin_top = 0 - 20 + positionTop;
						positionTop = 4;
					} else {
						triangle_margin_top = 0 - 20;
					}

					this.dropdown.find(" > div > .content").css("width", this.dropdown.find(" > div > .content").width()); // force width
					positionRight = elemleft + 2 - this.getContentWidth();

					this.dropdown.css({
						top: positionTop,
						left: positionRight,
						right: "auto",
						bottom: "auto",
						position: "absolute"
					});
					break;
				case "right":
					positionTop = elemtop - (this.dropdown.find(" > div > .content").height() / 2) + (elemheight / 2);
					// fix if dropdown is not in document
					if(positionTop < 0) {
						triangle_margin_top = 0 - 20 + positionTop;
						positionTop = 4;
					} else {
						triangle_margin_top = 0 - 20;
					}

					positionLeft = elemleft + elemwidth - 2;
					this.dropdown.css({
						top: positionTop,
						left: positionLeft,
						right: "auto",
						bottom: "auto",
						position: "absolute"
					});
					break;
				case "fixed":

					this.dropdown.css({
						top: "10%",
						left: "10%",
						right: "10%",
						bottom: "10%",
						position: "fixed",
						zIndex: 900
					});

					break;
			}

			// now set the triangle
			if(position != "fixed") {
				this.dropdown.find(" > div").prepend('<div class="triangle_position_' + this.triangle_position + ' triangle"><div></div></div>');
				if (typeof triangle_margin_top !== "undefined") {
					this.dropdown.find(" > div > .triangle").css("margin-top", triangle_margin_top);
				}
			}

			if(display) {
				this.dropdown.css("display", "block");
			} else {
				this.dropdown.fadeIn("fast");
			}

			for(var i in scrollElements) {
				var data = scrollElements[i];
				data.element.scrollTop(data.top);
				data.element.scrollLeft(data.left);
			}

			if(typeof profiler != "undefined") profiler.unmark("dropdownDialog.moveDropdown");
		},

		/**
		 * returns content-width.
		 */
		getContentWidth: function() {
			if(this.dropdown.find(" > div > .content").length > 0) {
				return this.dropdown.find(" > div > .content").outerWidth();
			}

			return 0;
		},

		/**
		 * sets the dropdown in loading state
		 *
		 *@name setLoading
		 */
		setLoading: function() {
			this.dropdown.css("display", "block");
			this.closeButton = false;
			this.setContent('<img src="system/templates/css/images/loading_big.gif" alt="loading" style="display: block;margin: auto;" />');
			this.closeButton = true;
		},

		/**
		 * sets the given content
		 * registers subDropdownEvents
		 *
		 *@name setContent
		 *@access public
		 */
		setContent: function(content) {
			if(typeof profiler != "undefined") profiler.mark("dropdownDialog.setContent");

			this.subDialogs = [];
			this.dropdown.find(" > div > .content").css("width", ""); // unlock width

			// check if string or jquery object
			if(typeof content == "string")
				this.dropdown.find(" > div > .content").html('<div>' + content + '</div>');
			else {
				this.dropdown.find(" > div > .content").html('');
				$(content).wrap("<div></div>").appendTo(this.dropdown.find(" > div > .content"));
			}

			// close-button
			if(!this.elem.hasClass("hideClose") && this.closeButton) {
				this.dropdown.find(" > div > .content > div").prepend('<a class="close" href="javascript:;">&times;</a>');
			} else {
				this.dropdown.find(" > div  > .content > .close").remove();
			}

			// closing over elements in dropdown
			var that = this;
			this.dropdown.find(".close, *[name=cancel]").click(function(){
				that.hide();
				return false;
			});

			// if is shown also now, we we'll move it to the right position
			if(this.dropdown.css("display") != "none")
				this.definePosition(this.position);

			this.dropdown.off(".subdrops");
			// register event for sub-dialogs
			this.dropdown.on("click.subdrops", "a", function(){
				if($(this).attr("rel").match(/dropdownDialog/)) {
					var $this = $(this);
					setTimeout(function(){
						that.subDialogs.push("dropdownDialog_" + $this.attr("id"));
					}, 100);
				}
			});

			// javascript-profiler
			if(typeof profiler != "undefined") {
				profiler.unmark("dropdownDialog.setContent");
			}

		},

		/**
		 * shows a specific uri in the dropdown
		 * for example: ./system/blah, it'll get data via ajax and show the result
		 *
		 *@name show
		 *@access public
		 */
		show: function(uri) {
			if(this.dropdown.css("display") == "block" && this.dropdown.attr("name") == uri) {
				return false;
			}
			this.setLoading();
			this.dropdown.attr("name", uri);
			var i;
			for(i in this.players) {
				if(typeof this.players[i] == "object") {
					if(typeof this.players[i].regexp != "undefined" && this.players[i].regexp.test(this.uri)) {
						return this.players[i].method(this, this.uri);
					}
				}
			}

			return this.player_ajax(this.uri);
		},

		/**
		 * removes the dropdown
		 *
		 *@name removeHeloper
		 */
		removeHelper: function() {
			this.dropdown.remove();
			this.dropdown = null;
		},

		/**
		 * hides the dropdown if no subdropdowns exist and removes it afterwards
		 *
		 *@name remove
		 */
		remove: function() {
			// first check if subDropdown is open
			for(var i in this.subDialogs) {
				if(this.subDialogs.hasOwnProperty(i)) {
					if ($("#" + this.subDialogs[i]).length > 0 && $("#" + this.subDialogs[i]).css("display") != "none" && !$("#" + this.subDialogs[i]).hasClass("hiding")) {
						return true;
					}
				}
			}

			if(this.dropdown != null) {
				// unregister dropdown
				dropdowns[this.dropdown.attr("id")] = null;
				elems[this.elem.attr("id")] = null;
				self.dropdownDialogs[this.id] = null;

				setTimeout(function () {
					this.dropdown.addClass("hiding");
				}.bind(this), 20);

				// animate dropdown
				this.dropdown.fadeOut("fast", function () {
					this.removeHelper();
				}.bind(this));
			}
		},

		/**
		 * alias for remove
		 *
		 *@name hide
		 */
		hide: function() {
			this.remove(); // better solution
		},

		/**
		 * player for content-type html
		 *
		 *@name play_html
		 */
		player_html: function(uri) {
			if(!this.copyElement) {
				$(uri).fadeIn("fast");
				this.setContent($(uri));
			} else {
				this.setContent($(uri).html());
			}
		},

		/**
		 * player for images
		 *
		 @name player_img
		 */
		player_img: function(uri) {
			var href = uri;

			// create an image to get dimensions of the image
			var preloader = new Image();
			var that = this;
			preloader.onload = function(){

				// now calculate correct dimensions, which fit into window
				var height = preloader.height;
				var width = preloader.width;
				var sv = width / height;
				var dheight = $(window).height() - 300;
				var dwidth = $(window).width() - 400;
				if(height > dheight ){
					var height = dheight;
					var width = height * sv;
				}

				// set img-tag
				that.setContent('<img src="'+href+'" alt="'+href+'" height="'+height+'" width="'+width+'" />');
			}

			// if an error happens, we can't do anything :(
			preloader.onerror = function(){
				that.setContent('<h3>Connection error!</h3> <br /> Please try again later!');
			}

			// now set src when events set, because of lags in some browsers
			preloader.src = href;
		},

		/**
		 * player for urls reachable via ajax
		 *
		 *@name player_ajax
		 */
		player_ajax: function(uri) {
			var that = this;
			var oldURI = uri;
			if(uri.indexOf("?") == -1) {
				uri += "?dropdownDialog=1&dropElem=" + this.id;
			} else {
				uri += "&dropdownDialog=1&dropElem=" + this.id;
			}
			$.ajax({
				url: uri,
				type: "get",
				// data should always be html as basic, we can interpret layteron
				dataType: "html"
			}).done(function(response, status, jqXHR){
				// run code
				try {
					goma.ui.loadResources(jqXHR).done(function(){
						var content_type = jqXHR.getResponseHeader("content-type");

						// we have to set it new, because of scoping issues
						var content = jqXHR.responseText;

						// if it is json-data
						if(content_type.indexOf("text/x-json") != -1) {
							try {
								var data = $.parseJSON(content);
								content = data.content;
								if(data.position !== undefined && data.position !== null) {
									that.position = data.position;
								}
								if(data.closeButton !== undefined && data.closeButton !== null) {
									that.closeButton = data.closeButton;
								}
								that.setContent(content);

								if(typeof data.exec !== "undefined") {

									// execution should not break json-data before
									try {
										var method;
										if (window.execScript) {
											window.execScript('method = function(' + data.exec + ')',''); // execScript doesn’t return anything
										} else {
											method = eval('(function(){' + data.exec + '})');
										}

										method.call(that);
									} catch(e) {
										alert(e);
									}
								}


							} catch(e) {
								alert(e);
								that.setContent("error parsing JSON");
							}

							// if it is javascript
						} else if(content_type === "text/javascript") {

							// execution for IE and all other Browsers
							var method;
							if (window.execScript) {
								window.execScript('method = ' + 'function(' + content + ')', ''); // execScript doesn’t return anything
							} else {
								method = eval('(function(){' + content + '});');
							}
							method.call(this);

						} else {
							// html just must be set to Dialog
							that.setContent(content);
						}

						RunAjaxResources(jqXHR);
					}).fail(function(){
						that.setContent('Error while fetching data from the server: <br /> Failed to fetch data from the server.');
					});
				} catch(e) {
					alert(e);
					location.href = oldURI;
				}


			}).fail(function(jqXHR){
				// try find out why it has failed
				if(jqXHR.textStatus === "timeout") {
					that.setContent('Error while fetching data from the server: <br /> The response timed out.');
				} else if(jqXHR.textStatus === "abort") {
					that.setContent('Error while fetching data from the server: <br /> The request was aborted.');
				} else {
					that.setContent('Error while fetching data from the server: <br /> Failed to fetch data from the server.');
				}
			});
		},
		players: [
			{
				"regexp": /^#/,
				"method": function(obj, uri) {
					obj.player_html(uri);
				}
			},
			{
				"regexp": /^.*\.(img|png|jpg|jpeg|gif|bmp)$/i,
				"method": function(obj,uri) {
					obj.player_img(uri);
				}
			}
		]
	};

	dropdownDialog.get = function(elem) {
		if(typeof dropdowns[elem] != "undefined") {
			return dropdowns[elem];
		} else if(typeof elems[elem] != "undefined") {
			return elems[elem];
		} else if(typeof self.dropdownDialogs[elem] != "undefined") {
			return self.dropdownDialogs[elem];
		} else {
			return null;
		}
	};

	// jQuery-Extension
	$.fn.extend({
		dropdownDialog: function(options) {
			if(typeof options == "string")
				options = {uri: options};

			var defaults = {
				"uri": "",
				"position": null
			};
			var o = $.extend(defaults, options);

			var that = this;
			var obj = {
				instances: [],
				hide: function() {
					for(i in obj.instances) {
						obj.instances[i].hide();
					}
				},
				remove: function() {
					for(i in obj.instances) {
						obj.instances[i].remove();
					}
				}
			};

			this.each(function(){
				var instance = new dropdownDialog(o.uri, this, o.position);
				obj.instances.push(instance);
			});

			return obj;

		}
	});
})(jQuery);
