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
			toolbar : [{ name: "document", items : [ "Source"] },
				{ name: "links", items : [ "Link","Unlink","Anchor" ] },
				{ name: "clipboard", items : [ "Cut","PasteText","PasteFromWord","-","Undo","Redo" ] },
				{ name: "basicstyles", items : [ "Bold","Italic","Underline","-","RemoveFormat" ] },
				{ name: "justify", items: ["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"] },
				{ name: "tools", items : [ "Maximize" ] },
				"/",
				{ name: "insert", items : [ "Image","Table"] },
				{ name: "styles", items : [ "Styles","Format" ] },
				{ name: "colors", items : [ "TextColor","BGColor" ] },
				{ name: "editing", items : [ "BidiLtr","BidiRtl" ] },
				{ name: "Scayt", items: ["Scayt"]},
				{ name: "paragraph", items : [ "NumberedList","BulletedList","-","Outdent","Indent"] }],
    		language: "$lang",
    		baseHref: "$baseUri",
    		contentsCss: "$css",
    		filebrowserUploadUrl: "$uploadpath",
    		filebrowserImageUploadUrl : "$imageuploadpath",
    		width: "$width",
    		resize_dir: "vertical",
    		autoGrow_maxHeight: $(document).height() - 300';
   
    /**
     * extra javascript-code for html.
    */
    static $htmlJS = '
		CKEDITOR.config.extraPlugins = "imagepaste";
		CKEDITOR.config.autoGrow_onStartup = true;';
	
	/**
	 * this method is called when a new Editor is generated. it should generate the text-field and JavaScript to generate the editor.
	 *
	 * @param 	string $name the name as which the data should posted to the server
	 * @param 	string $type type for which the editor should be generated
	 * @param 	string $text the text for the editor
	 * @param	array $params list of some params like width css baseUri lang
	*/
	public function generateEditor($name, $type, $text, $params = array()) {
		$id = $this->classname . "_" . $name;
		$width = isset($params["width"]) ? $params["width"] : "";
		
		Resources::addData('var CKEDITOR_BASEPATH = "'.BASE_URI.'system/libs/thirdparty/ckeditor4_4/";');
		Resources::add("system/libs/thirdparty/ckeditor4_4/ckeditor.js", "js");
		Resources::add("system/libs/ajax/ajaxupload.js", "js");
		if(ClassInfo::exists("pages")) Resources::add("system/libs/ckeditor_goma/pagelinks.js", "js");
		Resources::add("ckeditor_goma.css", "css");
		
		if($type == "html") {
			
			$config = self::$htmlConfig;
			$params = ArrayLib::map_key($params, "strtolower");
			if(preg_match_all('/\$([a-zA-Z0-9_]+)/i', $config, $matches)) {
				foreach($matches[1] as $k => $param) {
					if(isset($params[strtolower($param)]))
						$config = str_replace($matches[0][$k], $params[strtolower($param)], $config);
				}
			}
			
			
			Resources::addJS('$(function(){
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
				$("#'.$id.'").val(CKEDITOR.instances.'.$id.'.getData());
			} catch(e) {
			
			}
		});
		$("#'.$id.'").change(function(){
			
			CKEDITOR.instances.'.$id.'.setData($("#'.$id.'").val());
		});
		$(".editor_toggle").css("display", "block");
	}
});
window.toggleEditor_'.$name.' = function() {
	if(CKEDITOR.instances["'.$id.'"] != null) {
		CKEDITOR.instances["'.$id.'"].destroy();
	} else {
		CKEDITOR.replace("'.$id.'", {
    		'.$config.'
		});
	}
		
};');
		} else if($type == "bbcode") {
			
		}
		
		return new HTMLNode("textarea", array(
			"name"	=> $name,
			"id"	=> $id,
			"style"	=> "width: 100%;height: 400px;"
		), convert::raw2text($text));
	}
}