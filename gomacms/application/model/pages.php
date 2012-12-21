<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 19.12.2012
  * $Version 2.4.6
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Pages extends DataObject implements PermProvider, HistoryData, Notifier
{
		/**
		 * name
		*/
		public static $cname = '{$_lang_content}';
		
		/**
		 * show read-only edit if not enough rights
		*/
		public $showWithoutRight = true;
		
		/**
		 * the db-fields
		 *@name db_fields
		 *@var array
		*/
		public $db_fields = array(	'path' 				=> 'varchar(500)',
									'rights' 			=> 'int(2)',
									'mainbar' 			=> 'int(1)',
									'mainbartitle' 		=> 'varchar(200)',
									'googletitle'		=> "varchar(200)",
									'title' 			=> 'varchar(200)',
									'data' 				=> 'text',
									'sort'				=> 'int(8)',
									'search'			=> 'int(1)',
									'editright'			=> 'HTMLtext',
									'editright'			=> 'text',
									'meta_description'	=> 'varchar(200)',
									'meta_keywords'		=> 'varchar(200)');
									
		/**
		 * a page has a parent page
		 * a page has permissions
		 *
		 *@name has_one
		 *@var array
		*/
		public $has_one = array(	'parent' 				=> 'pages', 
									"read_permission" 		=> "Permission",
									"edit_permission"		=> "Permission",
									"publish_permission" 	=> "Permission");
									
		/**
		 * a page has many children
		 *
		 *@name has_many
		 *@var array
		*/
		public $has_many = array('children' => 'pages');
		
		/**
		 * searchable fields
		*/
		public $searchable_fields = array("data", "title", "mainbartitle", "meta_keywords");
		
		/**
		 * indexes to improve performance
		 *
		 *@name indexes
		 *@access public
		*/
		public $indexes = array(
			array("type" => "INDEX", "fields" => "path,sort,class_name", "name" => "path"),
			array("type" => "INDEX", "fields" => "parentid,mainbar,class_name", "name"	=> "mainbar"),
			array("type" => "INDEX", "fields" => "class_name,data,title,mainbartitle,meta_keywords,id","name" => "sitesearch")
		);
		
		/**
		 * which parents are allowed
		 *
		 *@name allow_parent
		*/
		public static $allow_parent = array();
		
		/**
		 * childs that are allowed
		 *
		 *@name allow_children
		*/
		public static $allow_children = array("Page", "WrapperPage");
		
		/**
		 * default sort
		*/
		public static $default_sort = "sort ASC";
		
		/**
		 * defaults
		*/
		public $defaults = array(	"parenttype" 	=> "root", 
									"search" 		=> 1,
									"mainbar"		=> 1,
									"sort"			=> 10000);
		
		/**
		 * activate versions
		 * 
		 *@name versioned
		*/
		public $versioned = true;
		
		/**
		  * we remove child-pages after removing parent page
		  *
		 *@name onAfterRemove
		 *@return bool
		*/
		public function onAfterRemove()
		{
			foreach($this->children() as $record) {
				$record->remove(true);
			}
		}
		
		/**
		 * local argument sql
		 *
		 *@name argumentSQL
		 *@access public
		*/
		public function argumentSQL(&$query) {
			$rank = Permission::getRank() - 1;
			
			// rights
			if(Permission::check(10) && (!isset($_SESSION['sites_ansicht']) || $_SESSION['sites_ansicht'] != lang("user"))) {
				// just add nothing ;)
			} else if(member::login()) {
				if(isset($this->many_many_tables["viewer_groups"]))
				{
						$table = $this->many_many_tables["viewer_groups"]["table"];
						$data = $this->many_many_tables["viewer_groups"];
				} else
				{
						return false;
				}
				$query->addFilter('viewer_type IN ("all", "password", "login", "") OR (viewer_type = "rights" AND `rights` <= '. Permission::getRank() .') OR (viewer_type = "groups" AND (SELECT count(*) FROM '.DB_PREFIX . $table.' AS '.$table.' WHERE '.$table.'.'.$data["field"].' = pages.id AND groupid IN ("'.implode('","', member::groupids()).'")))');
			} else {
				$query->addFilter(array("viewer_type" => array("", "all", "password")));
			}
			
			
		}
		
		/**
		 * makes the url
		 *
		 *@name geturl
		 *@return string
		*/
		public function getURL()
		{
			if($this->path == "" || ($this->fieldGet("parentid") == 0 && $this->fieldGet("sort") == 0)) {
				return ROOT_PATH . BASE_SCRIPT;
			} else {
				return  ROOT_PATH . BASE_SCRIPT . $this->path . URLEND;
			}
		}
		
		/**
		 * makes the org url without nothing for homepage
		 *
		 *@name getorgurl
		 *@return string
		*/
		public function getOrgURL()
		{
			return  ROOT_PATH . BASE_SCRIPT . $this->path . URLEND;
		}
		
		/**
		 * gets the parenttype
		 *@name getParentType
		 *@access public
		*/
		public function getParentType()
		{
				if(($this->parentid == 0 || $this->parentid == "") && in_array("pages", $this->allowed_parents()) && (Permission::check("PAGES_WRITE") && Permission::check("PAGES_PUBLISH")))
				{
						return "root";
				} else
				{
						return "subpage";
				}
		}
		
		/**
		 * gets prepended content
		 *
		 *@name getPrependedContent
		 *@access public
		*/
		public function getPrependedContent() {
			$object = new HTMLNode('div', array(
				"class" => "prependedContent"
			));
			$this->callExtending("prependContent", $object);
			return $object->html();
		}
		
		/**
		 * gets appended content
		 *
		 *@name getAppendedContent
		 *@access public
		*/
		public function getAppendedContent() {
			$object = new HTMLNode('div', array(
				"class" => "appendedContent"
			));
			$this->callExtending("appendContent", $object);
			return $object->html();
		}
		
		/**
		 * sets parentid
		 *@name setParentId
		 *@access public
		 *@param - value
		*/
		public function setParentId($value)
		{
				if($this->fieldGet("parenttype") == "root")
						$this->setField("parentid", "0");
				else
						$this->setField("parentid", $value);
					
				$this->viewcache = array();
				$this->data["parent"] = null;
		}
		
		/**
		 * gets the filename
		 *@name getFilename
		 *@access public
		*/
		public function getFilename()
		{
				$path = $this->path;
				if(strpos($path, '/') !== false)
				{
						$filename = substr($path, strrpos($path, '/') + 1);
				} else
				{
						return $path;
				}
				return $filename;
		}
		
		/**
		 * sets the filename
		 *
		 *@name setFilename
		 *@access public
		*/
		public function setFilename($value)
		{
				$this->setPath($value);
		}
		
		/**
		 * sets the path
		 *
		 *@name setPath
		 *@access public
		*/
		public function setPath($value)
		{
				$value = trim($value);
				$value = strtolower($value);
				
				// special chars
				$value = str_replace("ä", "ae", $value);
				$value = str_replace("ö", "oe", $value);
				$value = str_replace("ü", "ue", $value);
				$value = str_replace("ß", "ss", $value);
				$value = str_replace("ù", "u", $value);
				$value = str_replace("û", "u", $value);
				$value = str_replace("ú", "u", $value);
				
				$value = str_replace(" ",  "-", $value);
				// normal chars
				$value = preg_replace('/[^a-zA-Z0-9-_]/', '-', $value);
				$value = preg_replace('/[\-\-]/', '-', $value);
				$this->setField("path", $value);					

		}
		
		/**
		 * validates the path
		 *
		 *@name validatePath
		 *@access public
		*/
		public function validatePath($data)
		{
				$fullpath = "";
				$path = $data["path"];
				$parentid = $data["parentid"];
				
				if($data["parenttype"] == "root")
				{
						$parentid = 0;
				}
				
				if(isset($data["id"]))
				{
						$add = " AND pages.id != '".convert::raw2text($data["id"])."'";
				} else
				{
						$add = "";
				}
				
				return true;
		}

		/**
		 * validates the field parentid
		 *@name validateParentId
		 *@access public
		*/
		public function validatePageType($obj)
		{
				$data = $obj->form->result;
				$classname = strtolower($data["class_name"]);
				$parentid = $data["parentid"];
				
				if($data["parenttype"] == "subpage" && $data["parentid"] == null)
						return lang("form_required_fields", "Please fill out the oligatory fields") . ' \'' . lang("parentpage", "Parent Page"). '\'';;
				
				if($data["parenttype"] == "root")
				{
						$pclassname = "pages";
				} else
				{
						if(isset($data["recordid"]) && $data["parentid"] == $data["recordid"]) {
							return lang("error_page_self", "You can't suborder a page under itself!");
						}
						
						$d = DataObject::get("pages", array("id" => $parentid));
						if(isset($data["recordid"])) {
							$temp = $d;
							// validate if we subordered under subtree
							while($temp->parent) {
								if($temp->id == $data["recordid"]) {
									return lang("error_page_self", "You can't suborder a page under itself!");
								}
								$temp = $temp->parent;
							}
						}
						
						$pclassname = strtolower($d["class_name"]);
				}

				if(in_array($pclassname, $this->allowed_parents())) {
					return true;
				}
				
				return lang("form_bad_pagetype");
		}
		
		/**
		 * gets edit_permission
		 *
		 *@name getEdit_Permission
		 *@access public
		*/
		public function Edit_Permission() {
			$args = func_get_args();
			array_unshift($args, "edit_permission");
			
			if($data = call_user_func_array(array($this, "getHasOne"), $args)) {
				return $data;
			/*} else if($this->parent) {
				return $this->parent()->edit_permission;
			*/} else {
				if($this->parentid) {
					$inheritor = $this->parent->edit_permission();
				} else {
					$inheritor = Permission::forceExisting("PAGES_WRITE");
				}
				
				$perm = new Permission(array("type" => "admins", "inheritorid" => $inheritor->id));
				$perm->forModel = "pages";
				if($this->ID != 0) {
					$perm->write(true, true, 2, false, false);
					$this->edit_permissionid = $perm->id;
					$this->write(false, true, $this->isOrgPublished() ? 2 : 1, false, false);
				}
				
				return $perm;
			}
		}
		
		/**
		 * sets the edit-permission
		 *
		 *@name setEdit_Permission
		 *@access public
		*/
		public function setEdit_Permission($perm) {
			$perm->forModel = "pages";
			if($perm->inheritorid != 0) {
				if($perm->inheritor->name == "" && $this->parentid == 0) {
					$perm->inheritorid = Permission::forceExisting("PAGES_WRITE")->id;
				} else if($this->parentid != 0) {
					$perm->inheritorid = $this->parent->edit_permission->id;
				}
			}
			$this->setField("Edit_Permission", $perm);
			
			$this->viewcache = array();
		}
		
		/**
		 * gets publish_permission
		 *
		 *@name getPublish_Permission
		 *@access public
		*/
		public function Publish_Permission() {
			$args = func_get_args();
			array_unshift($args, "publish_permission");
			
			if($data = call_user_func_array(array($this, "getHasOne"), $args)) {
				return $data;
			/*} else if($this->parent) {
				return $this->parent()->edit_permission;
			*/} else {
				if($this->parentid) {
					$inheritor = $this->parent->publish_permission();
				} else {
					$inheritor = Permission::forceExisting("PAGES_PUBLISH");
				}
				$perm = new Permission(array("type" => "admins", "inheritorid" => $inheritor->id));
				$perm->forModel = "pages";
				
				if($this->ID != 0) {
					$perm->write(true, true, 2, false, false);
					$this->publish_permissionid = $perm->id;
					$this->write(false, true, $this->isOrgPublished() ? 2 : 1, false, false);
				}
				
				return $perm;
			}
		}
		
		/**
		 * sets the publish-permission
		 *
		 *@name setPublish_Permission
		 *@access public
		*/
		public function setPublish_Permission($perm) {
			$perm->forModel = "pages";
			if($perm->inheritorid != 0) {
				if($perm->inheritor->name == "" && $this->parentid == 0) {
					$perm->inheritorid = Permission::forceExisting("PAGES_PUBLISH")->id;
				} else if($this->parentid != 0) {
					$perm->inheritorid = $this->parent->publish_permission->id;
				}
			}
			
			$this->setField("Publish_Permission", $perm);
			$this->viewcache = array();
		}
		
		/**
		 * gets read_permission
		 *
		 *@name getRead_Permission
		 *@access public
		*/
		public function Read_Permission() {
			$args = func_get_args();
			array_unshift($args, "read_permission");
			
			if($data = call_user_func_array(array($this, "getHasOne"), $args)) {
				return $data;
			/*} else if($this->parent) {
				return $this->parent()->read_permission;
			*/} else {
				$perm = new Permission(array("type" => "all"));
				$perm->forModel = "pages";
				if($this->ID != 0) {
					$perm->write(true, true, 2, false, false);
					$this->read_permissionid = $perm->id;
					$this->write(false, true, $this->isOrgPublished() ? 2 : 1, false, false);
				}
				
				return $perm;
			}
		}
		/**
		 * sets the read-permission
		 *
		 *@name setRead_Permission
		 *@access public
		*/
		public function setRead_Permission($perm) {
			$perm->forModel = "pages";
			if($perm->inheritorid != 0) {
				if($perm->inheritor->name == "" && $this->parentid == 0) {
					$perm->inheritorid = 0;
				} else if($this->parentid != 0) {
					$perm->inheritorid = $this->parent->read_permission->id;
				}
			} else if($this->id == 0 && $this->parentid != 0) {
				$perm->inheritorid = $this->parent->read_permission->id;
			}
			$this->setField("Read_Permission", $perm);
			
			$this->viewcache = array();
		}
		
		/**
		 * validates page-filename
		 *
		 *@name validatePageFileName
		 *@access public
		 *@param obj - object
		*/
		public function validatePageFileName($obj) {
			$data = $obj->form->result;
			$filename = $data["filename"];
			$parentid = ($data["parentid"] == "") ? 0 : $data["parentid"];
			if(isset($obj->form->result["recordid"]))
				if(DataObject::count("pages", " path LIKE '".$filename."' AND parentid = '".$parentid."' AND pages.recordid != '".$obj->form->result["recordid"]."'") > 0) {
					return lang("site_exists", "The page with this filename already exists.");
				} else {
					return true;
				}
			else if(DataObject::count("pages", " path LIKE '".$filename."' AND parentid = '".$parentid."'") > 0) {
					return lang("site_exists", "The page with this filename already exists.");
			} else {
				return true;
			}	
		}
		
		/**
		 * gets class of a link
		 *
		 *@name getLinkClass
		 *@access public
		*/
		public function getLinkClass() {
			return ($this->active) ? "active" : ""; 
		}
		
		/**
		 * writes the form
		 *
		 *@name getForm
		 *@access public
		 *@param object - form
		*/
		public function getForm(&$form)
		{		
				
				parent::getForm($form);
				
				$allowed_parents = $this->allowed_parents();
				
				$form->addValidator(new requiredFields(array('path','title', 'parenttype')), "default_required_fields"); // valiadte it!
				$form->addValidator(new FormValidator(array($this, "validatePageType")), "pagetype");
				$form->addValidator(new FormValidator(array($this, "validatePageFileName")), "filename");
				
				$form->useStateData = true;
				$this->queryVersion = "state";
				
				if($this->id != 0 && isset($this->data["stateid"]) && $this->data["stateid"] !== null) {
					$html = "<div class=\"pageinfo versionControls\">";
					
					if($this->isPublished()) {
						$html .= '<div class="state"><div class="draft">'.lang("draft", "draft").'</div><div class="publish active">'.lang("published", "published").'</div></div>';
					} else {
						$html .= '<div class="state"><div class="draft active">'.lang("draft", "draft").'</div><div class="publish">'.lang("published", "published").'</div></div>';
					}
					
					if($this->everPublished()) {
						define("PREVIEW_URL", BASE_URI . BASE_SCRIPT.'?r='.$this->id);
						Resources::addJS("$(function(){ if(typeof pages_pushPreviewURL != 'undefined') pages_pushPreviewURL('".BASE_URI . BASE_SCRIPT.'?r='.$this->id."', '".BASE_URI . BASE_SCRIPT."?r=".$this->id . "&".$this->baseClass."_state', ".($this->isPublished() ? "true" : "false")."); });");
					} else {
						define("PREVIEW_URL", BASE_URI . BASE_SCRIPT.'?r='.$this->id);
						Resources::addJS("$(function(){ if(typeof pages_pushPreviewURL != 'undefined') pages_pushPreviewURL(false, '".BASE_URI . BASE_SCRIPT."?r=".$this->id . "&".$this->baseClass."_state', false); });");
					}
					$html .= '</div><div style="clear:right;"></div>';
					
					$form->add($links = new HTMLField('links', $html));
					$links->container->addClass("hidden");
				}
				
				define("EDIT_ID", $this->id);
				
				$form->add(new TabSet('tabs', array(
						new Tab('content', array(
							$title = new textField('title', lang("title_page", "title of the page")),
							$mainbartitle = new textField('mainbartitle', lang("menupoint_title", "title on menu")),
							
							$parenttype = new ObjectRadioButton("parenttype", lang("hierarchy", "hierarchy"), array(
								"root" => lang("no_parentpage", "Root Page"),
								"subpage" => array(
									lang("subpage","sub page"),
									"parent"
								)
							)),
							$parentDropdown = new HasOneDropDown("parent", lang("parentpage", "Parent Page"), "title", ' `pages`.`class_name` IN ("'.implode($allowed_parents, '","').'") AND `pages`.`id` != "'.$this->id.'"'),
							
						), lang("content", "content")),
						
						new Tab('meta', array(
							
							$description = new textField('meta_description', lang("site_description", "Description of this site")),
							$keywords = new textField('meta_keywords',lang("site_keywords", "Keywords of this site")),
							$wtitle = new TextField("googletitle", lang("window_title")),
							new checkbox('mainbar', lang("menupoint_add", "Show in menus")),
							new HTMLField(''),
							new checkbox('search', lang("show_in_search", "show in search?")),		
							$filename = new textField('filename', lang("filename"))
						), lang("settings", "settings")),
						$rightstab = new Tab('rightstab', array(
							$read = new PermissionField("read_permission", lang("viewer_types"), null, true),
							$write = new PermissionField("edit_permission", lang("editors"), array("type" => "inherit"), false, array("all")),
							$publish = new PermissionField("publish_permission", lang("publisher"), array("type" => "inherit"), false, array("all"))
						), lang("rights", "permissions"))
						
					) 
				));
				
				if(!$this->canWrite($this) || !Permission::check("PAGES_WRITE")) {
					$write->disable();
				}
				
				if(!$this->canPublish($this) || !Permission::check("PAGES_PUBLISH")) {
					$publish->disable();
				}
				
				// permissions
				if($this->parent) {
					if($this->parent()->read_permission) {
						$read->setInherit($this->parent()->read_permission(), $this->parent()->title);
					}
					
					if($this->parent()->edit_permission) {
						$write->setInherit($this->parent()->edit_permission(), $this->parent()->title);
					}
					
					if($this->parent()->publish_permission) {
						$publish->setInherit($this->parent()->publish_permission(), $this->parent()->title);
					}
				} else {
					$write->setInherit(Permission::forceExisting("PAGES_WRITE"));
					$publish->setInherit(Permission::forceExisting("PAGES_PUBLISH"));
				}
				
				// infos for users
				$parentDropdown->info_field = "url";
				$description->info = lang("description_info");
				$keywords->info = lang("keywords_info");
				$mainbartitle->info = lang("menupoint_title_info");
				$wtitle->info = lang("window_title_info");
				
				if(!in_array("pages", $allowed_parents) || (!Permission::check("PAGES_WRITE") && !Permission::check("PAGES_PUBLISH"))) {
					$parenttype->disableOption("root");
				}
				
				if(in_array("pages", $allowed_parents) && count($allowed_parents) == 1) {
					$parenttype->disableOption("subpage");	
				}
				
				// add some js
				$form->add(new JavaScriptField("change",'$(function(){
					$("#'.$title->ID().'").change(function(){
						if($(this).val() != "") {
							var value = $(this).val();
							$("#'.$mainbartitle->ID().'").val(value);
							if($("#'.$filename->ID().'").length > 0) {
								if($("#'.$filename->ID().'").val() == "") {
									// generate filename
									var filename = value.toLowerCase();
									filename = filename.trim();
									filename = filename.replace("ä", "ae");
									filename = filename.replace("ö", "oe");
									filename = filename.replace("ü", "ue");
									filename = filename.replace("ß", "ss");
									while(filename.match(/[^a-zA-Z0-9-_]/))
										filename = filename.replace(/[^a-zA-Z0-9-_]/, "-");
									
									while(filename.match(/\-\-/))
										filename = filename.replace("--", "-");
									

									$("#'.$filename->ID().'").val(filename);
									
								}
							}
						}
						
					});
				});'));
				
				
		}
		
		/**
		 * gets form-actions
		 *
		 *@name getActions
		 *@access public
		*/
		public function getActions(&$form, $edit = false) {
		
			if(false) { //$this->isDeleted() && $this->id != 0) {
				$form->addAction(new AjaxSubmitButton('_submit',lang("restore", "Restore"),"AjaxSave"));
			} else if($this->id != 0) {
				
				if($this->canDelete($this)) {
					$form->addAction(new HTMLAction("deletebutton", '<a rel="dropdownDialog" href="'.Core::$requestController->namespace.'/delete'.URLEND.'?redirect='.ROOT_PATH.'admin/content/" class="button delete formaction">'.lang("delete").'</a>'));
				}
				
				if($this->everPublished() && !$this->isOrgPublished() && $this->canWrite($this)) {
					$form->addAction(new HTMLAction("revert_changes", '<a class="draft_delete red button" href="'.Core::$requestController->namespace.'/revert_changes" rel="dropdownDialog">'.lang("draft_delete", "delete draft").'</a>'));
				}
				
				if($this->everPublished() && $this->canPublish($this)) {
					$form->addAction(new HTMLAction("unpublish", '<a class="button" href="'.Core::$requestController->namespace.'/unpublish" rel="ajaxfy">'.lang("unpublish", "Unpublish").'</a>'));
				}
				
				if($this->canWrite($this))
					$form->addAction(new AjaxSubmitButton("save_draft",lang("draft_save", "Save draft"),"AjaxSave"));
				
				if($this->canPublish($this))
					$form->addAction(new AjaxSubmitButton('publish',lang("publish", "Save & Publish"),"AjaxPublish", "Publish", array("green")));	
					
			} else {
				$form->addAction(new button('cancel',lang("cancel"), "LoadTreeItem(0);"));
				// we need special submit-button for adding
				
				$form->addAction(new AjaxSubmitButton('_submit',lang("save", "Save"),"AjaxSave"));
				
				$form->addAction(new AjaxSubmitButton('_publish',lang("save_publish", "Save & Publish"),"AjaxPublish", "Publish", array("green")));
				
			}	
			
		}
		
		/**
		 * returns versioned fields
		 *
		 *@name getVersionedFields
		 *@access public
		*/
		public function getVersionedFields() {
			return array(
				"title" 		=> lang("title"),
				"mainbartitle"	=> lang("menupoint_title"),
				"data"			=> lang("content")
			);
		}
		
		/**
		 * cache for allowed_parents
		 *@name cache_parent
		 *@access public
		*/
		private $cache_parent = array();
		
		/**
		 * gets allowed parents
		 *@name allowed_parents
		 *@access public
		*/		
		public function allowed_parents()
		{
				if($this->cache_parent == array())
				{
						if(PROFILE) Profiler::mark("pages::allowed_parents");
						
						$allowed_parents = array();
						
						// first check all pages
						$allPages = array_merge((array) array("pages"), ClassInfo::getChildren("pages"));
						foreach($allPages as $child) {
							
							// get allowed children for this page
							$allowed = ClassInfo::getStatic($child, "allow_children");
							if(count($allowed) > 0) {
								foreach($allowed as $allow) {
									$allow = strtolower($allow);
									
									// if ! these children are absolutly prohibited
									if(substr($allow, 0, 1) == "!") {
										if(substr($allow, 1) == $this->class || is_subclass_of($this->class, substr($allow, 1))) {
											unset($allowed_parents[$child]);
											continue 2;
										}
									} else {
										if($allow == $this->class || is_subclass_of($this->class, $allow)) {
											$allowed_parents[$child] = $child;
										}
									}
								}
							}
						}
						
						// now filter
						$allow_parents = ClassInfo::getStatic($this->class, "allow_parent");
						if(count($allow_parents) > 0) {
							foreach($allowed_parents as $parent) {
								
								// set found to false
								$found = false;
								
								// try find the parent
								foreach($allow_parents as $allow) {
									$allow = strtolower($allow);
									if(substr($allow, 0, 1) == "!") {
										if(substr($allow, 1) == $parent || is_subclass_of($parent, substr($allow, 1))) {
											unset($allowed_parents[$parent]);
											continue 2;
										}
									} else {
										if($allow == $parent || is_subclass_of($parent, $allow)) {
											$found = true;
										}
									}
								}
								
								// if not found, unset
								if(!$found) {
									unset($allowed_parents[$parent]);
								}
							}
						}
						
						if(PROFILE) Profiler::unmark("pages::allowed_parents");

						return $allowed_parents;
				} else
				{
						return $this->cache_parent;
				}	
		}
		
		/**
		 * checks if specifc class is allowed child for given page
		 *
		 *@name checkCanChild
		 *@access public
		 *@param string - child-class
		 *@param string - parent-class
		*/
		public function checkCanChild($child, $parent) {
			$children = ClassInfo::getStatic($parent, "allow_children");
			if(count($children) > 0) {
				foreach($children as $allowed_child) {
					if(is_a($child, $allowed_child))
						return true;
				}
			}
			
			return false;
		}
		
		/**
		 * gets the content
		*/
		public function getContent()
		{
				return $this->fieldGet("data");
		}
		
		/**
		 * checks if this site is active in mainbar
		 *@name getActive
		 *@access public
		*/
		public function getActive()
		{
				if(in_array($this->fieldGet("id"), contentController::$activeids))
						return true;
				else
						return false;
		}
		
		/**
		 * gets controller
		 *@name controller
		 *@access public
		*/
		public function controller($controller = null)
		{
				if(parent::controller($controller)) {
						return parent::controller($controller);
				} else {
						$this->controller = Object::instance("contentController");
						$this->controller->model_inst = $this;
						return $this->controller;
				}				
		}
		
		/**
		 * the path
		 *
		 *@name getPath
		 *@access public
		*/
		public function getPath()
		{
			if($this->parent) {
				return $this->parent()->getPath() . "/" . $this->fieldGet("path");
			}
			
			return $this->fieldGet("path");
		}
		
		
		
		/**
		 * can view history
		 *
		 *@name canViewHistory
		 *@access public
		*/
		public static function canViewHistory($record = null) {
			return (Permission::check("PAGES_WRITE") || Permission::check("PAGES_PUBLISH"));
		}
		
		/**
		 * permission-checks
		*/
		public function canWrite($row = null)
		{		
				if(Permission::check("superadmin"))
					return true;
				
				if(isset($row))
					return $row->edit_permission->hasPermission();
				
				return Permission::check("PAGES_WRITE");
		}
		
		/**
		 * can-publish-rights
		*/
		public function canPublish($row) {
			if(Permission::check("superadmin"))
				return true;
			
			if(isset($row))
				return $row->publish_permission->hasPermission();
			
			return Permission::check("PAGES_WRITE");
		}
		
		/**
		 * permission-checks
		*/
		public function canDelete($row = null)
		{
				return Permission::check("PAGES_DELETE");
		}
		
		/**
		 * permission-checks
		*/
		public function canInsert($row = null)
		{	
			if(isset($row)) {
				if($row->parentid != 0) {
					$data = DataObject::get_versioned("pages", "state", $row->parentid);
					if($data->Count() > 0) {
						return $data->first()->canWrite($data);
					}
				}
			}
			
			return Permission::check("PAGES_INSERT");
		}
		
		/**
		 * permissions
		 *@name providePermissions
		 *@access public
		*/
		public function providePerms()
		{
			return array(
				"PAGES_DELETE"	=> array(
					"title"		=> '{$_lang_pages_delete}',
					"default"	=> array(
						"type" => "admins",
						"inherit"	=> "ADMIN_CONTENT"
					),
					"category"	=> "ADMIN_CONTENT"
				),
				"PAGES_INSERT"	=> array(
					"title"		=> '{$_lang_pages_add}',
					"default"	=> array(
						"type" => "admins",
						"inherit"	=> "ADMIN_CONTENT"
					),
					"category"	=> "ADMIN_CONTENT"
				),
				"PAGES_WRITE"	=> array(
					"title"		=> '{$_lang_pages_edit}',
					"default"	=> array(
						"type" => "admins",
						"inherit"	=> "ADMIN_CONTENT"
					),
					"category"	=> "ADMIN_CONTENT"
				),
				"PAGES_PUBLISH"	=> array(
					"title"		=> '{$_lang_pages_publish}',
					'default'	=> array(
						"type"		=> "admins",
						"inherit"	=> "ADMIN_CONTENT"
					),
					"category"	=> "ADMIN_CONTENT"
				),
				"ADMIN_CONTENT"	=> array(
					"title" => '{$_lang_administration}: {$_lang_content}',
					"default"	=> array(
						"type"	=> "admins"
					),
					"category"	=> "ADMIN"
				)
			);
		}
		
		public function getSiteTree($search = "") {
			return $this->renderTree("admin/content/record/\$id/edit", 0, array($search), true, false);
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
		public function getTree($parentid = 0, $fields = array(), $activenode = 0, $params = array())
		{
			if(PROFILE) Profiler::mark("pages::getTree");
			
			/* --- */
			
			$arr = array();
			
			// get ids that are NOT collapsed cause of current edit-node
			$ids = array();
			if(defined("EDIT_ID") && EDIT_ID != 0 && $d = DataObject::get_by_id("pages", EDIT_ID)) {
				while($d->parent) {
					$ids[] = $d->parent->id;
					$d = $d->parent;
				}
			}
			
			// create filter
			$where = array("parentid" => $parentid);
			if(isset($params["deleted"]) && $params["deleted"]) {
				$data = DataObject::get_Versioned($this, "group", $where);
			} else {
				$data = DataObject::get($this, $where);
			}
			
			if(Permission::check("ADMIN_CONTENT") && (!isset($params["deleted"]) || !$params["deleted"])) $data->setVersion("state");
			
			foreach($data as $record) {
				
				$collapsed = null;
				
				if($record->id != $activenode && isset($params["published"]) && !$params["published"] && $record->isPublished() === true)
					if(!isset($params["deleted"]) || !$params["deleted"] || $record->isDeleted() === false)
						continue;
					
				if($record->id != $activenode && isset($params["edited"]) && !$params["edited"] && $record->isPublished() === false)
					if(!isset($params["deleted"]) || !$params["deleted"] || $record->isDeleted() === false)
						continue;
				
				if(isset($params["deleted"]) && $params["deleted"] && $record->isDeleted()) {
					$state = "deleted";
				}
				
				
				
				$mainbar = ($record["mainbar"] == 1) ? "withmainbar" : "nomainbar";
				if(!isset($state)) {
					if($record->isPublished())	
						$state = "published";
					else
						$state = "edited";
				}
				$class = "".$record["class_name"]. " page ".$mainbar . " " . $state;
				
				$where["parentid"] = $record->recordid;
				// children
				if(isset($params["deleted"]) && $params["deleted"]) {
					$children = DataObject::get_Versioned($this, "group", $where);					
				} else {
					$children = DataObject::get_Versioned($this, "state", $where);
				}
				
				$childcount = $children->count;
				unset($children);
				
				if($childcount > 0) {
					// we prefetch a maximum of 5 sites
					if($childcount < 6 || in_array($record["recordid"], $ids)) {
						$id = $record["recordid"];
						if(PROFILE) Profiler::unmark("pages::getTree");
						$children = $this->getTree($id, $fields, $activenode, $params);
						if(PROFILE) Profiler::mark("pages::getTree");
						if(in_array($record["recordid"], $ids)) {
							$collapsed = false;
						}
					} else {
						$children = "ajax";
					}
				} else {
					$children = "";
				}
				
				// get data
				$arr[] = array(
					"title" 		=> $record["title"],
					"attributes"	=> array("class" => $class),
					"data"			=> $record->ToArray(),
					"children"		=> $children,
					"collapsed"		=> $collapsed
				);
				
				unset($state);
			}
			if(PROFILE) Profiler::unmark("pages::getTree");
			
			return $arr;
		}
		
		/**
		 * provides tree-arguments
		 *
		 *@name provideTreeParams
		 *@access public
		*/
		public function provideTreeParams() {
			return array(
				"deleted" 	=> array(
					"title"		=> '{$_lang_deleted_page}',
					"default"	=> false,
					"css"		=> array(
						'text-decoration' 	=> 'line-through !important',
						"color"				=> '#c60004 !important'
					)
				),
				"published"	=> array(
					"title"		=> '{$_lang_published_site}',
					"default"	=> true,
					"css"		=> array(
					)
				),
				"edited"	=> array(
					"title"		=> '{$_lang_edited_page}',
					"default"	=> true,
					"css"		=> array(
						'font-style' => 'italic'
					)
				)
			);
		}
		/**
		 * gets the subtree from a given parentid or from 0, so from root
		 *
		 *@name searchTree
		 *@access public
		 *@param array - words
		 *@param array - fields
		*/
		public function searchTree($words = array(), $fields = array(), $activenode = 0)
		{
			if(PROFILE) Profiler::mark("pages::searchTree");
			
			/* --- */
			
			$arr = array();
		
			$where = array();
			
			$data = DataObject::search_Object($this, $words, $where, array('('.$this->baseTable.'.id = "'.$this->version.'")', 'DESC'), array(), array(), false, "recordid");
			$data->setVersion(false);
			if(Permission::check("ADMIN_CONTENT")) $data->setVersion("state");
			
			$parentid_cache = array();
			
			foreach($data as $record) {
				if($record["parentid"] == 0) {
					if(!isset($arr["_" . $record["id"]])) {
						if($record->first()->isDeleted()) {
							$state = "deleted";
						}
						// get class-attribute
						$mainbar = ($record["mainbar"] == 1) ? "withmainbar" : "nomainbar";
						if(!isset($state))
							$state = ($record->isPublished()) ? "published" : "edited";
						$class = "".$record["class_name"]. " page ".$mainbar . " " . $state;
						unset($state);
						
						$arr["_" . $record["id"]] = array(
							"title" 		=> $record["title"],
							"attributes"	=> array("class" => $class),
							"data"			=> $record->first()->ToArray(),
							"collapsed"		=> false,
							"collapsable"	=> false,
							"children"		=> array()
						);
						
						unset($class, $mainbar); // free memory
					}
				} else {
					
					$parentid = $record["parentid"];
					if(isset($arr[$parentid])) { // we are on the second level of the tree
						
						if(!isset($arr["_" . $parentid]["children"]["_" . $record["id"]])) {
							if($record->first()->isDeleted()) {
								$state = "deleted";
							}
							// get class-attribute
							$mainbar = ($record["mainbar"] == 1) ? "withmainbar" : "nomainbar";
							if(!isset($state))
								$state = ($record->first()->isPublished()) ? "published" : "edited";
							$class = "".$record["class_name"]. " page ".$mainbar . " " . $state;
							unset($state);
							
							$arr["_" . $parentid]["children"]["_" . $record["recordid"]] = array(
								"title" 		=> $record["title"],
								"attributes"	=> array("class" => $class),
								"data"			=> $record->first()->ToArray(),
								"collapsed"		=> false,
								"collapsable"	=> false,
								"children"		=> array()
							);
							
							unset($class, $mainbar); // free memory
						}
					
					} else { // we are at the third or lower level of the tree, so we have to generate path to root
						
						// we have to draw the tree from this node to top
						$to_insert = array($parentid => array($record));
						$current_parent_id = $parentid;
						// now read through data
						while($current_parent_id != 0) {
							
							$data = DataObject::get($this, array("id" => $current_parent_id),array('('.$this->baseTable.'.id = "'.$this->version.'")', 'DESC'), array(), array(), false, "recordid");
							$data->version = false;
							if(Permission::check("ADMIN_CONTENT")) $data->version = "state";
							
							if(isset($arr["_" . $data["parentid"]]) || $data["parentid"] == 0) { // found
								if($data["parentid"] != 0 && isset($arr["_" . $data["parentid"]])) {
									// if isn't set
									if(!isset($arr["_" . $data["parentid"]]["children"]["_" . $data["id"]])) {
										if($data->first()->isDeleted()) {
											$state = "deleted";
										}
										// get class-attribute
										$mainbar = ($data["mainbar"] == 1) ? "withmainbar" : "nomainbar";
										if(!isset($state))
											$state = ($data->first()->isPublished()) ? "published" : "edited";
										$class = "".$data["class_name"]. " page ".$mainbar . " " . $state;
										unset($state);
										
										$arr["_" . $data["parentid"]]["children"]["_" . $data["id"]] = array(
											"title" 		=> $data["title"],
											"attributes"	=> array("class" => $class),
											"data"			=> $data->first()->ToArray(),
											"collapsed"		=> false,
											"collapsable"	=> false,
											"children"		=> array()
										);
										
										unset($class, $mainbar); // free memory
										
									}
									
																		// now insert $to_insert-array
									$id = $data["id"];
									if(!isset($arr["_" . $data["parentid"]]["children"]["_" . $data["id"]]["children"])) {
										$arr["_" . $data["parentid"]]["children"]["_" . $data["id"]]["children"] = array();
									}
									
									
									$arr["_" . $data["parentid"]]["children"]["_" . $data["id"]]["children"] = array_merge_recursive_distinct($arr["_" . $data["parentid"]]["children"]["_" . $data["id"]]["children"],$this->generateFromToInsert($id, $to_insert));
									unset($to_insert);
									break;
								} else { // parentid is 0
									// if isn't set
									if(!isset($arr["_" . $data["id"]])) {
										if($data->first()->isDeleted()) {
											$state = "deleted";
										}
										// get class-attribute
										$mainbar = ($data["mainbar"] == 1) ? "withmainbar" : "nomainbar";
										if(!isset($state))
											$state = ($data->first()->isPublished()) ? "published" : "edited";
										$class = "".$data["class_name"]. " page ".$mainbar;
										unset($state);
										
										$arr["_" . $data["id"]] = array(
											"title" 		=> $data["title"],
											"attributes"	=> array("class" => $class),
											"data"			=> $data->first()->ToArray(),
											"collapsed"		=> false,
											"collapsable"	=> false,
											"children"		=> array()
										);
										
										unset($class, $mainbar); // free memory
										
									}
									
									// now insert $to_insert-array
									$id = $data["id"];
									if(!isset($arr["_" . $id]["children"])) {
										$arr["_" . $id]["children"] = array();
									}
									
									
									$arr["_" . $id]["children"] = array_merge_recursive_distinct($arr["_" . $id]["children"], $this->generateFromToInsert($id, $to_insert));
									unset($to_insert);
									break;
								}
								
								
							} else { // add entry to to_insert-array and progress
								$current_parent_id = $data["parentid"];
								$to_insert[$data["parentid"]][] = $data;
								unset($data);
								continue;
							}
						}
					}
				}
			}
			if(PROFILE) Profiler::unmark("pages::searchTree");
			return $arr;
		}
		
		/**
		 * this helper-function generates the array from the to_insert-array
		 *
		 *@name generateFromToInsert
		 *@access protected
		 *@param numeric - id to start
		 *@param to_insert-array
		*/
		protected function generateFromToInsert($id, $to_insert) {
			
			$arr = array();
			if(isset($to_insert[$id])) {
				foreach($to_insert[$id] as $record) {
					if(!$record["id"])
						continue;
					
					if($record->first()->isDeleted()) {
						$state = "deleted";
					}				
					// get class-attribute
					$mainbar = ($record["mainbar"] == 1) ? "withmainbar" : "nomainbar";
					if(!isset($state))
						$state = ($record->first()->isPublished()) ? "published" : "edited";
					$class = "".$record["class_name"]. " page ".$mainbar . " " . $state;
					unset($state);
					
					$arr["_" . $record["id"]] = array(
						"title" 		=> $record["title"],
						"attributes"	=> array("class" => $class),
						"data"			=> $record->first()->ToArray(),
						"collapsed"		=> false,
						"collapsable"	=> false,
						"children"		=> $this->generateFromToInsert($record["id"], $to_insert)
					);
					
					unset($class, $mainbar, $record); // free memory
				}
				return $arr;
			} else
			{
				return array();
			}
			
		}
		
		/**
		 * gets the data object of a site of a given url
		 *
		 *@name getByURL
		 *@access public
		 *@param string - url
		*/
		public static function getByURL($url) {
			$request = new Request("GET", $url);
			// check if a path is given, else give back homepage
			if($params = $request->match("\$path!")) {
				// first get the site with the first url-part
				$currentdata = DataObject::get("pages", array("path" => $params["path"], "parentid" => 0));
				if($currentdata > 0) {
					// then go part for part
					while($request->remaining() != "") {
						if($params = $request->match("\$path!")) {
							$newdata = DataObject::get("pages", array("path" => $params["path"], "parentid" => $currentdata["id"]));
							if($newdata->count() == 0) {
								break;
							} else {
								$currentdata = $newdata;
								unset($newdata);
							}
						} else {
							break;
						}
					}
					return $currentdata->first();
				} else {
					return false;
				}	
			} else {
				return DataObject::get_one("pages", array());
			}
			
		}
		
		/**
		 * returns text what to show about the event
		 *
		 *@name generateHistoryData
		 *@access public
		*/
		public static function generateHistoryData($record) {
			$compared = false;
			switch($record->action) {
				case "update":
					$lang = lang("h_pages_update", '$user updated the page <a href="$pageUrl">$page</a>');
					$icon = "images/icons/fatcow16/page_white_edit.png";
					$compared = true;
				break;
				case "insert":
					$lang = lang("h_pages_create", '$user created the page <a href="$pageUrl">$page</a>');
					$icon = "images/icons/fatcow16/page_white_add.png";
				break;
				case "publish":
					$lang = lang("h_pages_publish", '$user published the page <a href="$pageUrl">$page</a>');
					$icon = "images/icons/fatcow16/page_white_get.png";
					$compared = true;
				break;
				case "remove":
					$lang = lang("h_pages_remove", '$user removed the page <a href="$pageUrl">$page</a>');
					$icon = "images/icons/fatcow16/page_white_delete.png";
				break;
				case "unpublish":
					$lang = lang("h_pages_unpublish", '$user unpublished the page <a href="$pageUrl">$page</a>');
					$icon = "images/icons/fatcow16/page_white_edit.png";
				break;
				default:
					$lang = "unknowen event " . $record->action;
					$icon = "images/icons/fatcow16/page_white_edit.png";
				break;
			}
			$lang = str_replace('$pageUrl', "admin/content/record/" . $record->newversion()->id . "/edit" . URLEND, $lang);
			$lang = str_replace('$page', convert::Raw2text($record->newversion()->title), $lang);
			
			return array("icon" => $icon, "text" => $lang, "versioned" => true, "compared" => $compared, "editurl" => "admin/content/record/" . $record->newversion()->id . "/edit" . URLEND);
		}
		
	/**
	 * returns information about notification-settings of this class
	 * these are:
	 * - title
	 * - icon
	 * this API may extended with notification settings later
	 * 
	 *@name NotifySettings
	 *@access public
	*/
	public static function NotifySettings() {
		return array("title" => lang("content"), "icon" => "images/icons/other/content.png");
	}
		
}

/**
 * extension for the template to use mainbar-methods
 *
 *@name ContentTPLExtension
 *@access public
*/
class ContentTPLExtension extends Extension {
	/**
	 * prepended content
	 *
	 *@name prependedContent
	 *@access public
	*/
	public static $prependedContent = array();
	
	/**
	 * appended content
	 *
	 *@name appendedContent
	 *@access public
	*/
	public static $appendedContent = array();
	
	/**
	 * active mainbar cache
	 *
	 *@name active_mainbar
	 *@access protected
	*/
	protected static $active_mainbar;
	
	/**
	 * methods
	*/
	public static $extra_methods = array(
		"level",
		"mainbar",
		"active_mainbar_title",
		"mainbarByID",
		"prendedContent",
		"appendedContent",
		"active_mainbar_url",
		"pageByID",
		"pageByPath",
		"active_mainbar"
	);
	
	/**
	 * appends content
	 *
	 *@name appendContent
	 *@param string|object|array - content
	 *@access public
	*/
	public static function appendContent($content) {
		if(is_array($content))
			self::$appendedContent = array_merge(self::$appendedContent, $content);
		else
			self::$appendedContent[] = $content;
			
		return true;
	}
	
	/**
	 * prepends content
	 *
	 *@name prependContent
	 *@param string|object|array - content
	 *@access public
	*/
	public static function prependContent($content) {
		if(is_array($content))
			self::$prependedContent = array_merge(self::$prependedContent, $content);
		else
			self::$prependedContent[] = $content;
			
		return true;
	}
	
	/**
	 * mainbar
	*/
	public function level($level)
	{
			if($level == 1)
			{
					return true;
			}

			if(!isset(contentController::$activeids[$level - 2]))
			{
					return false;
			}
			$id = contentController::$activeids[$level - 2];
			return (DataObject::count("pages", array("parentid" => $id, "mainbar" => 1)) > 0);
			
	}
	/**
	 * gets data for mainbar
	*/
	public function mainbar($level = 1)
	{
			if($level == 1)
			{
					return DataObject::get("pages", array("parentid"	=> 0,"mainbar"	=> 1));
			} else
			{
					if(!isset(contentController::$activeids[$level - 2]))
					{
							return false;
					}
					$id = contentController::$activeids[$level - 2];
					return DataObject::get("pages", array("parentid"	=> $id, "mainbar"	=> 1));
			}
	}
	
	/**
	 * gets mainbar items by parentid of page
	 *
	 *@name mainbarByID
	 *@access public
	*/
	public function mainbarByID($id) {
		return DataObject::get("pages", array("parentid"	=> $id, "mainbar"	=> 1));
	}
	
	/**
	 * returns a page-object by id
	 *
	 *@name pageByID
	 *@access public
	*/
	public function pageByID($id) {
		return DataObject::get_by_id("pages", $id);
	}
	
	/**
	 * returns a page-object by path
	 *
	 *@name pageByPath
	 *@access public
	*/
	public function pageByPath($path) {
		return DataObject::get_one("pages", array("path" => $path));
	}
	
	/**
	 * gets the title of the active mainbar
	 *@name active_mainbar_title
	*/
	public function active_mainbar_title($level = 2)
	{
		return ($this->active_mainbar()) ? $this->active_mainbar()->mainbartitle : "";
	}
	
	/**
	 * gets the url of the active mainbar
	 *@name active_mainbar_title
	*/
	public function active_mainbar_url($level = 2)
	{
		return ($this->active_mainbar()) ? $this->active_mainbar()->url : null;
	}
	
	/**
	 * returns the active-mainbar-object
	 *
	 *@name active_mainbar
	 *@access public
	*/
	public function active_mainbar($level = 2)
	{

		if(!isset(contentController::$activeids[$level - 2]))
		{
				return false;
		}
		$id = contentController::$activeids[$level - 2];
		if($level == 2 && isset(self::$active_mainbar)) {
			$data = self::$active_mainbar;
		} else { 
			$data = DataObject::get("pages", array("id"	=> $id));
			if($level == 2)
				self::$active_mainbar = $data;
		}
		return $data;
			
	}
	
	/**
	 * returns the prepended content
	 *
	 *@prependedContent
	 *@access public
	*/
	public static function prependedContent() {
		$div = new HTMLNode('div', array(), self::$prependedContent);
		
		return $div->html();
	}
	
	/**
	 * returns the appended content
	 *
	 *@appendedContent
	 *@access public
	*/
	public static function appendedContent() {
		$div = new HTMLNode('div', array(), self::$appendedContent);
		
		return $div->html();
	} 
}

Object::Extend("tplCaller", "ContentTPLExtension");