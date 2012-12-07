<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 26.11.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HistoryController extends Controller {
	/**
	 * url-handlers
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		'compareVersion/$class!/$id!/$nid!'	=> "compareVersion",
		'restoreVersion/$class!/$id!'		=> "restoreVersion",
		'$c/$i'								=> "index"
	);
	
	/**
	 * allowed actions
	 *
	 *@name allowed_actions
	*/
	public $allowed_actions = array(
		"compareVersion"	=> "->canCompareVersion",
		"restoreVersion"	=> "->canRestoreVersion"
	);
	
	/**
	 * renders the history for given filter
	 *
	 *@name renderHistory
	 *@access public
	*/
	public static function renderHistory($filter, $namespace = null) {
		if(isset($filter["dbobject"])) {
			$dbObjectFilter = array();
			foreach((array) $filter["dbobject"] as $class) {
				$dbObjectFilter = array_merge($dbObjectFilter, array($class), ClassInfo::getChildren($class));
			}
			$filter["dbobject"] = array_intersect(ArrayLib::key_value($dbObjectFilter), History::supportHistoryView());
			if(count($filter["dbobject"]) == 0) {
				return false;
			}
		} else {
			$filter["dbobject"] = History::supportHistoryView();
		}
		//$filter[] = "autorid != 0";
		
		if(!is_a($filter, "DataObjectSet")) {
			$data = DataObject::get("History", $filter);
		} else {
			$data = $filter;
		}
		
		$id = "history_" . md5(var_export($filter, true));
		
		return $data->customise(array("id" => $id, "namespace" => $namespace))->renderWith("history/history.html");
	}
	
	/**
	 * name of this controller
	 *
	 *@name PageTitle
	*/
	public function PageTitle() {
		return lang("history");
	}
	
	/**
	 * index-method
	 *
	 *@name index
	*/
	public function index() {
		$filter = array();
		$class = $this->getParam("c");
		if(isset($class))
			$filter["dbobject"] = $class;
		
		$item = $this->getParam("i");
		if(isset($item))
			$filter["recordid"] = $item;
		
		
		// render the tabset
		$tabs = new Tabs("history");
		if(isset($filter["dbobject"]) && ClassInfo::exists($filter["dbobject"])) {
			$content = HistoryController::renderHistory($filter, $this->namespace);
			if($content) {
				$tabs->addTab(ClassInfo::getClassTitle($filter["dbobject"]), $content, $filter["dbobject"]);
			}
		}
		$tabs->addTab(lang("h_all"), HistoryController::renderHistory(array(), $this->namespace), "h_all");
		$output = $tabs->render();
		
		if(Core::is_ajax()) {
			HTTPResponse::setBody($output);
			HTTPResponse::output();
			exit;
		} else {
			return $output;
		}
	}
	
	/**
	 * Permissions
	*/
	
	/**
	 * you can restore a version if you are author or publisher
	 *
	 *@name canRestoreVersion
	*/
	public function canRestoreVersion() {
		if(ClassInfo::exists($this->getParam("class"))) {
			if($data = DataObject::get_one($this->getParam("class"), array("versionid" => $this->getParam("id")))) {
				if($data->canWrite($data) || $data->canPublish($data)) {
					return true;
				}
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * you can compare a version if you are author or publisher
	 *
	 *@name canCompareVersion
	*/
	public function canCompareVersion() {
		if(ClassInfo::exists($this->getParam("class"))) {
			if($data = DataObject::get_one($this->getParam("class"), array("versionid" => $this->getParam("nid")))) {
				if($data->canWrite($data) || $data->canPublish($data)) {
					return true;
				}
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * restores a version
	 *
	 *@name restoreVersion
	*/
	public function restoreVersion() {
		$version = DataObject::get_one($this->getParam("class"), array("versionid" => $this->getParam("id")));
		if($version->canWrite($version) || $version->canPublish($version)) {
			if($this->confirm(lang("restore_confirm"))) {
				if($version->canWrite($version)) {
					$version->write(false, true, 1);
				} else {
					$version->write(false, true, 2);
				}
				$this->redirectBack();
			}
		} else {
			return lang("less_rights");
		}
	}
	
	/**
	 * compares two versions
	 *
	 *@name compareVersion
	 *@access public
	*/
	public function compareVersion() {
		$oldversion = DataObject::get_one($this->getParam("class"), array("versionid" => $this->getParam("id")));
		$newversion = DataObject::get_one($this->getParam("class"), array("versionid" => $this->getParam("nid")));
		
		// get all fields for compare-view
		$compareFields = $oldversion->getVersionedFields();
		if($compareFields) {
			$view = new ViewAccessableData();
			$fieldset = new DataSet();
			foreach($compareFields as $field => $title) {
				// get data
				if(isset($oldversion[$field]) && isset($newversion[$field])) {
					$oldversiondata = $this->getDataFromVersion($field, $oldversion);
					$newversiondata = $this->getDataFromVersion($field, $newversion);
					
					// first check if HTML or other format
					if(!preg_match('/(\<img|\<a|\<div|\<p|\<span)/i', $oldversiondata) && !preg_match('/(\<img|\<a|\<div|\<p|\<span)/i', $newversiondata)) {
						$oldversiondata = convert::raw2text($oldversiondata);
						$newversiondata = convert::raw2text($newversiondata);
					}
					
					$object = new diff_match_patch;
					$diff = $object->diff_compute($oldversiondata, $newversiondata, true);
					$object->diff_cleanupEfficiency($diff);
					$fieldset->push(array("title" => $title, "content" => trim($this->diffToHTML($diff))));
				}
			}
			
			return $view->customise(array("fields" => $fieldset, "css" => $this->buildEditorCSS()))->renderWith("history/compare.html");
		} else {
			throwError(6, "Implementation Error", "No fields for version-comparing for class ".$oldversion->class.". Please create method ".$oldversion->class."::getVersionedFields with array as return-value.");
		}
	}
	
	/**
	 * gets correct data from versions
	 *
	 *@name getDataFromVersion
	 *@param string - field
	 *@param object - version
	*/
	public function getDataFromVersion($field, $version) {
		if(strpos($field, ".")) {
			$tmpItem = clone $version;
			$fieldNameParts = explode(".", $field);
			
			for($idx = 0; $idx < sizeof($fieldNameParts); $idx++) {
				$methodName = $fieldNameParts[$idx];
				// Last mmethod call from $columnName return what that method is returning
				if($idx == sizeof($fieldNameParts) - 1) {
					return (string) $tmpItem;
				}
				// else get the object from this $methodName
				$tmpItem = $tmpItem->$methodName();
			}
			return null;
		}
		
		if(isset($version[$field])) {
			return $version[$field];
		}
		
		throwError(6, "Invalid-Data-Error", "$field doesn't exist on version of type ".$version->class." with id ".$version->versionid."");
	}
	
	/**
	 * converts diff to HTML
	 *
	 *@name diffToHTML
	*/
	public function diffToHTML($diffs) {
		$html = array ();
		$blockElements = "p|h1|h2|h3|h4|h5|h6|div|blockquote|noscript|form|fieldset|adress|li|ul";
		$i =0;
		for ($x = 0; $x < count($diffs); $x++) {
			$html[$x] = "";
			$add = "";
			$op = $diffs[$x][0]; // Operation (insert, delete, equal)
			$data = $diffs[$x][1]; // Text of change.
			/*$text = preg_replace(array (
				'/&/',
				'/</',
				'/>/',
				"/\n/"
			), array (
				'&amp;',
				'&lt;',
				'&gt;',
				'&para;<BR>'
			), $data);*/
			$text = trim($data);
			
			if(trim($text) == "") {
				continue;
			}
			
			if(preg_match('/^(\<('.$blockElements.')[^\>]*\>)(.*)\<\/\2\>$/si', $text, $m)) {
				$html[$x] = $m[1];
				$text = $m[3];
				$add = "</".$m[2].">";
			}
			
			switch ($op) {
				case DIFF_INSERT :
					$html[$x] .= '<ins>' . $text . '</ins>';
					break;
				case DIFF_DELETE :
					$html[$x] .= '<del>' . $text . '</del>';
					break;
				case DIFF_EQUAL :
					$html[$x] .= $text;
					break;
			}
			
			$html[$x] = preg_replace('/^\s*\<(ins|del)\>\s*\<\/('.$blockElements.')\>\s*\<('.$blockElements.')\>/Usi', "</$2><$3><$1>", $html[$x]);
			
			if(isset($add)) {
				$html[$x] .= $add;
			}
			
			if ($op !== DIFF_DELETE) {
				$i += mb_strlen($data);
			}
		}
		$output = implode('',$html);
		
		
		// run output fixes here
		
		// img-fixes
		preg_match_all('/\<img(.*)\s\/\>/Usi', $output, $matches);
		foreach($matches[0] as $tag) {
			if(strpos($tag, "<ins>") && strpos($tag, "<del>")) {
				$delTag = $tag;
				$delTag = str_replace('<del>', '', $delTag);
				$delTag = str_replace('</del>', '', $delTag);
				$delTag = preg_replace('/\<ins>(.*)\<\/ins\>/Usi', "", $delTag);
				
				$insTag = $tag;
				$insTag = str_replace('<ins>', '', $insTag);
				$insTag = str_replace('</ins>', '', $insTag);
				$insTag = preg_replace('/\<del>(.*)\<\/del\>/Usi', "", $insTag);
				
				$tag = "<del style=\"display: block;\">".$delTag."</del><ins style=\"display: block;\">".$insTag."</ins>";
				
			} else if(strpos($tag, "<ins>")) {
				$tag = str_replace('<ins>', '', $tag);
				$tag = str_replace('</ins>', '', $tag);
				$tag = "<ins>".$tag."</ins>";
			} else if(strpos($tag, "<del>")) {
				$tag = str_replace('<del>', '', $tag);
				$tag = str_replace('</del>', '', $tag);
				$tag = "<del>".$tag."</del>";
			}
			
			$output = str_replace($matches[0], $tag, $output);
		}
		
		// a-fixes
		preg_match_all('/\<(a)(.*)\>(.*)\<\/\1\>/Usi', $output, $matches);
		foreach($matches[0] as $tag) {
			if(strpos($tag, "<ins>") && strpos($tag, "<del>")) {
				$delTag = $tag;
				$delTag = str_replace('<del>', '', $delTag);
				$delTag = str_replace('</del>', '', $delTag);
				$delTag = preg_replace('/\<ins>(.*)\<\/ins\>/Usi', "", $delTag);
				
				$insTag = $tag;
				$insTag = str_replace('<ins>', '', $insTag);
				$insTag = str_replace('</ins>', '', $insTag);
				$insTag = preg_replace('/\<del>(.*)\<\/del\>/Usi', "", $insTag);
				
				$tag = "<del style=\"display: block;\">".$delTag."</del><ins style=\"display: block;\">".$insTag."</ins>";
				
			} else if(strpos($tag, "<ins>")) {
				$tag = str_replace('<ins>', '', $tag);
				$tag = str_replace('</ins>', '', $tag);
				$tag = "<ins>".$tag."</ins>";
			} else if(strpos($tag, "<del>")) {
				$tag = str_replace('<del>', '', $tag);
				$tag = str_replace('</del>', '', $tag);
				$tag = "<del>".$tag."</del>";
			}
			
			$output = str_replace($matches[0], $tag, $output);
		}
		
		// script-tags - we remove them
		$output = preg_replace('/\<script(.*)\>(.*)\<\/script\>/Usi', '', $output);
		
		return $output;
	}
	
	/**
	 * builds editor.css
	 *
	 *@name buildEditorCSS
	*/
	public function buildEditorCSS() {
		$cache = ROOT . CACHE_DIRECTORY . "/editor.compare." . Core::GetTheme() . ".css";
		if((!file_exists($cache) || filemtime($cache) < TIME + 300) && file_exists("tpl/" . Core::getTheme() . "/editor.css")) {
			$css = self::importCSS("tpl/" . Core::getTheme() . "/editor.css");
			
			// parse CSS
			$css = preg_replace_callback('/([\.a-zA-Z0-9_\-,#\>\s\:\[\]\=]+)\s*{/Usi', array("historyController", "interpretCSS"), $css);
			FileSystem::write($cache, $css);
			
			return $cache;
		} else {
			return false;
		}
	}
	
	/**
	 * interprets the CSS
	 *
	 *@name interpretCSS
	*/
	public static function interpretCSS($matches) {
		if(preg_match('/^(body|html)?,?\s*(html|body)?$/i', trim($matches[1]))) {
			return "\n.compareView .content {";
		} else {
			$exps = explode(",", trim($matches[1]));
			$out = "\n";
			foreach($exps as $exp) {
				$out .= ".compareView .content " . trim($exp) . ", ";
			}
			return $out . " { ";
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