<?php defined('IN_GOMA') OR die();

defined("BACKUP_DIR") OR define("BACKUP_DIR", "backup");
define("BACKUP_MODEL_BACKUP_DIR", CURRENT_PROJECT . "/" . BACKUP_DIR);

/**
 * Backupmodel creates and holds a database of backups.
 *
 * @package		Goma\Backup
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		2.3.2
 *
 * @property int size
 * @property int create_date
 * @property string type
 * @property string name filename
 */
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
	static $db = array(
        "name"          => "varchar(200)",
		"create_date"	=> "varchar(200)",
		"size"			=> "bigint(30)",
		"type"			=> "varchar(40)"
	);
	
	static $default_sort = "create_date DESC";

    /**
     * returns if it is just SQL.
     */
    public function justSQL() {
        return preg_match('/\.sgfs$/i', $this->name);
    }

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
				if(!in_array($record["name"], $files)) {
                    $record->delete();
                }
			}
			
			// now re-index
			foreach($files as $file) {
				if($data->filter(array("name" => $file))->Count() == 0 && preg_match('/\.(gfs|sgfs)$/i', $file)) {
					$object = new BackupModel(array("name" => $file));
					try {
						$gfs = new GFS(self::BACKUP_PATH . "/" . $file);
						$info = $gfs->parsePlist("info.plist");
						if (isset($info["created"])) {
							$object->create_date = $info["created"];
							$object->size = filesize(self::BACKUP_PATH . "/" . $file);

							if ($info["backuptype"] == "SQLBackup") {
								$object->type = "SQL";
							} else {
								$object->type = "full";
							}

							$object->writeToDB(false, true);
						}
					} catch(Exception $e) {
						log_exception($e);
					}
				} else if($data->filter(array("name" => $file))->first()->type == null) {
					$object = $data->filter(array("name" => $file))->first();
					try {
						$gfs = new GFS(self::BACKUP_PATH . "/" . $file);
						$info = $gfs->parsePlist("info.plist");

						if (isset($info["backuptype"])) {
							if ($info["backuptype"] == "SQLBackup") {
								$object->type = "SQL";
							} else {
								$object->type = "full";
							}
							$object->writeToDB(false, true);
						}
					} catch(Exception $e) {
						log_exception($e);
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
     * @name onAfterRemove
     * @access public
     * @return void
     */
	public function onBeforeRemove() {
		if(file_exists(self::BACKUP_PATH . "/" . $this->name)) {
			@unlink(self::BACKUP_PATH . "/" . $this->name);
		}
	}

    /**
     * gets the size
     *
     * @name getSize
     * @access public
     * @return string
     */
	public function getSize() {
		return FileSizeFormatter::format_nice(filesize(self::BACKUP_PATH . "/" . $this->name));
	}

    /**
     * gets the type
     *
     * @name getType
     * @access public
     * @return string
     */
	public function getType() {
		return ($this->fieldGet("type") == "full") ? lang("backup_full") : lang("backup_db");
	}

    /**
     * gets created date
     *
     * @name getCreate_Date
     * @access public
     * @return string
     */
	public function getCreate_Date() {
		$val = $this->fieldGet("create_date");
		$obj = new Varchar("create_date", $val);
		return $obj->date();
	}
}

ClassInfo::onClassInfoLoaded(array("BackupModel", "syncFolder"));
Core::addToHook("rebuildDBInDev", array("BackupModel", "forceSyncFolder"));