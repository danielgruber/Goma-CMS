<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 13.02.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class GFSCreateTest extends Test
{
		public $name = "GFSCreate";
		public function render()
		{
				$gfs = new GFS(ROOT . "system.gfs");
				$gfs->addFromFile(ROOT . "system/", "/");
				$gfs->close();
				return 1;
		}
}

Object::extend("TestController", "GFSCreateTest");

class GFSUnpackTest extends Test
{
		public $name = "GFSUnpack";
		public function render()
		{
				$gfs = new GFS_Package_installer(ROOT . "system.gfs");
				$gfs->unpack(ROOT . "system/update/");
				return 1;
		}
}

Object::extend("TestController", "GFSUnpackTest");
