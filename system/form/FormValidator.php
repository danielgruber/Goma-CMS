<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 30.08.2012
  * $Version 2.0.2
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FormValidator extends Object
{
		/**
		 *@name array
		 *@var mixed - the data
		*/
		public $data;
		/**
		 * form
		 *
		 *@name form
		 *@access public
		 *@var object
		*/
		public $form;
		/**
		 * additional args for the function
		*/
		public $args = array();
		/**
		 *@name __construct
		 *@param mixed - datas
		 *@return object
		*/
		public function __construct($data = null)
		{
				parent::__construct();
				
				if($this->classname == "formvalidator" && !is_callable($data)) {
					throwError(6, "Invalid Argument", "FormValidator requires a valid callback to be given.");
				}
				
				$this->data = $data;
		}
		
		/**
		 * sets the form
		 *@name setForm
		 *@param object
		*/
		public function setForm(&$form)
		{
				$this->form = $form;
		}
		/**
		 * validates the data
		 *@name validate
		 *@return bool|string
		*/
		public function validate()
		{
				return call_user_func_array($this->data, array_merge(array($this), $this->args));
		}
		/**
		 * generates some javascript for validating
		 *@name js
		 *@access public
		*/
		public function JS()
		{
				return "";
		}
}