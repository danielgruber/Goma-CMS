<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 30.10.2011
  * $Version 003
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class systemController extends Controller {
	/**
	 * you can set the mobile state to active or disabled
	*/
	public $url_handlers = array(
		"setMobile/0"			=> "disableMobile",
		"setMobile/1"			=> "enableMobile",
		"setUserView/\$bool!"	=> "setUserView",
		"switchView"
	);
	
	public $allowed_actions = array("disableMobile", "enableMobile", "setUserView", "switchView");
	
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
	
}