<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 25.11.2012
  * $Version 1.1.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Group extends DataObject implements HistoryData, PermProvider
{
		/**
		 * name of this model
		 *
		 *@name name
		 *@access public
		*/
		public static $cname = '{$_lang_group}';
		
		/**
		 * icon for this model
		 *
		 *@name icon
		 *@access public
		*/
		static public $icon = "images/icons/fatcow16/group.png";
		
		
		/**
		 * belongs many-many
		 *
		 *@name belongs_many_many
		 *@access public
		*/
		public $belongs_many_many = array(
			"users"			=> "user",
			"permissions"	=> "Permission"
		);
		
		/**
		 * the table_name
		 *
		 *@name table_name
		 *@access public
		*/
		public $table_name = "groups";
		
		/**
		 * database-fields
		 *
		 *@name db_fields
		 *@access public
		 *@var array
		*/
		public $db_fields = array(	"name"	 	=> 'varchar(100)',
									"type"		=> 'enum("0", "1", "2")');
		
		/**
		 * fields, whch are searchable
		 *
		 *@name searchable_fields
		 *@access public
		*/
		public $searchable_fields = array(
			"name"
		);
		
		/**
		 * generates the form to create a new group
		 *
		 *@name getForm
		 *@access public
		*/
		public function getForm(&$form)
		{
				$form->add(new TabSet("tabs", array(
					new Tab("general",array(
						new TextField("name", lang("name", "Name")),
						new Select("type", lang("grouptype"), array(1 => lang("users"), 2 => lang("admins")))
					), lang("general", "general information"))
				)));

				
				$form->addValidator(new RequiredFields(array("name")), "valdiator");
				$form->addAction(new Button("cancel", lang("cancel", "cancel"), "LoadTreeItem(0);"));
				$form->addAction(new FormAction("savegroup", lang("save", "Save"), null, array("green")));
				
		}
		/**
		 * generates the form to edit a group
		 *
		 *@name getEditForm
		 *@access public
		*/
		public function getEditForm(&$form)
		{
				// default form
				$form->add($tabs = new TabSet("tabs", array(
					new Tab("general",array(
						new TextField("name",  lang("name", "Name")),
					), lang("general", "general information"))
				)));
				
				// permissions
				if(Permission::check("canManagePermissions")) {
					$form->tabs->add(new Tab("permissionstab", array(
						
					), lang("rights")));
					
					$form->permissionstab->add(new ClusterFormField("permissions", ""));
					
					foreach(Permission::$providedPermissions as $name => $data) {
						$active = ($this->permissions(array("name" => $name))->count() > 0) ? 1 : 0;
						$form->permissions->add(new Checkbox($name, parse_lang($data["title"]), $active));
					}
				}
				
				$form->addDataHandler(array($this, "handlePerms"));
				
				$form->addValidator(new RequiredFields(array("name")), "validator");
				
				$form->addAction(new CancelButton("cancel", lang("cancel", "cancel")));
				$form->addAction(new FormAction("savegroup", lang("save", "Save"), null, array("green")));
		}
		
		/**
		 * rewrites permissions to object
		 *
		 *@name handlePerms
		 *@access public
		*/
		public function handlePerms($data) {
			$dataset = new ManyMany_DataObjectSet("permission");
			foreach($data["permissions"] as $key => $val) {
				if($val) {
					// check for created
					Permission::forceExisting($key);
					if($record = DataObject::get_one("Permission", array("name" => $key)))
						$dataset->add($record);
				}
			}
			$data["permissions"] = $dataset;
			
			return $data;
		}
		
		/**
		 * TREE-API v2
		 * this API renders trees more flexibel and with better performance
		*/ 
		
		/**
		 * gets the subtree from a given parentid or from 0, so from root
		 *
		 *@name getTree
		 *@access public
		 *@param numeric - parentid of subtree
		 *@param array - fields
		*/
		public function getTree($parentid = 0)
		{
			if(PROFILE) Profiler::mark("group::getTree");
			
			/* --- */
			
			
			$arr = array();
			if($parentid == 0) {
				$data = DataObject::get("group");
				foreach($data as $record) {
					$class = $record["class_name"];
					
					// count subtree
					$count = $record->Users()->Count();
					if($count > 0) {
						if($count == 5) {
							$children = "ajax";
						} else {
							$children = $this->getTree($record["id"]);
						}
					} else {
						$children = array();
					}
					
					// get data
					$arr[] = array(
						"title" 		=> $record["name"],
						"attributes"	=> array("class" => $class),
						"data"			=> $record->toArray(),
						"children"		=> $children
					);
				}
			} else {
				$data = DataObject::get("group", array("id" => $parentid))->Users();
				foreach($data as $record) {
					if($record["status"] == 0) {
						$status = "not_unlocked";
					} else if($record["status"] == 2) {
						$status = "disabled";
					} else {
						$status = "activated";
					}
					$class = $record["class_name"] . " " . $status;
					// get data
					$arr[] = array(
						"title" 		=> $record["nickname"],
						"attributes"	=> array("class" => $class),
						"data"			=> $record->toArray(),
						"children"		=> array()
					);
				}
			}
			
			if(PROFILE) Profiler::unmark("group::getTree");
			
			return $arr;
		}
		/**
		 * gets the subtree from a given parentid or from 0, so from root
		 *
		 *@name searchTree
		 *@access public
		 *@param array - words
		 *@param array - fields
		*/
		public function searchTree($words = array())
		{
			if(PROFILE) Profiler::mark("group::searchTree");
			
			$arr = array();
			
			$data = DataObject::_search("user", $words, array());
			foreach($data as $record) {
				if(!isset($arr[$record["groupid"]])) {
					$parent = DataObject::_get("group", array("id" => $record["groupid"]));
					$arr[$parent["id"]] = array(
						"title"			=> $parent["name"],
						"attributes"	=> array("class" => $parent["class_name"]),
						"data"			=> $parent->toArray(),
						"children"		=> array(),
						"collapsable"	=> false,
						"collapsed"		=> false
					);
				}
				if($record["status"] == 0) {
					$status = "not_unlocked";
				} else if($record["status"] == 2) {
					$status = "disabled";
				} else {
					$status = "activated";
				}
				
				
				$arr[$record["groupid"]]["children"][] =  array(
					"title" 		=> $record["nickname"],
					"attributes"	=> array("class" => $record["class_name"] . " " . $status),
					"data"			=> $record->toArray(),
					"children"		=> array()
				);
			}
			
			if(PROFILE) Profiler::unmark("group::searchTree");
			
			return $arr;
		}
		
		/**
		 * provide perms
		*/
		public function providePerms() {
			return array(
				"canManagePermissions"	=> array(
					"title"		=> '{$_lang_rights_manage}',
					"default"	=> array(
						"type"	=> "admins"
					)
				)
			);
		}
		
		/**
		 * returns text what to show about the event
		 *
		 *@name generateHistoryData
		 *@access public
		*/
		public static function generateHistoryData($record) {
			switch($record->action) {
				case "update":
				case "publish":
					$lang = lang("h_group_update", '$user updated the group <a href="$groupUrl">$group</a>');
					$icon = "images/icons/fatcow16/group_edit.png";
				break;
				case "insert":
					$lang = lang("h_group_create", '$user created the group <a href="$groupUrl">$group</a>');
					$icon = "images/icons/fatcow16/group_add.png";
				break;
				case "remove":
					$lang = lang("h_user_remove", '$user removed the group $group');
					$icon = "images/icons/fatcow16/group_delete.png";
				break;
			}
			
			$lang = str_replace('$groupUrl', "admin/group/" . $record->record()->id . URLEND, $lang);
			$lang = str_replace('$group', convert::Raw2text($record->record()->name), $lang);
			
			return array("icon" => $icon, "text" => $lang);
		}
		
}

/**
 * needed by framework
*/
class groupController extends Controller { }
