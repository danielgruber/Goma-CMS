<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 30.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ControllerClassInfo extends Extension {
	/**
	 * generates extra class-info for controllers
	 *@name generate
	 *@access public
	 *@param string - class
	*/
	public function generate($class)
	{
		if(is_subclass_of($class, "RequestHandler")) {
			
			if(!ClassInfo::isAbstract($class)) {
				$c = new $class(null, null);
				
				$allowed_actions = $c->allowed_actions;
				foreach($c->callExtending("allowed_actions") as $actions) {
					$allowed_actions = array_merge($allowed_actions, $actions);
					unset($actions);
				}
				classinfo::$class_info[$class]["allowed_actions"] = array_map("strtolower", $allowed_actions);
				classinfo::$class_info[$class]["allowed_actions"] = ArrayLib::map_key("strtolower", classinfo::$class_info[$class]["allowed_actions"]);
				unset($allowed_actions);
				
				$url_handlers = $c->url_handlers;
				foreach($c->callExtending("url_handlers") as $handlers) {
					$url_handlers = array_merge($url_handlers, $handlers);
					unset($handlers);
				}
				classinfo::$class_info[$class]["url_handlers"] = $url_handlers;
				unset($url_handlers);
			}
		}
	}
}
Object::extend("ClassInfo", "ControllerClassInfo");