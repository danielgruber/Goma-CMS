<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 20.05.2011
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
		public function __construct($data)
		{
				parent::__construct();
				
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