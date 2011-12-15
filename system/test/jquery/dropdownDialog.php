
<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 01.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class DropDownDialogTest extends Test
{
	public $name = "dropdownDialog";
	public function render()
	{
			if(isset($_GET["data"]))
				return "Hallo Welt!<br /><br /><br /><br /><br /><br /><br /><br />";
			return tpl::render("test/dropdownDialog.html");
	}
	
}

Object::extend("TestController", "DropDownDialogTest");