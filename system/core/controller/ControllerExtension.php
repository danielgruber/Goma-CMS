<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 19.02.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 * you also can use a controller-extension, so you can controller-methods
*/
abstract class ControllerExtension extends Controller implements ExtensionModel
{
		/**
		 * works the same as on {@link requestHandler}
		 *
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array();
		
		/**
		 * works the same as on {@link requestHandler}
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array();
		
		/**
		 * extra_methods
		 *
		 *@name extra_methods
		 *@access public
		*/
		public static $extra_methods = array();
		
		/**
		 * the owner-class
		 *@name owner
		 *@access protected
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
					throw new InvalidArgumentException('$object isn\'t a object');
				}

				if(class_exists($object->classname))
				{
					$this->owner = $object;
				} else {
					throw new LogicException('Class '.$class.' doesn\'t exist in context.');
				}
		}
		
		/**
		 * gets the owner of class
		 *@name getOwner
		*/
		public function getOwner()
		{
				return $this->owner;
		}
		
		/**
		 * gets the url handlers
		*/
		public function url_handlers() {
			return $this->url_handlers;
		}
		
		/**
		 * gets the allowed_actions
		*/
		public function allowed_actions() {
			return $this->allowed_actions;
		}
}