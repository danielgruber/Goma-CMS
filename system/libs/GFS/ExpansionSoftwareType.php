<?php defined("IN_GOMA") OR die();

/**
 * The Software-Handler for Extensions. The type of the file is "expansion".
 *
 * See the topic about info.plist for more information about types.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version	1.5.14
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
	 * @name getInstallInfo
	 * @access public
	 * @return array
	 */
	public function getInstallInfo($controller, $forceInstall = false) {
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
			$data["installed"] = ExpansionManager::ExpVersion($appInfo["name"]);

			/*if(isset($appInfo["require_version"]) && goma_version_compare($appInfo["require_version"], ClassInfo::appVersion(), ">")) {
				$data["error"] = lang("update_version_newer_required") . " " . $appInfo["require_version"];
				$data["installable"] = false;
				
				return $data;
			}*/

			if(isset($info["changelog"]))
				$data["changelog"] = $info["changelog"];

			$errors = self::checkMovePerms($gfs, "contents/", ExpansionManager::getExpansionFolder($appInfo["name"]));

			if(!empty($errors)) {
				$data["error"] = lang("permission_error") . '('.implode(",", $errors).')';
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
				"destination"	=> ExpansionManager::getExpansionFolder($appInfo["name"])
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
	 * @name getRestoreInfo
	 * @access public
	 * @return bool
	 */
	public function getRestoreInfo($forceCompleteRestore = false) {
		return false;
	}

	/**
	 * generates a distro
	 *
	 * @name backup
	 * @access public
	 * @return bool
	 */
	public static function backup($file, $name, $changelog = null) {
		if(!isset(ClassInfo::$appENV["expansion"][$name])) {
			return false;
		}

		$folder = ExpansionManager::getExpansionFolder($name);

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
		$dict->add("version", new CFString(ExpansionManager::expVersion($name)));
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
	 * @name generateDistroFileName
	 * @access public
	 * @return bool|string
	 */
	public static function generateDistroFileName($name) {
		if(!isset(ClassInfo::$appENV["expansion"][$name])) {
			return false;
		}
		return $name . "." . ExpansionManager::expVersion($name) . ".gfs";
	}

	/**
	 * building the distro
	 *
	 * @param string $file
	 * @param null|string $name
	 * @param RequestHandler $controller
	 * @return bool|mixed|string
	 */
	public static function buildDistro($file, $name, $controller) {
		if(GlobalSessionManager::globalSession()->hasKey(g_SoftwareType::FINALIZE_SESSION_VAR))
			return gObject::instance("g_expansionSoftWareType")->finalizeDistro(GlobalSessionManager::globalSession()->get(g_SoftwareType::FINALIZE_SESSION_VAR));

		if(!isset(ClassInfo::$appENV["expansion"][$name])) {
			return false;
		}

		$title = isset(ClassInfo::$appENV["expansion"][$name]["title"]) ? ClassInfo::$appENV["expansion"][$name]["title"] : ClassInfo::$appENV["expansion"][$name]["name"];

		$form = new Form($controller, "buildDistro", array(
			new HiddenField("file", $file),
			new HiddenField("expName", $name),
			new HTMLField("title", "<h1>".convert::raw2text($title)."</h1><h3>".lang("distro_build")."</h3>"),
			$version = new TextField("version", lang("version"), ExpansionManager::expVersion($name)),
			new Textarea("changelog", lang("distro_changelog"))
		), array(
			new LinkAction("cancel", lang("cancel"), ROOT_PATH . BASE_SCRIPT . "dev/buildDistro"),
			new FormAction("submit", lang("download"), array(gObject::instance("g_expansionSoftWareType"), "finalizeDistro"))
		));

		$version->disable();

		return $form->render();
	}

	/**
	 * lists installed software
	 *
	 * @name listSoftware
	 * @access public
	 * @return array
	 */
	public static function listSoftware() {
		$arr = array();
		// generate for expansions
		if(isset(ClassInfo::$appENV["expansion"])) {
			foreach(ClassInfo::$appENV["expansion"] as $name => $data) {
				if(isset($data["build"])) {
					$data["version"] = $data["version"] . "-" . $data["build"];
				} else {
					$data["version"] = $data["version"];
				}

				if(isset($data["icon"]) && $data["icon"]) {
					$data["icon"] = ExpansionManager::getExpansionFolder($name) . "/" . $data["icon"];
				}


				if(!isset($data["title"])) {
					$data["title"] = $name;
				}

				$data["canDisable"] = true;

				$arr[$name] = $data;
			}
		}

		return $arr;
	}

	/**
	 * gets package info
	 *
	 * @name getPackageInfo
	 * @access public
	 * @return array
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
			$data["installed_version"] = ExpansionManager::ExpVersion($appInfo["name"]);
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

	/**
	 * @param array $data
	 * @return string
	 */
	protected function getDistroName($data)
	{
		return $data["expname"];
	}
}
