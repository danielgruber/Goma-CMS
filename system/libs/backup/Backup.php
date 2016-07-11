<?php defined("IN_GOMA") OR die();

/**
  * @package goma framework
  * @link http://goma-cms.org
  * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  * @author Goma-Team
*/
StaticsManager::addSaveVar("Backup", "excludeList");
StaticsManager::addSaveVar("Backup", "fileExcludeList");

class Backup extends gObject {
	/**
	 * list of excluded tables
	*/
	public static $excludeList = array("statistics", "statistics_state");
	
	/**
	 * excludes files
	*/
	public static $fileExcludeList = array("/uploads/d05257d352046561b5bfa2650322d82d","temp", "/backups", "/config.php", "/backup", "version.php");

	/**
	 * generates a database-backup
	 *
	 * @param string $file
	 * @param string $prefix
	 * @param array $excludeList
	 * @return string
	 * @throws GFSFileExistsException
	 * @throws GFSRealFilePermissionException
	 * @throws PListException
	 * @throws SQLException
	 */
	public static function generateDBBackup($file, $prefix = DB_PREFIX, $excludeList = array()) {
		$excludeList = array_merge(StaticsManager::getStatic("Backup", "excludeList"), $excludeList);
		// force GFS
		if(!preg_match('/\.sgfs$/i', $file))
			$file .= ".sgfs";
		
		$gfs = new GFS($file);
		
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("backup"));
		$dict->add("backuptype", new CFString("SQLBackup"));
		$dict->add("foldername", new CFString(APPLICATION));
		$dict->add("created", new CFDate(NOW));
		
		$td = new CFTypeDetector();  
		$excludeListPlist = $td->toCFType( $excludeList );
		$dict->add("excludedTables", $excludeListPlist);
		
		$dict->add("includedTables", $tables = new CFArray());
		
		$time = microtime(true);
		$i = 0;
		
		foreach(ClassInfo::$database as $table => $fields)
		{
			
			if($gfs->exists("database/" . $table . ".sql"))
				continue;
			
			$tables->add(new CFString($table));
			$data = "-- Table ".$table . "\n\n";
			
			// exclude drop
			if(!in_array($table, $excludeList))
				$data .= "DROP TABLE IF EXISTS ".$prefix.$table.";\n";
			
			// Create table
			$data .= "  -- Create \n";
			$sql = "DESCRIBE ".DB_PREFIX."".$table."";
			if($result = sql::query($sql)){
					$num = sql::num_rows($result);
					$end = 0;
					$data .= "CREATE TABLE IF NOT EXISTS ".$prefix.$table . " (\n";
					
					// get all fields
					while($array = sql::fetch_array($result))
					{
							$tab_name = $array["Field"];
							$tab_type = $array["Type"];
							$tab_null = " NOT NULL";
							$tab_default = (empty($array["Default"])) ? "" : " DEFAULT '" . $array["Default"] . "'";
							$tab_extra = (empty($array["Extra"])) ? "" : " " . $array["Extra"];
							$end++;
							$tab_komma = ($end<$num) ? ",\n" : "";
							$data .= " " . $tab_name . " " . $tab_type . $tab_null . $tab_default . $tab_extra . $tab_komma;
					}
			}
			
			// indexes
			$keyarray = array();
			$sql = "SHOW KEYS FROM ".DB_PREFIX."".$table;
			if($result = sql::query($sql))
			{
					while($info = sql::fetch_array($result))
					{
							$keyname = $info["Key_name"];
							$comment = (isset($info["Comment"])) ? $info["Comment"] : "";
							$sub_part = (isset($info["Sub_part"])) ? $info["Sub_part"] : "";
							if($keyname != "PRIMARY" && $info["Non_unique"] == 0) 
							{
									$keyname = "UNIQUE ".$keyname;
							}
							if($comment == "FULLTEXT") 
							{
									$keyname="FULLTEXT ".$keyname;
							}
							if(!isset($keyarray[$keyname])) 
							{
									$keyarray[$keyname] = array();
							}
							$keyarray[$keyname][] = ($sub_part > 1) ? $info["Column_name"] . "(" . $sub_part . ")" : $info["Column_name"];

					} // endwhile
					if(is_array($keyarray)) 
					{
							foreach($keyarray as $keyname => $columns) 
							{
									$data .= ",\n";
									if($keyname == "PRIMARY") 
									{
											$data .= "PRIMARY KEY (";
									} else if(substr($keyname, 0, 6) == "UNIQUE") {
									
											$data .= "UNIQUE " . substr($keyname, 7) . " (";
									} else if(substr($keyname, 0, 8) == "FULLTEXT") 
									{
											$data .= "FULLTEXT " . substr($keyname, 9) . " (";
									} else 
									{
											$data .= "KEY " . $keyname . " (";
									}
									$data .= implode($columns, ", ") . ")";

							}    // end foreach
					}   // end if

					$data .= ");\n";
					$data .= "\n";
			}

			if(!in_array($table, $excludeList)) {
				
				// values
				$sql = "SELECT * FROM ".DB_PREFIX."".$table."";
				if($result = sql::query($sql))
				{
						if(sql::num_rows($result) > 0)
						{
								
								$i = 0;
								while($row = sql::fetch_assoc($result))
								{
										if($i == 0) {
											$data .= "-- INSERT \n INSERT INTO ".$prefix."".$table." (".implode(", ", array_keys($row)).") VALUES ";
										}
										foreach($row as $key => $value)
										{
												$row[$key] = str_replace(array("\n\r", "\n", "\r"), '\n', $value);
												$row[$key] = addSlashes($row[$key]);
												$row[$key] = str_replace(APPLICATION, '{!#CURRENT_PROJECT}', $row[$key]);
										}
										if($i == 0)
										{
												$i++;
										} else
										{
												$data .= ",";
										}
										$data .= " ( '".implode("','", $row)."' )\n";
								}
						}
						$data .= "; \n\n\n\n\n\n";
				} else
				{
						throw new SQLException();
				}
			
			}
			
			$gfs->addFile("database/" . $table . ".sql", $data);
			unset($data);
			
			$i++;
			
			$diff = microtime(true) - $time;
			if($diff > 1) {
				if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)
		
				$template = new Template;
				$template->assign("destination", $_SERVER["REQUEST_URI"]);
				$template->assign("reload", true);
				$template->assign("archive", "SQL-Backup");
				$template->assign("progress", ($i / count(ClassInfo::$database)) * 100);
				$template->assign("status", "");
				$template->assign("current", $table);
				$template->assign("remaining", "");
				echo $template->display("/system/templates/GFSUnpacker.html");
				exit;
			}
		}
		
		$gfs->write("info.plist", $plist->toXML());
		$gfs->close();
		return $file;
	}
	/**
	 * generates a file-backup
	*/
	public static function generateFileBackup($file, $excludeList = array(), $includeTPL = true) {
		$backup = new GFS_Package_Creator($file);
		
		// for converting the PHP-Array to a plist-structure
		$detector = new CFTypeDetector();
		
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("backup"));
		$dict->add("name", new CFString(ClassInfo::$appENV["app"]["name"]));
		$dict->add("created", new CFDate(NOW));
		$dict->add("backuptype", new CFString("files"));
		$dict->add("templates", $templates = new CFArray());
		$dict->add("framework_version", new CFString(GOMA_VERSION . "-" . BUILD_VERSION));
		$dict->add("appENV", $detector->toCFType(ClassInfo::$appENV["app"]));
		
		foreach(scandir(ROOT . "tpl/") as $template) {
			if($template != "." && $template != ".." && is_dir(ROOT . "tpl/" . $template)) {
				$templates->add(new CFString($template));
			}
		}
		
		$td = new CFTypeDetector();  
		$excludeListPlist = $td->toCFType( $excludeList );
		$dict->add("excludedFiles", $excludeListPlist);
		
		
		$backup->write("info.plist", $plist->toXML());
		$backup->setAutoCommit(false);
		
		if($includeTPL) {
			$plist = new CFPropertyList();
			foreach(scandir(ROOT . "tpl/") as $file) {
				
				// first validate if it looks good
				if($file != "." && $file != ".." && file_exists(ROOT . "tpl/".$file."/info.plist")) {
					
					// then validate properties
					$plist->load(ROOT . "tpl/".$file."/info.plist");
					$info = $plist->ToArray();
					
					if(isset($info["type"]) && $info["type"] == "Template") {
						if(!isset($info["requireApp"]) || $info["requireApp"] == ClassInfo::$appENV["app"]["name"]) {
							if(!isset($info["requireAppVersion"]) || version_compare($info["requireAppVersion"], ClassInfo::appVersion(), "<=")) {
								if(!isset($info["requireFrameworkVersion"]) || version_compare($info["requireFrameworkVersion"], GOMA_VERSION . "-" . BUILD_VERSION, "<=")) {
									$backup->add(ROOT . "tpl/" . $file . "/", "/templates/" . $file, $excludeList);
								}
							}
						}
					}
				}
			}
		}
		
		if(defined("LOG_FOLDER")) {
			self::$fileExcludeList[] = "/" . LOG_FOLDER;
		}
		
		$backup->add(ROOT . APPLICATION, "/backup/", array_merge(StaticsManager::getStatic("Backup", "fileExcludeList"), $excludeList));
		$backup->commit();
		
		$backup->close();
		return true;
	}
	
	/**
	 * generates a backup
	 *
	 *@name generateBackup
	 *@access public
	*/
	public static function generateBackup($file, $excludeList = array(), $excludeSQLList = array(), $SQLprefix = DB_PREFIX, $includeTPL = true, $framework = null, $changelog = null) {
		if(GFS_Package_Creator::wasPacked() && GlobalSessionManager::globalSession()->hasKey("backup") &&
			GFS_Package_Creator::wasPacked(GlobalSessionManager::globalSession()->get("backup"))) {
			$file = GlobalSessionManager::globalSession()->get("backup");
		} else {
			GlobalSessionManager::globalSession()->set("backup", $file);
			self::generateFileBackup($file, $excludeList, $includeTPL);
		}
		$DBfile = self::generateDBBackup(ROOT . CACHE_DIRECTORY ."/database.sgfs", $SQLprefix, $excludeSQLList);
		$backup = new GFS($file);
		$backup->addFromFile($DBfile,basename($DBfile));
		@unlink($DBfile);
		unset($sql);
		
		// for converting the PHP-Array to a plist-structure
		$td = new CFTypeDetector();
		
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("backup"));
		$dict->add("created", new CFDate(NOW));
		$dict->add("backuptype", new CFString("full"));
		$dict->add("name", new CFString(ClassInfo::$appENV["app"]["name"]));
		$dict->add("version", new CFString(ClassInfo::appVersion()));
		
		// append changelog
		if(isset($changelog))
			$dict->add("changelog", new CFString($changelog));
		
		// append framework-version we need
		if(!isset($framework))
			$dict->add("framework_version", new CFString(GOMA_VERSION . "-" . BUILD_VERSION));
		else
			$dict->add("framework_version", new CFString($framework));
		
		// append current appENV
		$dict->add("appENV", $td->toCFType(ClassInfo::$appENV["app"]));
		
		$excludeListPlist = $td->toCFType( $excludeList );
		$dict->add("excludedFiles", $excludeListPlist);
		
		$td = new CFTypeDetector();  
		$excludeSQLListPlist = $td->toCFType(array_merge($excludeSQLList, StaticsManager::getStatic("Backup", "excludeList")));
		$dict->add("excludedTables", $excludeSQLListPlist);
		
		$dict->add("DB_PREFIX", new CFString($SQLprefix));
		
		$backup->write("info.plist", $plist->toXML());
		$backup->close();
		unset($plist);
	}
}
