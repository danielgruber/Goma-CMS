<?php defined("IN_GOMA") OR die();

/**
 * represents CKEditor.
 *
 * @package		Goma\libs\WYSIWYG
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0.1
*/

class GomaCKEditor extends GomaEditor {
	/**
	 * supports HTML and BBCode.
	*/
	static $types = array(
		"html"
	);
	
	/**
	 * representative title of this class.
	*/
	static $cname = "CKEditor";
	
	/**
	 * javascript-config for HTML-Code.
	*/
	static $htmlConfig = '
			toolbarGroups: [
				{ name: "clipboard", groups: [ "clipboard", "undo" ] },
				{ name: "editing", groups: [ "find", "selection", "spellchecker", "editing" ] },
				{ name: "links", groups: [ "links" ] },
				{ name: "insert", groups: [ "insert" ] },
				{ name: "forms", groups: [ "forms" ] },
				{ name: "tools", groups: [ "tools" ] },
				{ name: "document", groups: [ "mode", "document", "doctools" ] },
				{ name: "others", groups: [ "others" ] },
				{ name: "about", groups: [ "about" ] },
				"/",
				{ name: "basicstyles", groups: [ "basicstyles", "cleanup" ] },
				{ name: "colors", groups: [ "colors" ] },
				{ name: "paragraph", groups: [ "list", "indent", "blocks", "align", "bidi", "paragraph" ] },
				{ name: "styles", groups: [ "styles" ] }
			],
    		language: "$lang",
    		baseHref: "$baseUri",
    		contentsCss: "$css",
    		uploadUrl: "$uploadpath",
    		imageUploadUrl: "$imageuploadpath",
    		filebrowserUploadUrl: "$uploadpath",
    		filebrowserImageUploadUrl : "$imageuploadpath",
    		width: "$width",
    		resize_dir: "vertical",
    		autoGrow_maxHeight: $(document).height() - 300';
   
    /**
     * extra javascript-code for html.
    */
    static $htmlJS = '
		CKEDITOR.config.extraPlugins = "uploadimage";
		CKEDITOR.config.removeButtons = "Font";
		CKEDITOR.config.justifyClasses = [ "AlignLeft", "AlignCenter", "AlignRight", "AlignJustify" ];

		// Set the most common block elements.
		CKEDITOR.config.format_tags = "p;h1;h2;h3;pre";';

	/**
	 * this method is called when a new Editor is generated. it should generate the text-field and JavaScript to
	 * generate the editor.
	 *
	 * @param    string $name the name as which the data should posted to the server
	 *
	 * @param    string $type type for which the editor should be generated
	 * @param    string $text the text for the editor
	 * @param    array $params list of some params like width css baseUri lang
	 * @return 	 HTMLNode
	 */
	public function generateEditor($name, $type, $text, $params = array()) {
		$id = $this->classname . "_" . $name;

		
		return new HTMLNode("textarea", array(
			"name"	=> $name,
			"id"	=> $id,
			"style"	=> "width: 100%;height: 400px;"
		), convert::raw2text($text));
	}

	/**
	 * @param FormFieldRenderData $info
	 * @return void
	 */
	public function addEditorInfo($info)
	{
		$info->addCSSFile("ckeditor_goma.css");
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $text
	 * @param array $params
	 * @return string
	 */
	public function addEditorJS($name, $type, $text, $params = array())
	{
		$id = $this->classname . "_" . $name;

		$accessToken = CKEditorUploadsController::getUploadToken();
		$params["uploadpath"] = BASE_URI . BASE_SCRIPT.'/system/ck_uploader/?accessToken=' . $accessToken;
		$params["imageuploadpath"] = BASE_URI . BASE_SCRIPT.'/system/ck_imageuploader/?accessToken='.$accessToken;

		$config = self::$htmlConfig;
		$params = ArrayLib::map_key("strtolower", $params);
		if(preg_match_all('/\$([a-zA-Z0-9_]+)/i', $config, $matches)) {
			foreach($matches[1] as $k => $param) {
				if(isset($params[strtolower($param)])) {
					$config = str_replace($matches[0][$k], $params[strtolower($param)], $config);
				} else {
					$config = str_replace($matches[0][$k], "", $config);
				}
			}
		}
		$pageLinksJS = ClassInfo::exists("pages") ? "$.getScript(\"system/libs/ckeditor_goma/pagelinks.js\");" : "";

		return '
window.CKEDITOR_BASEPATH = "'.BASE_URI.'system/libs/thirdparty/ckeditor4_5/";
$.getScript("system/libs/thirdparty/ckeditor4_5/ckeditor.js").done(function(){
	'.$pageLinksJS.'
	$(function(){
		CKEDITOR.basePath = "'.BASE_URI.'system/libs/thirdparty/ckeditor4_5/";
		// apple bug with contenteditable of iOS 4 and lower
		// firefox 3 and above are supported, otherwise dont load up
		if((!isIDevice() || isiOS5()) && (getFirefoxVersion() > 2 || getFirefoxVersion() == -1)) {
			setTimeout(function(){
				if(CKEDITOR.instances.'.$id.' != null) CKEDITOR.remove(CKEDITOR.instances.'.$id.');
				'.self::$htmlJS.'
				CKEDITOR.replace("'.$id.'", {
					'.$config.'
				});

			}, 100);


			$("#'.$id.'").parents("form").on("beforesubmit",function(){
				try {
					if(CKEDITOR.instances.'.$id.' !== undefined) {
						$("#'.$id.'").val(CKEDITOR.instances.'.$id.'.getData());
					}
				} catch(e) {
					alert(e);
				}
			});
			$("#'.$id.'").change(function(){
				CKEDITOR.instances.'.$id.'.setData($("#'.$id.'").val());
			});
			$(".editor_toggle").css("display", "block");
		}
	});
});
window.toggleEditor_'.$name.' = function() {
	if(CKEDITOR.instances["'.$id.'"] != null) {
		CKEDITOR.instances["'.$id.'"].destroy();
	} else {
		CKEDITOR.replace("'.$id.'", {
    		'.$config.'
		});
	}
};
field.getValue = function() {
	if(CKEDITOR.instances.'.$id.' === undefined) {
		return $("#'.$id.'").val();
	}
	return CKEDITOR.instances.'.$id.'.getData();
};
field.setValue = function(val) {
	$("#'.$id.'").val(val);
	$("#'.$id.'").change();
	return this;
};';
	}
}
