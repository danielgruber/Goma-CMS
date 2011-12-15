<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 18.05.2011
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ControllerTest extends Test {
	var $name = "controllertest";
	public function render() {
		$controller = new Controller();
		if($controller->confirm("Willst du wirklich fortfahren mit dieser Scheisse hier?")) {
			if($name = $controller->prompt("Gib deinen Namen ein...")) {
				return "Hallo " . $name . "!";
			}
		}
		return "tja";
	}
}

Object::extend("TestController", "ControllerTest");