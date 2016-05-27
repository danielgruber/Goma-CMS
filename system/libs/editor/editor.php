<?php defined("IN_GOMA") OR die();

/**
 * base-class for editors.
 *
 *
 * @package		Goma\libs\WYSIWYG
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
*/
abstract class GomaEditor extends gObject {
	/**
	 * defines which types of code it supports.
	 *
	 * there are some values: html, bbcode, md
	*/
	static $types = array();
	
	/**
	 * an array of type => class for which type a specific subclass of GomaEditor is the editor.
	*/
	static $default = array(
		"html"		=> "GomaCKEditor",
		"bbcode"	=> "GomaCKEditor"
	);
	
	/**
	 * here you can hook in to transform the code posted to code which can be saved to the database.
	 *
	 * @param 	string $code code
	*/
	public static function toDB(&$code) {
		
	}

	/**
	 * generates a instance of GomaEditor, which can be used for given type.
	 *
	 * @param string $type
	 * @return GomaEditor
	 */
	public static function get($type) {
		$type = strtolower($type);
		self::$types = ArrayLib::map_key("strtolower", self::$types);
		if(isset(self::$default[$type]) && ClassInfo::exists(self::$default[$type]))
			return gObject::instance(self::$default[$type]);
		
		foreach(ClassInfo::getChildren("GomaEditor") as $class) {
			$types = array_map((array)StaticsManager::getStatic($class, "types"), "strtolower");
			if(in_array($type, $types)) {
				return gObject::instance($class);
			}
		}
		
		return false;
	}
	
	
	/**
	 * this method is called when a new Editor is generated. it should generate the text-field and JavaScript to generate the editor.
	 *
	 * @param 	string $name the name as which the data should posted to the server
	 * @param 	string $type type for which the editor should be generated
	 * @param 	string $text the text for the editor
	 * @param 	array $params
	*/
	abstract public function generateEditor($name, $type, $text, $params);

	/**
	 * @param FormFieldRenderData $info
	 * @return void
	 */
	abstract public function addEditorInfo($info);

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $text
	 * @param array $params
	 * @return string
	 */
	abstract public function addEditorJS($name, $type, $text, $params = array());
}
 