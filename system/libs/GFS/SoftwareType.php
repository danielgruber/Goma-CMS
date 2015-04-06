<?php defined("IN_GOMA") OR die();

define("DEFAULT_PACKAGE_FOLDER", FRAMEWORK_ROOT . "installer/data/apps");

/**
 * Base class for _every_ Goma SoftType-Handler.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version	1.7.2
 */
abstract class g_SoftwareType {
	/**
	 * file-name of the current file
	 *
	 *@name file
	 *@access protected
	*/
	protected $file;
	
	/**
	 * value of the type-info.plist-attribute
	 *
	 *@name type
	 *@access public
	*/
	public static $type;
	
	/**
	 * cache if goma-cms.org is available
	 *
	 *@name gomaAvailable
	 *@access protected
	*/
	protected static $gomaAvailable;

	/**
	 * folder for Packages.
	*/
	public static $package_folder = DEFAULT_PACKAGE_FOLDER;
	
	/**
	 * default __construct
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($file) {
		$this->file = $file;
	}
	
	/**
	 * gets information on how to install this software
	 * this method might expand files for installing
	 *
	 *@name install
	 *@access public
	*/
	abstract public function getInstallInfo($forceInstall = false);
	
	/**
	 * gets basic information about a package and validates basic info
	 *
	 *@name getPackageInfo
	 *@access public
	*/
	abstract public function getPackageInfo();
	
	/**
	 * with this method you can set some package-infos
	 *
	 *@name setPackageInfo
	 *@access public
	*/
	abstract public function setPackageInfo($data);
	
	/**
	 * restores this piece of software
	 *
	 *@name restore
	 *@access public
	*/
	abstract public function getRestoreInfo($forceCompleteRestore = false);
	
	/**
	 * makes a backup of the given name of software
	 *
	 *@name backup
	 *@access public
	*/
	abstract public static function backup($file, $name);
	
	/**
	 * builds a distributable version of this software
	 *
	 *@name buildDistro
	 *@access public
	*/
	abstract public static function buildDistro($file, $name);
	
	/**
	 * generates the filename for a distributable
	 *
	 *@name generateDistroFileName
	 *@access public
	*/
	abstract public static function generateDistroFileName($name);
	
	/**
	 * lists installed software of this type
	 * returns an array:
	 * array(
	 *	'[name]' => array('canDisable' => false, 'version' => '1.0', 'icon' => 'mysite/icon.png', 'title' => 'Goma Framework')
	 * )
	 *
	 *@name listSoftware
	 *@access public
	*/
	abstract public static function listSoftware();
	
	/**
	 * disables the software
	 *
	 *@name disable
	 *@access public
	*/
	public static function disable($name) {
	
	}
	
	/**
	 * enables the software
	 *
	 *@name enable
	 *@access public
	*/
	public static function enable($name) {
	
	}

    /**
     * cleanup for Uploaded Updates.
     *
     * @return void
     */
    public static function cleanUpUpdates() {
        FileSystem::Delete(ROOT . APPLICATION . "/uploads/e47ddedf1c2bd2d0006eae2d2eee39b4");

        $query = new SelectQuery("uploads", array("recordid"), array("realfile" => array("LIKE", "%/e47ddedf1c2bd2d0006eae2d2eee39b4/%")));
        if($query->execute()) {
            $manipulation["uploads"] = array(
                "table" 	=> "uploads",
                "command"	=> "delete",
                "where"		=> array(
                    "recordid" => array()
                )
            );

            $manipulation["uploads_state"] = array(
                "table" 	=> "uploads",
                "command"	=> "delete",
                "where"		=> array(
                    "id" => array()
                )
            );

            while($row = $query->fetch_assoc()) {
                $manipulation["uploads"]["where"]["recordid"][] = $row["recordid"];
                $manipulation["uploads_state"]["where"]["id"][] = $row["recordid"];
            }

            SQL::Manipulate($manipulation);
        } else {
            throw new SQLException();
        }
    }
	
	/**
	 * gets the correct class by type
	 *
	 *@name getByType
	 *@access public
	*/
	public static function getByType($type, $file) {
		if(strtolower($type) == "app") {
			$type = "backup";
		}
		
		foreach(ClassInfo::getChildren("G_SoftwareType") as $child) {
			if(strtolower(StaticsManager::getStatic($child, "type")) == strtolower($type)) {
				return new $child($file);
			}
		}
		
		throw new LogicException("Could not find SoftwareType '".convert::raw2text($type)."'.");
	}
	
	/**
	 * lists all software of the whole system
	 *
	 *@name listAllSoftware
	 *@access public
	*/
	final public static function listAllSoftware() {
		$apps = array();
		foreach(ClassInfo::getChildren("g_SoftwareType") as $child) {
			$return = call_user_func_array(array($child, "listSoftware"), array());
			foreach($return as $name => $data) {
				if(is_array($data) && !preg_match('/^[0-9]+$/', $name)) {
					$data["type"] = substr(substr($child, 2), 0, -12);
					$data["name"] = $name;
					$apps[$name] = $data;
				}
			}
		}
		
		return new ViewAccessableData($apps);
	}
	
	/**
	 * gets install-infos about a GFS-file
	 *
	 *@name getInstallInfos
	 *@access public
	*/
	public static function getInstallInfos($file, $forceInstall = false, $forceUpdate = false) {
		$gfs = new GFS($file);
		
		if(!$gfs->valid) {
			return lang("gfs_invalid");
		}
		
		$info = $gfs->parsePlist("info.plist");
		
		if(isset($info["type"])) {	
			if($info["type"] == "app")
				$info["type"] = "backup";
			
			foreach(ClassInfo::getChildren("G_SoftwareType") as $child) {
				if(StaticsManager::getStatic($child, "type") == $info["type"]) {
					$inst = new $child($file);
					
					$data = $inst->getInstallInfo($forceInstall);
					if($forceUpdate && $data["installType"] != "update") {
						$data["installable"] = false;
						$data["error"] = lang("install_not_update");
					}
					
					if($gfs->isSigned(self::getAppStorePublic())) {
						$data["signed"] = true;
					} else if(GFS::$openssl_problems) {
						$data["signed_ssl_not_installed"] = true;
					}
					
					return $data;
				}
			}
		}
		
		return lang("install_invalid_file");
	}
	
	/**
	 * installs with information
	 *
	 *@name install
	 *@access public
	*/
	public static function install($data) {
		if(is_object($data)) {
			$data = $data->ToArray();
		}
		
		$data = ArrayLib::map_key("strtolower", $data);
		
		if($data["installable"] && isset($data["installfolders"]["source"], $data["installfolders"]["destination"])) {
			
			$log = "Installing new Software.\nInformation:\n" . print_r($data, true) . "\n\n\n";
			
			// preflight
			if(isset($data["preflightcode"])) {
				if(is_array($data["preflightcode"])) {
					foreach($data["preflightcode"] as $code) {
						$file = FRAMEWORK_ROOT . "temp/" . md5($code) . ".php";
						file_put_contents($file, $code);
						include($file);
						@unlink($file);
					}
				} else {
					$file = FRAMEWORK_ROOT . "temp/" . md5($data["preflightcode"]) . ".php";
					file_put_contents($file, $data["preflightcode"]);
					include($file);
					@unlink($file);	
				}
			}
			
			if(isset($data["preflight"])) {
				if(is_array($data["preflight"])) {
					foreach($data["preflight"] as $file) {
						include($file);
					}
				} else {
					include($data["preflight"]);
				}
			}
			
			$log .= "Preflight OK.\n\nFlight:\n";
			
			// flight
			
			if(is_array($data["installfolders"]["source"])) {
				foreach($data["installfolders"]["source"] as $key => $folder) {
					
					if(file_exists($folder))
						$folder = realpath($folder);
					
					if(file_exists($data["installfolders"]["destination"][$key]))
						$data["installfolders"]["destination"][$key] = realpath($data["installfolders"]["destination"][$key]);
					
					if(isset($data["installfolders"]["destination"][$key])) {
						$log .= "Moving {$folder} to ".$data["installfolders"]["destination"][$key]."\n";
						$log .= FileSystem::moveLogged($folder, $data["installfolders"]["destination"][$key], false);

						$log .= "\n\n";
					}
				}
			} else {
				if(file_exists($data["installfolders"]["source"])) {
					$data["installfolders"]["source"] = realpath($data["installfolders"]["source"]);
				}
				
				if(file_exists($data["installfolders"]["destination"])) {
					$data["installfolders"]["destination"] = realpath($data["installfolders"]["destination"]);
				}
				
				$log .= "Moving ".$data["installfolders"]["source"]." to ".$data["installfolders"]["destination"].".\n";
				if(($return = FileSystem::moveLogged($data["installfolders"]["source"], $data["installfolders"]["destination"])) === false) {
					throwError(6, 'PHP-Error', "Could not move files of Update. Failed in file " . FileSystem::errFile());
				}
				
				$log .= $return;
				$log .= "\n\n";
			}
			
			$log .= "FLIGT OK.\n";
			
			// postflight
			if(isset($data["postflightcode"])) {
				if(is_array($data["postflightcode"])) {
					foreach($data["postflightcode"] as $code) {
						$file = FRAMEWORK_ROOT . "temp/" . md5($code) . ".php";
						file_put_contents($file, $code);
						include($file);
						@unlink($file);
					}
				} else {
					$file = FRAMEWORK_ROOT . "temp/" . md5($data["postflightcode"]) . ".php";
					file_put_contents($file, $data["postflightcode"]);
					include($file);
					@unlink($file);	
				}
			}
			
			if(isset($data["postflight"])) {
				if(is_array($data["postflight"])) {
					foreach($data["postflight"] as $file) {
						include($file);
					}
				} else {
					include($data["postflight"]);
				}
			}
			
			$log .= "POSTFLIGHT OK.\n";

			FileSystem::applySafeMode();
			
			// save log
			FileSystem::RequireDir(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/install/");
			file_put_contents(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/install/" . date("m-d-y_H-i-s") . ".log", $log);
			
			if(isset($data["installType"]) && $data["installType"] == "update")
				AddContent::addSuccess(lang("updateSuccess"));
			
			Dev::RedirectToDev();
			exit;
			
		} else {
			return false;
		}
	}
	
	/**
	 * forces that installer/data/apps/.index-db is Live
	 *
	 *@name forceLiveDB
	 *@access public
	*/
	public static function forceLiveDB() {
		if(!file_exists(self::$package_folder . "/.index-db")) {
			ClassInfo::delete();
			ClassInfo::loadFile();
		} else {
			$data = unserialize(file_get_contents(self::$package_folder . "/.index-db"));
			if($data["fileindex"] != scandir(self::$package_folder)) {
				ClassInfo::delete();
				ClassInfo::loadFile();
			}
		}
	}
	
	/**
	 * lists updateable software
	 *
	 *@name listUpdatePackages
	 *@access public
	*/
	public static function listUpdatePackages() {
		if(file_exists(self::$package_folder . "/.index-db")) {
			$dir = self::$package_folder . "/";
			$data = unserialize(file_get_contents(self::$package_folder . "/.index-db"));
			
			$updates = array();
			
			// build up update-information.
			foreach($data["packages"] as $type => $apps) {
				foreach($apps as $version => $appInfo) {
					if(isset($appInfo["file"])) {
						self::buildPackageInfo($type, $dir, $appInfo, $version, $updates);
					} else {
						foreach($appInfo as $app => $versionInfo) {
							if(isset($versionInfo["file"])) {

								self::buildPackageInfo($app, $type, $dir, $versionInfo, $version, $updates);

							}
						}
					}
				}
			}
			
			
			// app
			self::checkForUpdatePackage(ClassInfo::$appENV["app"]["name"], ClassInfo::appVersion(), $updates);

			// framework
			self::checkForUpdatePackage(ClassInfo::$appENV["framework"]["name"], GOMA_VERSION . "-" . BUILD_VERSION, $updates);
		
			// extensions
			if(isset(ClassInfo::$appENV["expansion"]) && ClassInfo::$appENV["expansion"]) {
				// expansions
				foreach(ClassInfo::$appENV["expansion"] as $app => $data) {
					self::checkForUpdatePackage($app, ClassInfo::expVersion($app), $updates);
				}
			}
			
			
			return $updates;
		} else {
			return array();
		}
	}
	
	/**
	 * lists updatable packages
	 *
	 *@name listUpdatablePackages
	 *@access public
	*/
	public static function listUpdatablePackages() {
		$apps = array();
		$apps[ClassInfo::$appENV["framework"]["name"]] = array(
			"name" 		=> ClassInfo::$appENV["framework"]["name"],
			"version" 	=> GOMA_VERSION . "-" . BUILD_VERSION
		);
		
		$apps[ClassInfo::$appENV["app"]["name"]] = array(
			"name"		=> ClassInfo::$appENV["app"]["name"],
			"version"	=> ClassInfo::appVersion()
		);
		
		if(isset(ClassInfo::$appENV["expansion"])) {
			// expansions
			foreach(ClassInfo::$appENV["expansion"] as $app => $data) {
				$apps[$app] = array(
					"name" 		=> $app,
					"version"	=> ClassInfo::expVersion($app)
				);
			}
		}
		
		return $apps;
	}
	
	/**
	 * isStoreAvailable
	 *
	 *@name isStoreAvailable
	 *@access public
	*/
	public function isStoreAvailable() {
		if(isset(self::$gomaAvailable))
			return self::$gomaAvailable;
		
		if(strpos(@file_get_contents("https://goma-cms.org"), "<html")) {
			self::$gomaAvailable = true;
			return true;
		} else {
			self::$gomaAvailable = false;
			return false;
		}
	}
	
	/**
	 * gets a data of a app from the app-store-server
	 *
	 *@name getAppStoreInfo
	 *@access public
	*/
	public function getAppStoreInfo($name, $version = null, $currVersion = 1.0) {
		if(PROFILE) Profiler::mark("G_SoftwareType::getAppStoreInfo");
		
		if(!self::isStoreAvailable()) {
			return false;
		}
		
		$url = "https://goma-cms.org/apps/api/v1/json/app/" . $name;
		
		if(isset($version)) {
			$url .= "/" . $version;
		}
		
		$url .= "/";
		$url .= "?framework=" . urlencode(GOMA_VERSION . "-" . BUILD_VERSION);
		$url .= "&current=".urlencode($currVersion);
		$url .= "&base_uri=" . urlencode(BASE_URI);
		
		$cacher = new Cacher("AppStore_" . md5($url));
		if($cacher->checkValid()) {
			if(PROFILE) Profiler::unmark("G_SoftwareType::getAppStoreInfo");
			return $cacher->getData();
		} else {
			
			if($response = @file_get_contents($url)) {
				if(substr($response, 0, 1) == "(")
					$response = substr($response, 1, -1);
				
				$data = json_decode($response, true);
				if(!is_array($data)) {
					return false;
				}
				$cacher->write($data, 3600 * 6);
				
				if(PROFILE) Profiler::unmark("G_SoftwareType::getAppStoreInfo");
				
				return $data;
			} else {
				if(PROFILE) Profiler::unmark("G_SoftwareType::getAppStoreInfo");
				return false;
			}
		}
	}
	
	/**
	 * returns the public key of the app-store
	 *
	 *@name getAppStorePublic
	 *@access public
	*/
	public function getAppStorePublic() {
		if(file_exists(FRAMEWORK_ROOT . "libs/GFS/appStorePublic.php")) {
			include(FRAMEWORK_ROOT . "libs/GFS/appStorePublic.php");
			return $publicKey;
		}
		
		return false;
	}
	
	/**
	 * lists installable software
	 *
	 *@name listInstallPackages
	 *@access public
	*/
	public static function listInstallPackages() {
		if(file_exists(self::$package_folder . "/.index-db")) {
			$dir = self::$package_folder . "/";
			$data = unserialize(file_get_contents(self::$package_folder . "/.index-db"));
			
			$packages = array();
			
			foreach($data["packages"] as $type => $apps) {
				foreach($apps as $app => $appInfo) {
					if(isset($appInfo["file"])) {

						self::buildPackageInfo($app, $type, $dir, $appInfo, $appInfo["version"], $packages, false);
					} else {
						foreach($appInfo as $version => $versionInfo) {
							if(isset($versionInfo["file"])) {
								self::buildPackageInfo($app, $type, $dir, $versionInfo, $version, $packages, false);
							}
						}
					}
				}
			}

			return $packages;
		} else {
			return array();
		}
	}

	/**
	 * builds information about a package from data.
	 * it puts it into $updates-array if version is newer.
	 *
	 * @name 	buildPackageInfo
	*/
	protected static function buildPackageInfo($app, $type, $dir, $data, $version, &$packages, $shouldUpdate = true) {
		$appdata = self::getByType($type, $dir . $data["file"])->getPackageInfo();
		$appdata["file"] = $dir . $data["file"];
		$appdata["plist_type"] = $type;
		if($appdata // data exists
			&& self::isValidPackageType($appdata, $shouldUpdate))
		{

			if(isset($packages[$app])) {
				if(goma_version_compare($packages[$app]["version"], $version, "<")) {
					$packages[$app] = $appdata;
				}
			} else {
				$packages[$app] = $appdata;
			}
		}
	}

	/**
	 * implements correct ifs for update and install-packages.
	 *
	 * @name isValidPackageType
	*/
	protected static function isValidPackageType($appData, $shouldUpdate) {

		if(isset($appdata["installable"]) && !$appdata["installable"]) {
			return false;
		}

		if($shouldUpdate) {
			return isset($appdata["installType"]) && $appdata["installType"] == "update" // valid type;
					&& goma_version_compare($appdata["version"], $appdata["installed_version"], ">"); // valid version
		}

		return !isset($appdata["installType"]) || $appdata["installType"] != "update";
	}

	/**
	 * checks in the Web for Updates for this App.
	 *
	 * @name 	checkForUpdatePackage
	*/
	protected static function checkForUpdatePackage($app, $version, &$updates) {
		if($data = self::getAppStoreInfo($app, null, $version)) {
			$data["installed_version"] = $version;
			$data["appinfo"]["autor"] = $data["autor"];
			$data["AppStore"] = $data["download"];
			
			if(isset($updates[$app])) {
				if(goma_version_compare($updates[$app]["version"], $data["version"], "<=")) {
					$updates[$app] = $data;
				}
			} else if(goma_version_compare($data["version"], ClassInfo::appVersion(), ">")) {
				$updates[$app] = $data;
			}
		}
	}

	/**
	 * gets current index data or an array with fileindex and packages as empty arrays.
	*/
	public static function getIndexData() {
		if(file_exists(self::$package_folder . "/.index-db")) {
			$data = @unserialize(file_get_contents(self::$package_folder . "/.index-db"));
			if($data == null) {
				$data = array("fileindex" => array(), "packages" => array());
			}
		} else {
			$data = array("fileindex" => array(), "packages" => array());
		}

		return $data;
	}

	/**
	 * builds .index.db and returns problematic files.
	*/
	public static function buildPackageIndex() {

		// load dependencies
		require_once (FRAMEWORK_ROOT . "/libs/GFS/gfs.php");
		require_once (FRAMEWORK_ROOT . "/libs/thirdparty/plist/CFPropertyList.php");

		$appFolder = self::$package_folder;
		$files = scandir($appFolder);
		$data = self::getIndexData();

		$errors = array();

		if($data["fileindex"] != $files) {
			$data = array("fileindex" => array(), "packages" => array());
			$data["fileindex"] = $files;

			/*
	  		 * iterate through all files and analyze them for the ability to being a package.
	  		 * it will also remove files that are packages, but newer packages were found.
			*/
			foreach($files as $file) {
				if(preg_match('/\.gfs$/i', $file)) {
					
					// check where to find information about the plist. its faster to get information from cached file.
					if (file_exists($appFolder . "/" . $file . ".plist")) {
						$info = self::getFromPlistOrGFS($appFolder . "/" . $file . ".plist", $appFolder . "/" . $file, "info.plist", true);

						if($info === false) {
							$errors[] = $appFolder . "/" . $file;
						}

						continue;
					} else {
						$info = self::getPlistFromGFS($appFolder . "/" . $file, "info.plist");
					}

					if($info === false) {
						$errors[] = $appFolder . "/" . $file;
						continue;
					}

					// this is important to know which file is meant by package record.
					$info["file"] = $file;

					self::fillPackageArray($data["packages"], $info, $appFolder . "/" . $file, $appFolder . "/" . $file . ".plist");
					
				}
			}

			if(!$errors) {
				FileSystem::write(ROOT . "system/installer/data/apps/.index-db", serialize($data));
			}
		}

		return $errors;
	}

	/**
	 *  fills packages array with information from plist.
	 *
	 * @param 	data packages-array
	 * @param 	info plist
	 * @param 	gfs
	 * @param 	plist
	*/
	public static function fillPackageArray(&$data, $info, $gfs, $plist) {

		// check requirements for package first
		if(isset($info["type"], $info["version"])) {
			if(isset($info["name"])) {
				if(isset($data[$info["type"]][$info["name"]])) {

					foreach($data[$info["type"]][$info["name"]] as $v => $d) {

						// delete file which was found and has older version
						if(goma_version_compare($v, $info["version"], "<")) {
							@unlink($appFolder . "/" . $d["file"]);
							@unlink($appFolder . "/" . $d["file"] . ".plist");
							unset($data[$info["type"]][$info["name"]][$v]);
							$data[$info["type"]][$info["name"]][$info["version"]] = $info;

							
						} else {

							// delete this file cause a newer version was found.
							@unlink($gfs);
							@unlink($plist);
						}
					}
				} else {

					// no version found until now, so its properbly the newest
					$data[$info["type"]][$info["name"]][$info["version"]] = $info;
				}
			}
		}
	}

	/**
	 * gets data from a plist or plist in GFS-Archive.
	 *
	 * @param 	string path to plist
	 * @param 	string path to gfs
	 * @param 	string path in GFS to plist
	 * @param 	boolean delete plist when not readable
	*/
	public static function getFromPlistOrGFS($plist, $gfs, $file, $deletePlist = false) {
		if(filemtime($plist) > filemtime($gfs) && $info = self::getPlistFromPlist($plist)) {
			return $info;
		}

		if($deletePlist) {
			@unlink($plist);
		}

		return self::getPlistFromGFS($gfs, $file);

	}

	/**
	 * gets array from plist.
	*/
	public static function getPlistFromPlist($plist) {

		if(!file_exists($plist)) {
			return false;
		}

		$plist = new CFPropertyList();
		try {
			$plist -> parse(file_get_contents($plist));
			$info = $plist -> toArray();

			return $info;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * gets plist-information from plist in GFS.
	*/
	public static function getPlistFromGFS($gfs, $file) {
		try {
			$gfs = new GFS($gfs);
			$info = $gfs->parsePlist($file);

			return $info;
		} catch(Exception $e) {
			return false;
		}
	}

	/**
	 * checks move permissions for installation from GFS.
	*/
	public static function checkMovePerms($gfs, $folder, $destination) {
		
		$filemover = self::getFileMover($gfs, $folder, $destination);
		$errors = $filemover->checkValid(false, true);

		// look to all errors and decide which is a real problem.
		$realerrors = array();
		foreach($errors as $file) {
			if(file_exists($destination. "/" . $file) && is_dir($destination . "/" . $file) && $gfs->isDir($folder . $file)) {
				continue;
			}

			if(file_exists($destination . "/" . $file) && $gfs->getMd5($folder . $file) == md5_file($destination . "/" . $file)) {
				continue;
			}

			$realerrors[] = $file;
		}

		return $realerrors;
	}

	/**
	 * generates the file-mover from gfs, folder and destination.
	*/	
	public static function getFileMover($gfs, $folder, $destination) {
		$files = array_keys($gfs->getDB());
		
		$files = array_filter($files, create_function('$val', 'return substr($val, 0, '.strlen($folder).') == '.var_export($folder, true).';'));
		
		$files = array_map(create_function('$val', 'return substr($val, '.var_export(strlen($folder), true).');'), $files);
		
		return new FileMover($files, null, $destination);
	}

	/**
	 * returns file of this class.
	*/
	public function getFile() {
		return $this->file;
	}

	public function __wakeup() {}
}

class StoreConnectionException extends Exception {
    /**
     * constructor.
     */
    public function __construct($m = "", $code = ExceptionManager::STORE_CONNECTION_FAIL, Exception $previous = null) {
        parent::__construct($m, $code, $previous);
    }
}