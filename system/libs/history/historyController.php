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
		'$c/$i'								=> "index",
		'compareVersion/$class!/$id!/$nid!'	=> "compareVersion",
		'restoreVersion/$class!/$id!'		=> "restoreVersion"
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
		if(isset($filter["dbobject"])) {
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
}