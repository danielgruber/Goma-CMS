<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 22.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class BackupCreateTest extends Test
{
		public $name = "BackupCreate";
		public function render()
		{
				Backup::generateBackup(ROOT . "/backup_full.gfs");
				
		}
}

Object::extend("TestController", "BackupCreateTest");

class BackupUnpackTest extends Test
{
		public $name = "BackupUnpack";
		public function render()
		{
				$gfs = new GFS_Package_installer(ROOT . "/backup_full.gfs");
				$gfs->unpack(ROOT . "temp_full/");
				
				$db_gfs = new GFS_Package_installer(ROOT . "/temp_full/database.sgfs");
				$db_gfs->unpack(ROOT . "temp_full/database/");
		}
}

Object::extend("TestController", "BackupUnpackTest");
