<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 13.01.2012
  * $Version 1.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HomePageController extends RequestHandler {
	/**
	 * shows install fronted if language is already selected, else shows lang-select
	*/
	public function index() {
		if(isset($_SESSION["langselected"]) || isset($_GET["setlang"])) {
			$_SESSION["langselected"] = true;
			$controller = new InstallController();
			return $controller->handleRequest($this->request);
		} else {
			return $this->langSelect();
		}
	}
	/**
	 * shows lang-select
	 *
	 *@name langSelect
	*/
	public function langSelect() {
		$data = new ViewAccessAbleData();
		return $data->customise(array("firstrun" => 1,"content" => tpl::Render("install/langselect.html")))->renderWith("install/install.html");
	}
	
	/**
	 * returns an array of the wiki-article and youtube-video for this controller
	 *
	 *@name helpArticle
	 *@access public
	*/
	public function helpArticle() {
		return array("yt" => "QcIBX3Rh0RA#t=03m08s");
	}
}