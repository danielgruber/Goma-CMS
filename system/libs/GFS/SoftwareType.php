<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 31.03.2013
  * $Version 1.5.10
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

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
	 * gets the correct class by type
	 *
	 *@name getByType
	 *@access public
	*/
	public static function getByType($type, $file) {
		if($type == "app")
			$type = "backup";
		
		foreach(ClassInfo::getChildren("G_SoftwareType") as $child) {
			if(ClassInfo::getStatic($child, "type") == $type) {
				return new $child($file);
			}
		}
		
		throwError(6, "PHP-Error", "Could not find Softwaretype '".convert::raw2text($type)."'.");
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
				if(ClassInfo::getStatic($child, "type") == $info["type"]) {
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
						if(($return = FileSystem::moveLogged($folder, $data["installfolders"]["destination"][$key])) === false) {
							throwError(6, 'PHP-Error', "Could not move files of Update. Failed in file " . FileSystem::errFile());
						}
						
						$log .= $return;
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
		if(!file_exists(FRAMEWORK_ROOT . "installer/data/apps/.index-db")) {
			ClassInfo::delete();
			ClassInfo::loadFile();
		} else {
			$data = unserialize(file_get_contents(FRAMEWORK_ROOT . "installer/data/apps/.index-db"));
			if($data["fileindex"] != scandir(FRAMEWORK_ROOT . "installer/data/apps/")) {
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
		if(file_exists(FRAMEWORK_ROOT . "installer/data/apps/.index-db")) {
			$dir = FRAMEWORK_ROOT . "installer/data/apps/";
			$data = unserialize(file_get_contents(FRAMEWORK_ROOT . "installer/data/apps/.index-db"));
			
			$updates = array();
			
			foreach($data["packages"] as $type => $apps) {
				foreach($apps as $version => $_data) {
					if(isset($_data["file"])) {
						$appdata = self::getByType($type, $dir . $_data["file"])->getPackageInfo();
						$appdata["file"] = $dir . $_data["file"];
						$appdata["plist_type"] = $type;
						if($appdata && isset($appdata["installType"]) && $appdata["installType"] == "update" && (!isset($appdata["installable"]) || $appdata["installable"]) && goma_version_compare($appdata["version"], $appdata["installed_version"], ">")) {
							if(isset($updates[$type])) {
								if(goma_version_compare($updates[$type]["version"], $version, "<")) {
									$updates[$type] = $appdata;
								}
							} else {
								$updates[$type] = $appdata;
							}
						}
					} else {
						foreach($_data as $app => $__data) {
							if(isset($__data["file"])) {
								$appdata = self::getByType($type, $dir . $__data["file"])->getPackageInfo();
								$appdata["file"] = $dir . $__data["file"];
								$appdata["plist_type"] = $type;
								if($appdata && isset($appdata["installType"]) && $appdata["installType"] == "update" && (!isset($appdata["installable"]) || $appdata["installable"]) && goma_version_compare($appdata["version"], $appdata["installed_version"], ">")) {
									if(isset($updates[$app])) {
										if(goma_version_compare($updates[$version]["version"], $version, "<")) {
											$updates[$version] = $appdata;
										}
									} else {
										$updates[$version] = $appdata;
									}
								}
							}
						}
					}
				}
			}
			
			
			// app
			$app = ClassInfo::$appENV["app"]["name"];
			if($data = self::getAppStoreInfo($app, null, ClassInfo::appVersion())) {
				$data["installed_version"] = ClassInfo::appVersion();
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
			
			// framework
			$app = ClassInfo::$appENV["framework"]["name"];
			if($data = self::getAppStoreInfo($app, null, GOMA_VERSION . "-" . BUILD_VERSION)) {
				$data["installed_version"] = GOMA_VERSION . "-" . BUILD_VERSION;
				$data["appinfo"]["autor"] = $data["autor"];
				$data["AppStore"] = $data["download"];
				
				if(isset($updates[$app])) {
					if(goma_version_compare($updates[$app]["version"], $data["version"], "<=")) {
						$updates[$app] = $data;
					}
				} else if(goma_version_compare($data["version"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
					$updates[$app] = $data;
				}
			}
		
			if(isset(ClassInfo::$appENV["expansion"]) && ClassInfo::$appENV["expansion"]) {
				// expansions
				foreach(ClassInfo::$appENV["expansion"] as $app => $data) {
					if($data = self::getAppStoreInfo($app, null, ClassInfo::expVersion($app))) {
						$data["installed_version"] = ClassInfo::expVersion($app);
						$data["appinfo"]["autor"] = $data["autor"];
						$data["AppStore"] = $data["download"];
						
						if(isset($updates[$app])) {
							if(goma_version_compare($updates[$app]["version"], $data["version"], "<=")) {
								$updates[$app] = $data;
							}
						} else if(goma_version_compare($data["version"], ClassInfo::expVersion($app), ">")) {
							$updates[$app] = $data;
						}
					}
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
	public function listUpdatablePackages() {
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
		
		if(strpos(@file_get_contents("http://goma-cms.org"), "<html")) {
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
		
		$url = "http://goma-cms.org/apps/api/v1/json/app/" . $name;
		
		if(isset($version)) {
			$url .= "/" . $version;
		}
		
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
		if(file_exists(FRAMEWORK_ROOT . "installer/data/apps/.index-db")) {
			$dir = FRAMEWORK_ROOT . "installer/data/apps/";
			$data = unserialize(file_get_contents(FRAMEWORK_ROOT . "installer/data/apps/.index-db"));
			
			$updates = array();
			
			foreach($data["packages"] as $type => $apps) {
				foreach($apps as $version => $_data) {
					if(isset($_data["file"])) {
						$appdata = self::getByType($type, $dir . $_data["file"])->getPackageInfo();
						$appdata["file"] = $dir . $_data["file"];
						$appdata["plist_type"] = $type;
						if($appdata && (!isset($appdata["installType"]) || $appdata["installType"] != "update") && (!isset($appdata["installable"]) || $appdata["installable"])) {
							if(isset($updates[$type])) {
								if(goma_version_compare($updates[$type]["version"], $version, "<")) {
									$updates[$type] = $appdata;
								}
							} else {
								$updates[$type] = $appdata;
							}
						}
					} else {
						foreach($_data as $app => $__data) {
							if(isset($__data["file"])) {
								$appdata = self::getByType($type, $dir . $__data["file"])->getPackageInfo();
								$appdata["file"] = $dir . $__data["file"];
								$appdata["plist_type"] = $type;
								if($appdata && (!isset($appdata["installType"]) || $appdata["installType"] != "update") && (!isset($appdata["installable"]) || $appdata["installable"])) {
									if(isset($updates[$app])) {
										if(goma_version_compare($updates[$version]["version"], $version, "<")) {
											$updates[$version] = $appdata;
										}
									} else {
										$updates[$version] = $appdata;
									}
								}
							}
						}
					}
				}
			}
			
			return $updates;
		} else {
			return array();
		}
	}
}

/**
 * represents the framework
 *
 *@name G_FrameworkSoftwareType
*/
class G_FrameworkSoftwareType extends G_SoftwareType {
	/**
	 * type is "framework"
	 *
	 *@name type
	 *@access public
	*/
	public static $type = "framework";
	
	/**
	 * installs the framework
	 * in this case we always upgrade the framework
	 *
	 *@name getInstallInfo
	 *@access public
	*/
	public function getInstallInfo($forceInstall = false) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("data/system/info.plist");
		
		$data = array("filename" => basename($this->file), "installType" => "update");
		if(isset($info["type"]) && $info["type"] == "framework") {
			
			$dir = FRAMEWORK_ROOT . "temp/" . md5($this->file);
			
			FileSystem::requireDir($dir);
			
			$data["type"] = lang("update_framework");
			$data["version"] = $info["version"];
			$data["installed"] = GOMA_VERSION . "-" . BUILD_VERSION;
			
			if(!goma_version_compare(GOMA_VERSION . "-" . BUILD_VERSION, $info["version"], "<=")) {
				$data["installable"] = false;
				$data["error"] = lang("update_version_error");
				return $data;
			}
			
			/*if(isset($appInfo["required_version"]) && goma_version_compare($appInfo["requiredVersion"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
				$data["installable"] = false;
				$data["error"] = lang("update_version_newer_required") . " <strong>".$appInfo["requiredVersion"]."</strong>";
				return $data;
			}*/
			
			if(!isset($info["isDistro"])) {
				return false;
			}
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
			
			// now check permissions
			$db = array_keys($gfs->getDB());
			$db = array_filter($db, create_function('$val', 'return substr($val, 0, '.strlen('data/').') == "data/";'));
			
			$db = array_map(create_function('$val', 'return substr($val, 5);'), $db);
			if(!FileSystem::checkMovePermsByList($db, ROOT)) {
				$data["error"] = lang("permission_error") . '('.convert::raw2text(FileSystem::errFile()).')';
				$data["installable"] = false;
				return $data;
			}
			
			$data["installable"] = true;
			
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); }'
			);
			
			/*if($gfs->exists(".preflight")) {
				$gfs->writeToFileSystem(".preflight", $dir . "/.preflight");
				$data["preflight"][] = $dir . "/.preflight";
			}
			
			if($gfs->exists(".postflight")) {
				$gfs->writeToFileSystem(".postflight", $dir . "/.postflight");
				$data["postflight"][] = $dir . "/.postflight";
			}*/
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/data/",
				"destination"	=> ROOT
			);
			
			// don't recheck permissions
			$data["permCheck"] = false;
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			/*if($gfs->exists(".getinstallinfo")) {
				$file = FRAMEWORK_ROOT . "temp/" . md5($this->file . ".installInfo") . ".php";
				$gfs->writeToFileSystem(".getinstallinfo", $file);
				include($file);
				@unlink($file);
			}*/
			
			return $data;
		} else {
			return false;
		}
	}
	
	/**
	 * gets package info
	 *
	 *@name getPackageInfo
	 *@access public
	*/
	public function getPackageInfo() {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("data/system/info.plist");
		
		if(!$appInfo)
			return false;
		
		$data = array("filename" => basename($this->file), "installType" => "update","version" => $info["version"]);
		
		$data["type"] = lang("update_framework");
		$data["title"] = "Goma " . $data["type"];
		
		$data["installed_version"] = GOMA_VERSION . "-" . BUILD_VERSION;
		
		$temp = "system/temp/" . basename($appInfo["icon"]) . "-" . md5($appInfo["name"]) . substr($appInfo["icon"], strrpos($appInfo["icon"], "."));
		$gfs->writeToFileSystem("data/system/" . $appInfo["icon"], $temp);
		$data["icon"] = $temp;
		
		$data["appInfo"] = $appInfo;
		
		if(isset($info["changelog"]))
			$data["changelog"] = $info["changelog"];
		
		if(isset($info["type"]) && $info["type"] == "framework") {
			if(!goma_version_compare(GOMA_VERSION . "-" . BUILD_VERSION, $info["version"], "<=")) {
				$data["installable"] = false;
				$data["error"] = lang("update_version_error");
				return $data;
			}
			
			return $data;
		} else {
			return false;
		}
	}
	
	/**
	 * sets the package info:
	 * version
	 * changelog
	 * icon
	*/
	public function setPackageInfo($data) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("data/system/info.plist");
		
		if(isset($data["version"])) {
			$info["version"] = $data["version"];
			if(isset($appInfo["build"])) {
				if(strpos($data["version"], "-")) {
					$build = substr($data["version"], strrpos($data["version"], "-") + 1);
					$version = substr($data["version"], 0, strrpos($data["version"], "-"));
					$appInfo["build"] = $build;
					$appInfo["version"] = $version;
				} else {
					$appInfo["version"] = $data["version"];
				}
			} else {
				$appInfo["version"] = $data["version"];
			}	
		}
		
		if(isset($data["changelog"])) {
			$info["changelog"] = $data["changelog"];
		}
		
		if(isset($data["icon"])) {
			$newExt = substr($data["icon"], strrpos($data["icon"], ".") + 1);
			if(substr($appInfo["icon"], strrpos($appInfo["icon"], ".") + 1) == $newExt) {
				$gfs->write("data/system/" . $appInfo["icon"], file_get_contents($data["icon"]));
			} else {
				$gfs->write("data/system/" . $appInfo["icon"] . $newExt , file_get_contents($data["icon"]));
				$appInfo["icon"] = $appInfo["icon"] . $newExt;
			}
		}
		
		$gfs->writePlist("info.plist", $info);
		$gfs->writePlist("data/system/info.plist", $appInfo);
		
		return true;
	}
	
	/**
	 * restores the framework
	 *
	 *@name getRestoreInfo
	 *@access public
	*/
	public function getRestoreInfo($forceCompleteRestore = false) {
		return false;
	}
	
	/**
	 * generates a distro
	 *
	 *@name backup
	 *@access public
	*/
	public static function backup($file, $name, $changelog = null) {
		$frameworkplist = new CFPropertyList(FRAMEWORK_ROOT . "info.plist");
		$frameworkenv = $frameworkplist->toArray();

		// if we are currently building the file, don't delete
		if(!GFS_Package_Creator::wasPacked($file)) {
			if(file_exists($file)) {
				@unlink($file);
			}
		}
		
		$gfs = new GFS_Package_Creator($file);

		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("framework"));
		$dict->add("version", new CFString(GOMA_VERSION . "-" . BUILD_VERSION));
		$dict->add("created", new CFDate(NOW));
		$dict->add("isDistro", new CFString("1"));
		$dict->add("name", new CFString(ClassInfo::$appENV["framework"]["name"]));
		
		if(isset($changelog)) {
			$dict->add("changelog", new CFString($changelog));
		}
		
		$gfs->write("info.plist", $plist->toXML());
		
		
		if(!GFS_Package_Creator::wasPacked($file)) {
			$gfs->setAutoCommit(false);
			$gfs->add(FRAMEWORK_ROOT, "/data/system/", array("temp", LOG_FOLDER, "/installer/data", "version.php"));
			$gfs->add(ROOT . "images/", "/data/images/", array("resampled"));
			$gfs->add(ROOT . "languages/", "/data/languages/");
			$gfs->commit();
		}	
		
		// add some files
		$gfs->addFromFile(ROOT . "index.php", "/data/index.php");
		//$gfs->addFromFile(ROOT . ".htaccess", "/data/.htaccess");
		$gfs->close();
		
		return true;
	}
	
	/**
	 * returns the current framework-version with gfs
	 *
	 *@name generateDistroFileName
	 *@access public
	*/
	public static function generateDistroFileName($name) {
		return "framework." . GOMA_VERSION . "-" . BUILD_VERSION . ".gfs";
	}
	
	/**
	 * builds a framework
	 *
	 *@name buildDistro
	 *@access public
	*/
	public static function buildDistro($file, $name) {
		if(isset($_SESSION["finalizeFrameworkDistro"]))
			return self::finalizeDistro($_SESSION["finalizeFrameworkDistro"]);
		
		if(file_exists($file))
			@unlink($file);
		
		$form = new Form(new G_FrameworkSoftwareType(null), "buildDistro", array(
			new HiddenField("file", $file),
			new HTMLField("title", "<h1>".lang("update_framework")."</h1><h3>".lang("distro_build")."</h3>"),
			$version = new TextField("version", lang("version"), GOMA_VERSION . "-" . BUILD_VERSION),
			new Textarea("changelog", lang("distro_changelog")),
			
			/*new HidableFieldSet("advanced", array(
				new Textarea("preflight", lang("install_option_preflight")),
				new Textarea("postflight", lang("install_option_postflight")),
				new Textarea("script_info", lang("install_option_getinfo"))
			), lang("install_advanced_options", "advanced install-options"))*/
		), array(
			new LinkAction("cancel", lang("cancel"), ROOT_PATH . BASE_SCRIPT . "dev/buildDistro"),
			new FormAction("submit", lang("download"), "finalizeDistro")
		));
		
		$version->disable();
		
		return $form->render();
	}
	
	/**
	 * finalizes the build
	 *
	 *@name finalizeDistro
	 *@access public
	*/
	public function finalizeDistro($data) {
		$_SESSION["finalizeFrameworkDistro"] = $data;
		
		$changelog = (empty($data["changelog"])) ? null : $data["changelog"];
		self::backup($data["file"], "framework", $changelog);
		
		
		$gfs = new GFS($data["file"]);
		if(isset($data["preflight"])) {
			$gfs->addFile(".preflight", "<?php " . $data["preflight"]);
		}
		
		if(isset($data["postflight"])) {
			$gfs->addFile(".postflight", "<?php " . $data["postflight"]);
		}
		
		if(isset($data["script_info"])) {
			$gfs->addFile(".getinstallinfo", "<?php " . $data["script_info"]);
		}
		
		$gfs->close();
		
		unset($_SESSION["finalizeFrameworkDistro"]);
		
		return true;
	}
	
	/**
	 * 
	*/
	
	/**
	 * lists installed software
	 *
	 *@name listSoftware
	 *@access public
	*/
	public static function listSoftware() {
		return array(
			"framework"	=> array(
				"title" 		=> "Goma " . lang("update_framework", "framework"),
				"version"		=> GOMA_VERSION . "-" . BUILD_VERSION,
				"icon"			=> "system/" . ClassInfo::$appENV["framework"]["icon"],
				"canDisable"	=> false
			)
		);
	}
}

/**
 * represents the installed application
 *
 *@name G_AppSoftwareType
*/
class G_AppSoftwareType extends G_SoftwareType {
	/**
	 * type is backup
	 *
	 *@name type
	 *@access public
	*/
	public static $type = "backup";
	
	/**
	 * stores the data from the form in $formResult
	 *
	 *@name saveFormData
	 *@access public
	*/
	public function saveFormData($data) {
		$_data = $data["installData"];
		
		if(!is_array($_data["postflightCode"])) {
			$_data["postflightCode"] = array($_data["postflightCode"]);
		}
		
		if(defined("PROJECT_LOAD_DIRECTORY") && PROJECT_LOAD_DIRECTORY != $data["folder"]) {
			$_data["postflightCode"][] = "<?php removeProject(".var_export(PROJECT_LOAD_DIRECTORY, true).");";
		}
		
		if(isset($data["type"]) && $data["type"] == "copyconfig") {
			$data["folder"] = APPLICATION;
		}
		
		$_data["installFolders"]["destination"] = ROOT . $data["folder"];
		
		$info = array();
		
		if(!isset($data["type"]) || $data["type"] != "copyconfig") {
			$info["db"] = array(
				"user"	=> $data["dbuser"],
				"db"	=> $data["dbname"],
				"pass"	=> $data["dbpwd"],
				"host"	=> $data["dbhost"],
				"prefix"=> $data["tableprefix"]
			);
		}
		
		$domain = isset($data["domain"]) ? $data["domain"] : null;
		
		// write config
		$_data["postflightCode"][] = '<?php writeProjectConfig('.var_export($info, true).', '.var_export($data["folder"], true).'); setProject('.var_export($data["folder"], true).', '.var_export($domain, true).');';
		
		// write version file
		$_data["postflightCode"][] = '<?php FileSystem::write('.var_export($data["folder"] . "/version.php", true).', "<?php \$version = '.var_export($_data["version"], true).';");';
		
		return $_data;
	}
	
	/**
	 * validates the installation
	 *
	 *@name validateInstall
	*/
	public function validateInstall($obj) {
		$result = $obj->form->result;
		$notAllowedFolders = array(
			"dev", "admin", "pm", "system"
		);
		if(file_exists(ROOT . $result["folder"]) || in_array($result["folder"], $notAllowedFolders) || !preg_match('/^[a-z0-9_]+$/', $result["folder"])) {
			return lang("install.folder_error");
		}
		
		if(isset($result["dbuser"])) {
			if(!SQL::test(SQL_DRIVER, $result["dbuser"], $result["dbname"], $result["dbpwd"], $result["dbhost"])) {
				return lang("install.sql_error");
			}
		}
		
		return true;
	}
	
	/**
	 * installs the framework
	 *
	 *@name getInstallInfo
	 *@access public
	*/
	public function getInstallInfo($forceInstall = false) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("backup/info.plist");
		
		$data = array("filename" => basename($this->file), "type" => lang("update_app"));
		
		if(!isset($info["version"]))
			return false;
		
		// check if we have a full backup
		if($info["backuptype"] != "full") {
			$data["installable"] = false;
			
			return $data;
		}
		
		// check if we have the correct framework-version
		if(goma_version_compare($info["framework_version"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
			$data["error"] = lang("update_frameworkError");
			$data["installable"] = false;
			
			return $data;
		}
		
		$dir = FRAMEWORK_ROOT . "temp/" . md5($this->file);
		
		FileSystem::requireDir($dir);
		
		/*
		if($gfs->exists(".preflight")) {
			$gfs->writeToFileSystem(".preflight", $dir . "/.preflight");
			$data["preflight"][] = $dir . "/.preflight";
		}
		
		if($gfs->exists(".postflight")) {
			$gfs->writeToFileSystem(".postflight", $dir . "/.postflight");
			$data["postflight"][] = $dir . "/.postflight";
		}*/
		
		$data["version"] = $info["version"];
		
		// check if we use install-method
		if($forceInstall || $appInfo["name"] != ClassInfo::$appENV["app"]["name"]) {
			// make install
			$data["installType"] = "install";
			
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); } $dbgfs = new GFS('.var_export($dir, true).' . "/database.sgfs"); $dbgfs->unpack('.var_export($dir . "/backup/" . getPrivateKey() . "-install/",true) .', "/database");'
			);
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/backup/"
			);
			
			$data["installable"] = true;
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
			
			// find a good folder-name
			if( defined("PROJECT_LOAD_DIRECTORY") && !file_exists(ROOT . PROJECT_LOAD_DIRECTORY)) {
				$default = PROJECT_LOAD_DIRECTORY;
			} else if(!file_exists(ROOT . "mysite")) {
				$default = "mysite";
			} else if(!file_exists(ROOT . "myproject")) {
				$default = "myproject";
			} else {
				$default = null;
			}
			
			if(defined("DOMAIN_LOAD_DIRECTORY")) {
				if(file_exists(ROOT . DOMAIN_LOAD_DIRECTORY)) {
					@rename(ROOT . DOMAIN_LOAD_DIRECTORY, ROOT . DOMAIN_LOAD_DIRECTORY . time());
				}
				
				if(DOMAIN_LOAD_DIRECTORY != "mysite") {
					$default = DOMAIN_LOAD_DIRECTORY;
					$disableDir = true;
				}
			}
			
			// get information for config.php
			$form = new Form($this, "installinfos", array(
				$folder = new TextField("folder", lang("install.folder"), $default),
				$host = new TextField("dbhost", lang("install.db_host"), "localhost"),
				new TextField("dbuser", lang("install.db_user")),
				new PasswordField("dbpwd", lang("install.db_password")),
				new TextField("dbname", lang("install.db_name")),
				$tableprefix = new TextField("tableprefix", lang("install.table_prefix"), "".$appInfo["name"]."_"),
				new HiddenField("installData", $data)
			), array(
				new FormAction("submit", lang("install.install"), "saveFormData")
			));
			
			$apps = ListApplications();
			if(defined("DOMAIN_LOAD_DIRECTORY") || (count($apps) > 0 && $apps[0]["directory"] != PROJECT_LOAD_DIRECTORY)) {
				$form->add(new TextField("domain", lang("domain")));
			}
			
			if(isset($disableDir)) {
				$folder->disable();
			}
			
			$form->addValidator(new RequiredFields(array("folder", "dbhost", "dbuser", "dbname")), "fields");
			$form->addValidator(new FormValidator(array($this, "validateInstall")), "validateInstall");
			
			$host->info = lang("install.db_host_info");
			$folder->info = lang("install.folder_info");
			
			if($info["DB_PREFIX"] != "{!#PREFIX}") {
				$tableprefix->value = $info["DB_PREFIX"];
				$tableprefix->disable();
			}
			
			return $form->render();
			
		} else {
			// update installed software
			$data["installType"] = "update";
			$data["installed"] = ClassInfo::AppVersion();
			
			/*if(isset($appInfo["require_version"]) && goma_version_compare($appInfo["require_version"], ClassInfo::appVersion(), ">")) {
				$data["error"] = lang("update_version_newer_required") . " " . $appInfo["require_version"];
				$data["installable"] = false;
				
				return $data;
			}*/
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
			
			$db = array_keys($gfs->getDB());
			
			$db = array_filter($db, create_function('$val', 'return substr($val, 0, '.strlen('backup/').') == "backup/";'));
			
			$db = array_map(create_function('$val', 'return substr($val, 7);'), $db);
			
			if(!FileSystem::checkMovePermsByList($db, ROOT . CURRENT_PROJECT . "/")) {
				$data["error"] = lang("permission_error");
				$data["installable"] = false;
				return $data;
			}
			
			$data["permCheck"] = true;
			
			$data["installable"] = true;
			
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); }'
			);
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/backup/",
				"destination"	=> ROOT . CURRENT_PROJECT . "/"
			);
			
			/*if($gfs->exists(".getinstallinfo")) {
				$file = FRAMEWORK_ROOT . "temp/" . md5($this->file . ".installInfo") . ".php";
				$gfs->writeToFileSystem(".getinstallinfo", $file);
				include($file);
				@unlink($file);
			}*/
			
			return $data;
		}
	}
	
	/**
	 * gets package info
	 *
	 *@name getPackageInfo
	 *@access public
	*/
	public function getPackageInfo() {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("backup/info.plist");
		
		if(!$appInfo)
			return false;
		
		$data = array("filename" => basename($this->file), "type" => lang("update_app"));
		
		if(isset($appInfo["icon"])) {
			$temp = "system/temp/" . basename($appInfo["icon"]) . "-" . md5($appInfo["name"]) . substr($appInfo["icon"], strrpos($appInfo["icon"], "."));
			$gfs->writeToFileSystem("backup/" . $appInfo["icon"], $temp);
			$data["icon"] = $temp;
		}
		
		if(isset($info["changelog"]))
			$data["changelog"] = $info["changelog"];
		
		$data["appInfo"] = $appInfo;
		
		// check if we have a full backup
		if($info["backuptype"] != "full") {
			$data["installable"] = false;
			
			return $data;
		}
		
		// check if we have the correct framework-version
		if(goma_version_compare($info["framework_version"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
			$data["error"] = lang("update_frameworkError");
			$data["installable"] = false;
			
			return $data;
		}
		$data["framework_version"] = $info["framework_version"];
		
		// check if we use install-method
		if($appInfo["name"] != ClassInfo::$appENV["app"]["name"]) {
		
		} else {
			$data["installType"] = "update";
			$data["installed_version"] = ClassInfo::AppVersion();
		}
		
		if(isset($appInfo["title"]))
			$data["title"] = $appInfo["title"];
		else
			$data["title"] = ClassInfo::$appENV["app"]["name"];
		
		$data["version"] = $info["version"];
		
		return $data;
	}
	
	/**
	 * sets the package info:
	 * version
	 * changelog
	 * icon
	 * title
	 * required framework-version: framework_version
	*/
	public function setPackageInfo($data) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("backup/info.plist");
		
		if(isset($data["version"])) {
			$info["version"] = $data["version"];
			if(isset($appInfo["build"])) {
				if(strpos($data["version"], "-")) {
					$build = substr($data["version"], strrpos($data["version"], "-") + 1);
					$version = substr($data["version"], 0, strrpos($data["version"], "-"));
					$appInfo["build"] = $build;
					$appInfo["version"] = $version;
				} else {
					$appInfo["version"] = $data["version"];
				}
			} else {
				$appInfo["version"] = $data["version"];
			}	
		}
		
		if(isset($data["changelog"])) {
			$info["changelog"] = $data["changelog"];
		}
		
		if(isset($data["icon"])) {
			$newExt = substr($data["icon"], strrpos($data["icon"], ".") + 1);
			if(substr($appInfo["icon"], strrpos($appInfo["icon"], ".") + 1) == $newExt) {
				$gfs->write("backup/" . $appInfo["icon"], file_get_contents($data["icon"]));
			} else {
				$gfs->write("backup/" . $appInfo["icon"] . $newExt , file_get_contents($data["icon"]));
				$appInfo["icon"] = $appInfo["icon"] . $newExt;
			}
		}
		
		if(isset($data["title"])) {
			$appInfo["title"] = $data["title"];
		}
		
		if(isset($data["framework_version"]))
			$info["framework_version"] = $data["framework_version"];
		
		$gfs->writePlist("info.plist", $info);
		$gfs->writePlist("backup/info.plist", $appInfo);
		
		return true;
	}
	
	/**
	 * stores the data from the form in $formResult for getRestoreInfo if we make a restore within a application
	 *
	 *@name saveRFormData
	 *@access public
	*/
	public function saveRFormData($data) {
		$_data = $data["installData"];
		
		if($_data["type"] == "copyconfig") {
			if(!is_array($_data["postflightCode"])) {
				$_data["postflightCode"] = array($_data["postflightCode"]);
			}
			
			$_data["installFolders"]["destination"] = ROOT . APPLICATION;
			
			$file = ROOT . md5(APPLICATION . "config" . time());
			
			$_data["preflightCode"][] = '<?php @copy(' . var_export(ROOT . APPLICATION . "/config.php", true) . ', '.var_export($file, true) . '); FileSystem::rmdir('.var_export(ROOT . APPLICATION, true).');';
			
			$_data["postflightCode"][] = '<?php  rename('.var_export($file, true).', '.var_export(ROOT . APPLICATION, true).');';

		} else {
		
			if(!is_array($_data["postflightCode"])) {
				$_data["postflightCode"] = array($_data["postflightCode"]);
			}
			
			$_data["installFolders"]["destination"] = ROOT . $data["folder"];
			
			$info["db"] = array(
				"user"	=> $data["dbuser"],
				"db"	=> $data["dbname"],
				"pass"	=> $data["dbpwd"],
				"host"	=> $data["dbhost"],
				"prefix"=> $data["tableprefix"]
			);
			
			$domain = isset($data["domain"]) ? $data["domain"] : null;
			
			$_data["postflightCode"][] = '<?php writeProjectConfig('.var_export($info, true).', '.var_export($data["folder"], true).'); setProject('.var_export($data["folder"], true).', '.var_export($domain, true).');';
		}
		
		return $_data;
	}
	
	/**
	 * validates the restore
	 *
	 *@name validateInstall
	*/
	public function validateRestore($obj) {
		$result = $obj->form->result;
		if($result["type"] != "copyconfig") {
			return $this->validateInstall($obj);
		} else {
			return true;
		}
	}

	
	/**
	 * restores the framework
	 *
	 *@name getRestoreInfo
	 *@access public
	*/
	public function getRestoreInfo($forceCompleteRestore = false) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("backup/info.plist");
		
		$data = array("filename" => basename($this->file), "type" => lang("update_app"));
		
		if(!isset($info["version"]))
			return false;
		
		$data["version"] = $info["version"];
		
		// check if we have a full backup
		if($info["backuptype"] != "full") {
			$data["installable"] = false;
			
			return $data;
		}
		
		// check if we have the correct framework-version
		if(goma_version_compare($info["framework_version"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
			$data["error"] = lang("update_frameworkError");
			$data["installable"] = false;
			
			return $data;
		}
		
		$dir = FRAMEWORK_ROOT . "temp/" . md5($this->file);
		
		FileSystem::requireDir($dir);
		
		
		
		// check if we use install-method
		if($forceCompleteRestore || $appInfo["name"] != ClassInfo::$appENV["app"]["name"] || !file_exists(ROOT . APPLICATION . "/config.php")) {
			// make install
			$data["installType"] = "install";
			
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); } $dbgfs = new GFS('.var_export($dir, true).' . "/database.sgfs"); $dbgfs->unpack('.var_export($dir . "/backup/" . getPrivateKey() . "-install/",true) .', "/database");'
			);
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/backup/"
			);
			
			$data["installable"] = true;
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
			
			// find a good folder-name
			if( defined("APPLICATION") && !file_exists(ROOT . APPLICATION)) {
				$default = APPLICATION;
			} else if( defined("PROJECT_LOAD_DIRECTORY") && !file_exists(ROOT . PROJECT_LOAD_DIRECTORY)) {
				$default = PROJECT_LOAD_DIRECTORY;
			} else if(!file_exists(ROOT . "mysite")) {
				$default = "mysite";
			} else if(!file_exists(ROOT . "myproject")) {
				$default = "myproject";
			} else {
				$default = null;
			}
			
			// get information for config.php
			$form = new Form($this, "installinfos", array(
				$folder = new TextField("folder", lang("install.folder"), $default),
				$host = new TextField("dbhost", lang("install.db_host"), "localhost"),
				new TextField("dbuser", lang("install.db_user")),
				new PasswordField("dbpwd", lang("install.db_password")),
				new TextField("dbname", lang("install.db_name")),
				$tableprefix = new TextField("tableprefix", lang("install.table_prefix"), "".$appInfo["name"]."_"),
				new HiddenField("installData", $data)
			), array(
				new FormAction("submit", lang("restore"), "saveFormData")
			));
			
			if(defined("DOMAIN_LOAD_DIRECTORY")) {
				$form->add(new TextField("domain", lang("domain")));
			}
			
			if(isset($disableDir)) {
				$folder->disable();
			}
			
			$form->addValidator(new RequiredFields(array("folder", "dbhost", "dbuser", "dbname")), "fields");
			$form->addValidator(new FormValidator(array($this, "validateInstall")), "validateResotre");
			
			$host->info = lang("install.db_host_info");
			$folder->info = lang("install.folder_info");
			
			if($info["DB_PREFIX"] != "{!#PREFIX}") {
				$tableprefix->value = $info["DB_PREFIX"];
				$tableprefix->disable();
			}
			
			return $form->render();
		} else {
			// make install
			$data["installType"] = "install";
			
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); } $dbgfs = new GFS('.var_export($dir, true).' . "/database.sgfs"); $dbgfs->unpack('.var_export($dir . "/backup/" . getPrivateKey() . "-install/",true) .', "/database"); copy('.var_export(ROOT . APPLICATION . "/config.php", true).', '.var_export($dir . "/backup/config.php", true).');'
			);
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/backup/"
			);
			
			$data["installable"] = true;
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
			
			// find a good folder-name
			if( defined("APPLICATION") && !file_exists(ROOT . APPLICATION)) {
				$default = APPLICATION;
			} else if( defined("PROJECT_LOAD_DIRECTORY") && !file_exists(ROOT . PROJECT_LOAD_DIRECTORY)) {
				$default = PROJECT_LOAD_DIRECTORY;
			} else if(!file_exists(ROOT . "mysite")) {
				$default = "mysite";
			} else if(!file_exists(ROOT . "myproject")) {
				$default = "myproject";
			} else {
				$default = null;
			}
			
			// get information for config.php
			$form = new Form($this, "installinfos", array(
				new HTMLField("head", '<div style="padding: 0 5px;"><h3>'.lang("restore").': '.convert::raw2text(basename($this->file)).'</h3></div>'),
				new ObjectRadioButton("type", lang("restoreType"), array(
					"copyconfig"	=> lang("restore_currentapp"),
					"new"			=> array(
						lang("restore_newapp"),
						"newconfig"
					)
				), "copyconfig"),
				new FieldSet("newconfig", array(
					$folder = new TextField("folder", lang("install.folder"), $default),
					$host = new TextField("dbhost", lang("install.db_host"), "localhost"),
					new TextField("dbuser", lang("install.db_user")),
					new PasswordField("dbpwd", lang("install.db_password")),
					new TextField("dbname", lang("install.db_name")),
					$tableprefix = new TextField("tableprefix", lang("install.table_prefix"), "".$appInfo["name"]."_"),
					new HiddenField("installData", $data),
					new TextField("domain", lang("domain"))
				))
			), array(
				new FormAction("submit", lang("restore"), "saveFormData")
			));
			
			if(isset($disableDir)) {
				$folder->disable();
			}
			
			$form->addValidator(new RequiredFields(array("type")), "fields");
			$form->addValidator(new FormValidator(array($this, "validateRestore")), "validateRestore");
			
			$host->info = lang("install.db_host_info");
			$folder->info = lang("install.folder_info");
			
			if($info["DB_PREFIX"] != "{!#PREFIX}") {
				$tableprefix->value = $info["DB_PREFIX"];
				$tableprefix->disable();
			}
			
			return $form->render();
		}
	}
	
	/**
	 * generates a distro
	 *
	 *@name backup
	 *@access public
	*/
	public static function backup($file, $name, $changelog = null) {
		
		$tables = ClassInfo::Tables("user");
		$tables = array_merge($tables, ClassInfo::Tables("history"));
		$tables = array_merge($tables, ClassInfo::Tables("permission"));
		if(isset(ClassInfo::$appENV["app"]["excludeModelsFromDistro"])) {
			foreach(ClassInfo::$appENV["app"]["excludeModelsFromDistro"] as $model) {
				$tables = array_merge($tables, ClassInfo::Tables($model));
			}
		}
		
		$excludeFiles = isset(ClassInfo::$appENV["app"]["excludeFiles"]) ? ClassInfo::$appENV["app"]["excludeFiles"] : array();
		
		Backup::generateBackup($file, $excludeFiles, $tables, '{!#PREFIX}', !isset($_GET["dontIncludeTPL"]), ClassInfo::$appENV["app"]["requireFrameworkVersion"], $changelog);
		
		return true;
	}
	
	/**
	 * returns the current framework-version with gfs
	 *
	 *@name generateDistroFileName
	 *@access public
	*/
	public static function generateDistroFileName($name) {
		return ClassInfo::$appENV["app"]["name"] . "." . ClassInfo::appVersion() . ".gfs";
	}
	
	/**
	 * building the distro
	*/
	public static function buildDistro($file, $name) {
		if(isset($_SESSION["finalizeCMSDistro"]))
			return self::finalizeDistro($_SESSION["finalizeCMSDistro"]);
		
		if(file_exists($file))
			@unlink($file);
		
		$title = isset(ClassInfo::$appENV["app"]["title"]) ? ClassInfo::$appENV["app"]["title"] : ClassInfo::$appENV["app"]["name"];
		
		$form = new Form(new G_AppSoftwareType(null), "buildDistro", array(
			new HiddenField("file", $file),
			new HTMLField("title", "<h1>".convert::raw2text($title)."</h1><h3>".lang("distro_build")."</h3>"),
			$version = new TextField("version", lang("version"), ClassInfo::appVersion()),
			new Textarea("changelog", lang("distro_changelog")),
			
			/*new HidableFieldSet("advanced", array(
				new Textarea("preflight", lang("install_option_preflight")),
				new Textarea("postflight", lang("install_option_postflight")),
				new Textarea("script_info", lang("install_option_getinfo"))
			), lang("install_advanced_options", "advanced install-options"))*/
		), array(
			new LinkAction("cancel", lang("cancel"), ROOT_PATH . BASE_SCRIPT . "dev/buildDistro"),
			new FormAction("submit", lang("download"), "finalizeDistro")
		));
		
		$version->disable();
		
		return $form->render();
	}
	
	/**
	 * finalizes the build
	 *
	 *@name finalizeDistro
	 *@access public
	*/
	public function finalizeDistro($data) {
		$_SESSION["finalizeCMSDistro"] = $data;
		
		$changelog = (empty($data["changelog"])) ? null : $data["changelog"];
		self::backup($data["file"], null, $changelog);
		
		$gfs = new GFS($data["file"]);
		if(isset($data["preflight"])) {
			$gfs->addFile(".preflight", "<?php " . $data["preflight"]);
		}
		
		if(isset($data["postflight"])) {
			$gfs->addFile(".postflight", "<?php " . $data["postflight"]);
		}
		
		if(isset($data["script_info"])) {
			$gfs->addFile(".getinstallinfo", "<?php " . $data["script_info"]);
		}
		
		$gfs->close();
		
		unset($_SESSION["finalizeCMSDistro"]);
		
		return true;
	}
	
	/**
	 * 
	*/
	
	/**
	 * lists installed software
	 *
	 *@name listSoftware
	 *@access public
	*/
	public static function listSoftware() {
		$data = array(
			ClassInfo::$appENV["app"]["name"]	=> array(
				"title" 		=> ClassInfo::$appENV["app"]["title"],
				"version"		=> ClassInfo::appVersion(),
				"canDisable"	=> false
			)
		);
		if(isset(ClassInfo::$appENV["app"]["icon"]) && ClassInfo::$appENV["app"]["icon"]) {
			$data[ClassInfo::$appENV["app"]["name"]]["icon"] = APPLICATION . "/" . ClassInfo::$appENV["app"]["icon"];
		}
		
		return $data;
	}
}


/**
 * represents the installed expansions
 *
 *@name G_ExpansionSoftwareType
*/
class G_ExpansionSoftwareType extends G_SoftwareType {
	/**
	 * type is backup
	 *
	 *@name type
	 *@access public
	*/
	public static $type = "expansion";
	
	/**
	 * installs the framework
	 *
	 *@name getInstallInfo
	 *@access public
	*/
	public function getInstallInfo($forceInstall = false) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("contents/info.plist");
		
		
		$data = array("filename" => basename($this->file), "type" => lang("update_expansion"));
		
		if(!isset($info["version"]))
			return false;
		
		// check if we have the correct framework-version
		if(isset($appInfo["requireFrameworkVersion"]) && goma_version_compare($appInfo["requireFrameworkVersion"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
			$data["error"] = lang("update_frameworkError");
			$data["installable"] = false;
			
			return $data;
		}
		
		$dir = FRAMEWORK_ROOT . "temp/" . md5($this->file);
		
		FileSystem::requireDir($dir);
		
		/*if($gfs->exists(".preflight")) {
			$gfs->writeToFileSystem(".preflight", $dir . "/.preflight");
			$data["preflight"][] = $dir . "/.preflight";
		}
		
		if($gfs->exists(".postflight")) {
			$gfs->writeToFileSystem(".postflight", $dir . "/.postflight");
			$data["postflight"][] = $dir . "/.postflight";
		}*/
		
		$data["version"] = $info["version"];
		
		if($forceInstall || !isset(ClassInfo::$appENV["expansion"][$appInfo["name"]])) {
			// let's install it
			// update installed software
			$data["installType"] = "install";
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
				
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); }'
			);
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			// write version file
			$_data["postflightCode"][] = '<?php FileSystem::write($data["installfolders"]["destination"] . "/version.php", "<?php $version = '.var_export($data["version"], true).';");';
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/contents/"
			);
			
			return $data;
		} else {
			// update installed software
			$data["installType"] = "update";
			$data["installed"] = ClassInfo::ExpVersion($appInfo["name"]);
			
			/*if(isset($appInfo["require_version"]) && goma_version_compare($appInfo["require_version"], ClassInfo::appVersion(), ">")) {
				$data["error"] = lang("update_version_newer_required") . " " . $appInfo["require_version"];
				$data["installable"] = false;
				
				return $data;
			}*/
			
			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];
			
			$db = array_keys($gfs->getDB());
			
			$db = array_filter($db, create_function('$val', 'return substr($val, 0, '.strlen('contents/').') == "contents/";'));
			
			$db = array_map(create_function('$val', 'return substr($val, 9);'), $db);
			
			if(!FileSystem::checkMovePermsByList($db, ClassInfo::getExpansionFolder($appInfo["name"]))) {
				$data["error"] = lang("permission_error");
				$data["installable"] = false;
				return $data;
			}
			
			$data["permCheck"] = true;
			
			$data["installable"] = true;
			
			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); }'
			);
			
			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);
			
			$data["installFolders"] = array(
				"source"		=> $dir . "/contents/",
				"destination"	=> ClassInfo::getExpansionFolder($appInfo["name"])
			);
			
			/*if($gfs->exists(".getinstallinfo")) {
				$file = FRAMEWORK_ROOT . "temp/" . md5($this->file . ".installInfo") . ".php";
				$gfs->writeToFileSystem(".getinstallinfo", $file);
				include($file);
				@unlink($file);
			}*/
			
			return $data;
		}
	}
	
	/**
	 * restores the framework
	 *
	 *@name getRestoreInfo
	 *@access public
	*/
	public function getRestoreInfo($forceCompleteRestore = false) {
		return false;
	}
	
	/**
	 * generates a distro
	 *
	 *@name backup
	 *@access public
	*/
	public static function backup($file, $name, $changelog = null) {
		if(!isset(ClassInfo::$appENV["expansion"][$name])) {
			return false;
		}
		
		$folder = ClassInfo::getExpansionFolder($name);
		
		if(!GFS_Package_Creator::wasPacked($file)) {
			if(file_exists($file)) {
				@unlink($file);
			}
		}
		
		$gfs = new GFS_Package_Creator($file);
		
		if(!GFS_Package_Creator::wasPacked($file)) {
			$gfs->add($folder, "/contents/", "", array("version.php"));
		}
		
		$plist = new CFPropertyList();
		$plist->add($dict = new CFDictionary());
		$dict->add("type", new CFString("expansion"));
		$dict->add("name", new CFString($name));
		$dict->add("version", new CFString(ClassInfo::expVersion($name)));
		$dict->add("created", new CFDate(NOW));
		$dict->add("isDistro", new CFString("1"));
		$dict->add("changelog", new CFString($changelog));
		
		$gfs->write("info.plist", $plist->toXML());
		$gfs->close();
		
		
		return true;
	}
	
	/**
	 * returns the current framework-version with gfs
	 *
	 *@name generateDistroFileName
	 *@access public
	*/
	public static function generateDistroFileName($name) {
		if(!isset(ClassInfo::$appENV["expansion"][$name])) {
			return false;
		}
		return $name . "." . ClassInfo::expVersion($name) . ".gfs";
	}
	
	/**
	 * building the distro
	*/
	public static function buildDistro($file, $name) {
		if(!isset(ClassInfo::$appENV["expansion"][$name])) {
			return false;
		}
		
		$title = isset(ClassInfo::$appENV["expansion"][$name]["title"]) ? ClassInfo::$appENV["expansion"][$name]["title"] : ClassInfo::$appENV["expansion"][$name]["name"];
		
		$form = new Form(new G_ExpansionSoftwareType(null), "buildDistro", array(
			new HiddenField("file", $file),
			new HiddenField("expName", $name),
			new HTMLField("title", "<h1>".convert::raw2text($title)."</h1><h3>".lang("distro_build")."</h3>"),
			$version = new TextField("version", lang("version"), ClassInfo::expVersion($name)),
			new Textarea("changelog", lang("distro_changelog")),
			
			/*new HidableFieldSet("advanced", array(
				new Textarea("preflight", lang("install_option_preflight")),
				new Textarea("postflight", lang("install_option_postflight")),
				new Textarea("script_info", lang("install_option_getinfo"))
			), lang("install_advanced_options", "advanced install-options"))*/
		), array(
			new LinkAction("cancel", lang("cancel"), ROOT_PATH . BASE_SCRIPT . "dev/buildDistro"),
			new FormAction("submit", lang("download"), "finalizeDistro")
		));
		
		$version->disable();
		
		return $form->render();
	}
	
	/**
	 * finalizes the build
	 *
	 *@name finalizeDistro
	 *@access public
	*/
	public function finalizeDistro($data) {
		$changelog = (empty($data["changelog"])) ? null : $data["changelog"];
		self::backup($data["file"], $data["expName"], $changelog);
		
		$gfs = new GFS($data["file"]);
		if(isset($data["preflight"])) {
			$gfs->addFile(".preflight", "<?php " . $data["preflight"]);
		}
		
		if(isset($data["postflight"])) {
			$gfs->addFile(".postflight", "<?php " . $data["postflight"]);
		}
		
		if(isset($data["script_info"])) {
			$gfs->addFile(".getinstallinfo", "<?php " . $data["script_info"]);
		}
		
		$gfs->close();
		
		return true;
	}
	
	/**
	 * 
	*/
	
	/**
	 * lists installed software
	 *
	 *@name listSoftware
	 *@access public
	*/
	public static function listSoftware() {
		$arr = array();
		// generate for expansions
		if(isset(ClassInfo::$appENV["expansion"])) {
			foreach(ClassInfo::$appENV["expansion"] as $name => $data) {
				if(isset($data["build"]))
					$data["version"] = $data["version"] . "-" . $data["build"];
				else
					$data["version"] = $data["version"];
					
				if(isset($data["icon"]) && $data["icon"])
					$data["icon"] = ClassInfo::getExpansionFolder($name) . "/" . $data["icon"];
				
				if(!isset($data["title"]))
					$data["title"] = $name;
				
				$data["canDisable"] = true;
				
				$arr[$name] = $data;
			}
		}
		
		return $arr;
	}
	
	/**
	 * gets package info
	 *
	 *@name getPackageInfo
	 *@access public
	*/
	public function getPackageInfo() {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("contents/info.plist");
		
		$data = array("filename" => basename($this->file), "type" => lang("update_expansion"));
		
		if(isset($appInfo["icon"])) {
			$temp = "system/temp/" . basename($appInfo["icon"]) . "-" . md5($appInfo["name"]) . substr($appInfo["icon"], strrpos($appInfo["icon"], "."));
			$gfs->writeToFileSystem("contents/" . $appInfo["icon"], $temp);
			$data["icon"] = $temp;
		}
		
		$data["appInfo"] = $appInfo;
		
		if(isset($info["changelog"]))
			$data["changelog"] = $info["changelog"];
		
		if(!isset($info["version"]))
			return false;
		
		
		// check if we have the correct framework-version
		if(isset($appInfo["requireFrameworkVersion"])) {
			if(goma_version_compare($appInfo["requireFrameworkVersion"], GOMA_VERSION . "-" . BUILD_VERSION, ">")) {
				$data["error"] = lang("update_frameworkError");
				$data["installable"] = false;
				
				return $data;
			}
			$data["framework_version"] = $appInfo["requireFrameworkVersion"];
		}
		
		// check if we use install-method
		if(isset(ClassInfo::$appENV["expansion"][$appInfo["name"]])) {
			$data["installType"] = "update";
			$data["installed_version"] = ClassInfo::ExpVersion($appInfo["name"]);
		}
		
		if(isset(ClassInfo::$appENV["expansion"][$appInfo["name"]]["title"]))
			$data["title"] = ClassInfo::$appENV["expansion"][$appInfo["name"]];
		else
			$data["title"] = $appInfo["name"];
		
		$data["version"] = $info["version"];
		
		return $data;
	}
	
	/**
	 * sets the package info:
	 * version
	 * changelog
	 * icon
	 * title
	 * required framework-version: framework_version
	*/
	public function setPackageInfo($data) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("backup/info.plist");
		
		if(isset($data["version"])) {
			$info["version"] = $data["version"];
			if(isset($appInfo["build"])) {
				if(strpos($data["version"], "-")) {
					$build = substr($data["version"], strrpos($data["version"], "-") + 1);
					$version = substr($data["version"], 0, strrpos($data["version"], "-"));
					$appInfo["build"] = $build;
					$appInfo["version"] = $version;
				} else {
					$appInfo["version"] = $data["version"];
				}
			} else {
				$appInfo["version"] = $data["version"];
			}	
		}
		
		if(isset($data["changelog"])) {
			$info["changelog"] = $data["changelog"];
		}
		
		if(isset($data["icon"])) {
			$newExt = substr($data["icon"], strrpos($data["icon"], ".") + 1);
			if(substr($appInfo["icon"], strrpos($appInfo["icon"], ".") + 1) == $newExt) {
				$gfs->write("backup/" . $appInfo["icon"], file_get_contents($data["icon"]));
			} else {
				$gfs->write("backup/" . $appInfo["icon"] . $newExt , file_get_contents($data["icon"]));
				$appInfo["icon"] = $appInfo["icon"] . $newExt;
			}
		}
		
		if(isset($data["title"])) {
			$appInfo["title"] = $data["title"];
		}
		
		if(isset($data["framework_version"]))
			$appInfo["requireFrameworkVersion"] = $data["framework_version"];
		
		$gfs->writePlist("info.plist", $info);
		$gfs->writePlist("backup/info.plist", $appInfo);
		
		return true;
	}
}