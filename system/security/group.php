<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 03.02.2013
  * $Version 1.2.1
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
		 * database-fields
		 *
		 *@name db
		 *@access public
		 *@var array
		*/
		static $db = array(	"name"	 	=> 'varchar(100)',
							"type"		=> 'enum("0", "1", "2")');
		
		
		/**
		 * fields, whch are searchable
		 *
		 *@name search_fields
		 *@access public
		*/
		static $search_fields = array(
			"name"
		);
		
		/**
		 * belongs many-many
		 *
		 *@name belongs_many_many
		 *@access public
		*/
		static $belongs_many_many = array(
			"users"			=> "user",
			"permissions"	=> "Permission"
		);
		
		/**
		 * sort by name
		*/
		static $default_sort = array("name", "ASC");
		
		/**
		 * the table_name
		 *
		 *@name table_name
		 *@access public
		*/
		public $table_name = "groups";
		
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
					$form->general->add(new ClusterFormField("permissions", lang("rights")));
					
					foreach(Permission::$providedPermissions as $name => $data) {
						$active = ($this->permissions(array("name" => $name))->count() > 0) ? 1 : 0;
						$form->permissions->add(new Checkbox($name, parse_lang($data["title"]), $active));
						if(isset($data["description"])) {
							$form->permissions->{$name}->info = parse_lang($data["description"]);
						}
					}
					
					$form->addDataHandler(array($this, "handlePerms"));
				}
				
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
			$dataset->setData();
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
		 * provide perms
		*/
		public function providePerms() {
			return array(
				"canManagePermissions"	=> array(
					"title"		=> '{$_lang_rights_manage}',
					"default"	=> array(
						"type"	=> "admins"
					),
					"category"	=> "ADMIN"
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
			if(!$record->record())
				return false;
			
			$relevant = true;
			
			if(!$record->autor || $record->record()->name == "") {
				$relevant = false;
			}
			
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
				default:
					$lang = "Unknowen event " . $record->action;
					$icon = "images/icons/fatcow16/group_edit.png";
			}
			
			$lang = str_replace('$groupUrl', "admin/group/" . $record->record()->id . URLEND, $lang);
			$lang = str_replace('$group', convert::Raw2text($record->record()->name), $lang);
			
			return array("icon" => $icon, "text" => $lang, "relevant" => $relevant);
		}
		
}

/**
 * needed by framework
*/
class groupController extends Controller { }
