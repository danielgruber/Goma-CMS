<?php defined("IN_GOMA") OR die();

/**
 * The Software-Handler for Framework-files. The type of the file is "framework".
 *
 * See the topic about info.plist for more information about types.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version	1.5.13
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
	 * @return array|bool
	 */
	public function getInstallInfo($controller, $forceInstall = false) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");

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

			$errors = self::checkMovePerms($gfs, "data/", ROOT);

			if(!empty($errors)) {
				$data["error"] = lang("permission_error") . '('.implode(",", $errors).')';
				$data["installable"] = false;
				return $data;
			}

			$data["installable"] = true;

			$data["preflightCode"] = array(
				'<?php if(!GFS_Package_Installer::wasUnpacked('.var_export($this->file, true).') || !is_dir('.var_export($dir, true).')) { $gfs = new GFS_Package_installer('.var_export($this->file, true).');$gfs->unpack('.var_export($dir, true).'); }'
			);

			$data["installFolders"] = array(
				"source"		=> $dir . "/data/",
				"destination"	=> ROOT
			);

			// don't recheck permissions
			$data["permCheck"] = false;

			$data["postflightCode"] = array(
				'<?php FileSystem::Delete('.var_export($dir, true).');'
			);

			return $data;
		} else {
			return false;
		}
	}

	public function checkMovePermissions() {

	}

	/**
	 * gets package info
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	public function getPackageInfo() {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("data/system/info.plist");

		if(!$appInfo)
			throw new Exception("Could not read Package-Info.");

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
	 * @param bool $forceCompleteRestore
	 * @return bool
	 */
	public function getRestoreInfo($forceCompleteRestore = false) {
		return false;
	}

	/**
	 * generates a distro
	 *
	 * @param string $file
	 * @param string $name
	 * @param string|null $changelog
	 * @return bool
	 */
	public static function backup($file, $name, $changelog = null) {
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
	 * @name generateDistroFileName
	 * @access public
	 * @return string
	 */
	public static function generateDistroFileName($name) {
		return "framework." . GOMA_VERSION . "-" . BUILD_VERSION . ".gfs";
	}

	/**
	 * builds a framework
	 *
	 * @param String $file filename
	 * @param String $name
	 * @param RequestHandler $controller
	 * @return bool|mixed|string
	 */
	public static function buildDistro($file, $name, $controller) {
		if(GlobalSessionManager::globalSession()->hasKey(g_SoftwareType::FINALIZE_SESSION_VAR))
			return gObject::instance("g_frameworkSoftWareType")->finalizeDistro(GlobalSessionManager::globalSession()->get(g_SoftwareType::FINALIZE_SESSION_VAR));

		if(file_exists($file))
			@unlink($file);

		$form = new Form($controller, "buildDistro", array(
			new HiddenField("file", $file),
			new HTMLField("title", "<h1>".lang("update_framework")."</h1><h3>".lang("distro_build")."</h3>"),
			$version = new TextField("version", lang("version"), GOMA_VERSION . "-" . BUILD_VERSION),
			new Textarea("changelog", lang("distro_changelog"))
		), array(
			new LinkAction("cancel", lang("cancel"), ROOT_PATH . BASE_SCRIPT . "dev/buildDistro"),
			new FormAction("submit", lang("download"), array(gObject::instance("g_frameworkSoftWareType"), "finalizeDistro"))
		));

		$version->disable();

		return $form->render();
	}

	/**
	 * lists installed software
	 *
	 * @return array
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

	/**
	 * @param array $data
	 * @return string
	 */
	protected function getDistroName($data)
	{
		return "framework";
	}
}
