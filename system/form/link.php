<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 15.01.2012
  * $Version 1.0.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class LinkAction extends FormAction
{
		/**
		 * whether to open in new window or not
		 *
		 *@name newwindow
		 *@access public
		 *@var bool
		*/
		public $newwindow = false;
		/**
		 * target url to link to 
		 *
		 *@name href
		 *@access public
		*/
		public $href;
		/**
		 * constructor
		 *
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param string - title
		 *@param string - href
		 *@param bool - whether to open in new window or not
		 *@param null|object - form
		*/
		public function __construct($name, $title = null, $href = null,$newwindow = false, $form = null)
		{
				$this->newwindow = $newwindow;
				$this->href = $href;
				parent::__construct($name, $title, null, $form);
		}
		/**
		 * creates the Node
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->setTag("a");
				$node->html($this->title);
				$node->href = $this->href;
				$node->addClass("button");
				if($this->newwindow)
					$node->target = "_blank";
				return $node;
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FormAction::field");
				
				$this->callExtending("beforeField");
				$this->input->val($this->title);
				
				$this->container->append($this->input);
				
				$this->container->setTag("span");
				$this->container->addClass("formaction");
				$this->container->removeClass("button");
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
}