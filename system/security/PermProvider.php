<?php
/**
  * this class provides some methods to check permissions of the current activated group or user
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 21.02.2013
  * $Version 2.1.8
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 * Permission-provider
*/
interface PermissionProvider
{
		public function providePermissions();
}

interface PermProvider {
	public function providePerms();
}