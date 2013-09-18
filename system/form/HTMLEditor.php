<?php defined("IN_GOMA") OR die();

/**
 * generates the Editor for HTML-Code.
 *
 * @package		Goma\libs\WYSIWYG
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.3.1
*/
class HTMLEditor extends Textarea
{
	/**
	 * generates the field
	 *
	 *@name field
	 *@access public
	*/
	public function field()
	{
			$this->callExtending("beforeField");
			
			$this->setValue();
			
			$this->container->append(new HTMLNode("label", array(
				'for'	=> $this->ID()
			), $this->title));
			
			$this->container->append(array(
				new HTMLNode("a", array(
					'href'		=> 'javascript:;',
					'onclick'	=> 'toggleEditor_'.$this->name.'()',
					"style"		=> "display: none;",
					"class"		=> "editor_toggle",
					"id"		=> "editor_toggle_" . $this->name
				), lang("EDITOR_TOGGLE", "Toggle Editor"))
			));
			
			$accessToken = randomString(20);
-			$_SESSION["uploadTokens"][$accessToken] = true;
			
			$params = array("width" => $this->width, "baseUri" => BASE_URI, "lang" => Core::$lang, "css" => $this->buildEditorCSS(), "uploadpath" => BASE_URI . BASE_SCRIPT.'/system/ck_uploader/?accessToken=' . $accessToken, "imageuploadpath" => BASE_URI . BASE_SCRIPT.'/system/ck_imageuploader/?accessToken='.$accessToken);
			$this->container->append(GomaEditor::get("html")->generateEditor($this->name, "html", $this->value, $params));
			
			$this->callExtending("afterRender");
			
			return $this->container;
	}
	
	/**
	 * builds editor.css
	 *
	 *@name buildEditorCSS
	*/
	public function buildEditorCSS() {
		$cache = CACHE_DIRECTORY . "/htmleditor_compare_" . Core::GetTheme() . ".css";
		if(/*(!file_exists($cache) || filemtime($cache) < TIME + 300) && */file_exists("tpl/" . Core::getTheme() . "/editor.css")) {
			$css = self::importCSS("system/templates/css/default.css") . "\n" . self::importCSS("tpl/" . Core::getTheme() . "/editor.css");
			
			// parse CSS
			//$css = preg_replace_callback('/([\.a-zA-Z0-9_\-,#\>\s\:\[\]\=]+)\s*{/Usi', array("historyController", "interpretCSS"), $css);
			FileSystem::write($cache, $css);
			
			return BASE_URI . $cache;
		} else {
			return false;
		}
	}	
	
	/**
	 * gets a consolidated CSS-File, where imports are merged with original file
	 *
	 *@name importCSS
	 *@param string - file
	*/
	public static function importCSS($file) {
		if(file_exists($file)) {
			$css = file_get_contents($file);
			// import imports
			preg_match_all('/\@import\s*url\(("|\')([^"\']+)("|\')\)\;/Usi', $css, $m);
			foreach($m[2] as $key => $_file) {
				$css = str_replace($m[0][$key], self::importCSS(dirname($file) . "/" . $_file), $css);
			}
			
			return $css;
		}
		
		return "";
	}
}
