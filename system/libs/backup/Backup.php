<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 31.10.2011
  * $Version 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

ClassInfo::addSaveVar("Backup", "excludeList");

class Backup extends Object {
	/**
	 * list of excluded tables
	 *
	 *@name excludeList
	 *@access public
	*/
	public static $excludeList = array("statistics", "statistics_state");
	/**
	 * generates a database-backup
	 *
	 *@name generateDBBackup
	 *@access public
	*/
	public static function generateDBBackup($file, $prefix = DB_PREFIX, $excludeList = array()) {
		$excludeList = array_merge(self::$excludeList, $excludeList);
		// force GFS
		if(!preg_match('/\.sgfs$/i', $file))
			$file .= ".sgfs";
		
		$gfs = new GFS($file);
		
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("backup"));
		$dict->add("backuptype", new CFString("SQLBackup"));
		$dict->add("foldername", new CFString(APPLICATION));
		
		$td = new CFTypeDetector();  
		$excludeListPlist = $td->toCFType( $excludeList );
		$dict->add("excludedTables", $excludeListPlist);
		
		$dict->add("includedTables", $tables = new CFArray());

		
		foreach(ClassInfo::$database as $table => $fields)
		{
			if(!in_array($table, $excludeList)) {
				$tables->add(new CFString($table));
				$data = "-- Table ".$table . "\n\n";
				$data .= "DROP TABLE IF EXISTS ".$prefix.$table.";\n";
				
				// Create table
				$data .= "  -- Create \n";
				$sql = "DESCRIBE ".DB_PREFIX."".$table."";
				if($result = sql::query($sql)){
						$num = sql::num_rows($result);
						$end = 0;
						$data .= "CREATE TABLE ".$prefix.$table . " (\n";
						
						// get all fields
						while($array = sql::fetch_array($result))
						{
								$tab_name = '`'.$array["Field"].'`';
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
										$keyname = "UNIQUE `".$keyname."`";
								}
								if($comment == "FULLTEXT") 
								{
										$keyname="FULLTEXT `".$keyname."`";
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
												$data .= "KEY `" . $keyname . "` (";
										}
										$data .= implode($columns, ", ") . ")";

								}    // end foreach
						}   // end if

						$data .= ");\n";
						$data .= "\n";
				}
				
				// values
				$sql = "SELECT * FROM `".DB_PREFIX."".$table."`";
				if($result = sql::query($sql))
				{
						if(sql::num_rows($result) > 0)
						{
								
								$i = 0;
								while($row = sql::fetch_assoc($result))
								{
										if($i == 0) {
											$data .= "-- INSERT \n INSERT INTO ".$prefix."".$table." (`".implode("` , `", array_keys($row))."`) VALUES ";
										}
										foreach($row as $key => $value)
										{
												$row[$key] = str_replace(array("\n\r", "\n", "\r"), '\n', $value);
												$row[$key] = addSlashes($row[$key]);
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
						throwErrorById(3);
				}
				
				$gfs->addFile("database/" . $table . ".sql", $data);
				unset($data);
			}
		}
		
		$gfs->addFile("info.plist", $plist->toXML());
		$gfs->close();
		return $file;
	}
	/**
	 * generates a file-backup
	 *
	 *@name generateFileBackup
	 *@access public
	*/
	public static function generateFileBackup($file, $excludeList = array(), $includeTPL = true) {
		$backup = new GFS_Package_Creator($file);
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("backup"));
		$dict->add("created", new CFDate(NOW));
		$dict->add("backuptype", new CFString("filesonly"));
		$dict->add("templates", $templates = new CFArray());
		
		foreach(scandir(ROOT . "tpl/") as $template) {
			if($template != "." && $template != ".." && is_dir(ROOT . "tpl/" . $template)) {
				$templates->add(new CFString($template));
			}
		}
		
		$td = new CFTypeDetector();  
		$excludeListPlist = $td->toCFType( $excludeList );
		$dict->add("excludedFiles", $excludeListPlist);
		
		
		$backup->addFile("info.plist", $plist->toXML());
		$backup->add(ROOT . APPLICATION, "/backup/", array_merge(array("temp", "/backups", "/log", "/config.php", "/backup"), $excludeList));
		if($includeTPL)
			$backup->add(ROOT . "tpl/", "/templates/", $excludeList);
		
		$backup->close();
		return true;
	}
	
	/**
	 * generates a backup
	 *
	 *@name generateBackup
	 *@access public
	*/
	public static function generateBackup($file, $excludeList = array(), $excludeSQLList = array(), $SQLprefix = DB_PREFIX, $includeTPL = true) {
		self::generateFileBackup($file, $excludeList, $includeTPL);
		$DBfile = self::generateDBBackup(ROOT . CACHE_DIRECTORY ."/database.sgfs", $SQLprefix, $excludeSQLList);
		$backup = new GFS($file);
		$backup->addFromFile($DBfile,basename($DBfile));
		@unlink($DBfile);
		unset($sql);
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("backup"));
		$dict->add("created", new CFDate(NOW));
		$dict->add("backuptype", new CFString("full"));
		
		$td = new CFTypeDetector();  
		$excludeListPlist = $td->toCFType( $excludeList );
		$dict->add("excludedFiles", $excludeListPlist);
		
		$td = new CFTypeDetector();  
		$excludeSQLListPlist = $td->toCFType(array_merge($excludeSQLList, self::$excludeList));
		$dict->add("excludedTables", $excludeSQLListPlist);
		
		$dict->add("DB_PREFIX", new CFString($SQLprefix));
		
		$backup->write("info.plist", $plist->toXML());
		
		unset($plist);
	}
}

