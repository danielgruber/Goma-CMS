<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 13.12.2011
  * $Version 019
 ^
 
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Pages extends DataObject implements PermissionProvider
{
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
									'title' 			=> 'varchar(200)',
									'data' 				=> 'text',
									'sort'				=> 'int(8)',
									'search'			=> 'int(1)',
									'editright'			=> 'text',
									'meta_description'	=> 'varchar(200)',
									'meta_keywords'		=> 'varchar(200)',
									"edit_type"			=> "varchar(10)",
									"viewer_type"		=> "varchar(20)",
									"readpassword"		=> "varchar(50)");
									
		/**
		 * important fields
		*/
		public $important_db_fields = array(
			"viewer_type"
		);
		/**
		 * has one
		 *@name has_one
		 *@var array
		*/
		public $has_one = array('parent' => 'pages');	
		/**
		 * has many
		 *@name has_many
		 *@var array
		*/
		public $has_many = array('children' => 'pages');
		/**
		 * many-many
		 *@name many_many
		 *@access public
		 *@var array
		*/
		public $many_many = array('edit_groups' => 'group', "viewer_groups"	=> "group");
		/**
		 * searchable fields
		*/
		public $searchable_fields = array("data", "title", "mainbartitle", "meta_keywords");
		/**
		 * indexes 
		*/
		public $indexes = array(
			array("type" => "INDEX", "fields" => "path,sort,class_name", "name" => "path"),
			array("type" => "INDEX", "fields" => "parentid,mainbar,class_name", "name"	=> "mainbar"),
			array("type" => "INDEX", "fields" => "class_name,data,title,mainbartitle,meta_keywords,id","name" => "sitesearch")
		);
		/**
		 * which parents are allowed
		*/
		public $can_parent = array();
		/**
		 * childs that are allowed
		 *@name allowed_children
		*/
		public $allowed_children = array();
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
		*/
		public $versioned = true;
		/**
		 * delete
		*/
		/*public function onAfterDelete() {
			foreach($this as $record) {
				$record->children()->_delete(true);
			}
		}*/
		
		/**
		 * can-publish-rights
		*/
		public function canPublish() {
			return Permission::check("PAGES_PUBLISH");
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
			if(Permission::check(10) && (!isset($_SESSION['sites_ansicht']) || $_SESSION['sites_ansicht'] != $GLOBALS['lang']['user'])) {
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
		 *@name geturl
		 *@return string
		*/
		public function geturl()
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
				if(($this->parentid == 0 || $this->parentid == "") && in_array("pages", $this->allowed_parents()))
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
			return $object->render();
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
			return $object->render();
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
		 *@name setFilename
		 *@access public
		*/
		public function setFilename($value)
		{
				$this->setPath($value);
		}
		/**
		 * sets the path
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
						$add = " AND `pages`.`id` != '".dbescape($data["id"])."'";
				} else
				{
						$add = "";
				}
				
				return true;
		}
		/**
		 * gets the edit-type
		 *@name getedit_type
		 *@access public
		*/
		public function getedit_type()
		{
				if($this->fieldGet("edit_type") == "")
				{
						return "all";
				} else
				{
						return $this->fieldGet("edit_type");
				}
		}
	
		/**
		 * viewer_types
		 *@name setviewer_types
		 *@access public
		*/
		public function setviewer_type($value)
		{
				if($value == "all")
				{
						$this->rights = 1;
				} else if($value == "login")
				{
						$this->rights = 2;
				}
				$this->setField("viewer_type", $value);
		}
		/**
		 * gets the viewer-type
		 *
		 *@name getViewerType
		*/
		public function getviewer_type() {
			if($this->fieldGet("viewer_type") == "")
				return "all";
			else
				return $this->fieldGet("viewer_type");
		}
		/**
		 * sets rights
		 *@name setRights
		 *@access public
		*/
		public function setviewer_types($value)
		{
				$viewer_type = $this->fieldGet("viewer_type");
				if($viewer_type == "all" || $viewer_type == "login")
				{
						return true;
				}
				$this->setField("rights", $value);
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
						$d = DataObject::_get("pages", array("id" => $parentid), array("class_name"));
						$pclassname = strtolower($d["class_name"]);
				}

				if(in_array(strtolower($pclassname), Object::instance($classname)->can_parent))
				{
						return true;
				} else if($d->_count() > 0 && in_array(strtolower($classname), Object::instance($pclassname)->allowed_children))
				{
						return true;
				}
				
				return lang("form_bad_pagetype");
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
				
				if($this->id != 0 && isset($this->data["stateid"]) && $this->data["stateid"] !== null) {
					if($this->versions()->count > 1) {
						$html = '<div style="text-align: right;" class="pagelinks"><a href="'.Core::$requestController->namespace.'/versions/?redirect='.Core::$url.'">'.lang("browse_versions", "Browse all Versions").'</a>&nbsp;&middot;&nbsp;';
						if($this->publishedid != 0)
							$html .= '<a href="'.Core::$requestController->namespace.'/revert_changes" rel="ajaxfy">'.lang("revert_changes", "Revert Changes").'</a>&nbsp;&middot;&nbsp;';
					} else
						$html = '<div style="text-align: right;" class="pagelinks">';
					
					if($this->publishedid != 0) {
						$html .= '<a target="_blank" class="published" href="'.BASE_URI.BASE_SCRIPT."/?r=".$this->id.'&">'.lang("published_site", 'Published Site').'</a>&nbsp;&middot;&nbsp;';
					}
					$html .= '<a class="state" target="_blank" href="'.BASE_URI.BASE_SCRIPT."/?r=".$this->id.'&'.$this->baseClass.'_state">'.lang("current_state", 'Current State').'</a>';
					$html .= "</div>";
					
					$form->add($links = new HTMLField('links', $html));
					$links->container->addClass("hidden");
				}
				
				$form->add(new TabSet('tabs', array(
						new Tab('content', array(
						
							$title = new textField('title',$GLOBALS['lang']['title']),
							$mainbartitle = new textField('mainbartitle',$GLOBALS['lang']['menupoint_title'])

						),$GLOBALS['lang']['content']),
						
						new Tab('meta', array(
							$parenttype = new ObjectRadioButton("parenttype", lang("parentpage", "Parent Page"), array(
								"root" => lang("no_parentpage", "Root Page"),
								"subpage" => array(
									lang("subpage","sub page"),
									"parentid"
								)
							)),
							new HasOneDropDown("parent", lang("parentpage", "Parent Page"), "title", ' `pages`.`class_name` IN ("'.implode($allowed_parents, '","').'") AND `pages`.`id` != "'.$this->id.'"'),
							
							$description = new textField('meta_description', lang("site_description", "Description of this site")),
							$keywords = new textField('meta_keywords',lang("site_keywords", "Keywords of this site")),
							new checkbox('mainbar', lang("menupoint_add", "Show in menus")),
							new HTMLField(''),
							new checkbox('search', $GLOBALS['lang']['show_in_search']),		
							$filename = new textField('filename',$GLOBALS['lang']['filename'])
						),$GLOBALS['lang']['meta']),
						$rightstab = new Tab('rightstab', array(
							new ObjectRadioButton("viewer_type", lang("viewer_types", "Viewer Groups"), array(
								"all" 	  	=> lang("everybody", "everybody"),
								"login" 	=> lang("login_groups", "Everybody, who can login"),
								"rights"	=> array(
									lang("following_rights", "following rights"),
									"rights"
								),
								"password"	=> array(
									lang("password", "password"),
									"readpassword"
								),
								"groups"	=> array(
									lang("following_groups", "Following Groups"),
									"viewer_groups"
								)
							)),
							new select('rights',$GLOBALS['lang']['min_rights'],  array(1, 2, 3, 4,5,6,7,8,9, 10)),
							new TextField("readpassword"),
							new ManyManyDropDown("viewer_groups", lang("viewer_groups", "Viewer Groups"), "name")
						), $GLOBALS['lang']['rights']),
						
					) 
				));
				
				$description->info = lang("description_info");
				$keywords->info = lang("keywords_info");
				
				if(!in_array("pages", $allowed_parents))
						$parenttype->disable("root");
				
				// add some js
				$form->add(new JavaScriptField("change",'$(function(){
					$("#'.$title->ID().'").change(function(){
						if($(this).val() != "") {
							var value = $(this).val();
							$("#'.$mainbartitle->ID().'").val(value);
							if($("#'.$filename->ID().'").length > 0) {
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
								
								
								if(confirm("'.lang("confirm_change_url").' " + filename)) {
									$("#'.$filename->ID().'").val(filename);
								}
							}
						}
						
					});
				});'));
				
				if(right(10))
				{
						$rightstab->add(new ObjectRadioButton("edit_type", lang("editors", "Editors"), array(
								"all"	 => lang("content_edit_users", "All Users, who can edit pages."),
								"groups" => array(
									lang("following_groups", "The following groups"),
									"edit_groups"
								)
							)));
						$rightstab->add(new manymanydropdown("edit_groups", lang("editor_groups", "Editor Groups"),  "name", array("advrights" => array("name" => "PAGES_WRITE"))));
				}
				
				
		}
		
		/**
		 * gets form-actions
		 *
		 *@name getActions
		 *@access public
		*/
		public function getActions(&$form) {
			if($this->isDeleted() && $this->id != 0) {
				$form->addAction(new AjaxSubmitButton('_submit',lang("restore", "Restore"),"AjaxSave"));
			} else if($this->id != 0) {
					if($this->canDelete($this))
							$form->addAction(new HTMLAction("deletebutton", '<a rel="ajaxfy" href="'.Core::$requestController->namespace.'/delete'.URLEND.'?redirect='.ROOT_PATH.'admin/content/" class="button">'.lang("delete").'</a>'));
					
					$form->addAction(new AjaxSubmitButton('_submit',lang("save", "Save"),"AjaxSave"));
					if($this->everPublished())
						$form->addAction(new HTMLAction("unpublish", '<a class="button" href="'.Core::$requestController->namespace.'/unpublish" rel="ajaxfy">'.lang("unpublish", "Unpublish").'</a>'));
					$form->addAction(new AjaxSubmitButton('_publish',lang("publish", "Publish"),"AjaxPublish", "Publish"));
					
					
					
			} else {
				$form->addAction(new button('cancel',$GLOBALS['lang']['cancel'], "LoadTreeItem(0);"));
				// we need special submit-button for adding
				$form->addAction(new AjaxSubmitButton('_submit',lang("save", "Save"),"AjaxSave"));
				$form->addAction(new AjaxSubmitButton('_publish',lang("publish", "Publish"),"AjaxPublish", "Publish"));
				
			}	
			
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
						$allowed_parents = array();
						$can_parent =  array_map("strtolower",Object::instance($this->class)->can_parent);
						
						$allowed_parents = $can_parent;
						
						foreach(ClassInfo::getChildren("pages") as $child)
						{
								if(in_array($this->getRecordClass(), array_map("strtolower",Object::instance($child)->allowed_children)))
								{
										if(!in_array($child, $allowed_parents) && $child != $this->class)
										{
												$allowed_parents[] = $child;
										}
								}
						}
						
						$this->cache_parent = $allowed_parents;
						
						return $allowed_parents;
				} else
				{
						return $this->cache_parent;
				}	
		}
		/**
		 * get adds for the pageselector
		 *@name _add
		 *@access public
		*/
		public function _add()
		{
				if(in_array("pages", $this->allowed_parents()))
				{
						return array(0 => $GLOBALS['lang']['no_parentpage']);
				} else
				{
						return array();
				}
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
				if(parent::controller($controller))
						return parent::controller($controller);
				else
				{
						$this->controller = Object::instance("contentController");
						$this->controller->model_inst = $this;
						return $this->controller;
				}				
		}
		/**
		 * the path
		*/
		public function getPath()
		{
			if($this->fieldGet("parentid") != 0)
			{
				return $this->parent()->getPath() . "/" . $this->fieldGet("path");
			}
			return $this->fieldGet("path");
		}
		/**
		 * permission-checks
		*/
		public function canWrite($row = array())
		{
				
				if(right(10))
						return true;
				
				// first validate if it is an object
				if(!is_object($row))
				{
						if($this->id == $row["id"])
						{
								$row = $this;
						} else
						{
								$row = DataObject::get_by_id($this->class, $row["id"]);
						}
				}
				if(!$row)
					return false;
				
				if(parent::canWrite($row))
						if($row->edit_type == "all")
								return true;
						else if($row->is_many_many("edit_groups", member::groupids()))
						{
								return true;
						}
				else
						return false;
		}
		/**
		 * permission-checks
		*/
		public function canDelete($row)
		{
				if(right(10))
						return true;
				
				// first validate if it is an object
				if(!is_object($row))
				{
						if($this->id == $row["id"])
						{
								$row = $this;
						} else
						{
								$row = DataObject::get_by_id($this->class, $row["id"]);
						}
				}
				
				
				
				if(parent::canDelete($row))
						if($row->edit_type == "all")
								return true;
						else if($row->is_many_many("edit_groups", member::$groupid))
						{
								return true;
						}
				else
						return false;
		}
		/**
		 * permission-checks
		*/
		public function canInsert($row)
		{	
				if(right(10))
						return true;
				
				if(parent::canInsert($row))
				{
						// now get parent
						$parentid = $row["parentid"];
						if($parentid == 0)
						{
								return true;
						} else
						{
								$data = DataObject::get_by_id("pages", $parentid);
								if($data->canWrite($data))
								{
										return true;
								} else
								{
										return false;
								}
						}
				}
				else
						return false;
		}
		/**
		 * permissions
		 *@name providePermissions
		 *@access public
		*/
		public function providePermissions()
		{
			return array(
				"PAGES_ALL"	=> array(
					"title"		=> '{$_lang_sites_edit}',
					"default"	=> 9,
					"implements"=> array(
						"ADMIN_ALL",
						"PAGES_DELETE",
						"PAGES_INSERT",
						"PAGES_WRITE",
						"PAGES_PUBLISH"
					)
				),
				"PAGES_DELETE"	=> array(
					"title"		=> '{$_lang_pages_delete}',
					"default"	=> 9,
					"implements"=> array(
						"ADMIN_ALL"
					)
				),
				"PAGES_INSERT"	=> array(
					"title"		=> '{$_lang_pages_add}',
					"default"	=> 7,
					"implements"=> array(
						"ADMIN_ALL"
					)
				),
				"PAGES_WRITE"	=> array(
					"title"		=> '{$_lang_pages_edit}',
					"default"	=> 7,
					"implements"=> array(
						"ADMIN_ALL"
					)
				),
				"PAGES_PUBLISH"	=> array(
					"title"		=> '{$_lang_publish}',
					'default'	=> 7
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
			Profiler::mark("pages::getTree");
			
			/* --- */
			
			$arr = array();
			
			$where = array("parentid" => $parentid);
			if(isset($params["deleted"]) && $params["deleted"]) {
				$data = DataObject::get_Versioned($this, "group", $where);
			} else {
				$data = DataObject::get($this, $where);
			}
			
			if(Permission::check("ADMIN_WRITE") && (!isset($params["deleted"]) || !$params["deleted"])) $data->version = "state";
			
			foreach($data as $record) {
				
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
					if($childcount < 6) {
						$id = $record["recordid"];
						Profiler::unmark("pages::getTree");
						$children = $this->getTree($id, $fields, $activenode, $params);
						Profiler::mark("pages::getTree");
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
					"children"		=> $children
				);
				
				unset($state);
			}
			Profiler::unmark("pages::getTree");
			
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
			Profiler::mark("pages::searchTree");
			
			/* --- */
			
			$arr = array();
		
			$where = array();
			
			$data = DataObject::_search($this, $words, $where, array('('.$this->baseTable.'.id = "'.$this->version.'")', 'DESC'), array(), array(), false, "recordid");
			$data->version = false;
			if(Permission::check("ADMIN_WRITE")) $data->version = "state";
			
			$parentid_cache = array();
			
			foreach($data as $record) {
				if($record["parentid"] == 0) {
					if(!isset($arr["_" . $record["id"]])) {
						
						if($record->isDeleted()) {
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
							"data"			=> $record->ToArray(),
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
							if($record->isDeleted()) {
								$state = "deleted";
							}
							// get class-attribute
							$mainbar = ($record["mainbar"] == 1) ? "withmainbar" : "nomainbar";
							if(!isset($state))
								$state = ($record->isPublished()) ? "published" : "edited";
							$class = "".$record["class_name"]. " page ".$mainbar . " " . $state;
							unset($state);
							
							$arr["_" . $parentid]["children"]["_" . $record["recordid"]] = array(
								"title" 		=> $record["title"],
								"attributes"	=> array("class" => $class),
								"data"			=> $record->ToArray(),
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
							if(Permission::check("ADMIN_WRITE")) $data->version = "state";
							
							if(isset($arr["_" . $data["parentid"]]) || $data["parentid"] == 0) { // found
								if($data["parentid"] != 0 && isset($arr["_" . $data["parentid"]])) {
									// if isn't set
									if(!isset($arr["_" . $data["parentid"]]["children"]["_" . $data["id"]])) {
										if($data->isDeleted()) {
											$state = "deleted";
										}
										// get class-attribute
										$mainbar = ($data["mainbar"] == 1) ? "withmainbar" : "nomainbar";
										if(!isset($state))
											$state = ($data->isPublished()) ? "published" : "edited";
										$class = "".$data["class_name"]. " page ".$mainbar . " " . $state;
										unset($state);
										
										$arr["_" . $data["parentid"]]["children"]["_" . $data["id"]] = array(
											"title" 		=> $data["title"],
											"attributes"	=> array("class" => $class),
											"data"			=> $data->ToArray(),
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
										if($data->isDeleted()) {
											$state = "deleted";
										}
										// get class-attribute
										$mainbar = ($data["mainbar"] == 1) ? "withmainbar" : "nomainbar";
										if(!isset($state))
											$state = ($data->isPublished()) ? "published" : "edited";
										$class = "".$data["class_name"]. " page ".$mainbar;
										unset($state);
										
										$arr["_" . $data["id"]] = array(
											"title" 		=> $data["title"],
											"attributes"	=> array("class" => $class),
											"data"			=> $data->ToArray(),
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
			Profiler::unmark("pages::searchTree");
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
					if($record->isDeleted()) {
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
						"data"			=> $record->ToArray(),
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
		 *@param array - fields
		*/
		public static function getByURL($url, $fields = array()) {
			if($fields != array()) {
				$fields = array_merge(array("path", "id", "parentid"), $fields);
			}
			$request = new Request("GET", $url);
			// check if a path is given, else give back homepage
			if($params = $request->match("\$path!")) {
				// first get the site with the first url-part
				$currentdata = DataObject::_get("pages", array("path" => $params["path"], "parentid" => 0), $fields);
				if($currentdata > 0) {
					// then go part for part
					while($request->remaining() != "") {
						if($params = $request->match("\$path!")) {
							$newdata = DataObject::_get("pages", array("path" => $params["path"], "parentid" => $currentdata["id"]), $fields);
							if($newdata->_count() == 0) {
								break;
							} else {
								$currentdata = $newdata;
								unset($newdata);
							}
						} else {
							break;
						}
					}
					return $currentdata;
				} else {
					return false;
				}	
			} else {
				return DataObject::getone("pages", array(), $fields);
			}
			
		}
		
}


class MainbarTPLExtension extends Extension {
	/**
	 * methods
	*/
	public static $extra_methods = array(
		"level",
		"mainbar",
		"active_mainbar_title"
	);
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
	 * gets the title of the active mainbar
	 *@name active_mainbar_title
	*/
	public function active_mainbar_title($level = 2)
	{

			if(!isset(contentController::$activeids[$level - 2]))
			{
					return false;
			}
			$id = contentController::$activeids[$level - 2];
			$data = DataObject::get("pages", array("id"	=> $id));
			return $data->mainbartitle;
			
	}
}

Object::Extend("tplCaller", "MainbarTPLExtension");