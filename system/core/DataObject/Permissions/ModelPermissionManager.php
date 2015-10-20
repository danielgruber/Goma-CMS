<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for Permission-Managment.
 *
 * @package     Goma\Model\Permission
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class ModelPermissionManager
{
    const PERMISSION_TYPE_INSERT = "insert";
    const PERMISSION_TYPE_WRITE = "write";
    const PERMISSION_TYPE_DELETE = "delete";
    const PERMISSION_TYPE_READ = "read";
    const PERMISSION_TYPE_PUBLISH = "publish";
}
