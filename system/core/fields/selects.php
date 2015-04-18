<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 30.05.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class SelectSQLField extends DBField {
    /**
     * gets the field-type
     *
     * @return string
     */
	static public function getFieldType($args = array()) {
		return 'enum("'.implode('"', $args).'")';
	}

    /**
     * generates the default form-field for this field
     *
     * @param string $title
     * @param array $args, optional
     * @return FormField|Select
     */
	public function formfield($title = null, $args = array())
	{

        $field = new Select($this->name, $title, $args, $this->value);

        return $field;
	}
}

class RadiosSQLField extends SelectSQLField {

}