<?php
defined("IN_GOMA") OR die();

StaticsManager::addSaveVar(History::ID, "storeHistoryForDays");

/**
 * Model for History.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 * @property 	string action
 * @property 	string dbobject
 * @property 	int writeType
 *
 * @version    1.0
 */

class History extends DataObject {

	const ID = "History";

	/**
	 * store history for this count of days.
	 *
	 * @var int
	 */
	public static $storeHistoryForDays = 365;

	/**
	 * db-fields
	 */
	static $db = array(
		"dbobject"		=> "varchar(100)",
		"record"		=> "int(10)",
		"oldversion"	=> "int(10)",
		"newversion"	=> "int(10)",
		"action"		=> "varchar(30)",
		"writetype"		=> "int(10)",
		"changecount"	=> "int(10)",
		"changed"		=> "text"
	);

	/**
	 * indexes
	 */
	static $index = array(
		"dbobject"	=> array(
			"type"		=> "INDEX",
			"name"		=> "dbobject",
			"fields"	=> "dbobject,class_name"
		)
	);

	/**
	 * @var array
	 */
	static $search_fields = false;

	/**
	 * disable history for this dataobject, because we would have an endless loop
	 *
	 *@name history
	 *@access public
	 */
	static $history = false;

	/**
	 * small cache for classes supporting HistoryView
	 *
	 *@name supportHistoryView
	 *@access private
	 */
	static $supportHistoryView;

	/**
	 * sort-direction of the history
	 *
	 *@name default_sort
	 */
	static $default_sort = "created DESC";

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
	 * @name push
	 * @access public
	 * @param string $class -name of the data to insert
	 * @param int|DataObject $oldrecord version-id of data
	 * @param int|DataObject $newrecord version-id of data
	 * @param int $recordid of the record to which the versions belong
	 * @param int $command by IModelRespository. you can also put here custom action. (max 30 chars)
	 * @param int $writeType by IModelRepository
	 * @param array - changed data
	 * @return History
	 */
	public static function push($class, $oldrecord, $newrecord, $recordid, $command, $writeType = -1, $changed = null) {

		if(PROFILE) Profiler::mark("history::push");

		// if it's an object, get the class-name from the object
		$class = ClassManifest::resolveClassName($class);

		// if we've got the version as object given, get versionid from object
		if(is_object($oldrecord))
			$oldrecord = $oldrecord->versionid;

		// if we've got the version as object given, get versionid from object
		if(is_object($newrecord))
			$newrecord = $newrecord->versionid;

		// check if history is enabled on that dataobject
		if(!StaticsManager::getStatic($class, "history")) {
			return false;
		}

		if(isset($changed) && !DataObject::versioned($class)) {
			$serializedChanged = serialize($changed);
		}

		// create the history-record
		$record = new History(array(
			"dbobject" 		=> $class,
			"oldversion"	=> $oldrecord,
			"newversion"	=> $newrecord,
			"record"		=> $recordid,
			"action"		=> $command,
			"writetype" 	=> $writeType,
			"changed"		=> isset($serializedChanged) ? $serializedChanged : null
		));

		$record->callExtending("onBeforeAddHistory");

		// insert data, we force to insert and to write, so override permission-system ;)
		Core::repository()->write($record, true, true);

		if(PROFILE) Profiler::unmark("history::push");

		$record->callExtending("historyAdded");

		return $record;
	}

	/**
	 * returns a list of classes supporting HistoryView
	 *
	 * @name supportHistoryView
	 * @access public
	 * @return array
	 */
	public static function supportHistoryView() {
		if(isset(self::$supportHistoryView))
			return self::$supportHistoryView;

		self::$supportHistoryView = array();
		foreach(ClassInfo::getChildren("DataObject") as $child) {
			if(StaticsManager::getStatic($child, "history") && ClassInfo::hasInterface($child, "HistoryData") && call_user_func_array(array($child, "canViewHistory"), array($child))) {
				self::$supportHistoryView[] = $child;
			}
		}

		return self::$supportHistoryView;
	}

	/**
	 * if we can this history-event
	 *
	 * @name canSeeEvent
	 * @return bool
	 */
	public function canSeeEvent() {
		if($this->historyData() !== false && call_user_func_array(array($this->dbobject, "canViewHistory"), array($this))) {
			return true;
		}
		return false;
	}

	/**
	 * returns the text for a history-element
	 * makes $content in template available or $object->content
	 *
	 * @name getContent
	 * @access public
	 * @return bool|mixed
	 */
	public function getContent() {
		if($data = $this->historyData()) {
			$text = $data["text"];
			if(preg_match('/\$user/', $text)) {
				// generate user
				if($this->autor) {
					$user = '<a href="member/'.$this->autor->ID . URLEND.'" class="user">' . convert::Raw2text($this->autor->title) . '</a>';
				} else {
					$user = '<span style="font-style: italic;">System</span>';
				}
				return str_replace('$user', $user, $text);
			} else {
				return $text;
			}
		}

		return false;
	}

	/**
	 * returns the icon for a history-element
	 * makes $content in template available or $object->content
	 *
	 * @name getIcon
	 * @access public
	 * @return bool|string
	 */
	public function getIcon() {
		if($data = $this->historyData()) {
			return ClassInfo::findFile($data["icon"], $this->dbobject);
		}

		return false;
	}

	/**
	 * gets the info if all versions are available for this history-object
	 *
	 * @name getIsVersioned
	 * @return bool
	 */
	public function getIsVersioned() {
		if(isset($this->_versioned)) {
			return $this->_versioned;
		}

		$this->_versioned = false;
		$data = $this->historyData();
		if(isset($data["versioned"]) && $data["versioned"] && isset($data["editurl"])) {
			if(!DataObject::versioned($this->dbobject) || $this->fieldGet("newversion") == 0 || $this->fieldGet("oldversion") == 0) {
				return false;
			}

			if(DataObject::count($this->dbobject, array("versionid" => array($this->fieldGet("newversion"), $this->fieldGet("oldversion")))) == 2) {
				$this->_versioned = true;
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
	 * @name getEditURL
	 * @return null
	 */
	public function getEditURL() {
		$data = $this->historyData();
		return isset($data["editurl"]) ? $data["editurl"] : null;
	}

	/**
	 * gets the info if all versions are available for this history-object and comparing
	 *
	 * @name getCompared
	 * @return bool
	 */
	public function getCompared() {
		$data = $this->historyData();
		$temp = new $this->dbobject();
		if(!isset($data["compared"]) || $data["compared"]) {
			return ($this->getIsVersioned() && $temp->getVersionedFields());
		}

		return false;
	}

	/**
	 * returns the retina-icon for a history-element
	 * makes $content in template available or $object->content
	 *
	 * @name getIcon
	 * @access public
	 * @return bool|string
	 */
	public function getRetinaIcon() {
		if($data = $this->historyData()) {
			$icon = ClassInfo::findFile($data["icon"], $this->dbobject);
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
	 * @name historyData
	 * @return bool|mixed
	 */
	public function historyData() {
		if(isset($this->historyData)) {
			return $this->historyData;
		}

		if(ClassInfo::exists($this->dbobject)) {
			if(gObject::method_exists($this->dbobject, "generateHistoryData")) {
				$data = call_user_func_array(array($this->dbobject, "generateHistoryData"), array($this));
				if(isset($data["text"], $data["icon"])) {
					$this->historyData = $data;
				} else if(is_array($data)) {
					throw new LogicException("Invalid Result from ".$this->dbobject."::generateHistoryData: icon & text required!");
				} else {
					$this->historyData = false;
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
	 * @name newversion
	 * @access public
	 * @return DataObject|null
	 */
	public function newversion() {
		if($this->fieldGet("newversion") && ClassInfo::exists($this->dbobject)) {
			if(DataObject::versioned($this->dbobject)) {
				return DataObject::get_one($this->dbobject, array("versionid" => $this->fieldGet("newversion")));
			}
		}

		return null;
	}

	/**
	 * returns the id of the new version
	 *
	 * @name newversionid
	 * @access public
	 * @return string
	 */
	public function newversionid() {
		return $this->fieldGet("newversion");
	}

	/**
	 * returns the old version
	 * it's a object of $this->dbobject
	 * returns false if not available, because of versions disabled
	 *
	 * @name oldversion
	 * @access public
	 * @return DataObject|null
	 */
	public function oldversion() {
		if($this->fieldGet("oldversion") && ClassInfo::exists($this->dbobject)) {

			if(DataObject::versioned($this->dbobject)) {
				return DataObject::get_one($this->dbobject, array("versionid" => $this->fieldGet("oldversion")));
			}
		}

		return null;
	}

	/**
	 * returns the id of the old version
	 *
	 * @name oldversionid
	 * @access public
	 * @return string
	 */
	public function oldversionid() {
		return $this->fieldGet("oldversion");
	}

	/**
	 * returns the record
	 *
	 * @name record
	 * @access public
	 * @return DataObject|null
	 */
	public function record() {
		if(ClassInfo::exists($this->dbobject)) {
			return DataObject::get_by_id($this->dbobject, $this->fieldGet("record"));
		}

		return null;
	}

	/**
	 * class-name of a event
	 *
	 * @name EventClass
	 * @return string
	 */
	public function EventClass() {
		$data = $this->HistoryData();
		return "history_" . $this->dbobject . (isset($data["class"]) ? " " . $data["class"] : "") . ((isset($data["relevant"]) && $data["relevant"]) ? " relevant" : " irrelevant");
	}

	/**
	 * clean up DB
	 *
	 *@name cleanUpDB
	 *@åccess public
	 */
	public function cleanUpDB($prefix = DB_PREFIX, &$log) {
		parent::cleanUpDB();

		if(self::$storeHistoryForDays > 0) {
			$id = null;
			$sql = "SELECT id FROM " . DB_PREFIX . $this->Table() . " WHERE last_modified < " . (NOW - self::$storeHistoryForDays * 60 * 60 * 24) . " ORDER BY id DESC LIMIT 1";
			if ($result = SQL::Query($sql)) {
				if ($row = SQL::fetch_object($result)) {
					$id = $row->id;
				}
			}

			if ($id) {
				// delete
				$sqlDeleteData = "DELETE FROM " . DB_PREFIX . $this->Table() . " WHERE id < " . $id . "";
				$sqlDeleteState = "DELETE FROM " . DB_PREFIX . $this->baseTable . "_state WHERE publishedid < " . $id . "";

				SQL::Query($sqlDeleteData, true);
				SQL::Query($sqlDeleteState, true);
			}
		}
	}
}
