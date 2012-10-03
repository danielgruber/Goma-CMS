<?php
/**
  * inspiration by Silverstripe 3.0 GridField
  * http://silverstripe.org
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 01.10.2012
  * $Version - 1.0
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class tableField extends FormField {
	/**
	 * configuration of this field
	 *
	 *@name config
	 *@access protected
	*/
	protected $config;
	
	/**
	 * dataset
	 *
	 *@name data
	 *@access protected
	*/
	protected $data;
	
	/**
	 * if you want to override dataclass $this->data->dataClass
	 *
	 *@name modelClass
	 *@access protected
	*/
	protected $modelClass;
	
	/**
	 * Internal dispatcher for column handlers.
	 * Keys are column names and values are TableField_ColumnProvider objects
	 * 
	 * @var array
	 */
	protected $columnDispatch = null;
	
	/**
	 * data-manipulators
	 *
	 *@name customDataFields
	 *@access protected
	*/
	protected $customDataFields = array();
	
	/**
	 * constructor
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($name = null, $title = null, $dataset = null, $config = null, $parent = null) {
		if(isset($config) && is_a($config, "tableFieldConfig")) {
			$this->config = $config;
		} else {
			$this->config = TableFieldConfig_Base::Create();
		}
		parent::__construct($name, $title, null, $parent);
		
		if(isset($dataset))
			$this->setData($dataset);
		
		$this->callExtending("TableField_StartUp");
		$this->callExtending("TableField_manipulateConfig");
	}
	
	/**
	 * Get the TableFieldConfig
	 *
	 * @return TableFieldConfig
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * @param TableFieldConfig $config
	 * @return TableField
	 */
	public function setConfig(TableFieldConfig $config) {
		$this->config = $config;
		$this->columnDispatch = null;
		$this->callExtending("TableField_manipulateConfig");
		return $this;
	}
	
	public function getComponents() {
		return $this->config->getComponents();
	}
	
	/**
	 * returns the data of this TableField
	 *
	 *@return DataObjectSet
	*/
	public function getData() {
		return $this->data;
	}
	
	/**
	 * sets the data
	 *
	 *@param DataObjectSet
	*/
	public function setData(DataSet $data) {
		$this->data = $data;
		return $this;
	}
	
	/**
	 * gets the name of the model the tablefield holds
	 *
	 *@name getModel
	 *@access public
	*/
	public function getModel() {
		if(isset($this->modelClass))
			return $this->modelClass;
		
		return $this->data->dataClass;
	}
	
	/**
	 * sets the modelClass
	 *
	 *@name setModel
	 *@access public
	*/
	public function setModel($model = null) {
		$this->modelClass = $model;
	}
	
	/**
	 * builds the column-dispatch
	 *
	 *@name buildColumnDispatch
	 *@access public
	*/
	public function buildColumnDispatch() {
		$this->columnDispatch = array();
		foreach($this->getComponents() as $comp) {
			if($comp instanceof TableField_ColumnProvider) {
				foreach($comp->getColumnsHandled($this) as $field) {
					$this->columnDispatch[$field][] = $comp;
				} 
			}
		}
		return $this;
	}
	
	/**
	 * gets all columns
	 *
	 *@name getColumns
	 *@access public
	*/
	public function getColumns() {
		$columns = array();
		foreach($this->getComponents() as $comp) {
			if($comp instanceof TableField_ColumnProvider) {
				$comp->augumentColumns($this, $columns);
			}
		}
		return $columns;
	}
	
	/**
	 * Add additional calculated data fields to be used on this TableField
	 * @param array $fields a map of fieldname to callback.  The callback will bed passed the record as an argument.
	 */
	public function addDataFields($fields) {
		if($this->customDataFields) $this->customDataFields = array_merge($this->customDataFields, $fields);
		else $this->customDataFields = $fields;		
	}
	
	/**
	 * Get the value of a named field  on the given record.
	 * Use of this method ensures that any special rules around the data for this tablefield are followed.
	 */
	public function getDataFieldValue($record, $fieldName) {
		// Custom callbacks
		if(isset($this->customDataFields[$fieldName])) {
			$callback = $this->customDataFields[$fieldName];
			return $callback($record);
		}
		
		// Default implementation
		if(Object::method_exists($record, $fieldName)) {
			return $record->$fieldName();
		} else {
			return $record->$fieldName;
		}
	}
	
	/**
	 * Cast a arbitrary value with the help of a castingDefintion
	 * 
	 * @param $value 
	 * @param $castingDefinition
	 */
	public function getCastedValue($value, $castingDefinition) {
		return DBField::convertByCasting($castingDefinition, "blob", $value);
	}	
	
	/**
	 * gets the column-content
	 *
	 *@name getColumnContent
	 *@access public
	*/
	public function getColumnContent($record, $column) {
		if(!$this->columnDispatch) {
			$this->buildColumnDispatch();
		}
		
		if(!empty($this->columnDispatch[$column])) {
			$content = "";
			foreach($this->columnDispatch[$column] as $handler) {
				$content .= $handler->getColumnContent($this, $record, $column);
			}
			return $content;
		} else {
			throwErro(6, "Invalid-Exception", "Bad Column " . $column);
		}
	}
	
	/**
	 * gets the column-meta-data
	 *
	 *@name getColumnAttributes
	 *@access public
	*/
	public function getColumnAttributes($record, $column) {
		if(!$this->columnDispatch) {
			$this->buildColumnDispatch();
		}
		
		if(!empty($this->columnDispatch[$column])) {
			$attr = array();
			foreach($this->columnDispatch[$column] as $handler) {
				$_attr = $handler->getColumnAttributes($this, $record, $column);
				
				if(is_array($_attr)) {
					$attr = array_merge($attr, $_attr);
				} else {
					throwErro(6, "Logic-Exception", "Handler should give back Array at " . $handler->class . "::getColumnAttributes");
				}
			}
			return $attr;
		} else {
			throwErro(6, "Invalid-Exception", "Bad Column " . $column);
		}
	}
	
	/**
	 * gets the column-meta-data
	 *
	 *@name getColumnMetaData
	 *@access public
	*/
	public function getColumnMetaData($column) {
		if(!$this->columnDispatch) {
			$this->buildColumnDispatch();
		}
		
		if(!empty($this->columnDispatch[$column])) {
			$metadata = array();
			foreach($this->columnDispatch[$column] as $handler) {
				$_metadata = $handler->getColumnMetaData($this, $column);
				
				if(is_array($_metadata)) {
					$metadata = array_merge($metadata, $_metadata);
				} else {
					throwErro(6, "Logic-Exception", "Handler should give back Array at " . $handler->class . "::getColumnMetaData");
				}
			}
			return $metadata;
		} else {
			throwErro(6, "Invalid-Exception", "Bad Column " . $column);
		}
	}
	
	/**
	 * gets the column-count
	 *
	 *@name getColumnCount
	 *@access public
	*/
	public function getColumnCount() {
		if(!$this->columnDispatch) {
			$this->buildColumnDispatch();
		}
		return count($this->columnDispatch);
	}
	
	/**
	 * renders the field
	 *
	 *@name field
	 *@access public
	*/
	public function field() {
		
		Resources::add("tablefield.css");
		
		$container = $this->container;
		
		$columns = $this->getColumns();
		
		$data = $this->getData();
		foreach($this->getComponents() as $item) {
 			if($item instanceof TableField_DataManipulator) {
				$data = $item->manipulate($this, $data);
			}
		}
		
		// Render headers, footers, etc
		$content = array(
			"before" 	=> "",
			"after" 	=> "",
			"header" 	=> "",
			"footer" 	=> ""
		);

		
		// get fragments
		foreach($this->getComponents() as $item) {			
			if($item instanceof TableField_HTMLProvider) {
				$fragments = $item->provideFragments($this);
				if($fragments) foreach($fragments as $k => $v) {
					$k = strtolower($k);
					if(!isset($content[$k])) $content[$k] = "";
					$content[$k] .= $v . "\n";
				}
			}
		}
		
		// trim a little bit ;)
		$content = array_map("trim", $content);
		
		
		// !Fragment-Rendering
		
		// Replace custom fragments and check which fragments are defined
		// Nested dependencies are handled by deferring the rendering of any content item that 
		// Circular dependencies are detected by disallowing any item to be deferred more than 5 times
		// It's a fairly crude algorithm but it works
		$fragmentsDefined = array(
			"before"	=> true,
			"after"		=> true,
			"header"	=> true,
			"footer"	=> true
		);
		
		reset($content);
		while(list($k,$v) = each($content)) {
			if(preg_match_all('/\$DefineFragment\(([a-z0-9\-_]+)\)/i', $v, $matches)) {
				foreach($matches[1] as $match) {
					$fragmentName = strtolower($match);
					$fragmentDefined[$fragmentName] = true;
					$fragment = isset($content[$fragmentName]) ? $content[$fragmentName] : "";

					// If the fragment still has a fragment definition in it, when we should defer this item until later.
					if(preg_match('/\$DefineFragment\(([a-z0-9\-_]+)\)/i', $fragment, $matches)) {
						// If we've already deferred this fragment, then we have a circular dependency
						if(isset($fragmentDeferred[$k]) && $fragmentDeferred[$k] > 5) {
							throwError(6, "Logical Exception", "Fragment ".$k." is a circular dependency.");
						}
						
						// Otherwise we can push to the end of the content array
						unset($content[$k]);
						$content[$k] = $v;
						if(!isset($fragmentDeferred[$k])) {
							$fragmentDeferred[$k] = 1;
						} else {
							$fragmentDeferred[$k]++;
						}
						break;
					} else {
						$content[$k] = preg_replace('/\$DefineFragment\(' . $fragmentName . '\)/i', $fragment, $content[$k]);
					}
				}
			}
		}
		
		// Check for any undefined fragments, and if so throw an exception
		// While we're at it, trim whitespace off the elements
		foreach($content as $k => $v) {
			if(empty($fragmentsDefined[$k])) throwError(6, "Logical Error", "TableField HTML fragment ".$k." was gaving content, but not defined. Perhaps there is a supporting Tablefield component you need to add?");
		}
		
		$total = $data->Count();
		if($total > 0) {
			$rows = array();
			foreach($data as $id => $record) {
				
				$rowContent = "";
				foreach($this->getColumns() as $column) {
					$colContent = $this->getColumnContent($record, $column);
					// A return value of null means this columns should be skipped altogether.
					if($colContent === null) continue;
					$colAttributes = $this->getColumnAttributes($record, $column);
					$rowContent .= $this->createTag('td', $colAttributes, $colContent);
				}
				
				$classes = array('tablefield-item');
				if ($record->first()) $classes[] = 'first';
				if ($record->last()) $classes[] = 'last';
				$classes[] = ($record->white()) ? 'even' : 'odd';
				
				$row = $this->createTag(
					'tr',
					array(
						"class" => implode(' ', $classes),
						'data-id' => $record->ID,
						// !TODO: Allow to customise this as customising columns
						'data-class' => $record->ClassName,
					),
					$rowContent
				);
				$rows[] = $row;
			}
			
			$content['body'] = implode("\n", $rows);
		}
		
		// Display a message when the table field is empty
		if(!(isset($content['body']) && $content['body'])) {    
			$content['body'] = $this->createTag(
				'tr',
				array("class" => 'tablefield-item tablefield-no-items'),
				$this->createTag('td', array('colspan' => count($columns)), _t('TableField.NoItemsFound', 'No items found'))
			);
		}
		
		// create the relevant parts of the table
		$head = $content['header'] ? $this->createTag('thead', array(), $content['header']) : '';
		$body = $content['body'] ? $this->createTag('tbody', array('class' => 'tablefield-items'), $content['body']) : '';
		$foot = $content['footer'] ? $this->createTag('tfoot', array(), $content['footer']) : '';
		
		$tableAttrs = array(
			'id' => isset($this->id) ? $this->id : null,
			'class' => 'tablefield-table',
			'cellpadding' => '0',
			'cellspacing' => '0'
		);
		
		return $this->createTag('fieldset', array(), 
				$content['before'] .
				$this->createTag('table', $tableAttrs, $head."\n".$foot."\n".$body) .
				$content['after']
			);
	}
	
	/**
	 * Custom request handler that will check component handlers before proceeding to the default implementation.
	 * 
	 * @todo There is too much code copied from RequestHandler here.
	 */
	function handleRequest($request) {

		$this->request = $request;
		
		if($this->getParam("tableState")) {
			if(is_string($this->getParam("tableState"))) {
				$this->state = json_decode($this->getParam("tableState"));
			} else {
				$this->state = $this->getParam("tableState");
			}
		}
		
		foreach($this->getComponents() as $component) {
			if(!($component instanceof TableField_URLHandler)) {
				continue;
			}
			
			$urlHandlers = $component->getURLHandlers($this);
			
			if($urlHandlers) foreach($urlHandlers as $rule => $action) {
				if($params = $request->match($rule, true)) {
					// Actions can reference URL parameters, eg, '$Action/$ID/$OtherID' => '$Action',
					if($action[0] == '$') $action = $params[substr(strtolower($action),1)];
					if(!method_exists($component, 'checkAccessAction') || $component->checkAccessAction($action)) {
						if(!$action) {
							$action = "index";
						} else if(!is_string($action)) {
							throwError(6, "Logical Exception", "Non-string method name: " . var_export($action, true));
						}

						return $component->$action($this, $request);
					}
				}
			}
		}
		
		return parent::handleRequest($request);
	}
	
	/**
	 * Pass an action on the first TableField_ActionProvider that matches the $actionName
	 *
	 * @param string $actionName
	 * @param mixed $args
	 * @param arrray $data - send data from a form
	 * @return type
	 * @throws InvalidArgumentException
	 */
	public function handleAction($actionName, $args, $data) {
		$actionName = strtolower($actionName);
		foreach($this->getComponents() as $component) {
			if(!($component instanceof TableField_ActionProvider)) {
				continue;
			}
			
			if(in_array($actionName, array_map('strtolower', (array)$component->getActions($this)))) {
				return $component->handleAction($this, $actionName, $args, $data);
			}
		}
		throwError(6, "Not Found", "Can't handle action '$actionName'");
	}
}

class TableField_FormAction extends FormAction {
	/**
	 *
	 * @param TableField $tableField
	 * @param type $name
	 * @param type $label
	 * @param type $actionName
	 * @param type $args 
	 */
	public function __construct($tableField = null, $name = null, $title = null, $actionName = null, $args = null) {
		if(!is_object($tableField))
			return ;
		$this->tableField = $tableField;
		$this->actionName = $actionName;
		$this->args = $args;
		parent::__construct($this->tableField->name . "_" . $name, $title);
	}
	
	/**
	 * returns false, because a tableField-action never triggers the form to submit
	 * but we hook into
	 *
	 *@name canSubmit
	*/
	public function canSubmit($data) {
		$this->tableField->handleAction($this->actionName, $this->args, $data);
		return false;
	}
}