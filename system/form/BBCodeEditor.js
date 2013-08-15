/**
 * The JS for bb code editors.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0
 */
(function($) {

	// thanks to http://aktuell.de.selfhtml.org/artikel/javascript/bbcode/
	function insert(input, aTag, eTag) {
		input.focus();
		/* für Internet Explorer */
		if ( typeof document.selection != 'undefined') {
			/* Einfügen des Formatierungscodes */
			var range = document.selection.createRange();
			var insText = range.text;
			range.text = aTag + insText + eTag;
			/* Anpassen der Cursorposition */
			range = document.selection.createRange();
			if (insText.length == 0) {
				range.move('character', -eTag.length);
			} else {
				range.moveStart('character', aTag.length + insText.length + eTag.length);
			}
			range.select();
		}
		/* für neuere auf Gecko basierende Browser */
		else if ( typeof input.selectionStart != 'undefined') {
			/* Einfügen des Formatierungscodes */
			var start = input.selectionStart;
			var end = input.selectionEnd;
			var insText = input.value.substring(start, end);
			input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
			/* Anpassen der Cursorposition */
			var pos;
			if (insText.length == 0) {
				pos = start + aTag.length;
			} else {
				pos = start + aTag.length + insText.length + eTag.length;
			}
			input.selectionStart = pos;
			input.selectionEnd = pos;
		}
	}

	var methods = {
		/**
		 * inits the editor
		 */
		init : function(options) {

			var o = $.extend($.fn.BBCodeEditor.defaults, options);

			preloadLang(["bb.img_prompt", "bb.link_prompt", "bb.link_prompt_title", "bb.img", "bb.link", "bb.bold", "bb.italic", "bb.underlined", "bb.code", "bb.quote"]);

			return this.each(function() {
				if ($(this).prev(".bbcode_actions").length == 0 && $(this).get(0).tagName.toLowerCase() == "textarea") {
					$(this).addClass("bbcoded_editor");
					$(this).before('<div class="bbcode_actions"></div>');

					var actions = $(this).prev(".bbcode_actions");

					actions.css("width", $(this).outerWidth());
					var input = $(this).get(0);

					// create UI

					if (o.showBaseStyle) {
						actions.append('<div class="itemset"><div  title="' + lang("bb.bold") + '" class="item strong">B</div><div title="' + lang("bb.italic") + '" class="item italic">I</div><div title="' + lang("bb.underlined") + '" class="item underlined">U</div></div>');

						actions.find(".strong").click(function() {
							insert(input, "[b]", "[/b]");
						});
						actions.find(".italic").click(function() {
							insert(input, "[i]", "[/i]");
						});
						actions.find(".underlined").click(function() {
							insert(input, "[u]", "[/u]");
						});
					}

					if (o.showAlign) {
						actions.append('<div class="itemset"><div class="item img align-left"><span></span></div><div class="item img align-center"><span></span></div><div class="item img align-right"><span></span></div><div class="item img align-block"><span></span></div></div>');

						actions.find(".align-left").click(function() {
							insert(input, "[left]", "[/left]");
						});
						actions.find(".align-right").click(function() {
							insert(input, "[right]", "[/right]");
						});
						actions.find(".align-center").click(function() {
							insert(input, "[center]", "[/center]");
						});
						actions.find(".align-block").click(function() {
							insert(input, "[justify]", "[/justify]");
						});

					}

					if (o.showMedia) {
						// URL and Image
						actions.append('<div class="itemset"><div class="item img link" title="' + lang("bb.link") + '"><span></span></div><div title="' + lang("bb.img") + '" class="item img image"><span></span></div></div>');

						actions.find(".image").click(function() {
							if ( url = prompt(lang("bb.img_prompt"), "http://")) {
								insert(input, "[img]" + url + "[/img]", "");
							}
						});

						actions.find(".link").click(function() {
							if ( url = prompt(lang("bb.link_prompt"), "http://")) {
								if ( title = prompt(lang("bb.link_prompt_title"), url)) {
									if (title == url) {
										insert(input, "[url]" + url + "[/url]", "");
									} else {
										insert(input, "[url=" + url + "]" + title + "[/url]", "");
									}
								} else {
									insert(input, "[url]" + url + "[/url]", "");
								}
							}
						});
					}

					if (o.showCode) {
						// Code
						actions.append('<div class="itemset codeSet"><div title="' + lang("bb.code") + '" class="item img code">&lt;\\&gt;</div></div>');
						actions.find(".code").click(function() {
							insert(input, "[code]", "[/code]");
						});
					}

					if (o.showQuote) {
						actions.append('<div class="itemset quoteSet"><div title="' + lang("bb.quote") + '" class="item img quote"><span></span></div></div>');
						actions.find(".quote").click(function() {
							insert(input, "[quote]", "[/quote]");
						});

					}

					for (i in $.fn.BBCodeEditor.extensions) {
						if ( typeof $.fn.BBCodeEditor.extensions[i] == "function") {
							return $.fn.BBCodeEditor.extensions[i].apply(this, [actions, input]);
						}
					}

				}
			});
		},

		destroy : function() {
			return this.each(function() {
				$(this).prev(".bbcode_actions").remove();
				$(this).removeClass("bbcoded_editor");
			});
		}
	};

	$.fn.extend({
		BBCodeEditor : function(method) {
			if (methods[method]) {
				return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if ( typeof method === 'object' || !method) {
				return methods.init.apply(this, arguments);
			} else {
				$.error('Method ' + method + ' does not exist on jQuery.BBCodeEditor');
			}
		}
	});

	$.fn.BBCodeEditor.defaults = {
		showBaseStyle : true,
		showAlign : true,
		showMedia : true,
		showCode : true,
		showQuote : true
	};

	$.fn.BBCodeEditor.extensions = {

	};
})(jQuery); 