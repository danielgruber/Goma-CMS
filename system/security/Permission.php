<?php
/**
  * this class provides some methods to check permissions of the current activated group or user
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 20.05.2012
  * $Version 2.1.5
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

ClassInfo::addSaveVar("Permission","providedPermissions");

class Permission extends DataObject
{
		/**
		 * defaults
		 *
		 *@name defaults
		 *@access public
		*/
		public $defaults = array(
			"type"	=> "admins"
		);
		
		/**
		 * all permissions, which are available in this object
		 *
		 *@name providedPermissions
		 *@access public
		*/
		public static $providedPermissions = array(
			"superadmin"	=> array(
				"title"		=> '{$_lang_full_admin_permissions}',
				"default"	=> array(
					"type"	=> "admins"
				)
			)
		);
		
		/**
		 * fields of this set
		 *
		 *@name db_fields
		 *@access public 
		*/
		public $db_fields = array(
			"name"			=> "varchar(100)",
			"type"			=> "enum('all', 'users', 'admins', 'password', 'groups')",
			"password"		=> "varchar(100)",
			"invert_groups"	=> "int(1)",
			"forModel"		=> "varchar(100)"
		);
		
		/**
		 * every permission can be inherited by a parent permission, here we define this relation-ship
		 *
		 *@name has_one
		 *@access public
		*/
		public $has_one = array(
			"inheritor"	=> "Permission"
		);
		
		/**
		 * groups-relation of this set
		 *
		 *@name many_many
		 *@access public
		*/
		public $many_many = array(
			"groups"	=> "group"
		);
		
		/**
		 * indexes
		 *
		 *@name indexes
		 *@access public
		*/
		public $indexes = array(
			"name" => "INDEX"
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
		public function addPermissions($perms) {
			self::$providedPermissions = ArrayLib::map_key("strtolower", array_merge(self::$providedPermissions, $perms));
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
							
							$perm = new Permission(array_merge(self::$providedPermissions[$r]["default"], array("name" => $r)));
							if(isset($perm->inherit)) {
								if($data = DataObject::get_one("Permission", array("name" => $perm->inherit))) {
									$data->consolidate();
									$data->inheritorid = $data->id;
									$data->forModel = "permission";
									$data = $data->_clone();
									$data->name = $perm->name;
									self::$perm_cache[$r] = $data->hasPermission();
									$data->write(true, true, 2);
									return self::$perm_cache[$r];
								}
							} else
							if($perm->inheritorid) {
								if($data = DataObject::get_by_id("Permission",$perm->inheritorid)) {
									$data->consolidate();
									$data->inheritorid = $perm->inheritorid;
									$data->forModel = "permission";
									$data = $data->_clone();
									$data->name = $perm->name;
									self::$perm_cache[$r] = $data->hasPermission();
									$data->write(true, true, 2);
									return self::$perm_cache[$r];
								}
							}
							
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
			if($data = DataObject::get_one("Permission", array("name" => array("LIKE", $r)))) {
				return $data;
			} else {
				$perm = new Permission(array_merge(self::$providedPermissions[$r]["default"], array("name" => $r)));
				if(isset($perm->inherit)) {
					if($data = DataObject::get_one("Permission", array("name" => $perm->inherit))) {
						$data->consolidate();
						$data->inheritorid = $data->id;
						$data->forModel = "permission";
						$data = $data->_clone();
						$data->name = $perm->name;
						$data->write(true, true, 2);
						return $data;
					}
				} else
				if($perm->inheritorid) {
					if($data = DataObject::get_by_id("Permission",$perm->inheritorid)) {
						$data->consolidate();
						$data->inheritorid = $perm->inheritorid;
						$data->forModel = "permission";
						$data = $data->_clone();
						$data->name = $perm->name;
						$data->write(true, true, 2);
						return $data;
					}
				}
				
				if(isset(self::$providedPermissions[$r]["default"]["type"]))
					$perm->setType(self::$providedPermissions[$r]["default"]["type"]);
				
				$perm->write(true, true, 2);
				
				return $perm;
			}
		}
		
		/** 
		 * writing
		 *
		 *@name onBeforeWrite
		 *@access public
		*/
		public function onBeforeWrite() {
			if($this->inheritorid == $this->id)
				$this->inheritorid = 0;
			
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
				$data = DataObject::Get("Permission", array("inheritorid" => $this->id));
				if($data->Count() > 0) {
					foreach($data as $record) {
						if($record->id != $record->inheritorid) {
							$newrecord = $this->_clone();
							$newrecord->name = $record->name;
							$newrecord->inheritorid = $record->inheritorid;
							$newrecord->write(true, true);
						}
					}
				}
			}
			
			parent::onBeforeWrite();
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
				
				if($needed < 11) {
					return (member::$groupType > 1);
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

}