<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 18.11.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class History extends DataObject {
	/**
	 * db-fields
	*/
	public $db_fields = array(
		"dbobject"		=> "varchar(100)",
		"record"		=> "int(10)",
		"oldversion"	=> "int(10)",
		"newversion"	=> "int(10)",
		"action"		=> "varchar(100)"
	);
	
	/**
	 * disable history for this dataobject, because we would have an endless loop
	 *
	 *@name history
	 *@access public
	*/
	public static $history = false;
	
	/**
	 * small cache for classes supporting HistoryView
	 *
	 *@name supportHistoryView
	 *@access private
	*/
	private static $supportHistoryView;
	
	/**
	 * you can push history-events by yourself into this
	 *
	 *@name push
	 *@access public
	*/
	public static function push($class, $oldrecord, $newrecord, $recordid, $action) {
		if(is_object($class))
			$class = $class->class;
		
		if(is_object($oldrecord))
			$oldrecord = $oldrecord->versionid;
		
		if(is_object($newrecord))
			$newrecord = $newrecord->versionid;
		
		if(!ClassInfo::getStatic($class, "history")) {
			return false;
		}
		
		$record = new History(array(
			"dbobject" 		=> $class,
			"oldversion"	=> $oldrecord,
			"newversion"	=> $newrecord,
			"record"		=> $recordid,
			"action"		=> $action
		));
		return $record->write(true, true);
	}
	
	/**
	 * renders the history for given filter
	 *
	 *@name renderHistory
	 *@access public
	*/
	public static function renderHistory($filter) {
		if(isset($filter["dbobject"])) {
			$filter["dbobject"] = array_intersect($filter["class_name"], self::supportHistoryView());
		} else {
			$filter["dbobject"] = self::supportHistoryView();
		}
		$filter[] = "autorid != 0";
		
		if(!is_a($filter, "DataObjectSet")) {
			$data = DataObject::get("History", $filter);
		} else {
			$data = $filter;
		}
		
		return $data->renderWith("history/history.html");
	}
	
	/**
	 * returns a list of classes supporting HistoryView
	 *
	 *@name supportHistoryView
	 *@access public
	*/
	public static function supportHistoryView() {
		if(isset(self::$supportHistoryView))
			return self::$supportHistoryView;
			
		self::$supportHistoryView = array();
		foreach(ClassInfo::getChildren("DataObject") as $child) {
			if(ClassInfo::getStatic($child, "history") && ClassInfo::hasInterface($child, "HistoryView")) {
				self::$supportHistoryView[] = $child;
			}
		}
		
		return self::$supportHistoryView;
	}
	
	/**
	 * returns the text for a history-element
	 * makes $content in template available or $object->content
	 *
	 *@name getContent
	 *@access public
	*/
	public function getContent() {
		if(ClassInfo::exists($this->dbobject)) {
			if(Object::method_exists($this->dbobject, "generateHistoryText")) {
				return call_user_func_array(array($this->dbobject, "generateHistoryText"), array($this));
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * returns the new version
	 * it's a object of $this->dbobject
	 * returns false if not available, because of versions disabled
 	 *
	 *@name newversion
	 *@access public
	*/
	public function newversion() {
		if($this->fieldGet("newversion") && ClassInfo::exists($this->dbobject)) {
			$temp = new $this->dbobject();
			$versioned = $temp->versioned;
			$temp = null;
			
			if($versioned) {
				return DataObject::get($this->dbobject, array("versionid" => $this->fieldGet("newversion")));
			}
		}
		
		return false;
	}
	
	/**
	 * returns the old version
	 * it's a object of $this->dbobject
	 * returns false if not available, because of versions disabled
 	 *
	 *@name oldversion
	 *@access public
	*/
	public function oldversion() {
		if($this->oldversion && ClassInfo::exists($this->dbobject)) {
			$temp = new $this->dbobject();
			$versioned = $temp->versioned;
			$temp = null;
			
			if($versioned) {
				return DataObject::get($this->dbobject, array("versionid" => $this->oldversion));
			}
		}
		
		return false;
	}
	
	/**
	 * returns the record
	 *
	 *@name record
	 *@access public
	*/
	public function record() {
		if(ClassInfo::exists($this->dbobject)) {
			return DataObject::get_by_id($this->dbobject, $this->record);
		}
		
		return false;
	}
}

interface HistoryView {
	/**
	 * returns text what to show about the event
	 *
	 *@name generateHistoryText
	 *@access public
	*/
	public static function generateHistoryText($record);
}