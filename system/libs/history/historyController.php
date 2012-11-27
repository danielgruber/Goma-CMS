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
					
					$diff = $this->htmlDiff($oldversiondata, $newversiondata);
					$fieldset->push(array("title" => $title, "content" => trim($diff)));
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
	
	/*
    Paul's Simple Diff Algorithm v 0.1
    (C) Paul Butler 2007 <http://www.paulbutler.org/>
    May be used and distributed under the zlib/libpng license.

    This code is intended for learning purposes; it was written with short
    code taking priority over performance. It could be used in a practical
    application, but there are a few ways it could be optimized.

    Given two arrays, the function diff will return an array of the changes.
    I won't describe the format of the array, but it will be obvious
    if you use print_r() on the result of a diff on some test data.

    htmlDiff is a wrapper for the diff command, it takes two strings and
    returns the differences in HTML. The tags used are <ins> and <del>,
    which can easily be styled with CSS.  
	*/
	
	function diff($old, $new){
		$maxlen = 0;
	    foreach($old as $oindex => $ovalue){
	        $nkeys = array_keys($new, $ovalue);
	        foreach($nkeys as $nindex){
	            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
	                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
	            if($matrix[$oindex][$nindex] > $maxlen){
	                $maxlen = $matrix[$oindex][$nindex];
	                $omax = $oindex + 1 - $maxlen;
	                $nmax = $nindex + 1 - $maxlen;
	            }
	        }   
	    }
	    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
	    return array_merge(
	        $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
	        array_slice($new, $nmax, $maxlen),
	        $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
	}
	
	function htmlDiff($old, $new){
	    $diff = $this->diff(explode(' ', $old), explode(' ', $new));
	    $ret = "";
	    foreach($diff as $k){
	        if(is_array($k))
	            $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
	                (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
	        else $ret .= $k . ' ';
	    }
	    return $ret;
	}
}