<?php defined("IN_GOMA") OR die();
/**
 * this class provides some methods to check permissions of the current activated group or user
 *
 * @package     goma framework
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author      Goma-Team
 * @version     2.2.1
 *
 * @property int parentid
 * @property string forModel
 * @property int id
 * @property string type
 * @property string password
 */
class Permission extends DataObject
{
    /**
     * disable sort
     * @var bool
     */
    public static $default_sort = false;

    /**
     * defaults
     *
     * @name defaults
     * @access public
     */
    static $default = array(
        "type" => "admins"
    );

    /**
     * all permissions, which are available in this object
     *
     * @name providedPermissions
     * @access public
     */
    public static $providedPermissions = array(
        "superadmin" => array(
            "title" => '{$_lang_full_admin_permissions}',
            "default" => array(
                "type" => "admins"
            ),
            "description" => '{$_lang_full_admin_permissions_info}'
        )
    );

    /**
     * cache for reordered permissions
     *
     * @name reorderedPermissions
     */
    static $reorderedPermissions;

    /**
     * fields of this set
     *
     * @name db_fields
     * @access public
     */
    static $db = array(
        "name" => "varchar(100)",
        "type" => "enum('all', 'users', 'admins', 'password', 'groups')",
        "password" => "varchar(100)",
        "invert_groups" => "int(1)",
        "forModel" => "varchar(100)"
    );

    /**
     * groups-relation of this set
     *
     * @name many_many
     * @access public
     */
    static $many_many = array(
        "groups" => "group"
    );

    /**
     * indexes
     *
     * @name indexes
     * @access public
     */
    static $index = array(
        "name" => "INDEX"
    );

    /**
     * extensions for this class
     */
    static $extend = array(
        "Hierarchy"
    );

    /**
     * perm-cache
     *
     * @name perm_cache
     * @access private
     */
    private static $perm_cache = array();

    /**
     * adds available Permission-groups
     *
     * @name addPermissions
     * @access public
     */
    public static function addPermissions($perms)
    {
        self::$providedPermissions = ArrayLib::map_key("strtolower", array_merge(self::$providedPermissions, $perms), false);
    }

    /**
     * reorders all permissions as in hierarchy
     *
     * @name reOrderedPermissions
     * @return array
     */
    public static function reorderedPermissions()
    {
        if (isset(self::$reorderedPermissions)) {
            return self::$reorderedPermissions;
        }

        $perms = array();
        foreach (self::$providedPermissions as $name => $data) {
            if (!isset($data["category"]) && $name != "superadmin") {
                $perms[$name] = $data;
                // get children
                if ($children = self::reorderedPermissionsHelper($name)) {
                    $perms[$name]["children"] = $children;
                }
            }
        }

        $perms = array(
            "superadmin" => array_merge(self::$providedPermissions["superadmin"], array(
                "children" => array_merge($perms, self::reorderedPermissionsHelper("superadmin")),
                "forceSubOn1" => true
            ))
        );

        self::$reorderedPermissions = $perms;
        return $perms;

    }

    /**
     * helper which gets all children for given permission
     *
     * @name reorderedPermissionsHelper
     * @access protected
     * @return array
     */
    protected static function reorderedPermissionsHelper($perm)
    {
        $perms = array();
        $perm = strtolower($perm);
        foreach (self::$providedPermissions as $name => $data) {
            // get children for given perm
            if (isset($data["category"]) && strtolower($data["category"]) == $perm) {
                $perms[$name] = $data;

                // get children for current subperm
                if ($children = self::reorderedPermissionsHelper($name)) {
                    $perms[$name]["children"] = $children;
                }
            }
        }
        return $perms;
    }

    /**
     * checks if the user has the given permission
     *
     * @name check
     * @access public
     * @param $permissionCode string - permission
     * @return bool
     */
    public static function check($permissionCode)
    {
        $userId =  member::$loggedIn ? member::$loggedIn->id : 0;
        $permissionCode = strtolower($permissionCode);

        if (!defined("SQL_INIT"))
            return true;

        if (isset(self::$perm_cache[$userId][$permissionCode]))
            return self::$perm_cache[$userId][$permissionCode];

        if ($permissionCode != "superadmin" && self::check("superadmin")) {
            return true;
        }

        if (RegexpUtil::isNumber($permissionCode)) {
            return self::right($permissionCode);
        } else {
            if (isset(self::$providedPermissions[$permissionCode])) {
                /** @var Permission $data */
                if ($data = DataObject::get_one("Permission", array("name" => array("LIKE", $permissionCode)))) {
                    self::$perm_cache[$userId][$permissionCode] = $data->hasPermission();
                    $data->forModel = "permission";
                    if ($data->type != "groups") {
                        $data->writeToDB(false, true, 2, false, false);
                    }
                    return self::$perm_cache[$userId][$permissionCode];
                } else {

                    if (isset(self::$providedPermissions[$permissionCode]["default"]["inherit"]) && strtolower(self::$providedPermissions[$permissionCode]["default"]["inherit"]) != $permissionCode) {
                        if ($data = self::forceExisting(self::$providedPermissions[$permissionCode]["default"]["inherit"])) {
                            $perm = clone $data;
                            $perm->consolidate();
                            $perm->id = 0;
                            $perm->parentid = 0;
                            $perm->name = $permissionCode;
                            $data->forModel = "permission";
                            self::$perm_cache[$userId][$permissionCode] = $perm->hasPermission();
                            $perm->writeToDB(true, true, 2);
                            return self::$perm_cache[$userId][$permissionCode];
                        }
                    }
                    $perm = new Permission(array_merge(self::$providedPermissions[$permissionCode]["default"], array("name" => $permissionCode)));

                    if (isset(self::$providedPermissions[$permissionCode]["default"]["type"]))
                        $perm->setType(self::$providedPermissions[$permissionCode]["default"]["type"]);

                    self::$perm_cache[$userId][$permissionCode] = $perm->hasPermission();
                    $perm->writeToDB(true, true, 2, false, false);
                    return self::$perm_cache[$userId][$permissionCode];
                }
            } else {
                if (Member::Admin()) {
                    return true; // soft allow
                }

                return false; // soft deny
            }
        }
    }

    /**
     * forces that a specific permission exists
     *
     * @name forceExisting
     * @return Permission
     */
    public static function forceExisting($r)
    {
        $r = strtolower(trim($r));
        if (isset(self::$providedPermissions[$r])) {
            if ($data = DataObject::get_one("Permission", array("name" => array("LIKE", $r)))) {
                return $data;
            } else {
                if (isset(self::$providedPermissions[$r]["default"]["inherit"]) && strtolower(self::$providedPermissions[$r]["default"]["inherit"]) != $r) {
                    if ($data = self::forceExisting(self::$providedPermissions[$r]["default"]["inherit"])) {
                        $perm = clone $data;
                        $perm->consolidate();
                        $perm->id = 0;
                        $perm->parentid = 0;
                        $perm->name = $r;
                        $data->forModel = "permission";
                        self::$perm_cache[$r] = $perm->hasPermission();
                        $perm->writeToDB(true, true, 2, false, false);
                        return self::$perm_cache[$r];
                    }
                }
                $perm = new Permission(array_merge(self::$providedPermissions[$r]["default"], array("name" => $r)));

                if (isset(self::$providedPermissions[$r]["default"]["type"]))
                    $perm->setType(self::$providedPermissions[$r]["default"]["type"]);

                $perm->writeToDB(true, true, 2, false, false);

                return $perm;
            }
        } else {
            return false;
        }
    }

    /**
     * setting the parent-id
     *
     * @name setParentID
     */
    public function setParentID($parentid)
    {
        $this->setField("parentid", $parentid);
        if ($this->parentid != 0 && $perm = DataObject::get_by_id("Permission", $this->parentid)) {
            if ($this->hasChanged()) {
                $this->type = $perm->type;
                $this->password = $perm->password;
                $this->invert_groups = $perm->invert_groups;
                if ($this->type == "groups")
                    $this->groupsids = $perm->groupsids;
            }
        } else {
            $this->parentid = 0;
        }
    }

    /**
     * writing
     *
     * @name onBeforeWrite
     * @access public
     */
    public function onBeforeWrite($modelWriter)
    {
        if ($this->parentid == $this->id)
            $this->parentid = 0;

        if ($this->name) {
            if ($this->type != "groups") {
                switch ($this->type) {
                    case "all":
                    case "users":
                        $this->groups()->addMany(DataObject::get("group"));
                        break;
                    case "admins":
                        $this->groups()->addMany(DataObject::get("group", array("type" => 2)));
                        break;
                }
                $this->groups = $this->groups();
                $this->type = "groups";
            }
        }

        parent::onBeforeWrite($modelWriter);
    }

    /**
     * on before manipulate
     *
     * @name onBeforeManipulate
     * @access public
     */
    public function onBeforeManipulate(&$manipulation, $job)
    {
        if ($this->id != 0 && $job == "write") {
            $subversions = $this->getAllChildVersionIDs();
            if (count($subversions) > 0) {

                if ($this->type == "groups") {
                    $relationShip = $this->getManyManyInfo("groups");
                    $table = $relationShip->getTableName();
                    $manipulation["perm_groups_delete"] = array(
                        "table_name" => $table,
                        "command" => "delete",
                        "where" => array(
                            $relationShip->getOwnerField() => $subversions
                        )
                    );

                    $manipulation["perm_groups_insert"] = array(
                        "table_name" => $table,
                        "command" => "insert",
                        "ignore" => true,
                        "fields" => array()
                    );

                    if ($this->groupsids && count($this->groupsids) > 0) {
                        $i = 10000;
                        foreach ($subversions as $version) {
                            foreach ($this->groupsids as $groupid) {
                                if (is_array($groupid)) {
                                    $groupid = $groupid["versionid"];
                                }

                                $manipulation["perm_groups_insert"]["fields"][] = array(
                                    $relationShip->getOwnerField() => $version,
                                    $relationShip->getTargetField() => $groupid,
                                    $relationShip->getOwnerSortField()  => $i,
                                    $relationShip->getTargetSortField() => $i
                                );
                                $i++;
                            }
                        }
                    }
                }

                $manipulation["perm_update"] = array(
                    "command" => "update",
                    "table_name" => $this->baseTable,
                    "fields" => array(
                        "type" => $this->type,
                        "password" => $this->password,
                        "invert_groups" => $this->invert_groups
                    ),
                    "where" => array(
                        "id" => $subversions
                    )
                );
            }
        }
    }

    /**
     * on before manipulate many-many-relation
     *
     * @param array $manipulation
     * @param ManyMany_DataObjectSet $dataset
     * @param array $writeData
     * @access public
     * @return mixed|void
     */
    public function onBeforeManipulateManyMany(&$manipulation, $dataset, $writeData)
    {
        $ownValue = $dataset->getRelationOwnValue();
        $relationShip = $dataset->getRelationShip();

        $i = 10000;
        foreach ($writeData as $id => $bool) {
            if ($data = DataObject::get_one("Permission", array("versionid" => $id))) {
                foreach ($data->getAllChildVersionIDs() as $childVersionId) {
                    $manipulation[ManyMany_DataObjectSet::MANIPULATION_INSERT_NEW]["fields"][$childVersionId] = array(
                        $relationShip->getOwnerField() => $ownValue,
                        $relationShip->getTargetField() => $childVersionId,
                        $relationShip->getOwnerSortField()  => $i,
                        $relationShip->getTargetSortField() => $i
                    );

                    $i++;
                }
            }
        }
    }

    /**
     * preserve Defaults
     *
     * @name preserveDefaults
     * @åccess public
     * @return bool|void
     */
    public function preserveDefaults($prefix = DB_PREFIX, &$log)
    {
        parent::preserveDefaults($prefix, $log);

        foreach (self::$providedPermissions as $name => $data) {
            self::forceExisting($name);
        }
    }

    /**
     * sets the type
     *
     * @name setType
     * @access public
     */
    public function setType($type)
    {
        switch ($type) {
            case "all":
            case "every":
            case "everyone":
                $type = "all";
                break;

            case "group":
            case "groups":
                $type = "groups";
                break;

            case "admin":
            case "admins":
            case "root":
                $type = "admins";
                break;

            case "password":
                $type = "password";
                break;

            case "user":
            case "users":
                $type = "users";
                break;

            default:
                $type = "users";
                break;
        }

        $this->setField("type", $type);
    }

    /**
     * checks whether a user have the rights for an action
     * @name rechte
     * @param numeric - needed rights
     * @return bool
     */
    function right($needed)
    {
        if (!defined("SQL_INIT"))
            return true;

        if ($needed < 2) {
            return true;
        }

        if ($needed < 7) {
            return (member::$groupType > 0);
        }

        if ($needed < 10) {
            return (member::$groupType > 1);
        }

        if ($needed == 10) {
            return Permission::check("superadmin");
        }
    }


    /**
     * checks if the current user has the permission to do this
     *
     * @name hasPermission
     * @access public
     * @return bool
     */
    public function hasPermission()
    {
        if (!defined("SQL_INIT"))
            return true;

        if ($this->type == "all") {
            return true;
        }

        if ($this->type == "users") {
            return (member::$groupType > 0);
        }

        if ($this->type == "admins") {
            return (member::$groupType > 1);
        }

        if ($this->type == "password") {

        }

        if ($this->type == "groups") {
            $groups = $this->Groups()->fieldToArray("id");
            if ($this->invert_groups) {
                if (count(array_intersect($groups, member::groupids())) > 0) {
                    return false;
                } else {
                    return true;
                }
            } else {
                if (count(array_intersect($groups, member::groupids())) > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return (member::$groupType > 0);
    }

    // DEPRECATED API!
    public function inheritor()
    {
        Core::deprecate("2.1", "inheritor is deprecated, use parent instead");
        return $this->parent;
    }

    public function inheritorid()
    {
        Core::deprecate("2.1", "inheritorid is deprecated, use parentid instead");
        return $this->parentid;
    }

    public function setinheritor($parent)
    {
        Core::deprecate("2.1", "inheritor is deprecated, use parent instead");
        $this->setField("parent", $parent);
    }

    public function setinheritorid($parentid)
    {
        Core::deprecate("2.1", "inheritorid is deprecated, use parentid instead");
        $this->setField("parentid", $parentid);
    }
}
StaticsManager::addSaveVar("Permission", "providedPermissions");
