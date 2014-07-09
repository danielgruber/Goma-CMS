<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 18.12.2012
  * $Version 2.2.2
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class UpdateController extends adminController {
	/**
	 * allowed actions
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array(
		"installUpdate",
		"showInfo",
		"upload",
		"showPackageInfo"
	);
	
	/**
	 * title in view of this controller
	 *
	 *@name title
	 *@access public
	*/
	public function title() {
		return lang("update");
	}
	
	/**
	 *@name index
	 *@access public
	*/
	public function index() {
		$view = new ViewAccessableData();
		if(isset($_GET["noJS"])) {
			G_SoftwareType::forceLiveDB();
			$updates = G_SoftwareType::listUpdatePackages();
			foreach($updates as $name => $data) {
				$data["secret"] = randomString(20);
				if(!isset($data["AppStore"])) {
					$_SESSION["updates"][$data["file"]] = $data["secret"];
				} else {
					$_SESSION["AppStore_updates"][$data["AppStore"]] = $data["secret"];
				}
				$updates[$name] = $data;
			}
		} else {
			$updates = array();
		}
		
		$updates = new DataSet($updates);
		$storeAvailable = G_SoftwareType::isStoreAvailable();
		$updatables = G_SoftwareType::listUpdatablePackages();
		
		$view->customise(array("updates" => $updates, "BASEURI" => BASE_URI, "storeAvailable" => $storeAvailable, "updatables" => new DataSet($updatables), "updatables_json" => json_encode($updatables)));
		
		return $view->renderWith("admin/update.html");
	}
	
	/**
	 * shows info about a given package
	 *
	 *@name showPackageInfo
	 *@access public
	*/
	public function showPackageInfo() {
		if($id = $this->getParam("id")) {
			$_SESSION["AppStore_updates"] = isset($_SESSION["AppStore_updates"]) ? $_SESSION["AppStore_updates"] : array();
			$_SESSION["updates"] = isset($_SESSION["updates"]) ? $_SESSION["updates"] : array();
			
			if(!in_array($this->getParam("id"), $_SESSION["updates"]) && !in_array($this->getParam("id"), $_SESSION["AppStore_updates"])) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
				exit;
			}
			
			if(in_array($this->getParam("id"), $_SESSION["AppStore_updates"])) {
				$url = array_search($this->getParam("id"), $_SESSION["AppStore_updates"]);
				$folder = ROOT . "system/installer/data/apps";
				if(G_SoftwareType::isStoreAvailable()) {
					if($handle = @fopen($url, "r")) {
						file_put_contents($folder . "/" . basename($url), $handle);
						@chmod($folder . "/" . basename($url), 0777);
						$file = $folder . "/" . basename($url);
						$_SESSION["updates"][$file] = $id;
					} else {
						return "Could not read from Server!";
					}
				} else {
					return '<div class="error">'.lang("update_connection_failed").'</div>';
				}
			} else {	
				$file = array_search($this->getParam("id"), $_SESSION["updates"]);
			}

			if(!preg_match('/\.gfs$/i', $file)) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
				exit;
			}
			
			$data = G_SoftwareType::getInstallInfos($file);
			
			if(is_string($data))
				return $data;
			else if(is_array($data)) {
				$inst = new ViewAccessableData($data);
				$inst->filename = basename($file);
				$inst->fileid = convert::raw2text($id);
				
				session_store("update_" . $inst->fileid, $inst);
				
				return $inst->renderWith("admin/updateInfo.html");
			}
			
			AddContent::addError(lang("install_invalid_file", "The file you uploaded isn't a valid installer-package."));
			
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
			exit;
			
			
		} else {
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
			exit;
		}
	}
	
	/**
	 * index of this
	 *
	 *@name index
	 *@access public
	*/
	public function upload() {
		if(isset($_GET["download"]) && preg_match('/^http(s)?\:\/\/(www\.)?goma\-cms\.org/i', $_GET["download"])) {
			$filename = ROOT . CACHE_DIRECTORY . md5(basename($_GET["download"])) . ".gfs";
			if(file_put_contents(ROOT . CACHE_DIRECTORY . md5(basename($_GET["download"])) . ".gfs", @file_get_contents($_GET["download"]))) {
				if($model = Uploads::addFile(basename($_GET["download"]), $filename, "updates")) {
					HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/showInfo/" . $model->id);
					exit;
				}
			}
			
			$form = new Form($this, "update", array(
				new HTMLField("download", '<a href="'.addslashes($_GET["download"]).'" class="button">'.lang("update_file_download").'</a>'),
				$file = new FileUpload("file", lang("update_file_upload"), array("gfs"), null, "updates")
			), array(
				new FormAction("submit", lang("submit"), "checkUpdate")
			));
		} else {
			$form = new Form($this, "update", array(
				new InfoField("file_info", lang("update_file_info")),
				$file = new FileUpload("file", lang("update_file"), array("gfs"), null, "updates")
			), array(
				new FormAction("submit", lang("submit"), "checkUpdate")
			));
		}
		
		$file->max_filesize = -1;
		$form->addValidator(new RequiredFields(array("file")), "valid");
		return $form->render();
	}
	
	/**
	 * shows the information of the file with the given id
	 *
	 *@name showInfo
	 *@access public
	*/
	public function showInfo() {
		if($id = $this->getParam("id")) {
			if(!($fileObj = DataObject::get_one("Uploads", array("id" => $id)))) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
				exit;
			}
			
			$file = $fileObj->realfile;
			
			if(!preg_match('/\.gfs$/i', $file)) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
				exit;
			}
			
			$data = G_SoftwareType::getInstallInfos($file);
			
			if(is_string($data))
				return $data;
			else if(is_array($data)) {
				$inst = new ViewAccessableData($data);
				$inst->filename = $fileObj->filename;
				$inst->fileid = $fileObj->id;
				
				session_store("update_" . $inst->fileid, $inst);
				
				return $inst->renderWith("admin/updateInfo.html");
			}
			
			AddContent::addError(lang("install_invalid_file", "The file you uploaded isn't a valid installer-package."));
			
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
			exit;
			
			
		} else {
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
			exit;
		}
	}
	
	/**
	 * validates the update
	 *
	 *@name checkUpdate
	 *@access public
	*/
	public function checkUpdate($data) {
		$file = $data["file"]->realfile;
		if(!file_exists($file)) {
			HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
			exit;
		}
		
		
		HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/showInfo/" . $data["file"]->id . URLEND . "?redirect=" . urlencode(BASE_URI . BASE_SCRIPT . "admin" . URLEND));
		exit;
	}
	
	/**
	 * installs the update
	 *
	 *@name installUpdate
	 *@access public
	*/
	public function installUpdate() {
		if(preg_match('/^[0-9]+$/', $this->getParam("update"))) {
			if(!($fileid = $this->getParam("update"))) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
				exit;
			}
			
			if(!($file = DataObject::get_one("Uploads", array("id" => $fileid)))) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
				exit;
			}
			
			clearstatcache();
			if(!file_exists($file->realfile)) {
				$file->remove(true);
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
				exit;
			}
		
			if(!session_store_exists("update_" . $file->id)) {
				AddContent::addError(lang("less_rights", "You are not permitted to do this."));
				
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
				exit;
			}
			
			$data = session_restore("update_" . $file->id);
		} else {
			
			if(!in_array($this->getParam("update"), $_SESSION["updates"])) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
				exit;
			}
			
			$file = array_search($this->getParam("update"), $_SESSION["updates"]);
			
			if(!preg_match('/\.gfs$/i', $file)) {
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
				exit;
			}
			
			if(!session_store_exists("update_" . $this->getParam("update"))) {
				AddContent::addError(lang("less_rights", "You are not permitted to do this."));
				
				HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
				exit;
			}
			
			$data = session_restore("update_" . $this->getParam("update"));
		}
		
		return G_SoftwareType::install($data);
	}
}