<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 15.06.2011
  * $Version: 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class InfoTextField extends Extension {
	/**
	 * adds the info to the field
	*/
	public function afterField() {
		if(isset($this->owner->info) && $this->owner->info)
			$this->owner->container->append(new HTMLNode("div", array("class" => "info_field"), $this->owner->info));
		
	}
}

Object::extend("FormField", "InfoTextField");