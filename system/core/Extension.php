<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 19.02.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

abstract class Extension extends ViewAccessAbleData implements ExtensionModel
{		

		/**
		 * extra_methods
		*/
		public static $extra_methods = array();
		/**
		 * the owner-class
		 *@name owner
		*/
		protected $owner;
		/**
		 * sets the owner-class
		 *@name setOwner
		*/		
		public function setOwner($object)
		{
				if(!is_object($object))
				{
						throwError(20,'PHP-Error', '$object isn\'t a object in '.__FILE__.' on line '.__LINE__.'');
				}
				if(class_exists($object->class))
				{
						$this->owner = $object;
				} else
				{
						throwError(20, 'PHP-Error', 'Class '.$class.' doesn\'t exist in context.');
				}
				
				return $this;
		}
		/**
		 * gets the owner of class
		 *@name getOwner
		*/
		public function getOwner()
		{
				return $this->owner;
		}
}

