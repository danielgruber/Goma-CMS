<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 15.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLAction extends FormAction
{
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
				Profiler::mark("FormAction::field");
				
				$this->callExtending("beforeField");
				
				$this->container->append($this->html);
				
				$this->container->setTag("span");
				$this->container->addClass("formaction");
				
				$this->callExtending("afterField");
				
				Profiler::unmark("FormAction::field");
				
				return $this->container;
		}
}