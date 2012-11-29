<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 27.11.2012
  * $Version 1.4.3
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
		"ck_uploader"			=> "ckeditor_upload"
	);
	
	public $allowed_actions = array("disableMobile", "enableMobile", "setUserView", "switchView", "getLang", "ckeditor_upload", "logJSProfile");
	
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
		if(empty($lang) || $lang == "*") {
			$output = $GLOBALS["lang"];
		} else {
			if(is_array($lang) && count($lang) > 0) {
				
				foreach($lang as $value) {
					if(isset($GLOBALS["lang"][$value])) {
						$output[$value] = $GLOBALS["lang"][$value];
					} else {
						$output[$value] = null;
					}
				}
			} else if(is_string($lang)) {
				if(isset($GLOBALS["lang"][$lang])) {
						$output[$lang] = $GLOBALS["lang"][$lang];
					} else {
						$output[$lang] = null;
					}
			}
		}
		$cacher = new Cacher("lang_" . Core::$lang . count(i18n::$languagefiles) . count(ClassInfo::$appENV["expansion"]));
		$mtime = $cacher->created;
		$etag = strtolower(md5("lang_" . var_export($this->getParam("lang"),true) . $output));
		HTTPResponse::addHeader('Cache-Control','public, max-age=5511045');
		HTTPResponse::addHeader("pragma","Public");
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
		HTTPResponse::setCachable(NOW + $expiresAdd, $mtime, true);
		
		HTTPResponse::setHeader("content-type", "text/x-json");
		HTTPResponse::output('('.json_encode($output).')');
		exit;
	}
	
	/**
	 * saves a debug-log for javascript
	 *
	 *@name logJSProfile
	 *@access public
	*/
	public function logJSProfile() {
		FileSystem::requireFolder(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/jsprofile/");
		if(Core::is_ajax() && isset($_POST["JSProfile"]) && (strlen($_POST["JSProfile"]) / 1024) <= self::JS_DEBUG_LIMIT && DEV_MODE) {
			$folder = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/jsprofile/".date("m-d-y");
			FileSystem::requireFolder($folder);
			foreach($_POST["JSProfile"]["profiles"] as $data) {
				file_put_contents($folder . "/" . $data["name"] . ".log", "\n\nCount: ".$data["count"]."   Time: ".$data["time"] * 1000 ."ms   User-Agent: ".$_POST["JSProfile"]["user-agent"]."   URL: ".$_POST["JSProfile"]["url"]."", FILE_APPEND);
			}
			return 1;
		}
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
			"xls"
		);
		$allowed_size = 20 * 1024 * 1024;
		
		if(isset($_FILES["upload"])) {
			if($_FILES["upload"]["error"] == 0) {
				if(preg_match('/\.('.implode("|", $allowed_types).')$/i',$_FILES["upload"]["name"])) {
					$filename = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $_FILES["upload"]["name"]);
					if($_FILES["upload"]["size"] <= $allowed_size) {
						if($response = Uploads::addFile($filename, $_FILES["upload"]["tmp_name"], "ckeditor_uploads")) {
							return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "./'.$response->path.'", "");</script>';
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
}