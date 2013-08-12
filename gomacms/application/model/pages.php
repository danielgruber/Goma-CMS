<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for each GomaCMS-Page.
 *
 * It defines basic fields like Title, Meta-Tags, Hierarchy and Permissions. It also implements Tree-Generation and History. 
 * If you want to create a new page-type, you have to extend (@link Page).
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.6
 */

class Pages extends DataObject implements PermProvider, HistoryData, Notifier
{
		/**
		 * name
		*/
		static $cname = '{$_lang_content}';
		
		/**
		 * activate versions
		 * 
		 *@name versions
		*/
		static $versions = true;
		
		/**
		 * the db-fields
		 *
		 *@name db
		 *@var array
		*/
		static $db = array(	'path' 				=> 'varchar(500)',
							'rights' 			=> 'int(2)',
							'mainbar' 			=> 'int(1)',
							'mainbartitle' 		=> 'varchar(200)',
							'googletitle'		=> "varchar(200)",
							'title' 			=> 'varchar(200)',
							'data' 				=> 'HTMLtext',
							'sort'				=> 'int(8)',
							'search'			=> 'int(1)',
							'meta_description'	=> 'varchar(200)');
		
		/**
		 * searchable fields
		 *
		 *@name search_fields
		*/
		static $search_fields = array("data", "title", "mainbartitle", "meta_keywords");
		
		
		/**
		 * indexes to improve performance
		 *
		 *@name index
		 *@access public
		*/
		static $index = array(
			array("type" => "INDEX", "fields" => "path,sort,class_name", "name" => "path"),
			array("type" => "INDEX", "fields" => "parentid,mainbar,class_name", "name"	=> "mainbar"),
			array("type" => "INDEX", "fields" => "class_name,data,title,mainbartitle,meta_keywords,id","name" => "sitesearch")
		);
		
		/**
		 * which parents are allowed
		 *
		 *@name allow_parent
		*/
		static $allow_parent = array();
		
		/**
		 * childs that are allowed
		 *
		 *@name allow_children
		*/
		static $allow_children = array("Page", "WrapperPage");
		
		/**
		 * default sort
		*/
		static $default_sort = "sort ASC";
		
		/**
		 * show read-only edit if not enough rights
		*/
		public $showWithoutRight = true;
									
		/**
		 * a page has a parent page
		 * a page has permissions
		 *
		 *@name has_one
		 *@var array
		*/
		static $has_one = array(	"read_permission" 		=> "Permission",
									"edit_permission"		=> "Permission",
									"publish_permission" 	=> "Permission");
									
		
		/**
		 * link-tracking
		 *
		 *@name many_many
		 *@access public
		*/
		static $many_many = array(
			"UploadTracking"	=> "Uploads"
		);
		
		/**
		 * extensions of pages
		*/
		static $extend = array(
			"Hierarchy"
		);
		
		/**
		 * defaults
		*/
		static $default = array(	"parenttype" 	=> "root", 
									"search" 		=> 1,
									"mainbar"		=> 1,
									"sort"			=> 10000);
		
		/**
		 * icon
		*/
		static $icon = "images/icons/goma16/file.png";
		
		//!Getters and Setters
		
		/**
		 * makes the url
		 *
		 *@name geturl
		 *@return string
		*/
		public function getURL()
		{
			$path = $this->path;
			if($path == "" || ($this->fieldGet("parentid") == 0 && $this->fieldGet("sort") == 0)) {
				return ROOT_PATH . BASE_SCRIPT;
			} else {
				return  ROOT_PATH . BASE_SCRIPT . $path . URLEND;
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
				$value = preg_replace('/[^a-zA-Z0-9\-\._]/', '-', $value);
				$value = str_replace('--', '-', $value);
				$this->setField("path", $value);					

		}
		
		/**
		 * gets the title of the window
		 *
		 *@name getWindowTitle
		*/
		public function getWindowTitle() {
			if($this->fieldGet("googleTitle")) {
				return $this->googleTitle;
			} else {
				return $this->title;
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
		 * gets the content
		*/
		public function getContent()
		{
				return $this->data()->forTemplate();
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
		 * returns the representation of this record
		 *
		 *@name generateResprensentation
		 *@access public
		*/
		public function generateRepresentation($link = false) {
			$title = $this->title;
			
			if(ClassInfo::findFile(self::getStatic($this->classname, "icon"), $this->classname)) {
				$title = '<img src="'.ClassInfo::findFile(self::getStatic($this->classname, "icon"), $this->classname).'" /> ' . $title;
			}
			
			if($link)
				$title = '<a href="'.BASE_URI.'?r='.$this->id.'&pages_version='.$this->versionid.'" target="_blank">' . $title . '</a>';
			
			return $title;
		}
	
		
		//!Permission-Getters and Setters
		
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
			} else if(!$this->isPublished() && $this->wasPublished()) {
				if($data = DataObject::Get_one("Permission", array(), array(), array(
					'INNER JOIN ' . DB_PREFIX . "pages AS pages ON pages.edit_permissionid = permission.id AND pages.id = '".$this->publishedid."'"
				))) {
					return $data;
				}
			}
			
			//logging("edit:" . print_r($this->data, true) . print_r(debug_backtrace(), true));
			
			if($this->parent) {
				$parent = $this->parent->edit_permission();
			} else {
				$parent = Permission::forceExisting("PAGES_WRITE");
			}
			
			$perm = new Permission(array("type" => "admins"));
			$perm->parentid = $parent->id;
			$perm->forModel = "pages";
			if($this->ID != 0) {
				$perm->write(true, true, 2, false, false);
				$this->edit_permissionid = $perm->id;
				$this->write(false, true, $this->isOrgPublished() ? 2 : 1, false);
			}
			
			return $perm;
			
		}
		
		/**
		 * sets the edit-permission
		 *
		 *@name setEdit_Permission
		 *@access public
		*/
		public function setEdit_Permission($perm) {
			$perm->forModel = "pages";
			if($perm->parentid != 0) {
				if($perm->parent->name == "" && $this->parentid == 0) {
					$perm->parentid = Permission::forceExisting("PAGES_WRITE")->id;
				} else if($this->parentid != 0) {
					$perm->parentid = $this->parent->edit_permission->id;
				}
			}
			$perm->name = "";
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
			} else if(!$this->isPublished() && $this->wasPublished()) {
				if($data = DataObject::Get_one("Permission", array(), array(), array(
					'INNER JOIN ' . DB_PREFIX . "pages AS pages ON pages.publish_permissionid = permission.id AND pages.id = '".$this->publishedid."'"
				))) {
					return $data;
				}
			}
			
			//logging("publish:" . print_r($this->data, true) . print_r(debug_backtrace(), true));
			
			if($this->parent) {
				$parent = $this->parent->publish_permission();
			} else {
				$parent = Permission::forceExisting("PAGES_PUBLISH");
			}
			$perm = new Permission(array("type" => "admins"));
			$perm->parentid = $parent->id;
			$perm->forModel = "pages";
			
			if($this->ID != 0) {
				$perm->write(true, true, 2, false, false);
				$this->publish_permissionid = $perm->id;
				$this->write(false, true, $this->isOrgPublished() ? 2 : 1, false);
			}
			
			return $perm;
		}
		
		/**
		 * sets the publish-permission
		 *
		 *@name setPublish_Permission
		 *@access public
		*/
		public function setPublish_Permission($perm) {
			$perm->forModel = "pages";
			if($perm->parentid != 0) {
				if($perm->parent->name == "" && $this->parentid == 0) {
					$perm->parentid = Permission::forceExisting("PAGES_PUBLISH")->id;
				} else if($this->parentid != 0) {
					$perm->parentid = $this->parent->publish_permission->id;
				}
			}
			$perm->name = "";
			
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
			} else if(!$this->isPublished() && $this->wasPublished()) {
				if($data = DataObject::Get_one("Permission", array(), array(), array(
					'INNER JOIN ' . DB_PREFIX . "pages AS pages ON pages.read_permissionid = permission.id AND pages.id = '".$this->publishedid."'"
				))) {
					return $data;
				}
			}
			
			$perm = new Permission(array("type" => "all"));
			$perm->forModel = "pages";
			if($this->ID != 0) {
				$perm->write(true, true, 2, false, false);
				$this->read_permissionid = $perm->id;
				$this->write(false, true, $this->isOrgPublished() ? 2 : 1, false, false);
			}
			
			return $perm;		
		}
		/**
		 * sets the read-permission
		 *
		 *@name setRead_Permission
		 *@access public
		*/
		public function setRead_Permission($perm) {
			$perm->forModel = "pages";
			if($perm->parentid != 0) {
				if($perm->parent->name == "" && $this->parentid == 0) {
					$perm->parentid = 0;
				} else if($this->parentid != 0) {
					$perm->parentid = $this->parent->read_permission->id;
				}
			} else if($this->id == 0 && $this->parentid != 0) {
				$perm->parentid = $this->parent->read_permission->id;
			}
			$perm->name = "";
			$this->setField("Read_Permission", $perm);
			
			$this->viewcache = array();
		}
		
		//!Events
		
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
		 * on before writing
		 *
		 *@name onBeforeWrite
		*/
		public function onBeforeWrite() {
			$this->data["uploadtrackingids"] = array();
			
			if($this->sort == 10000) {
				if($this->id == 0)
					$this->data["sort"] = DataObject::count("pages", array("parentid" => $this->data["parentid"]));
				else {
					$i = 0;
					$sort = 0;
					foreach(DataObject::get("pages", array("parentid" => $this->data["parentid"])) as $record) {
						if($record->id != $this->id) {
							$record->sort = $i;
							$record->writeSilent(false, true, $this->isOrgPublished() ? 2 : 1, false);
							logging("Write Record " . $record->id . " to sort " . $i);
						} else {
							$sort = $i;
						}
						$i++;
					}
					
					$this->data["sort"] = $sort;
				}
			}
		}
		
		//!Validators

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
				if($filename == "index" || DataObject::count("pages", array("path" => array("LIKE", $filename), "parentid" => $parentid, "recordid" => array("!=", $obj->form->result["recordid"]))) > 0) {
					return lang("site_exists", "The page with this filename already exists.");
				} else {
					return true;
				}
			else if(DataObject::count("pages", array("path" => array("LIKE", $filename), "parentid" => $parentid)) > 0) {
					return lang("site_exists", "The page with this filename already exists.");
			} else {
				return true;
			}	
		}
		
		//!Form
		
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
				
				// render head-bar
				$html = '<div class="headBar"><a href="#" class="leftbar_toggle" title="{$_lang_toggle_sidebar}"><img src="system/templates/images/appbar.list.png" alt="{$_lang_show_sidebar}" /></a><span class="'.$this->classname.' pageType"><img src="'.ClassInfo::getClassIcon($this->classname).'" alt="" /><span>';
				
				// title in head-bar
				if($this->title)
					$html .= convert::raw2text($this->title);
				else
					$html .= convert::raw2text(ClassInfo::getClassTitle($this->classname));
				
				// end of title in head-bar
				$html .= ' </span></span>';
				
				// version-state-status
				if($this->id != 0 && isset($this->data["stateid"]) && $this->data["stateid"] !== null) {
					
					$html .= '<div class="pageinfo versionControls">';
					
					if($this->isPublished()) {
						$html .= '<div class="state"><div class="draft">'.lang("draft", "draft").'</div><div class="publish active">'.lang("published", "published").'</div></div>';
					} else {
						$html .= '<div class="state"><div class="draft active">'.lang("draft", "draft").'</div><div class="publish">'.lang("published", "published").'</div></div>';
					}
					
					if($this->everPublished()) {
						define("PREVIEW_URL", BASE_URI . BASE_SCRIPT.'?r='.$this->id);
						Resources::addJS("$(function(){ if(typeof pages_pushPreviewURL != 'undefined') pages_pushPreviewURL('".BASE_URI . BASE_SCRIPT.'?r='.$this->id."', '".BASE_URI . BASE_SCRIPT."?r=".$this->id . "&".$this->baseClass."_state', ".($this->isPublished() ? "true" : "false").", ".var_export($this->title, true)."); });");
					} else {
						define("PREVIEW_URL", BASE_URI . BASE_SCRIPT.'?r='.$this->id);
						Resources::addJS("$(function(){ if(typeof pages_pushPreviewURL != 'undefined') pages_pushPreviewURL(false, '".BASE_URI . BASE_SCRIPT."?r=".$this->id . "&".$this->baseClass."_state', false); });");
					}
					$html .= '</div>';
					
				}
				
				$html .= '<div style="clear:both;"></div>';
				
				// end of headbar and add it to form
				$html .= '</div>';
				$form->add($links = new HTMLField('links', $html));
				$links->container->addClass("hidden");
				
				define("EDIT_ID", $this->id);
				
				$form->add(new TabSet('tabs', array(
						new Tab('content', array(
							
							
						), lang("content", "content")),
						
						new Tab('meta', array(
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
							$description = new textField('meta_description', lang("site_description", "Description of this site")),
							$wtitle = new TextField("googletitle", lang("window_title")),
							new checkbox('mainbar', lang("menupoint_add", "Show in menus")),
							new HTMLField(''),
							new checkbox('search', lang("show_in_search", "show in search?")),		
							$filename = new textField('filename', lang("path"))
						), lang("settings", "settings")),
						$rightstab = new Tab('rightstab', array(
							$read = new PermissionField("read_permission", lang("viewer_types"), null, true),
							$write = new PermissionField("edit_permission", lang("editors"), array("type" => "inherit"), false, array("all")),
							$publish = new PermissionField("publish_permission", lang("publisher"), array("type" => "inherit"), false, array("all"))
						), lang("rights", "permissions"))
						
					) 
				));
				
				// append the field title, mainbar-title and hierarchy
				// they are in Editor-Area in an other tab than in add-view
				/*if($this->id == 0) {
					$titleHolder = $form->content;
				} else {
					$titleHolder = $form->meta;
				}
				
				$titleHolder->add($title = new textField('title', lang("title_page", "title of the page")), 1);
				$titleHolder->add($mainbartitle = new textField('mainbartitle', lang("menupoint_title", "title on menu")), 1);
				
				$titleHolder->add($parenttype = new ObjectRadioButton("parenttype", lang("hierarchy", "hierarchy"), array(
					"root" => lang("no_parentpage", "Root Page"),
					"subpage" => array(
						lang("subpage","sub page"),
						"parent"
					)
				)), 1);
				
				$titleHolder->add($parentDropdown = new HasOneDropDown("parent", lang("parentpage", "Parent Page"), "title", ' `pages`.`class_name` IN ("'.implode($allowed_parents, '","').'") AND `pages`.`id` != "'.$this->id.'"'), 2);*/
				
				
				// check for permissions
				if(!$this->can("Write") || !Permission::check("PAGES_WRITE")) {
					$write->disable();
				}
				
				if(!$this->can("Publish") || !Permission::check("PAGES_PUBLISH")) {
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
				$mainbartitle->info = lang("menupoint_title_info");
				$wtitle->info = lang("window_title_info");
				
				if(!in_array("pages", $allowed_parents) || ($this->id == 0 && !Permission::check("PAGES_WRITE") && !Permission::check("PAGES_PUBLISH"))) {
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
				
				if($this->can("Delete")) {
					$form->addAction(new HTMLAction("deletebutton", '<a rel="dropdownDialog" href="'.Core::$requestController->namespace.'/delete'.URLEND.'?redirect='.ROOT_PATH.'admin/content/" class="button delete formaction">'.lang("delete").'</a>'));
				}
				
				if($this->everPublished() && !$this->isOrgPublished() && $this->can("Write")) {
					$form->addAction(new HTMLAction("revert_changes", '<a class="draft_delete red button" href="'.Core::$requestController->namespace.'/revert_changes" rel="dropdownDialog">'.lang("draft_delete", "delete draft").'</a>'));
				}
				
				if($this->everPublished() && $this->can("Publish")) {
					$form->addAction(new HTMLAction("unpublish", '<a class="button" href="'.Core::$requestController->namespace.'/unpublish" rel="ajaxfy">'.lang("unpublish", "Unpublish").'</a>'));
				}
				
				if($this->can("Write"))
					$form->addAction(new AjaxSubmitButton("save_draft",lang("draft_save", "Save draft"),"AjaxSave"));
				
				if($this->can("Publish"))
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
		
		//!Permissions
		
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
		 * returns that everyone who has the permission to view the content-page in admin-panel can view drafts and versions
		 *
		 *@name canViewVersions
		*/
		public function canViewVersions() {
			return Permission::check("ADMIN_CONTENT");
		}
		
		/**
		 * permission-checks
		*/
		public function canWrite($row = null) {		
			if(Permission::check("superadmin"))
				return true;
			
			if(isset($row) && is_object($row->edit_permission) && $row->edit_permission->type != "admins") {
				return $row->edit_permission->hasPermission();
			}
			
			return Permission::check("PAGES_WRITE");
		}
		
		/**
		 * can-publish-rights
		*/
		public function canPublish($row) {
			if(Permission::check("superadmin"))
				return true;
			
			if(isset($row) && is_object($row->publish_permission) && $row->publish_permission->type != "admins")
				return $row->publish_permission->hasPermission();
			
			
			return Permission::check("PAGES_PUBLISH");
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
						return $data->first()->can("Write", $data);
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
		
		/**
		 * local argument sql to inherit view-permissions
		 *
		 *@name argumentQuery
		 *@access public
		*/
		
		public function argumentQuery(&$query) {
			parent::argumentQuery($query);
			
			if(!Permission::check("superadmin")) {
				if(!member::login()) {
					$query->leftJOIN("permission_state", "view_permission_state.id = pages.read_permissionid", "view_permission_state");
					$query->leftJOIN("permission", "view_permission.id = view_permission_state.publishedid", "view_permission");
					
					$query->addFilter("read_permissionid = 0 OR view_permission.type IN ('all', 'password')");
				} else if(Permission::check("ADMIN_CONTENT")) {
					$query->leftJOIN("permission_state", "view_permission_state.id = pages.read_permissionid", "view_permission_state");
					$query->leftJOIN("permission", "view_permission.id = view_permission_state.publishedid", "view_permission");
					
					$query->addFilter("read_permissionid = 0 OR view_permission.type IN ('all', 'password', 'users', 'admin') OR view_permission.id IN (SELECT permissionid FROM ".DB_PREFIX . ClassInfo::$class_info["permission"]["many_many_tables"]["groups"]["table"]." WHERE groupid IN ('".implode("','", member::$loggedIn->groupsids)."'))");
				} else {
					$query->leftJOIN("permission_state", "view_permission_state.id = pages.read_permissionid", "view_permission_state");
					$query->leftJOIN("permission", "view_permission.id = view_permission_state.publishedid", "view_permission");
					
					$query->addFilter("read_permissionid = 0 OR view_permission.type IN ('all', 'password', 'users') OR view_permission.id IN (SELECT permissionid FROM ".DB_PREFIX . ClassInfo::$class_info["permission"]["many_many_tables"]["groups"]["table"]." WHERE groupid IN ('".implode("','", member::$loggedIn->groupsids)."'))");
				}
			}
		}
		
		/**
		 * builds the tree.
		 * 
		 * @param 	TreeNode|int $parent parent node or no parent node = 0
		 * @param 	array $dataparams "version", "filter"
		*/
		static function build_tree($parentNode = null, $dataParams = array()) {
			if(!is_object($parentNode) && $parentNode == 0) {
				$data = DataObject::get("pages", array("parentid" => 0));
			} else if(is_a($parentNode, "TreeNode")) {
				if($parentNode->model) {
					$data = $parentNode->model->children();
				} else {
					$data = DataObject::get("pages", array("parentid" => $parentNode->recordid));
				}
			} else if(is_int($parentNode)) {
				$data = DataObject::get("pages", array("parentid" => $parentNode));
			}
			
			// add Version-Params
			if(isset($dataParams["version"]))
				$data->setVersion($dataParams["version"]);
			
			if(isset($dataParams["filter"]))
				$data->addFilter($dataParams["filter"]);
				
			$nodes = array();
			foreach($data as $record) {
				$node = new TreeNode("page_" . $record->versionid, $record->id, $record->title, $record->class);
				
				// add a bubble for changed or new pages.
				if(!$record->isPublished())
					if($record->everPublished())
						$node->addBubble(lang("CHANGED"), "red");
					else
						$node->addBubble(lang("NEW"), "blue");
						
						
				if($record->children()->count() > 0) {
					$node->setChildCallback(array("pages", "build_tree"));
				}
				
				$nodes[] = $node;
			}
			
			return $nodes;
		}
		
		
		//!APIs
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
			$relevant = true;
			
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
			
			return array("icon" => $icon, "text" => $lang, "versioned" => true, "compared" => $compared, "editurl" => "admin/content/record/" . $record->newversion()->id . "/edit" . URLEND, "relevant" => $relevant);
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
	
	/**
	 * cache for allowed_parents
	 *@name cache_parent
	 *@access public
	*/
	private static $cache_parent = array();
	
	/**
	 * gets allowed parents
	 *@name allowed_parents
	 *@access public
	*/		
	public function allowed_parents() {
        	if(PROFILE) Profiler::mark("pages::allowed_parents");
        
       	 	$cacher = new Cacher("cache_parents");
        	if($cacher->checkValid()) {
           		self::$cache_parent = $cacher->getData();
        	}
        
		// for performance reason we cache this part
		if(!isset(self::$cache_parent[$this->classname]) || self::$cache_parent[$this->classname] == array()) {
				
			
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
							if(substr($allow, 1) == $this->classname || is_subclass_of($this->classname, substr($allow, 1))) {
								unset($allowed_parents[$child]);
								continue 2;
							}
						} else {
							if($allow == $this->classname || is_subclass_of($this->classname, $allow)) {
								$allowed_parents[$child] = $child;
							}
						}
					}
				}
			}
			
			// now filter
			$allow_parents = ClassInfo::getStatic($this->classname, "allow_parent");
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
					
					// if not found, unset
					if(!$found) {
						unset($allowed_parents[$parent]);
					}
				}
			}
	        
	        	self::$cache_parent[$this->classname] = $allowed_parents;
				
			if(PROFILE) Profiler::unmark("pages::allowed_parents", "pages::allowed_parents generate");
	        
	        	$cacher->write(self::$cache_parent, 86400);
	        
			return $allowed_parents;
		} else {
            if(PROFILE) Profiler::unmark("pages::allowed_parents");
				return self::$cache_parent[$this->classname];
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
	 * gets controller
	 *@name controller
	 *@access public
	*/
	public function controller($controller = null) {
		if(parent::controller($controller)) {
			return parent::controller($controller);
		} else {
			$this->controller = Object::instance("contentController");
			$this->controller->model_inst = $this;
			return $this->controller;
		}				
	}
		
}

/**
 * Extends the TemplateCaller with some new methods to get content of pages.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0
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
		return DataObject::get_one("pages", array("path" => array("LIKE" => $path)));
	}
	
	/**
	 * gets the title of the active mainbar
	 *@name active_mainbar_title
	*/
	public function active_mainbar_title($level = 2)
	{
		return ($this->active_mainbar($level)) ? $this->active_mainbar($level)->mainbartitle : "";
	}
	
	/**
	 * gets the url of the active mainbar
	 *@name active_mainbar_title
	*/
	public function active_mainbar_url($level = 2)
	{
		return ($this->active_mainbar($level)) ? $this->active_mainbar($level)->url : null;
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

class UploadsPageBacktrace extends DataObjectExtension {
	/**
	 * relation to pages
	 *
	 *@name belongs_many_many
	*/
	static $belongs_many_many = array(
		"linkingPages"	=> "pages"
	);
}

Object::extend("Uploads", "UploadsPageBacktrace");
Object::extend("tplCaller", "ContentTPLExtension");
