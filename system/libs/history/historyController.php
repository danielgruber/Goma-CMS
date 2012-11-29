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
			$tabs->addTab(ClassInfo::getClassTitle($filter["dbobject"]), HistoryController::renderHistory($filter, $this->namespace), $filter["dbobject"]);
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
			
			return $view->customise(array("fields" => $fieldset))->renderWith("history/compare.html");
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
		$i = 0;
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
			
			if(preg_match('/^(\<p[^\>]*\>)(.*)\<\/p\>$/i', $text, $m)) {
				$html[$x] = $m[1];
				$text = $m2[2];
				$add = "</p>";
			}
			
			switch ($op) {
				case DIFF_INSERT :
					$html[$x] .= '<INS STYLE="background:#E6FFE6;" TITLE="i=' . $i . '">' . $text . '</INS>';
					break;
				case DIFF_DELETE :
					$html[$x] .= '<DEL STYLE="background:#FFE6E6;" TITLE="i=' . $i . '">' . $text . '</DEL>';
					break;
				case DIFF_EQUAL :
					$html[$x] .= '<SPAN TITLE="i=' . $i . '">' . $text . '</SPAN>';
					break;
			}
			
			if(isset($add)) {
				$html[$x] .= "</p>";
			}
			
			if ($op !== DIFF_DELETE) {
				$i += mb_strlen($data);
			}
		}
		return implode('',$html);
	}
}