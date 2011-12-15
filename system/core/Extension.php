<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 23.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

abstract class extension extends ViewAccessAbleData
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

