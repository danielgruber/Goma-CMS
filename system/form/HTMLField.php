<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 24.03.2012
  * $Version 1.0.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLField extends FormField 
{
		/**
		 * this var stores the html for this field
		 *
		 *@name html
		 *@access public
		*/
		public $html;
		
		/**
		 * defines that these fields doesn't have a value
		 *
		 *@name hasNoValue
		*/
		public $hasNoValue = true;
		
		/**
		 * constructor
		*/
		public function __construct($name, $html = null, &$form = null)
		{
				parent::__construct($name, null, null, $form);
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
				$this->callExtending("beforeField");
				
								
				$this->container->append($this->html);
				$this->container->addClass("hidden");
				
				// some patch
				if($this->html == "" || strlen($this->html) < 15) {
					$this->container->addClass("hidden");
				}
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
}