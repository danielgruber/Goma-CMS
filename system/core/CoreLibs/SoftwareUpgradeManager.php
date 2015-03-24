<?php defined("IN_GOMA") OR die();
/**
 * Goma Software-Upgrade-Manager.
 *
 * @package		Goma\Core
 * @version		1.0
 */

class SoftwareUpgradeManager {
	
	/**
	 * this function checks for upgrade-scripts in a given folder with 
	 * given version version
	 *
	 * @param 	string folder
	 * @param 	string version
	 * @return 	boolean it was upgraded.
	 */
	public static function checkForUpgradeScripts($folder, $current_version) {

		// get installed version
		$version = self::getInstalledVersion($folder);

		if(goma_version_compare($current_version, $version, ">")) {
			// run upgrade-scripts
			if(is_dir($folder . "/upgrade")) {
				$versions = self::getPendingScripts($folder);

				if(!empty($versions)) {
					self::runScripts($folder, $versions);
				}
				
				if(!self::writeVersion($folder . "/version.php", $current_version)) {	
					throw new SoftwareUpgradeWriteManagerError("Could not write file " . $folder . "/version.php. Please set file-permissions to 0777.");
				}

				return true;
			}

			if(!self::writeVersion($folder . "/version.php", $current_version)) {	
				throw new SoftwareUpgradeWriteManagerError("Could not write file " . $folder . "/version.php. Please set file-permissions to 0777.");
			}
		}

		// no upgrade
		return false;
	}

	/**
	 * get all pending upgrade scripts.
	 *
	 * @param 	string folder
	 * @return 	array
	*/
	public static function getPendingScripts($folder) {
		if(is_dir($folder . "/upgrade")) {

			$files = scandir($folder . "/upgrade");
			$versions = array();
			foreach($files as $file) {
				if(is_file($folder . "/upgrade/" . $file) && preg_match('/\.php$/i', $file)) {
					if(goma_version_compare(substr($file, 0, -4), $version, ">") && goma_version_compare(substr($file, 0, -4), $current_version, "<=")) {
						$versions[] = substr($file, 0, -4);
					}
				}
			}
			usort($versions, "goma_version_compare");

			return $versions;
		}

		return array();
	}

	/**
	 * runs all upgrade scripts by list and folder.
	 *
	 * @param 	string 	folder
	 * @param 	array 	scripts
	*/
	public static function runScripts($folder, $versions) {
		foreach($versions as $v) {
			if(!self::writeVersion($folder . "/version.php", $v)) {
				throw new SoftwareUpgradeWriteManagerError("Could not write file " . $folder . "/version.php. Please set file-permissions to 0777.");
			}

			include ($folder . "/upgrade/" . $v . ".php");
		}
	}

	/**
	 * writes version to file.
	 *
	 * @param 	string 	file
	 * @param 	string 	version
	*/
	public static function writeVersion($file, $version) {
		return FileSystem::write($file, '<?php $version = ' . var_export($version, true) . ';', LOCK_EX);
	}

	/**
	 * gets installed version from folder.
	 *
	 * @param 	string folder
	*/
	public static function getInstalledVersion($folder) {
		if(file_exists($folder . "/version.php")) {
			include ($folder . "/version.php");
			return $version;
		} else {
			return 0;
		}
	}
}

class SoftwareUpgradeWriteManagerError extends LogicException {
	/**
	 * constructor.
	*/
	public function __construct($msg, $code = ExceptionManager::SOFTWARE_UPGRADE_WRITE_ERROR, $e = null) {
		parent::__construct($msg, $code, $e);
	}
}