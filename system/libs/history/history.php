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
	 * cache for history-data
	 *
	 *@name data
	 *@access private
	*/
	private $historyData;
	
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
			$dbObjectFilter = array();
			foreach((array) $filter["dbobject"] as $class) {
				$dbObjectFilter = array_merge($dbObjectFilter, array($class), ClassInfo::getChildren($class));
			}
			$filter["dbobject"] = array_intersect(ArrayLib::key_value($dbObjectFilter), self::supportHistoryView());
		} else {
			$filter["dbobject"] = self::supportHistoryView();
		}
		//$filter[] = "autorid != 0";
		
		if(!is_a($filter, "DataObjectSet")) {
			$data = DataObject::get("History", $filter);
		} else {
			$data = $filter;
		}
		
		$id = "history_" . md5(var_export($filter, true));
		
		return $data->customise(array("id" => $id))->renderWith("history/history.html");
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
			if(ClassInfo::getStatic($child, "history") && ClassInfo::hasInterface($child, "HistoryData")) {
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
		if($data = $this->historyData()) {
			$text = $data["text"];
			// generate user
			if($this->autor) {
				$user = '<a href="member/'.$this->autor->ID . URLEND.'" class="user">' . convert::Raw2text($this->autor->title) . '</a>';
			} else {
				$user = '<span style="font-style: italic;">System</span>';
			}
			return str_replace('$user', $user, $text);
		}
		
		return false;
	}
	
	/**
	 * returns the icon for a history-element
	 * makes $content in template available or $object->content
	 *
	 *@name getIcon
	 *@access public
	*/
	public function getIcon() {
		if($data = $this->historyData()) {
			return $data["icon"];
		}
		
		return false;
	}
	
	/**
	 * returns the retina-icon for a history-element
	 * makes $content in template available or $object->content
	 *
	 *@name getIcon
	 *@access public
	*/
	public function getRetinaIcon() {
		if($data = $this->historyData()) {
			$icon = $data["icon"];
			$retinaPath = substr($icon, 0, strrpos($icon, ".")) . "@2x" . substr($icon, strrpos($icon, "."));
			if(file_exists($retinaPath))
				return $retinaPath;
			
			return $icon;
		}
		
		return false;
	}
	
	/**
	 * gets history-data
	 *
	 *@name historyData
	*/
	public function historyData() {
		if(isset($this->historyData)) {
			return $this->historyData;
		}
		
		if(ClassInfo::exists($this->dbobject)) {
			if(Object::method_exists($this->dbobject, "generateHistoryData")) {
				$data = call_user_func_array(array($this->dbobject, "generateHistoryData"), array($this));
				if(isset($data["text"], $data["icon"])) {
					$this->historyData = $data;
				} else {
					throwError(6, "Invalid Result", "Invalid Result from ".$this->dbobject."::generateHistoryData: icon & text required!");
				}
				return $data;
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
		if($this->fieldGet("oldversion") && ClassInfo::exists($this->dbobject)) {
			$temp = new $this->dbobject();
			$versioned = $temp->versioned;
			$temp = null;
			
			if($versioned) {
				return DataObject::get($this->dbobject, array("versionid" => $this->fieldGet("oldversion")));
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
			return DataObject::get_by_id($this->dbobject, $this->fieldGet("record"));
		}
		
		return false;
	}
}

interface HistoryData {
	/**
	 * returns text what to show about the event
	 *
	 *@name generateHistoryData
	 *@access public
	 *@return array("icon" => ..., "text" => ...)
	*/
	public static function generateHistoryData($record);
}

interface HistoryView {}