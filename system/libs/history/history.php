<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 06.12.2012
  * $Version 1.0.1
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
		"action"		=> "varchar(100)",
		"changecount"	=> "int(10)",
		"changed"		=> "text"
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
	 *@param class-name of the data to insert
	 *@param old version-id of data
	 *@param new version-id of data
	 *@param id of the record to which the versions belong
	 *@param name of the action which happended, for example: "insert", "delete", "update"
	 *@param array - changed data
	*/
	public static function push($class, $oldrecord, $newrecord, $recordid, $action, $changed = null) {
		
		// if it's an object, get the class-name from the object
		if(is_object($class))
			$class = $class->class;
		
		// if we've got the version as object given, get versionid from object
		if(is_object($oldrecord))
			$oldrecord = $oldrecord->versionid;
		
		// if we've got the version as object given, get versionid from object
		if(is_object($newrecord))
			$newrecord = $newrecord->versionid;
		
		// check if history is enabled on that dataobject
		if(!ClassInfo::getStatic($class, "history")) {
			return false;
		}
		
		if(isset($changed)) {
			$cc = count($changed);
			$c = serialize($changed);
		} else {
			$c = 0;
			$cc = null;
		}
		
		// create the history-record
		$record = new History(array(
			"dbobject" 		=> $class,
			"oldversion"	=> $oldrecord,
			"newversion"	=> $newrecord,
			"record"		=> $recordid,
			"action"		=> $action,
			"changed"		=> $c,
			"cc"			=> $cc
		));
		
		// insert data, we force to insert and to write, so override permission-system ;)
		return $record->write(true, true, 2, true, false);
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
			if(ClassInfo::getStatic($child, "history") && ClassInfo::hasInterface($child, "HistoryData") && call_user_func_array(array($child, "canViewHistory"), array($child))) {
				self::$supportHistoryView[] = $child;
			}
		}
		
		return self::$supportHistoryView;
	}
	
	/**
	 * if we can this history-event
	 *
	 *@name canSeeEvent
	*/
	public function canSeeEvent() {
		if(call_user_func_array(array($this->dbobject, "canViewHistory"), array($this)) && $this->historyData()) {
			return true;
		}
		return false;
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
	 * gets the info if all versions are available for this history-object
	 *
	 *@name getVersioned
	*/
	public function getVersioned() {
		$data = $this->historyData();
		if(isset($data["versioned"]) && $data["versioned"] && isset($data["editurl"])) {
			$temp = new $this->dbobject();
			if(!$temp->versioned)
				return false;
			
			if(DataObject::count($this->dbobject, array("versionid" => array($this->fieldGet("newversion"), $this->fieldGet("oldversion")))) == 2) {
				return true;
			}
			
			return false;
		} else {
			return false;
		}
	}
	
	/**
	 * gets the edit-url
	 *
	 *@name getEditURL
	*/
	public function getEditURL() {
		$data = $this->historyData();
		return isset($data["editurl"]) ? $data["editurl"] : null;
	}
	
	/**
	 * gets the info if all versions are available for this history-object and comparing
	 *
	 *@name getCompared
	*/
	public function getCompared() {
		$data = $this->historyData();
		$temp = new $this->dbobject();
		if(!isset($data["compared"]) || $data["compared"]) {
			return ($this->getVersioned() && $temp->getVersionedFields());
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
				} else if(is_array($data)) {
					throwError(6, "Invalid Result", "Invalid Result from ".$this->dbobject."::generateHistoryData: icon & text required!");
				} else {
					return false;
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
	 * returns the id of the new version
	 *
	 *@name newversionid
	 *@access public
	*/
	public function newversionid() {
		return $this->fieldGet("newversion");
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
	 * returns the id of the old version
	 *
	 *@name oldversionid
	 *@access public
	*/
	public function oldversionid() {
		return $this->fieldGet("oldversion");
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