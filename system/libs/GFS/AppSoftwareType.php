<?php defined("IN_GOMA") OR die();

/**
 * The Software-Handler for Goma-Apps. The type of the file is "backup".
 *
 * See the topic about info.plist for more information about types.
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version	1.5.14
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
	 * default __construct
	 *
	 *@name __construct
	 *@access public
	 */
	public function __construct($file = null) {
		parent::__construct($file);
	}

	/**
	 * stores the data from the form in $formResult
	 *
	 *@name saveFormData
	 *@access public
	 */
	public function saveFormData($data) {
		$_data = $data["installdata"];

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

		// templates
		$_data["postflightCode"][] = '<?php $dir = "'.$data["installFolders"]["source"].'/../templates"; if(file_exists($dir)) foreach(scandir($dir) as $tpl) {
			if(file_exists($dir . "/" . $tpl . "/info.plist")) {
				FileSystem::move($dir . "/" . $tpl, ROOT . "tpl/" . $tpl);
			}
		}';

		return $_data;
	}

	/**
	 * validates the installation
	 *
	 * @name validateInstall
	 * @return bool|string
	 */
	public function validateInstall($obj) {
		$result = $obj->getForm()->result;
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
	 * @name getInstallInfo
	 * @access public
	 *
	 * @return array
	 */
	public function getInstallInfo($controller, $forceInstall = false) {
		$gfs = new GFS($this->file);
		$info = $gfs->parsePlist("info.plist");
		$appInfo = $gfs->parsePlist("backup/info.plist");

		$data = array("filename" => basename($this->file), "type" => lang("update_app"));

		if(!isset($info["version"])) {
			return false;
		}

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
			$form = new Form($controller, "installinfos", array(
				$folder = new TextField("folder", lang("install.folder"), $default),
				$host = new TextField("dbhost", lang("install.db_host"), "localhost"),
				new TextField("dbuser", lang("install.db_user")),
				new PasswordField("dbpwd", lang("install.db_password")),
				new TextField("dbname", lang("install.db_name")),
				$tableprefix = new TextField("tableprefix", lang("install.table_prefix"), "".$appInfo["name"]."_"),
				new HiddenField("installData", $data)
			), array(
				new FormAction("submit", lang("install.install"), array($this, "saveFormData"))
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

			$errors = self::checkMovePerms($gfs, "backup/", ROOT . CURRENT_PROJECT . "/");

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
	 * @name getPackageInfo
	 * @access public
	 * @return array
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
		if($appInfo["name"] == ClassInfo::$appENV["app"]["name"]) {
			$data["installType"] = "update";
			$data["installed_version"] = ClassInfo::AppVersion();
		}

		if(isset($appInfo["title"])) {
			$data["title"] = $appInfo["title"];
		} else {
			$data["title"] = ClassInfo::$appENV["app"]["name"];
		}

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
		$_data = $data["installdata"];

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
	 * @param FormValidator $obj
	 * @name validateInstall
	 * @return bool|string
	 */
	public function validateRestore($obj) {
		$result = $obj->getForm()->result;
		if($result["type"] != "copyconfig") {
			return $this->validateInstall($obj);
		} else {
			return true;
		}
	}


	/**
	 * restores the framework
	 *
	 * @name getRestoreInfo
	 * @access public
	 * @return mixed|string
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
	 * @param string $file
	 * @param string|null $name
	 * @param string|null $changelog
	 * @return bool
	 */
	public static function backup($file, $name, $changelog = null) {
		$tables = array_merge(ClassInfo::Tables("user"), ClassInfo::Tables("UserAuthentication"), ClassInfo::Tables("history"));
		//$tables = array_merge($tables, ClassInfo::Tables("permission"));
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
	 * @name generateDistroFileName
	 * @access public
	 * @return string
	 */
	public static function generateDistroFileName($name) {
		return ClassInfo::$appENV["app"]["name"] . "." . ClassInfo::appVersion() . ".gfs";
	}

	/**
	 * building the distro
	 *
	 * @param string $file
	 * @param null|string $name
	 * @param RequestHandler $controller
	 * @return mixed|string
	 */
	public static function buildDistro($file, $name, $controller) {
		if(GlobalSessionManager::globalSession()->hasKey(g_SoftwareType::FINALIZE_SESSION_VAR))
			return Object::instance("g_appSoftwareType")->finalizeDistro(GlobalSessionManager::globalSession()->get(g_SoftwareType::FINALIZE_SESSION_VAR));

		if(file_exists($file))
			@unlink($file);

		$title = isset(ClassInfo::$appENV["app"]["title"]) ? ClassInfo::$appENV["app"]["title"] : ClassInfo::$appENV["app"]["name"];

		$form = new Form($controller, "buildDistro", array(
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
			new FormAction("submit", lang("download"), array(Object::instance("g_appSoftwareType"), "finalizeDistro"))
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

	/**
	 * @param array $data
	 * @return string
	 */
	protected function getDistroName($data)
	{
		return null;
	}
}
