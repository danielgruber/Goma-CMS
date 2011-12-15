<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 26.08.2011
  * $Version 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang("install");

class HomePageController extends RequestHandler {
	/**
	 * shows install fronted if language is already selected, else shows lang-select
	*/
	public function index() {
		if(isset($_SESSION["lang"])) {
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
		return $data->customise(array("content" => tpl::Render("install/langselect.html")))->renderWith("install/install.html");
	}
}