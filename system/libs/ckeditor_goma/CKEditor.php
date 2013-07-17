<?php defined("IN_GOMA") OR die();

/**
 * represents CKEditor.
 *
 * @package		Goma\libs\WYSIWYG
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
*/

class GomaCKEditor extends GomaEditor {
	/**
	 * supports HTML and BBCode.
	*/
	static $types = array(
		"html", "bbcode"
	);
	
	/**
	 * javascript-config for HTML-Code.
	*/
	static $htmlConfig = '
				toolbar : "Goma",
        		language: "$lang",
        		baseHref: "$baseUri",
        		contentsCss: "$css",
        		filebrowserUploadUrl: "$uploadpath",
        		filebrowserImageUploadUrl : "$ckeditorimageuploadpath",
        		width: "$width",
        		resize_dir: "vertical",
        		autoGrow_maxHeight: $(document).height() - 300';
	
	/**
	 * this method is called when a new Editor is generated. it should generate the text-field and JavaScript to generate the editor.
	 *
	 * @param 	string $name the name as which the data should posted to the server
	 * @param 	string $type type for which the editor should be generated
	 * @param 	string $text the text for the editor
	*/
	public static function generateEditor($name, $type, $text) {
		if($type == "html") {
			
		} else if($type == "bbcode") {
			
		}
		
		return new HTMLNode("textarea", array(
			"name"	=> $name,
			"css"	=> "width: 100%; height: 400px;"
		), convert::raw2text($text));
	}
}