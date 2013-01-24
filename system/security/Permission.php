<?php
/**
  * this class provides some methods to check permissions of the current activated group or user
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 07.12.2012
  * $Version 2.1.6
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

ClassInfo::addSaveVar("Permission","providedPermissions");

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


class Permission extends DataObject
{
		/**
		 * defaults
		 *
		 *@name defaults
		 *@access public
		*/
		static $default = array(
			"type"	=> "admins"
		);
		
		/**
		 * all permissions, which are available in this object
		 *
		 *@name providedPermissions
		 *@access public
		*/
		public static $providedPermissions = array(
			"superadmin"		=> array(
				"title"			=> '{$_lang_full_admin_permissions}',
				"default"		=> array(
					"type"		=> "admins"
				),
				"description"	=> '{$_lang_full_admin_permissions_info}'
			)
		);
		
		/**
		 * cache for reordered permissions
		 *
		 *@name reorderedPermissions
		*/
		static $reorderedPermissions;
		
		/**
		 * fields of this set
		 *
		 *@name db_fields
		 *@access public 
		*/
		static $db = array(
			"name"			=> "varchar(100)",
			"type"			=> "enum('all', 'users', 'admins', 'password', 'groups')",
			"password"		=> "varchar(100)",
			"invert_groups"	=> "int(1)",
			"forModel"		=> "varchar(100)"
		);
		
		/**
		 * groups-relation of this set
		 *
		 *@name many_many
		 *@access public
		*/
		static $many_many = array(
			"groups"	=> "group"
		);
		
		/**
		 * indexes
		 *
		 *@name indexes
		 *@access public
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
		 *@name perm_cache
		 *@access private
		*/
		private static $perm_cache = array();
		
		/**
		 * adds available Permission-groups
		 *
		 *@name addPermissions
		 *@access public
		*/
		public static function addPermissions($perms) {
			self::$providedPermissions = ArrayLib::map_key("strtolower", array_merge(self::$providedPermissions, $perms));
		}
		
		/**
		 * reorders all permissions as in hierarchy
		 *
		 *@name reOrderedPermissions
		*/
		public static function reorderedPermissions() {
			if(isset(self::$reorderedPermissions)) {
				return self::$reorderedPermissions;
			}
			
			$perms = array();
			foreach(self::$providedPermissions as $name => $data) {
				if(!isset($data["category"]) && $name != "superadmin") {
					$perms[$name] = $data;
					// get children
					if($children = self::reorderedPermissionsHelper($name)) {
						$perms[$name]["children"] = $children;
					}
				}
			}
			
			$perms = array(
				"superadmin" => array_merge(self::$providedPermissions["superadmin"], array(
					"children" 		=> array_merge($perms, self::reorderedPermissionsHelper("superadmin")),
					"forceSubOn1" 	=> true
				))
			);
			
			self::$reorderedPermissions = $perms;
			return $perms;
			
		}
		
		/**
		 * helper which gets all children for given permission
		 *
		 *@name reorderedPermissionsHelper
		 *@access protected
		*/
		protected static function reorderedPermissionsHelper($perm) {
			$perms = array();
			$perm = strtolower($perm);
			foreach(self::$providedPermissions as $name => $data) {
				// get children for given perm
				if(isset($data["category"]) && strtolower($data["category"]) == $perm) {
					$perms[$name] = $data;
					
					// get children for current subperm
					if($children = self::reorderedPermissionsHelper($name)) {
						$perms[$name]["children"] = $children;
					}
				}
			}
			return $perms;
		}
		
		/**
		 * checks if the user has the given permission 
		 *
		 *@name check
		 *@param string - permission
		*/
		public static function check($r)
		{
				$r = strtolower($r);
				
				if(!defined("SQL_INIT"))
					return true;
				
				if(isset(self::$perm_cache[$r]))
					return self::$perm_cache[$r];
				
				if($r != "superadmin" && self::check("superadmin")) {
					return true;
				}
				
				if(_ereg('^[0-9]+$',$r)) {
					return self::right($r);
				} else {
					if(isset(self::$providedPermissions[$r])) {
						if($data = DataObject::get_one("Permission", array("name" => array("LIKE", $r)))) {
							self::$perm_cache[$r] = $data->hasPermission();
							$data->forModel = "permission";
							if($data->type != "groups") {
								$data->write(false, true, 2);
							}
							return self::$perm_cache[$r];
						} else {
							
							if(isset(self::$providedPermissions[$r]["default"]["inherit"]) && strtolower(self::$providedPermissions[$r]["default"]["inherit"]) != $r) {
								if($data = self::forceExisting(self::$providedPermissions[$r]["default"]["inherit"])) {
									$perm = clone $data;
									$perm->consolidate();
									$perm->id = 0;
									$perm->parentid = $data->id;
									$perm->name = $r;
									$data->forModel = "permission";
									self::$perm_cache[$r] = $perm->hasPermission();
									$perm->write(true, true, 2);
									return self::$perm_cache[$r];
								}
							}
							$perm = new Permission(array_merge(self::$providedPermissions[$r]["default"], array("name" => $r)));
							
							if(isset(self::$providedPermissions[$r]["default"]["type"]))
								$perm->setType(self::$providedPermissions[$r]["default"]["type"]);
							
							self::$perm_cache[$r] = $perm->hasPermission();
							$perm->write(true, true, 2);
							return self::$perm_cache[$r];
						}
					} else {
						if(Member::Admin()) {
							return true; // soft allow
						}
						
						return false; // soft deny
					}
				}
		}
		
		/**
		 * forces that a specific permission exists
		 *
		 *@name forceExisting
		 *@return Permission
		*/
		public function forceExisting($r) {
			$r = strtolower(trim($r));
			if(isset(self::$providedPermissions[$r])) { 
				if($data = DataObject::get_one("Permission", array("name" => array("LIKE", $r)))) {
					return $data;
				} else {
					if(isset(self::$providedPermissions[$r]["default"]["inherit"]) && strtolower(self::$providedPermissions[$r]["default"]["inherit"]) != $r) {
						if($data = self::forceExisting(self::$providedPermissions[$r]["default"]["inherit"])) {
							$perm = clone $data;
							$perm->consolidate();
							$perm->id = 0;
							$perm->parentid = $data->id;
							$perm->name = $r;
							$data->forModel = "permission";
							self::$perm_cache[$r] = $perm->hasPermission();
							$perm->write(true, true, 2);
							return self::$perm_cache[$r];
						}
					}
					$perm = new Permission(array_merge(self::$providedPermissions[$r]["default"], array("name" => $r)));
					
					if(isset(self::$providedPermissions[$r]["default"]["type"]))
						$perm->setType(self::$providedPermissions[$r]["default"]["type"]);
					
					$perm->write(true, true, 2);
					
					return $perm;
				}
			} else {
				return false;
			}
		}
		
		/** 
		 * writing
		 *
		 *@name onBeforeWrite
		 *@access public
		*/
		public function onBeforeWrite() {
			if($this->parentid == $this->id)
				$this->parentid = 0;
			
			if($this->parentid != 0 && $perm = DataObject::get_by_id("Permission", $this->parentid)) {
				if($this->hasChanged()) {
					$this->type = $perm->type;
					$this->password = $perm->password;
					$this->invert_groups = $perm->invert_groups;
					$this->groups = $perm->groups;
				}
			} else {
				$this->parentid = 0;
			}
			
			if($this->name) {
				if($this->type != "groups") {
					switch($this->type) {
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
			
			if($this->id != 0) {
				// inherit permissions to subordinated perms
				$data = DataObject::Get("Permission", array("parentid" => $this->id));
				if($data->Count() > 0) {
					foreach($data as $record) {
						if($record->id != $record->parentid) {
							$newrecord = clone $this;
							$newrecord->consolidate();
							$newrecord->parentid = $this->id;
							$newrecord->id = $record->id;
							$newrecord->name = $record->name;
							$newrecord->parentid = $record->parentid;
							
							$newrecord->write(false, true);
						}
					}
				}
			}
			
			parent::onBeforeWrite();
		}
		
		/**
		 * preserve Defaults
		 *
		 *@name preserveDefaults
		 *@åccess public
		*/
		public function preserveDefaults($prefix = DB_PREFIX, &$log) {
			parent::preserveDefaults($prefix, $log);
			
			foreach(self::$providedPermissions as $name => $data) {
				self::forceExisting($name);
			}
		}
		
		/**
		 * sets the type
		 *
		 *@name setType
		 *@access public
		*/
		public function setType($type) {
			switch($type) {
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
		 *@name rechte
		 *@param numeric - needed rights
		 *@return bool
		*/
		function right($needed)
		{
				if(!defined("SQL_INIT"))
					return true;
				
				if($needed < 2) {
					return true;
				}
				
				if($needed < 7) {
					return (member::$groupType > 0);
				}
				
				if($needed < 10) {
					return (member::$groupType > 1);
				}
				
				if($needed == 10) {
					return Permission::check("superadmin");
				}
		}
		
		
		/**
		 * checks if the current user has the permission to do this
		 *
		 *@name hasPermission
		 *@access public
		*/
		public function hasPermission() {
			if(!defined("SQL_INIT"))
				return true;
			
			if($this->type == "all") {
				return true;
			}
			
			if($this->type == "users") {
				return (member::$groupType > 0);
			}
			
			if($this->type == "admins") {
				return (member::$groupType > 1);
			}
			
			if($this->type == "password") {
				
			}
			
			if($this->type == "groups") {
				$groups = $this->Groups()->fieldToArray("id");
				if($this->invert_groups) {
					if(count(array_intersect($groups, member::groupids())) > 0) {
						return false;
					} else {
						return true;
					}
				} else {
					if(count(array_intersect($groups, member::groupids())) > 0) {
						return true;
					} else {
						return false;
					}
				}
			}
			
			return (member::$groupType > 0);
		}
		
		// DEPRECATED API!
		public function inheritor() {
			Core::deprecate("2.0.1", "inheritor is deprecated, use parent instead");
			return $this->parent;
		}
		public function inheritorid() {
			Core::deprecate("2.0.1", "inheritorid is deprecated, use parentid instead");
			return $this->parentid;
		}

}