<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 09.01.2013
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class members extends Page
{
		/**
		 * icon for this page
		*/
		static $icon = "images/icons/fatcow16/group.png";
		
		/**
		 *@name name
		*/
		static $cname = '{$_lang_mem_members}';
		
		/**
		 * gets the data for memberlist
		 *
		 *@name member
		 *@access public
		*/
		public function member()
		{
				if(isset($this->viewcache["members"]))
					return $this->viewcache["members"];
					
				// online - not yet fully supported
				if(isset($_GET["online"]))
				{
					if($GLOBALS['cms_ajaxbar'] == 1)
					{
							$time_online = $GLOBALS['cms_ajaxbar_timeout'] / 1000 + 2;
					} else
					{
							$time_online = 300;
					}
					$last = TIME - $time_online;
					$this->viewcache["members"] = DataObject::get("user"," statistics.last_update > ".convert::raw2sql($last)."", array(), array(), array('statistics' => 'statistics.user = `users`.`id`'));
					return $this->viewcache["members"];
				} else {
					$this->viewcache["members"] = DataObject::get("user", array(), array(), array(), array(), null, true);
					return $this->viewcache["members"];
				}
		}
		
		/**
		 * generates the form for this page
		 *
		 *@name getForm
		 *@access public
		*/
		public function getForm(&$form)
		{
				parent::getForm($form);
				
				$form->remove("pagecomments");
				$form->remove("rating");
		}
}

class membersController extends PageController
{
		
		public $url_handlers = array(
			'member/$id!'		=> 'showmember'
		);
		
		public $allowed_actions = array(
			"showmember"
		);
		/**
		 * template of this controller
		 *@var string
		*/
		public $template = "account/memberlist.html";
		/**
		 * shows a specific member
		 *
		 *@name showmember
		 *@access public
		*/
		public function showmember()
		{
				
				$id = $this->request->getParam("id");
				$userdata = DataObject::get("user", array("id" => $id));
				
				return Object::instance("ProfileController")->index($id);
		}
}
