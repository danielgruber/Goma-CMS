<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 31.08.2013
  * $Version 1.5
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class systemController extends Controller {
	/**
	 * js-debug-limit in KB
	 *
	 *@name JS_DEBUG_LIMIT
	 *@access public
	*/
	const JS_DEBUG_LIMIT = 2048;
	
	/**
	 * you can set the mobile state to active or disabled
	*/
	public $url_handlers = array(
		"setMobile/0"			=> "disableMobile",
		"setMobile/1"			=> "enableMobile",
		"setUserView/\$bool!"	=> "setUserView",
		"switchView",
		"getLang/\$lang"		=> "getLang",
		"ck_uploader"			=> "ckeditor_upload",
		"ck_imageuploader"		=> "ckeditor_imageupload",
		"indexSearch/\$max"		=> "indexSearch"
	);
	
	public $allowed_actions = array("disableMobile", "enableMobile", "setUserView", "switchView", "getLang", "ckeditor_upload", "ckeditor_imageupload", "indexSearch");
	
	/**
	 * disables the mobile version
	*/
	public function disableMobile() {
		$_SESSION["nomobile"] = true;
		$this->redirectback();
	}
	
	/**
	 * enables the mobile version
	*/
	public function enableMobile() {
		unset($_SESSION["nomobile"]);
		$this->redirectback();
	}
	
	/**
	 * by default, without modifier enable mobile state
	*/
	public function index() {
		HTTPResponse::redirect(BASE_URI);
		exit;
	}
	/**
	 * sets the user view
	 *
	 *@name setUserView
	 *@access public
	*/
	public function setUserView() {
		if($this->getParam("bool") == 1) {
			$_SESSION["adminAsUser"] = true;
		} else {
			unset($_SESSION["adminAsUser"]);
		}
		$this->redirectback();
	}
	/**
	 * switches the view
	 *
	 *@name switchView
	 *@access public
	*/
	public function switchView() {
		if(isset($_SESSION["adminAsUser"]))
			unset($_SESSION["adminAsUser"]);
		else
			$_SESSION["adminAsUser"] = 1;
		
		HTTPResponse::unsetCachable();
		
		$this->redirectBack();
	}
	
	/**
	 * sends language as json to the user
	 *
	 *@name getLang
	 *@access public
	*/
	public function getLang() {
		$lang = $this->getParam("lang");
		$output = array();
		$outputNull = false;
		if(empty($lang) || $lang == "*") {
			$output = $GLOBALS["lang"];
		} else {
			if(is_array($lang) && count($lang) > 0) {
				foreach($lang as $value) {
					$value = strtoupper($value);
					if(isset($GLOBALS["lang"][$value])) {
						$output[$value] = $GLOBALS["lang"][$value];
					} else {
						$output[$value] = null;
						$outputNull = true;
					}
				}
			} else if(is_string($lang)) {
				$lang = strtoupper($lang);
				if(isset($GLOBALS["lang"][$lang])) {
						$output[$lang] = $GLOBALS["lang"][$lang];
					} else {
						$output[$lang] = null;
						$outputNull = true;
					}
			}
		}
		
		$expCount = isset(ClassInfo::$appENV["expansion"]) ? count(ClassInfo::$appENV["expansion"]) : 0;
		$cacher = new Cacher("lang_" . Core::$lang . count(i18n::$languagefiles) . $expCount);
		$mtime = $cacher->created;
		$etag = strtolower(md5("lang_" . var_export($this->getParam("lang"),true) . var_export($output, true)));
		if($outputNull === false) {
			HTTPResponse::addHeader('Cache-Control','public, max-age=5511045');
			HTTPResponse::addHeader("pragma","Public");
		}
		
		HTTPResponse::addHeader("Etag", '"'.$etag.'"');
		
		// 304 by HTTP_IF_MODIFIED_SINCE
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{					
				if(strtolower(gmdate('D, d M Y H:i:s', $mtime).' GMT') == strtolower($_SERVER['HTTP_IF_MODIFIED_SINCE']))
				{
						HTTPResponse::setResHeader(304);
						HTTPResponse::sendHeader();
						if(PROFILE)
							Profiler::End();
							
						exit;
				}
		}
		
		// 304 by ETAG
		if(isset($_SERVER["HTTP_IF_NONE_MATCH"]))
		{
				if($_SERVER["HTTP_IF_NONE_MATCH"] == '"' . $etag . '"')
				{
						HTTPResponse::setResHeader(304);
						HTTPResponse::sendHeader();
						
						if(PROFILE)
							Profiler::End();
						
						exit;
				}
		}
		
		$expiresAdd = defined("DEV_MODE") ? 3 * 60 * 60 : 48 * 60 * 60;
		if($outputNull === false) {
			HTTPResponse::setCachable(NOW + $expiresAdd, $mtime, true);
		}
		
		HTTPResponse::setHeader("content-type", "text/x-json");
		HTTPResponse::output('('.json_encode($output).')');
		exit;
	}
	
	/**
	 * uploads files for the ckeditor
	 *
	 *@name ckeditor_upload
	 *@access public
	*/
	public function ckeditor_upload() {
	
		if(!isset($_GET["accessToken"]) || !isset($_SESSION["uploadTokens"][$_GET["accessToken"]])) {
			die(0);
		}
	
		$allowed_types = array(
			"jpg",
			"png",
			"bmp",
			"jpeg",
			"zip",
			"rar",
			"doc",
			"txt",
			"text",
			"pdf",
			"dmg",
			"7z",
			"gif",
			"mp3",
			"xls",
			"xlsx",
			"docx",
			"pptx",
			"numbers",
			"key",
			"pages"
		);
		$allowed_size = 100 * 1024 * 1024;
		
		
		if(isset($_SERVER["HTTP_X_FILE_NAME"]) && !isset($_FILES["upload"])) {
			if(Core::$phpInputFile) {
				$tmp_name = Core::$phpInputFile;

				if(filesize($tmp_name) == $_SERVER["HTTP_X_FILE_SIZE"]) {
					$_FILES["upload"] = array(
						"name" => $_SERVER["HTTP_X_FILE_NAME"],
						"size" => $_SERVER["HTTP_X_FILE_SIZE"],
						"error" => 0,
						"tmp_name" => $tmp_name
					);
				}
				
			}
		}
		
		if(isset($_FILES["upload"])) {
			if($_FILES["upload"]["error"] == 0) {
				if(GOMA_FREE_SPACE - $_FILES["upload"]["size"] < 10 * 1024 * 1024) {
					return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("error_disk_space").'");</script>';
				}
		
		
				if(preg_match('/\.('.implode("|", $allowed_types).')$/i',$_FILES["upload"]["name"])) {
					$filename = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $_FILES["upload"]["name"]);
					if($_FILES["upload"]["size"] <= $allowed_size) {
						if($response = Uploads::addFile($filename, $_FILES["upload"]["tmp_name"], "ckeditor_uploads")) {
							echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', \'./'.$response->path.'\', "");</script>';
							exit;
						} else {
							return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
						}
					} else {
						return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.filesize_failure").'");</script>';
					}
				} else {
					return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.filetype_failure").'");</script>';

				}
			} else {
				return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
			}
		} else {
			return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
		}
	}
	
	/**
	 * uploads files for the ckeditor
	 *
	 *@name ckeditor_upload
	 *@access public
	*/
	public function ckeditor_imageupload() {
	
		if(!isset($_GET["accessToken"]) || !isset($_SESSION["uploadTokens"][$_GET["accessToken"]])) {
			die(0);
		}
	
		$allowed_types = array(
			"jpg",
			"png",
			"bmp",
			"jpeg",
			"gif"
		);
		$allowed_size = 20 * 1024 * 1024;
		
		if(isset($_SERVER["HTTP_X_FILE_NAME"]) && !isset($_FILES["upload"])) {
			if(Core::$phpInputFile) {
				$tmp_name = Core::$phpInputFile;

				if(filesize($tmp_name) == $_SERVER["HTTP_X_FILE_SIZE"]) {
					$_FILES["upload"] = array(
						"name" => $_SERVER["HTTP_X_FILE_NAME"],
						"size" => $_SERVER["HTTP_X_FILE_SIZE"],
						"error" => 0,
						"tmp_name" => $tmp_name
					);
				}
				
			}
		}
		
		if(isset($_FILES["upload"])) {
			if($_FILES["upload"]["error"] == 0) {
				if(GOMA_FREE_SPACE - $_FILES["upload"]["size"] < 10 * 1024 * 1024) {
					return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("error_disk_space").'");</script>';
				}
				
				if(preg_match('/\.('.implode("|", $allowed_types).')$/i',$_FILES["upload"]["name"])) {
					$filename = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $_FILES["upload"]["name"]);
					if($_FILES["upload"]["size"] <= $allowed_size) {
						if($response = Uploads::addFile($filename, $_FILES["upload"]["tmp_name"], "ckeditor_uploads")) {
							$info = GetImageSize($response->realfile);
							$width = $info[0];
							$height = $info[0];
							if(filesize($response->realfile) > 1024 * 1024 || $width > 2000 || $height > 2000) {
								$add = 'alert(parent.lang("alert_big_image"));';
							} else {
								$add = "";
							}
							
							echo '<script type="text/javascript">'.$add.'
							window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', \'./'.$response->path . "/index" . substr($response->filename, strrpos($response->filename, ".")).'\', "");</script>';
							exit;
						} else {
							return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
						}
					} else {
						return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.filesize_failure").'");</script>';
					}
				} else {
					return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.filetype_failure").'");</script>';

				}
			} else {
				return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
			}
		} else {
			return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
		}
	}
	
	/**
	 * indexes some records for search.
	*/
	public function indexSearch() {
		if(!Permission::check("ADMIN"))
			return false;
		
		session_write_close();
		$maximum = $this->getParam("max") ? $this->getParam("max") : 10;
		$manipulation = array();
		foreach(ClassInfo::getChildren("DataObject") as $class) {
			
			
			if (in_array("searchindex", Object::$extensions[$class])) {
				$notIndexed = DataObject::get($class, "indexversion = 0 OR indexversion < '".SearchIndex::VERSION."'", array(), $max);
				foreach($notIndexed as $record) {
					if(microtime(true) - EXEC_START_TIME > 2.0)
						return true;
					
					SearchIndex::indexRecord($record);
					$manipulation[] = array(
							"command"		=> "update",
							"table_name"	=> $record->table(),
							"id"			=> $record->versionid,
							array(
								"indexversion"	=> SearchIndex::VERSION
							)
						);
				}
				
				SQL::manipulate($manipulation);
			}
		}
		
		return 1;
	}
}