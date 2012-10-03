<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 21.02.2012
  * $Version 1.1.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class infoField extends HTMLField
{
		/**
		 * this var stores the html for this field
		 *
		 *@name html
		 *@access public
		*/
		public $html;
		/**
		 * special field with special style
		 *
		 *@name __construct
		 *@access public
		*/
		public function __construct($name, $html = null, &$form = null)
		{
				parent::__construct($name, null, $form);
				$this->html = $html;
		}
		/**
		 * generates the field
		 *
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FormField::field");
				
				$this->callExtending("beforeField");
				
				$this->container->append('
							<div class="info_box">
								'.$this->html.'
							</div>');
				$this->container->addClass("hidden");
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormField::field");
				
				return $this->container;
		}
}