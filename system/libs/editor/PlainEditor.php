<?php defined("IN_GOMA") OR die();

/**
 * a simple class representing a simple textarea as edtitor.
 *
 *
 * @package		Goma\libs\Editors
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
*/
class GomaPlainEditor extends GomaEditor {
	/**
	 * supports HTML and BBCode.
	*/
	static $types = array(
		"html", "bbcode", "md"
	);
	
	/**
	 * representative title of this class.
	*/
	static $cname = '{$_lang_NO_EDITOR}';

	/**
	 * this method is called when a new Editor is generated. it should generate the text-field and JavaScript to generate the editor.
	 *
	 * @param    string $name the name as which the data should posted to the server
	 * @param    string $type type for which the editor should be generated
	 * @param    string $text the text for the editor
	 * @param    array $params list of some params like width css baseUri lang
	 * @return HTMLNode
	 */
	public function generateEditor($name, $type, $text, $params = array()) {
		return new HTMLNode("textarea", array(
			"name"	=> $name,
			"id"	=> "plaineditor_" . $name,
			"style"	=> "width: 100%;height: 400px;"
		), convert::raw2text($text));
	}

	/**
	 * @param FormFieldRenderData $info
	 * @return void
	 */
	public function addEditorInfo($info)
	{
		// TODO: Implement addEditorInfo() method.
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
		return "";
	}
}