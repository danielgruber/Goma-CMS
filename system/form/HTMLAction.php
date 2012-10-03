<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 21.12.2010
  * $Version 1.0.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLAction extends FormAction
{
		/**
		 * this var stores the html for this field
		 *
		 *@name html
		 *@access public
		*/
		public $html;
		/**
		 * constructor
		*/
		public function __construct($name, $html = null, $form = null)
		{
				parent::__construct($name, null, null, $form);
				$this->html = $html;
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FormAction::field");
				
				$this->callExtending("beforeField");
				
				$this->container->append($this->html);
				
				$this->container->setTag("span");
				$this->container->addClass("formaction");
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
}