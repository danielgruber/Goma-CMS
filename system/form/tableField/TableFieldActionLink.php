<?php
/**
 * Table-Field plugin to create a link in the action-column with custom HTML between the a-tags.
 *
 * @package     Goma\Form-Framework\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0.2
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableFieldActionLink implements TableField_ColumnProvider {

	/**
	 * description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $inner;

	/**
	 * @var bool|mixed
	 */
	protected $requirePerm;

	/**
	 * @var null|string
	 */
	protected $title;

	/**
	 * @var array
	 */
	protected $classes;

	/**
	 * Constructor.
	 *
	 * @param   string $destination link-URL with params replaced by data of record
	 * @param   string $inner HTML between a-tags
	 * @param    string $title
	 * @param   mixed $requirePerm how to check if permissions is required (callback, string, boolean)
	 * @param array $classes
	 */
	public function __construct($destination, $inner, $title = null, $requirePerm = false, $classes = array()) {
		$this->destination = $destination;
		$this->inner = $inner;
		$this->requirePerm = $requirePerm;
		$this->title = $title;
		$this->classes = $classes;
	}
	
	
	/**
	 * Add a column 'Actions'.
	 * 
	 * @param TableField $tableField
	 * @param array $columns 
	 */
	public function augmentColumns($tableField, &$columns) {
		if(!in_array('Actions', $columns))
			$columns[] = 'Actions';
	}
	
	/**
	 * Return any special attributes that will be used for the column.
	 *
	 * @param TableField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 *
	 * @return array
	 */
	public function getColumnAttributes($tableField, $columnName, $record) {
		return array('class' => 'col-buttons');
	}
	
	/**
	 * Add the title.
	 * 
	 * @param TableField $tableField
	 * @param string $columnName
	 *
	 * @return array
	 */
	public function getColumnMetadata($tableField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}
	
	/**
	 * Which columns are handled by this component.
	 * 
	 * @param TableField $tableField
	 *
	 * @return array
	 */
	public function getColumnsHandled($tableField) {
		return array('Actions');
	}
	
	/**
	 * generates the content of the column "Actions".
	 *
	 * @param TableField $tableField
	 * @param DataObject $record
	 * @param string $columnName
	 *
	 * @return string - the HTML for the column 
	 */
	public function getColumnContent($tableField, $record, $columnName) {
		if($this->requirePerm) {
			if(is_callable($this->requirePerm)) {
				if(!call_user_func_array($this->requirePerm, array($tableField, $record)))
					return;
			} else if(!$record->can($this->requirePerm)){
				return;
			}
		}

		// required for parsing variables
		$data = $record;
		
		// format innerhtml
		$format = str_replace('"', '\\"', $this->inner);
		$format = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("TableFieldDataColumns", "convert_vars"), $format);
		$format = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("TableFieldDataColumns", "vars"), $format);
		
		eval('$value = "' . $format . '";');

		// format destination
		$formatDestination = str_replace('"', '\\"', $this->destination);
		$formatDestination = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("TableFieldDataColumns", "convert_vars"), $formatDestination);
		$formatDestination = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("TableFieldDataColumns", "vars"), $formatDestination);
		eval('$destination = "' . $formatDestination . '";');
		
		// format title
		$formatTitle = str_replace('"', '\\"', $this->title);
		$formatTitle = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("TableFieldDataColumns", "convert_vars"), $formatTitle);
		$formatTitle = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("TableFieldDataColumns", "vars"), $formatTitle);
		eval('$title = "' . $formatTitle . '";');

		// format title
		$formatInner = str_replace('"', '\\"', $this->inner);
		$formatInner = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("TableFieldDataColumns", "convert_vars"), $formatInner);
		$formatInner = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("TableFieldDataColumns", "vars"), $formatInner);
		eval('$inner = "' . $formatInner . '";');
		
		
		$data = new ViewAccessableData();
		/** @var string $destination */
		$data->setField("destination", $destination);
		/** @var string $inner */
		$data->setField("inner", $inner);
		/** @var $title $title */
		$data->setField("title", $title);
		$data->setField("classes", implode(" " , (array) $this->classes));
		
		return $data->renderWith("form/tableField/actionLink.html");
	}
}
