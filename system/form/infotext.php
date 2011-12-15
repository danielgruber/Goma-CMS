<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 15.06.2011
  * $Version: 2.0.0 - 004
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class InfoTextField extends Extension {
	/**
	 * adds the info to the field
	*/
	public function afterField() {
		if($this->owner->info)
			$this->owner->container->append(new HTMLNode("div", array("class" => "info_field"), $this->owner->info));
		
	}
}

Object::extend("FormField", "InfoTextField");