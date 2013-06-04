<?php
/**
  * this class is a form-validator, used for dataobject's validation
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 23.01.2013
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class DataValidator extends FormValidator
{
		/**
		 *@name __construct
		 *@param object - dataobject
		 *@return object
		*/
		public function __construct(object $data)
		{
				
				if(!is_subclass_of($data, "dataobject") || $data === null)
				{
						throwError(6, 'PHP-Error', '$data is no child of dataobject in '.__FILE__.' on line '.(__LINE__ - 3).'');
				}
				$this->data = $data;
		}
		/**
		 * validates the data
		 *@name valdiate
		 *@access public
		*/
		public function validate()
		{
				$valid = true;
				$errors = array();
				if(is_object($this->form->result))
						$result = $this->form->result->ToArray();
				else
						$result = $this->form->result;
				if(is_array($this->data->ToArray()))
				{
						$_data = array_merge($this->data->ToArray(), $result);
				} else
				{
						$_data = $result;
				}
				
				foreach($this->form->result as $field => $data)
				{
						if(Object::method_exists($this->data->classname, "validate" . $field))
						{
								$method = "validate" . $field;
								$str = $this->data->$method($_data);
								if($str === true)
								{
										// everything ok
								} else
								{
										$valid = false;
										$errors[] = $str;
								}
						}
				}
				
				if($valid)
				{
						return true;
				} else
				{
						return implode(",", $errors);
				}
		}
}