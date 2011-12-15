<?php
/**
  *@package goma  framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.09.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class group extends DataObject
{
		public $name = '{$_lang_group}';
		static public $icon = "images/icons/fatcow-icons/16x16/group.png";
		/**
		 * connection to adv_rights
		*/
		public $many_many = array('advrights' => 'advrights');
		/**
		 * the table_name
		*/
		public $table_name = "groups";
		/**
		 * database-fields
		*/
		public $db_fields = array(	'name'	 => 'varchar(100)',
									'rights' => 'int(2)');
									
		/**
		 * has_many
		*/
		public $has_many = array
		(
			'users'	=> 'user'
		);
		
		public $searchable_fields = array(
			"name"
		);
		
		public function canRead() {
			return true;
		}
		
		
		
		
		public function getForm(&$form, $data)
		{
				if(!right(10))
				{
						$form->addField("root", new HMTLarea(lang("less_rights", "Forbidden")));
				}
				$form->add(new TabSet("tabs", array(
					new Tab("general",array(
						new TextField("name", lang("name", "Name")),
						$right = new Select("rights", lang("rights", "Rights"), array( 2, 3, 4, 5, 6, 7, 8, 9, 10) )
					), lang("general", "general information"))
				)));
				
				$right->info = lang("rights_info");

				
				$form->addValidator(new RequiredFields(array("name")), "valdiator");
				
				$form->addAction(new AjaxSubmitButton("savegroup", lang("save", "Save"), "AjaxSave"));
				$form->addAction(new Button("cancel", lang("cancel", "cancel"), "LoadTreeItem(0);"));
		}
		/**
		 * edit-form
		 *
		 *@name getEditForm
		*/
		public function getEditForm(&$form)
		{
				
				if(!right(10))
				{
						$form->addField("root", new HTMLarea(lang("less_rights", "Forbidden")));
						
						return "";
				}


				// default form
				$form->add($tabs = new TabSet("tabs", array(
					new Tab("general",array(
						new HTMLField("rights",lang("rights", "Rights") . ": <strong>" . $this["rights"] . "</strong>"),
						new TextField("name",  lang("name", "Name")),
					), lang("general", "general information"))
				)));
				
				// advanced rights for groups under 10, with 10 you can do everything, there's no restriction at all
				if($this->rights != 10) {
					// rights
					$tabs->add($rightstab = new Tab("_rights", array(
						
					), lang("rights", "Rights")),0, "tabs");
					
					
					// draw fields
					$i = 0;
					foreach(ClassInfo::$advrights as $name => $data)
					{
							
							
							if(_eregi("^(.*)_all$", $name))
									$rightstab->add(new HTMLField("seperator_" . $name, "<br />"));
							
							if(isset($data["hide"]) && $data["hide"]) {
								continue;
							}
							
							
							if(advrights($name, $this->id)) {
									$rightstab->add($box = new Checkbox("advright_".$name."", parse_lang($data["title"]), true));
							} else {
									$rightstab->add($box = new Checkbox("advright_".$name."", parse_lang($data["title"]), false));
							}
							
							
					}
					
					// draw javscript
					foreach(ClassInfo::$advrights as $name => $data)
					{
						if(isset($form->fields['advright_' . $name])) {
							$box = $form->fields['advright_' . $name];
							if(isset($data["implements"]))
							{
									$alljs = '$(function(){ $("#'.$box->ID().'").change(function(){
										if($("#'.$box->ID().'").attr("checked"))
										{';
									
									$ownjs = "$(function(){";
									
									foreach($data["implements"] as $_name)
									{
											$alljs .= '$("#'.$form->fields["advright_" . $_name]->id().'").attr("checked", "checked");$("#form_field_advright_'.$_name.'").change();';
											$ownjs .= '$("#'.$form->fields["advright_" . $_name]->id().'").change(function(){
												if(!$(this).attr("checked"))
												{
													$("#'.$box->ID().'").attr("checked", false);
													$("#'.$box->ID().'").change();
												}
											});$("#'.$form->fields["advright_" . $_name]->id().'").change();';
									}
									
									$ownjs .= '});';
									
									$alljs .= '
										}
									}); });';
									$form->add(new JavaScriptField("js_all_".$name, $alljs . $ownjs));
							}
						}
					}
				}
				$form->addDataHandler(array($this, "handleData"));
				$form->addValidator(new RequiredFields(array("name")), "validator");
				
				$form->addAction(new HTMLAction("delete", '<a href="'.ROOT_PATH.'admin/usergroup/del/'.$this->id . '/group' .URLEND.'?redirect='.urlencode(ROOT_PATH . "admin/usergroup/").'" rel="ajaxfy" class="button red">'.lang("delete", "Delete").'</a>'));
				
				$form->addAction(new AjaxSubmitButton("savegroup", lang("save", "Save"), "ajaxsave"));
				$form->addAction(new Button("cancel", lang("cancel", "cancel"), "LoadTreeItem(0);"));
		}
		/**
		 * handles the data
		*/
		public function handleData($data) {
			$arr = array();
			$arr["advrightsids"] = array();
			foreach($data as $key => $value) {
				if(_ereg("^advright_", $key)) {
					$arr["advrightsids"][] = DataObject::_get("advrights", array("name" => substr($key, 9)))->id;
				} else {
					$arr[$key] = $value;
				}
			}
			
			return $arr;
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
			Profiler::mark("group::getTree");
			
			/* --- */
			
			
			$arr = array();
			if($parentid == 0) {
				$data = DataObject::get("group");
				foreach($data as $record) {
					$class = $record["class_name"];
					
					// count subtree
					$count = DataObject::count("user", array("groupid" => $record["id"]));
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
						"children"		=> $children,
						"id"			=> "group_" . $record["id"]
					);
				}
			} else {
				$data = DataObject::get("user", array("groupid" => $parentid));
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
			
			Profiler::unmark("group::getTree");
			
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
			Profiler::mark("group::searchTree");
			
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
						"collapsed"		=> false,
						"id"			=> "group_" . $parent["id"]
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
			
			Profiler::unmark("group::searchTree");
			
			return $arr;
		}
}

class groupController extends Controller 
{ 

}
