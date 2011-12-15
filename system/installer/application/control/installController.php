<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 01.11.2011
  * $Version 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class InstallController extends RequestHandler {
	/**
	 * url_handlers
	*/
	public $url_handlers = array(
		"installapp/\$app!" => "installApp",
		"execInstall"		=> "execInstall"
	);
	/**
	 * actions
	*/
	public $allowed_actions = array(
		"install", "installApp", "langselect", "execInstall"
	);
	/**
	 * shows install fronted if language is already selected, else shows lang-select
	*/
	public function index() {
		if(isset($_SESSION["lang"])) {
			return tpl::render("install/index.html");
		} else {
			HTTPResponse::Redirect(BASE_URI . BASE_SCRIPT . "/install/langselect/");
		}
	}
	/**
	 * shows lang-select
	 *
	 *@name langSelect
	*/
	public function langSelect() {
		$data = new ViewAccessAbleData();
		return $data->renderWith("install/lang.html");
	}
	/**
	 * starts a goma-installation
	*/
	public function install() {
		
		$files = scandir(APP_FOLDER . "data/apps/");
		$apps = array();
		foreach($files as $app) {
			if($app == "framework" || $app == "." || $app == ".." || !is_dir(APP_FOLDER . "data/apps/" . $app)) {
				continue;
			}
			
			$appPackagePath = APP_FOLDER . "data/apps/" . $app ."/";
			// then check updates for the app-setup
			if(!file_exists($appPackagePath . ".latest_version") || filemtime($appPackagePath . "/.latest_version") < NOW + 86400 || filemtime($appPackagePath . "/.latest_version") < filemtime($appPackagePath)) {
				$latestVersion = 0;
				foreach(scandir($appPackagePath) as $file) {
					if(preg_match('/^(.*)\.gfs$/i', $file, $matches)) {
						if(version_compare($matches[1], $latestVersion, ">")) {
							$latestVersion = $matches[1];
						}
					}
				}
				@chmod(APP_FOLDER . "data/apps/" . $app . "/", 0777);
				file_put_contents($appPackagePath . "/.latest_version", $latestVersion);
			}
			
			if(isset($latestVersion)) {
				$version = $latestVersion;
			} else {
				$version = file_get_contents($appPackagePath . "/.latest_version");
			}
			
			if($version == 0) {
				continue;
			}
			
			$apps[$app] = $app . " (V" . $version . ")";
		}
		
		if(count($apps) == 1) {
			$apps = array_keys($apps);
			HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "install/installApp/" . $apps[0]);
		} else if(count($apps) == 0) {
			return lang("install.no_app_found");
		}
		
		$form = new Form($this, "appselect", array(
			new HTMLField("select_app", '<p>' . lang("install.select_app") . '</p>'),
			new Select("app", lang("install.app"), $apps)
		), array(
			new FormAction("save", lang("install.select"), "selectApp")
		));
		$form->addValidator(new RequiredFields(array("app")), "required");
		
		return $form->render();
	}
	/**
	 * selects the app
	 *
	 *@name selectApp
	*/
	public function selectApp($data) {
		HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "install/installApp/" . $data["app"]);
		exit;
	}
	/**
	 * starts an installation of an specific app
	 *
	 *@name installApp
	 *@access public
	*/
	public function installApp() {
		$app = $this->getParam("app");
		if(file_exists(APP_FOLDER . "data/apps/" . $app . "/.latest_version")) {
			$version = file_get_contents(APP_FOLDER . "data/apps/" . $app . "/.latest_version");
			if(file_exists(APP_FOLDER . "data/apps/" . $app . "/" . $version . ".gfs")) {
				
				$form = new Form($this, "install", array(
					$folder = new TextField("folder", lang("install.folder"), defined("PROJECT_LOAD_DIRECTORY") ? PROJECT_LOAD_DIRECTORY : "mysite"),
					$host = new TextField("dbhost", lang("install.db_host"), "localhost"),
					new TextField("dbuser", lang("install.db_user")),
					new PasswordField("dbpwd", lang("install.db_password")),
					new TextField("dbname", lang("install.db_name"), $app),
					$tableprefix = new TextField("tableprefix", lang("install.table_prefix"), "".$app."_"),
					new HiddenField("file", APP_FOLDER . "data/apps/" . $app . "/" . $version . ".gfs")
				), array(
					new FormAction("submit", lang("install.install"), "startInstall")
				));
				
				$form->addValidator(new RequiredFields(array("folder", "dbhost", "dbuser", "dbname")), "fields");
				$form->addValidator(new FormValidator(array($this, "validateInstall")), "validateInstall");
				
				@chmod(APP_FOLDER . "data/apps/" . $app . "/" . $version . ".gfs", 0777);
				// check if table-prefix can be set
				$gfs = new GFS(APP_FOLDER . "data/apps/" . $app . "/" . $version . ".gfs");
				
				if($gfs->valid === false) {
					if($gfs->error == 1) {
						
					} else {
						return "Package corrupted";
					}					
				}
				
				$plist = new CFPropertyList();
				$plist->parse($gfs->getFileContents("info.plist"));
				
				$data = $plist->toArray();
				
				if($data["DB_PREFIX"] != "{!#PREFIX}") {
					$tableprefix->value = $data["DB_PREFIX"];
					$tableprefix->disable();
				}
				
				$host->info = lang("install.db_host_info");
				$folder->info = lang("install.folder_info");
				return $form->render();
				
			} else {
				return "app not found!";
			}
		} else {
			return "app not found!";
		}
	}
	/**
	 * validates the installation
	 *
	 *@name validateInstall
	 *@access public
	*/
	public function validateInstall($obj) {
		$result = $obj->form->result;
		$notAllowedFolders = array(
			"dev", "admin", "pm"
		);
		if(file_exists(ROOT . $result["folder"]) || in_array($result["folder"], $notAllowedFolders) || !preg_match('/^[a-z0-9_]+$/', $result["folder"])) {
			return lang("install.folder_error");
		}
		
		if(!SQL::test(SQL_DRIVER, $result["dbuser"], $result["dbname"], $result["dbpwd"], $result["dbhost"])) {
			return lang("install.sql_error");
		}
		
		return true;
	}
	/**
	 * starts the installation
	*/
	public function startInstall($data) {
	
		return $this->execInstall($data["file"], $data["folder"], array(
			"user" 	=> $data["dbuser"],
			"db"	=> $data["dbname"],
			"pass"	=> $data["dbpwd"],
			"host"	=> $data["dbhost"],
			"prefix"=> $data["tableprefix"]
		));
	}
	/**
	 * executess the installation with a give file
	 *
	 *@name execInstall
	 *@access public
	*/
	public function execInstall($file = null, $directory = null, $db = null) {
		if(isset($file)) {
			unset($_SESSION["install"]);
			$_SESSION["install"]["file"] = $file;
			$_SESSION["install"]["folder"] = $directory;
			$_SESSION["install"]["db"] = $db;
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "install/execInstall/");
		}
		
		if(!isset($_SESSION["install"])) {
			throwError(8, "Install Error", "Session not initiated.");
		}
		$file = $_SESSION["install"]["file"];
		$directory = $_SESSION["install"]["folder"];
		$db = $_SESSION["install"]["db"];
		
		
		$dir = ROOT . CACHE_DIRECTORY . md5($file);
		FileSystem::requireDir($dir);
		
		FileSystem::createFile($dir . "/write.test");
		
		if(!isset($_SESSION["install"]["unpack1"])) {
			$gfs = new GFS_Package_installer($file);
			$gfs->unpack($dir);
			
			$_SESSION["install"]["unpack1"] = true;
		}
		
		if(!isset($_SESSION["install"]["unpack2"])) {
			$db_gfs = new GFS_Package_installer($dir . "/database.sgfs");
			$db_gfs->unpack($dir . "/database/");
			$_SESSION["install"]["unpack2"] = true;
		}
		
		$plist = new CFPropertyList();
		$plist->parse(file_get_contents($dir . "/database/info.plist"));
				
		$data = $plist->toArray();
		
		define("NO_AUTO_CONNECT", true);
		define("DB_PREFIX", $db["prefix"]);
		
		SQL::Init();
		SQL::connect($db["user"], $db["db"], $db["pass"], $db["host"]);
		
		foreach(scandir($dir . "/database/database/") as $sqlfile) {
			$queries = file_get_contents($dir . "/database/database/" . $sqlfile);
			$queries = sql::split($queries);
			foreach($queries as $sql) {
				$sql = str_replace('{!#PREFIX}', $db["prefix"], $sql);
				$sql = str_replace($data["foldername"], $directory, $sql);
				$sql = str_replace('\n', "\n", $sql);
				
				SQL::Query($sql);
			}
		}
		
		if(!file_exists(ROOT . $directory)) {
			// first move in main directory
			rename($dir . "/backup/", ROOT . $directory);
		} else {
			return "Could not move Directory: Directory exists.";
		}
		
		foreach(scandir($dir . "/templates/") as $template) {
			if(!file_exists(ROOT . "tpl/" . $template)) {
				@chmod(ROOT . "tpl/", 0777);
				rename($dir . "/templates/" . $template, ROOT . "tpl/" . $template);
			}
		}
		
		writeProjectConfig(array("db" => $db), $directory);
		
		file_put_contents(ROOT . $directory . "/application/ENABLE_WELCOME", "");
		
		HTTPResponse::redirect(BASE_URI);
		
	}
	/**
	 * serve
	*/
	public function serve($content) {
		$data = new ViewAccessAbleData();
		return $data->customise(array("content" => $content))->renderWith("install/install.html");
	}
}