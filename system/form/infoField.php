<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
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