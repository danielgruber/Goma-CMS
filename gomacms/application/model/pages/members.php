<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.04.2013
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
		*/
		public function member()
		{
			return DataObject::get("user", array(), array(), array(), array(), null, true);
		}

		/**
		 * generates the form for this page
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
				
				return gObject::instance("ProfileController")->index($id);
		}
}
