<?php
/**
  *@package goma framework
  *@subpackage template
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@Copyright (C) 2009 - 2011 Goma-Team
  * last modified: 05.02.2011
*/  


defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class template extends gObject
{
        /**
		 * the variables
		 *@var array - vars
		 *@access public
		 */
        protected $vars = array();
		/**
		 * the ifs
		 *@var array - ifs
		 *@access public
		**/
		protected $ifs = array();
		/**
		 * this var contains the object
		 *@var gObject
		*/
		protected $object;
		/**
		 * construction
		 *@name __construct
		 *@param object
		 *@access public
		*/
		public function __construct($object = false)
		{
				parent::__construct();
				
				/* --- */
				
				if(!$object)
				{
					$object = gObject::instance("ViewAccessAbleData");
				}
				$this->object = $object;
		}
		/**
		 * to init a template
		 *@access public
		 *@param string - name of the file
		 *@return string - generated html-code
		**/
		public function init($name)
		{
				
		       	return $this->object->customise($this->vars)->renderWith($name);
		}
		/**
		 * to init a template
		 *@access public
		 *@param string - name of the file
		 *@return string - generated html-code
		**/
		public function display($name)
		{
		       	return $this->init($name);
		}
		/**
		 * to assign a variable
		 * you can access with {$variablename} in template to it
		 *@access public
		 *@param string - name of the variable
		 *@param string - value of the variable
		 */
		public function assign($name, $value)
		{
		        $this->vars[$name] = $value;
		}
		/**
		 * for arrays
		 * in templatecode: <!-- BEGIN name --> {$name.variable} <!-- end name -->
		 *@access public
		 *@param string - name
		 *@param array - the array, e.g. array('variable' => 'Hello World');
		 */
		public function assign_vars($name, $arr)
		{
		        $this->vars[$name][] = $arr;
		}
}