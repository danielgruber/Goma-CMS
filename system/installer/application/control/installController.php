<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 26.05.2012
  * $Version 2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang('backup');

class InstallController extends RequestHandler {
	/**
	 * url_handlers
	*/
	public $url_handlers = array(
		"installapp/\$app!" 		=> "installApp",
		"execInstall/\$rand!"		=> "execInstall",
		"restore"					=> "selectRestore"
	);
	
	/**
	 * actions
	*/
	public $allowed_actions = array(
		"install", "installApp", "langselect", "execInstall", "selectRestore", "showRestore", "installBackup", "installFormBackup"
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
		return $data->renderWith("install/langselect.html");
	}
	
	/**
	 * lists apps to select
	 *
	 *@name install
	 *@access public
	*/
	public function install() {
		G_SoftwareType::forceLiveDB();
		
		$data = unserialize(file_get_contents(FRAMEWORK_ROOT . "installer/data/apps/.index.db"));
		if(!$data)
			Dev::RedirectToDev();
		
		$apps = G_SoftwareType::listInstallPackages();
		foreach($apps as $key => $val) {
			$apps[$key]["app"] = $key;
			if($val["plist_type"] != "backup") {
				unset($apps[$key]);
			}
		}
		
		$data = new DataSet($apps);
		return $data->renderWith("install/selectApp.html");
	}
	
	/**
	 * starts an installation of an specific app
	 *
	 *@name installApp
	 *@access public
	*/
	public function installApp() {
		G_SoftwareType::forceLiveDB();
		
		$data = unserialize(file_get_contents(FRAMEWORK_ROOT . "installer/data/apps/.index.db"));
		if(!$data)
			Dev::RedirectToDev();
		
		$apps = G_SoftwareType::listInstallPackages();
		
		$app = $this->getParam("app");
		
		if(isset($apps[$app])) {
			$t = G_SoftwareType::getByType($apps[$app]["plist_type"], $apps[$app]["file"]);
			$data = $t->getInstallInfo();
			if(is_array($data)) {
				$rand = randomString(20);
				$data["rand"] = $rand;
				$_SESSION["install"] = array();
				$_SESSION["install"][$rand] = $data;
				
				$dataset = new ViewAccessableData($data);
				return $dataset->renderWith("install/showInfo.html");
			} else {
				return $data;
			}
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
	 * executess the installation with a give file
	 *
	 *@name execInstall
	 *@access public
	*/
	public function execInstall() {
		$rand = $this->getParam("rand");
		if(isset($_SESSION["install"][$rand])) {
			$data = $_SESSION["install"][$rand];
			G_SoftwareType::install($data);
			HTTPResponse::redirect(BASE_URI);
		} else {
			HTTPResponse::redirect(BASE_URI);
		}
	}
	
	/**
	 * serve
	 *
	 *@name content
	 *@access public
	*/
	public function serve($content) {
		$data = new ViewAccessAbleData();
		return $data->customise(array("content" => $content))->renderWith("install/install.html");
	}
	
	/**
	 * shows a form to select a file to restore
	 *
	 *@name selectRestore
	 *@access public
	*/
	public function selectRestore() {
		$backups = array();
		$files = scandir(APP_FOLDER . "data/restores/");
		foreach($files as $file) {
			if(preg_match('/\.gfs$/i', $file)) {
				$backups[] = $file;
			}
		}
		
		if(empty($backups))
			return '<div class="notice">' . lang("install.no_backup") . '</div>';
		
		$form = new Form($this, "selectRestore", array(
			new Select("backup", lang("install.backup"), $backups)
		), array(
			new FormAction("submit", lang("install.restore"))
		));
		
		$form->setSubmission("submitSelectRestore");
		
		return $form->render();
	}
	
	/**
	 * submit-action for selectRestore-form
	 *
	 *@name submitSelectRestore
	 *@access public
	*/
	public function submitSelectRestore($data) {
		HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "install/showRestore".URLEND."?restore=" . $data["backup"]);
		exit;
	}
	
	/**
	 * shows up the file to restore and some information
	 *
	 *@name showRestore
	 *@access public
	*/
	public function showRestore() {
		if(!$this->getParam("restore")) {
			HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "install/selectRestore" . URLEND);
			exit;
		}
			
		if(file_exists(APP_FOLDER . "data/restores/" . basename($this->getParam("restore")))) {
			$gfs = new GFS(APP_FOLDER . "data/restores/" . basename($this->getParam("restore")));
			if(!$gfs->valid) {
				if($gfs->error == 1) {
					return lang("file_perm_error") . " (<strong>installer/data/restores/" . text::protect(basename($this->getParam("restore"))) ."</strong>)";
				}
				return "Package corrupded.";
			}
			$data = $gfs->parsePlist("info.plist");
			$t = G_SoftwareType::getByType($data["type"], APP_FOLDER . "data/restores/" . basename($this->getParam("restore")));
			
			$data = $t->getRestoreInfo();
			if(is_array($data)) {
				$rand = randomString(20);
				$data["rand"] = $rand;
				$_SESSION["install"] = array();
				$_SESSION["install"][$rand] = $data;
				
				$dataset = new ViewAccessableData($data);
				return $dataset->renderWith("restore/showInfo.html");
			} else {
				return $data;
			}
		} else {
			return "file not found";
		}
	}
	
	/**
	 * shows the install form for the backup
	*/
	public function installFormBackup() {
		if(!$this->getParam("restore")) {
			HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "install/selectRestore" . URLEND);
			exit;
		}
		
		if(file_exists(APP_FOLDER . "data/restores/" . basename($this->getParam("restore")))) {
			$gfs = new GFS(APP_FOLDER . "data/restores/" . basename($this->getParam("restore")));
			if(!$gfs->valid) {
				if($gfs->error == 1) {
					return '<div class="notice">' . lang("file_perm_error") . '</div>';
				}
				return "Package corrupded.";
			}
			$plist = new CFPropertyList();
			$plist->parse($gfs->getFileContents("info.plist"));
			
			$data = $plist->ToArray();
			
			if(!version_compare(GOMA_VERSION . "-" . BUILD_VERSION, $data["framework_version"], ">=") || $data["backuptype"] != "full") {
				return false;
			}
			
			// find a good folder-name :)
			if( defined("PROJECT_LOAD_DIRECTORY") && !file_exists(ROOT . PROJECT_LOAD_DIRECTORY)) {
				$default = PROJECT_LOAD_DIRECTORY;
			} else if(!file_exists(ROOT . "mysite")) {
				$default = "mysite";
			} else if(!file_exists(ROOT . "myproject")) {
				$default = "myproject";
			} else {
				$default = null;
			}
			
			$form = new Form($this, "installBackup", array(
				$restore_info = new TextField("restore_info", lang("install.backup"), $this->getParam("restore")),
				$folder = new TextField("folder", lang("install.folder"), $default),
				new HiddenField("restore", $this->getParam("restore")),
				$host = new TextField("dbhost", lang("install.db_host"), "localhost"),
				new TextField("dbuser", lang("install.db_user")),
				new PasswordField("dbpwd", lang("install.db_password")),
				new TextField("dbname", lang("install.db_name"), "goma"),
				$tableprefix = new TextField("tableprefix", lang("install.table_prefix"), "gf_"),
			), array(
				new FormAction("install", lang("restore"), "installBackup")
			));
			
			$restore_info->disable();
			
			if($data["DB_PREFIX"] != "{!#PREFIX}") {
				$tableprefix->value = $data["DB_PREFIX"];
				$tableprefix->disable();
			}
			
			$host->info = lang("install.db_host_info");
			
			$folder->info = lang("install.folder_info");
			$form->addValidator(new FormValidator(array($this, "validateInstall")), "validate");
			$form->addValidator(new RequiredFields(array("folder", "dbhost", "dbuser", "dbname")), "fields");
			
			return $form->render();
		} else {
			return "file not found";
		}
	}
	
	/**
	 * installs the backup
	*/
	public function installBackup($data) {
		$restore = basename($data["restore"]);
		if(file_exists(APP_FOLDER . "data/restores/" . $restore)) {
			$gfs = new GFS(APP_FOLDER . "data/restores/" . $restore);
			if(!$gfs->valid) {
				if($gfs->error == 1) {
					return lang("file_perm_error");
				}
				return "Package corrupded.";
			}
			$plist = new CFPropertyList();
			$plist->parse($gfs->getFileContents("info.plist"));
			
			$plist_data = $plist->ToArray();
			
			if(!version_compare(GOMA_VERSION . "-" . BUILD_VERSION, $plist_data["framework_version"], ">=")  || $plist_data["backuptype"] != "full") {
				return false;
			}
			
			return $this->execInstall(APP_FOLDER . "data/restores/" . $restore, $data["folder"], array(
				"user" 	=> $data["dbuser"],
				"db"	=> $data["dbname"],
				"pass"	=> $data["dbpwd"],
				"host"	=> $data["dbhost"],
				"prefix"=> $data["tableprefix"]
			), false);
		} else {
			return "file not found";
		}
	}
}