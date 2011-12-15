<?php
/**
  * this class provides some methods to check permissions of the current activated group or user
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 2.09.2011
  * $Version 2.0.0 - 003
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Permission extends Object
{
		/**
		 * current permission-level
		 *@name currrights
		*/
		public static $currrights = false;
		/**
		 * current groupid
		 *@name groupid
		*/
		public static $groupid = "";
		/**
		 * cache for grouprights
		*/
		public static $groupcache = array();
		/**
		 * checks if the user has the given permission 
		*/
		public static function check($r)
		{
				if(!defined("SQL_INIT"))
					return true;
					
				if(_ereg('^[0-9]+$',$r)) {
					return self::right($r);
				} else if(self::right(10)) {
					return true;
				} else {
					return self::advrights($r, member::groupids());
				}
		}
		/**
		 * gets the current numeric rank
		 *
		 *@name getRank
		 *@access public
		*/
		public function getRank() {
			if(!defined("SQL_INIT"))
					return 10;
			
			if(self::$currrights === false) {
				self::right(2); // for generate
			}
			$max = arraylib::first(self::$currrights);
			if(is_int($max))
				return $max;
			else
				return 1;
		}
		/**
		 * checks if a group have the rights
		 *@name advrights
		 *@param string - name of the rights
		 *@param string - name of group
		 *@return bool
		*/
		public static function advrights($name, $ids)
		{
			if(!defined("SQL_INIT"))
					return true;
			
			if(($ids == member::groupids() || (!is_array($ids) && array($ids) == member::groupids())) && self::right(10)) {
				return true;
			}
			
			
			if(is_array($ids))
				$rang = md5(implode("", $ids));
			else
				$rang = $ids;
				
			if(!isset(self::$groupcache[$rang])) {
				$data = DataObject::get(
					"advrights", // class
					array("groups.recordid" => $ids), // filter
					array(), // sort
					array(), // limits
					array(
						"INNER JOIN `".DB_PREFIX."many_many_group_advrights_advrights` as `advrights_many` ON `advrights_many`.`advrightsid` = `advrights`.`id`",
						"INNER JOIN `".DB_PREFIX."groups` AS `groups` ON `groups`.`id` = `advrights_many`.`groupid`"
					) // joins
				);
				
				$advrights = array();
				foreach($data as $record) {
					$advrights[] = $record->name;
				}
				
				unset($data, $record);
				self::$groupcache[$rang] = $advrights;
			} else {
				$advrights = self::$groupcache[$rang];
			}
			
			if(in_array($name, $advrights)) {
				return true;
			} else {
				return false;
			}
				
				
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
				
				if(isset($_SESSION["user_id"])) {
					if($needed == 1 || $needed == 2)
						return true;
					
					if(self::$currrights === false) {
						$user = DataObject::get("user", array("id" => $_SESSION["user_id"]));
						$group = $user->group(array(), array("rights"));
						
						if($group->rights == 10) {
							$rights = 10;
							self::$currrights = array($group->id => 10);
							return true;
						} else {
							self::$currrights = array($group->id => $group->rights);
							foreach($user->groups() as $group) {
								self::$currrights[$group->id] = $group->rights;
							}		
						}
						arsort(self::$currrights);
					}
					foreach(self::$currrights as $right) {
						if($right >= $needed) {
							return true;
						} else {
							return false;
						}
					}
				} else {
					if($needed == 1) {
						return true;
					}
				}
				return false;
		}
		/**
		 * to use in advrights
		 *@name getcurrentgroup
		 *@return string
		*/
		public static function getcurrentgroup()
		{
				return member::$groupid;
		}

}