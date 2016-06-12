<?php defined("IN_GOMA") OR die();

/**
 * inspiration by Silverstripe 3.0 GridField
 * http://silverstripe.org
 *
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 17.05.2013
 * $Version - 1.0.2
 */
class TableFieldDataColumns implements TableField_ColumnProvider
{
	/**
	 * fields with casted values
	 *
	 * @name fieldCasting
	 * @access public
	 */
	public $fieldCasting = array();

	/**
	 * field-formatting, for example an img or a link for the column
	 *
	 * @name fieldFormatting
	 * @access public
	 */
	public $fieldFormatting = array();

	/**
	 * display-fields
	 *
	 * @name displayFields
	 * @access public
	 */
	public $displayFields = array();

	/**
	 * Inits the Component.
	 * @param TableField $tableField
	 */
	public function Init($tableField)
	{
		/** @var TableFieldFilterHeader $filter */
		if ($filter = $tableField->getConfig()->getComponentByType('TableFieldFilterHeader')) {
			foreach ($this->fieldFormatting as $field => $values) {
				$filter->addCastedValues($field, array_flip((array)$values));
			}
		}
	}

	/**
	 * sets the display-fields
	 *
	 * @name setDisplayFields
	 * @access public
	 * @return $this
	 */
	public function setDisplayFields($displayFields)
	{
		if (is_array($displayFields)) {
			$this->displayFields = $displayFields;
			return $this;
		} else {
			throw new InvalidArgumentException("First argument of TableFieldDataColumns::setDisplayFields should be an array.");
		}
	}

	/**
	 * returns the display-fields
	 *
	 * @param TableField $tableField
	 * @return array
	 */
	public function getDisplayFields($tableField)
	{
		if (empty($this->displayFields) && gObject::method_exists($tableField->getModelClass(), "summaryFields")) {
			return gObject::instance($tableField->getModelClass())->summaryFields();
		}

		return $this->displayFields;
	}

	/**
	 * sets the fieldFormatting
	 *
	 * @name setFieldFormatting
	 * @access public
	 * @return $this
	 */
	public function setFieldFormatting($fieldFormatting)
	{
		if (is_array($fieldFormatting)) {
			$this->fieldFormatting = $fieldFormatting;

			return $this;
		} else {
			throw new InvalidArgumentException("First argument for TableFieldDataColumns::setFieldFormatting should be an array.");
		}
	}

	/**
	 * sets the fieldCasting
	 *
	 * @name setFieldCasting
	 * @access public
	 * @return $this
	 */
	public function setFieldCasting($fieldCasting)
	{
		if (is_array($fieldCasting)) {
			$this->fieldCasting = $fieldCasting;

			return $this;
		} else {
			throw new InvalidArgumentException("First argument for TableFieldDataColumns::setFieldCasting should be an array.");
		}
	}

	/**
	 * add columns in the order you want to have them in the table
	 * you have full control over all the columns through the reference of $columns
	 * @param TableField $tableField
	 * @param array $columns
	 */
	public function augmentColumns($tableField, &$columns)
	{
		foreach (array_keys($this->getDisplayFields($tableField)) as $field) {
			array_push($columns, $field);
		}
	}

	/**
	 * similiar to augmentColumns, but with the difference that you just give back an unsorted list of all the columns you handle in this class
	 *
	 * @param TableField $tableField
	 * @return array
	 */
	public function getColumnsHandled($tableField)
	{
		return array_keys($this->getDisplayFields($tableField));
	}

	/**
	 * returns the content of the given column to the given record
	 *
	 * @param TableField $tableField
	 * @param ViewAccessableData $record
	 * @param string $columnName
	 * @return mixed|string
	 */
	public function getColumnContent($tableField, $record, $columnName)
	{
		if(!is_object($record) || !is_string($columnName)) {
			throw new InvalidArgumentException("Second argument must be object. Third string. TableFieldDataColumns::getColumnContent");
		}

		$fields = $this->getDisplayFields($tableField);

		if (is_array($fields[$columnName]) && isset($fields[$columnName]["callback"])) {
			$value = call_user_func_array($fields[$columnName]["callback"], array($record));
		} else {
			$value = $tableField->getDataFieldValue($record, $columnName);
		}

		$value = $this->castValue($tableField, $columnName, $value);

		$value = $this->formatValue($tableField, $record, $columnName, $value);

		if ($value == "") {
			$value = '<span class="no-value">' . lang("no_value", "no value") . '</span>';
		}

		return $value;
	}

	/**
	 * returns the attributes of the given column to the given record
	 *
	 * @name getColumnAttributes
	 * @access public
	 * @return array
	 */
	public function getColumnAttributes($tableField, $columnName, $record)
	{
		return array("class" => "col-" . preg_replace('/[^\w]/', '-', $columnName));
	}

	/**
	 * returns the meta-data of the given column for all records
	 *
	 * @param $tableField
	 * @param $columnName
	 * @return array
	 * @internal param $getColumnMetadata
	 * @access public
	 */
	public function getColumnMetadata($tableField, $columnName)
	{
		$fields = $this->getDisplayFields($tableField);

		$title = null;
		if (is_string($fields[$columnName])) {
			$title = $fields[$columnName];
		} else if (is_array($fields[$columnName]) && isset($fields[$columnName]["title"])) {
			$title = $fields[$columnName]["title"];
		}
		return array(
			"title" => $title
		);
	}

	/**
	 * Casts a field to a string which is safe to insert into HTML
	 *
	 * @param TableField $tableField
	 * @param string $fieldName
	 * @param string $value
	 * @return string
	 */
	protected function castValue($tableField, $fieldName, $value)
	{
		// If a fieldCasting is specified, we assume the result is safe
		if (array_key_exists($fieldName, $this->fieldCasting)) {
			$value = $tableField->getCastedValue($value, $this->fieldCasting[$fieldName]);
		} else if (is_object($value)) {
			// If the value is an object, we do one of two things
			if (method_exists($value, 'Nice')) {
				// If it has a "Nice" method, call that & make sure the result is safe
				$value = Convert::raw2xml($value->Nice());
			} else {
				// Otherwise call forTemplate - the result of this should already be safe
				$value = $value->__toString();
			}
		} else {
			// Otherwise, just treat as a text string & make sure the result is safe
			$value = Convert::raw2xml($value);
		}

		return $value;
	}

	/**
	 *
	 * @param TableField $tableField
	 * @param DataObject $data
	 * @param string $fieldName
	 * @param string $value
	 * @return string
	 */
	protected function formatValue($tableField, $data, $fieldName, $value)
	{
		if (!array_key_exists($fieldName, $this->fieldFormatting)) {
			return $value;
		}

		$spec = $this->fieldFormatting[$fieldName];
		if (is_callable($spec)) {
			return $spec($value, $data);
		} else if (is_array($spec)) {
			if (isset($spec[$value]))
				return $spec[$value];

			return $value;
		} else {

			$format = str_replace('$value', "__VAL__", $spec);
			$format = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("TableFieldDataColumns", "convert_vars"), $format);
			$format = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("TableFieldDataColumns", "vars"), $format);
			$format = str_replace('__VAL__', '$value', $format);
			eval('$value = "' . $format . '";');
			return $value;
		}
	}

	/**
	 * vars with convertion
	 * @name convert_vars
	 * @access public
	 * @return string
	 */
	public static function convert_vars($matches)
	{

		$php = '$data';
		$var = $matches[1];
		$function = $matches[2];
		$params = $matches[3];

		// isset-part
		$isset = '$data';
		// parse params
		$params = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("tpl", "percent_vars"), $params);
		// parse functions in params
		$params = preg_replace_callback('/([a-zA-Z0-9_\.]+)\((.*)\)/si', array("tpl", "functions"), $params);

		if (strpos($var, ".")) {
			$varparts = explode(".", $var);
			$i = 0;
			$count = count($varparts);
			$count--;
			foreach ($varparts as $part) {
				if ($count == $i) {
					// last
					$php .= '->doObject("' . $part . '")';
					$isset .= '["' . $part . '"]';
				} else {
					$php .= '["' . $part . '"]';
					$isset .= '["' . $part . '"]';
				}
				$i++;
			}
		} else {
			$php .= '->doObject("' . $var . '")';
			$isset .= '["' . $var . '"]';
		}
		$php .= "->" . $function . "(" . $params . ")";
		$php = '" . ((isset(' . $isset . ') && ' . $isset . ') ? ' . $php . ' : "") . "';

		return $php;
	}

	/**
	 * callback for vars in format
	 * @name vars
	 * @return string
	 */
	public static function vars($matches)
	{
		$name = $matches[1];

		if ($name == "caller")
			return '$caller';

		if ($name == "data")
			return '$data';

		if (preg_match('/^_lang_([a-zA-Z0-9\._-]+)/i', $name, $data)) {
			return '" . lang("' . $data[1] . '", "' . $data[1] . '") . "';
		}

		if (preg_match('/^_cms_([a-zA-Z0-9_-]+)/i', $name, $data)) {
			return '" . Core::getCMSVar(' . var_export($data[1], true) . ') . "';
		}

		return '" . $data->getTemplateVar(' . var_export($name, true) . ') . "';
	}
}
