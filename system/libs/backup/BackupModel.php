<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 29.08.2012
  * $Version 1.1.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

defined("BACKUP_DIR") OR define("BACKUP_DIR", "backup");
define("BACKUP_MODEL_BACKUP_DIR", CURRENT_PROJECT . "/" . BACKUP_DIR);

class BackupModel extends DataObject {
	/**
	 * version
	*/
	const VERSION = "1.1";
	/**
	 * path
	 *
	 *@name BACKUP_PATH
	*/
	const BACKUP_PATH = BACKUP_MODEL_BACKUP_DIR;
	
	/**
	 * db-fields
	 *
	 *@name db_fields
	 *@access public
	*/
	public $db_fields = array(
		"name" 			=> "varchar(200)",
		"create_date"	=> "varchar(200)",
		"justSQL"		=> "int(1)",
		"size"			=> "bigint(30)",
		"type"			=> "varchar(40)"
	);
	
	/**
	 * syncs the path with the model
	 *
	 *@name syncFolder
	 *@access public
	*/
	public static function syncFolder($force = false) {
		FileSystem::requireDir(self::BACKUP_PATH);
		
		if($force || isset($_GET["flush"]) || !file_exists(self::BACKUP_PATH . "/syncStatus_" . self::VERSION) || filemtime(self::BACKUP_PATH) > filemtime(self::BACKUP_PATH . "/syncStatus_" . self::VERSION)) {
			// we have to sync
			
			// get data from db
			$data = DataObject::get("BackupModel");
			
			// get files from folder
			$files = scandir(self::BACKUP_PATH);
			
			// so clean-up first deleted files
			foreach($data as $record) {
				if(!in_array($record["name"], $files))
					$record->delete();
			}
			
			// now re-index
			foreach($files as $file) {
				if($data->filter(array("name" => $file))->Count() == 0 && preg_match('/\.(gfs|sgfs)$/i', $file)) {
					$object = new BackupModel(array("name" => $file));
					$gfs = new GFS(self::BACKUP_PATH . "/" . $file);
					$info = $gfs->parsePlist("info.plist");
					if(isset($info["created"])) {
						$object->create_date = $info["created"];
						$object->justSQL = preg_match('/\.sgfs$/i', $file);
						$object->size = filesize(self::BACKUP_PATH . "/" . $file);
						
						if($info["backuptype"] == "SQLBackup") {
							$object->type = "SQL";
						} else {
							$object->type = "full";
						}
						
						$object->write(false, true);
					}
				} else if($data->filter(array("name" => $file))->first()->type == null) {
					$object = $data->filter(array("name" => $file))->first();
					$gfs = new GFS(self::BACKUP_PATH . "/" . $file);
					$info = $gfs->parsePlist("info.plist");
					
					if(isset($info["backuptype"])) {
						if($info["backuptype"] == "SQLBackup") {
							$object->type = "SQL";
						} else {
							$object->type = "full";
						}
						$object->write(false, true);
					}
				}
			}
			
			FileSystem::write(self::BACKUP_PATH . "/syncStatus_" . self::VERSION, 1);
		}
	}
	
	/**
	 * forces to sync the folder
	 *
	 *@name forceSyncFolder
	 *@access public
	*/
	public static function forceSyncFolder() {
		return self::syncFolder(true);
	}
	
	/**
	 * removes the file after remove
	 *
	 *@name onAfterRemove
	 *@access public
	*/
	public function onBeforeRemove() {
		if(file_exists(self::BACKUP_PATH . "/" . $this->name)) {
			@unlink(self::BACKUP_PATH . "/" . $this->name);
		}
	}
	
	/**
	 * gets the size
	 *
	 *@name getSize
	 *@access public
	*/
	public function getSize() {
		return FileSystem::filesize_nice(self::BACKUP_PATH . "/" . $this->name);
	}
	
	/**
	 * gets the type
	 *
	 *@name getType
	 *@access public
	*/
	public function getType() {
		return ($this->fieldGet("type") == "full") ? lang("backup_full") : lang("backup_db");
	}
	
	/**
	 * gets created date
	 *
	 *@name getCreate_Date
	 *@access public
	*/
	public function getCreate_Date() {
		$val = $this->fieldGet("create_date");
		$obj = new Varchar("create_date", $val);
		return $obj->date();
	}
}

ClassInfo::onClassInfoLoaded(array("BackupModel", "syncFolder"));
Core::addToHook("rebuildDBInDev", array("BackupModel", "forceSyncFolder"));