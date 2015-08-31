<?php defined('IN_GOMA') OR die();

/**
 * Customisable field to edit data in a table.
 *
 * Inspiration by Silverstripe 3.0 GridField
 * http://silverstripe.org
 *
 * @package     Goma\Form\TableField
 * @property 	state set of objects
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
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
     * @param DatatSet $data
     * @return $this
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
     * @name buildColumnDispatch
     * @access public
     * @return $this
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
     * @name getColumns
     * @access public
     * @return array
     */
	public function getColumns() {
		$columns = array();
		foreach($this->getComponents() as $comp) {
			if($comp instanceof TableField_ColumnProvider) {
				$comp->augmentColumns($this, $columns);
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
     *
     * @param   object $record
     * @param   string $fieldName
     * @return  string
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
			return $record->getTemplateVar($fieldName);
		}
	}

    /**
     * Cast a arbitrary value with the help of a castingDefintion
     *
     * @param $value
     * @param $castingDefinition
     *
     * @return mixed
     */
	public function getCastedValue($value, $castingDefinition) {
		return DBField::convertByCasting($castingDefinition, "blob", $value);
	}

    /**
     * gets the column-content
     *
     * @name    getColumnContent
     * @access  public
     * @param   object
     * @param   string
     * @return  string
     */
	public function getColumnContent($record, $column) {
        $this->columExistsOrThrow($column);

        $content = "";
        foreach($this->columnDispatch[$column] as $handler) {
            $content .= $handler->getColumnContent($this, $record, $column);
        }
        return $content;
	}

    /**
     * gets the column-meta-data
     *
     * @name    getColumnAttributes
     * @access  public
     * @param   Object record
     * @param   string column
     * @return  array
     */
	public function getColumnAttributes($record, $column) {
        return $this->generateArrayData("getColumnAttributes", $column, $record);
	}

    /**
     * builds array data with given method.
     *
     * @param   string method
     * @param   string column
     * @param   object record
     * @return  array
     */
    public function generateArrayData($method, $column, $record) {
        $this->columExistsOrThrow($column);

        $arr = array();

        foreach($this->columnDispatch[$column] as $handler) {
            $generated = call_user_func_array(array($handler, $method), array($this, $column, $record));

            if(is_array($generated)) {
                $arr = array_merge($arr, $generated);
            } else {
                throw new LogicException( 'Handler should give Array at ' . get_class($handler) . '::getColumnAttributes');
            }
        }
        return $arr;
    }

    /**
     * checks if a column exists and throws an exception when not.
     * it also builds columns dispath.
     */
    public function columExistsOrThrow($column) {
        if(!$this->columnDispatch) {
            $this->buildColumnDispatch();
        }

        if(!empty($this->columnDispatch[$column])) {
            return true;
        } else {
            throw new LogicException('Bad Column ' . $column);
        }
    }

    /**
     * gets the column-meta-data
     *
     * @name    getColumnMetaData
     * @access  public
     * @param   string
     * @return  array
     */
	public function getColumnMetaData($column) {
        return $this->generateArrayData("getColumnMetaData", $column, null);
	}

    /**
     * gets the column-count
     *
     * @return int
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
     * @return HTMLNode|string
     */
	public function field() {
		
		Resources::add("tablefield.css");
		Resources::add("system/form/tableField/tableField.js");
		
		$container = $this->container;
		
		$columns = $this->getColumns();
		
		// first init all
		foreach($this->getComponents() as $item) {
 			if(Object::method_exists($item, "Init")) {
				$item->Init($this);
			}
		}
		
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
                /** @var array $item */
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
			if(preg_match_all('/DefineFragment\(([a-z0-9\-_]+)\)/i', $v, $matches)) {
				foreach($matches[1] as $match) {
					$fragmentName = strtolower($match);
					$fragmentsDefined[$fragmentName] = true;
					$fragment = isset($content[$fragmentName]) ? $content[$fragmentName] : "";

					// If the fragment still has a fragment definition in it, when we should defer this item until later.
					if(preg_match('/DefineFragment\(([a-z0-9\-_]+)\)/i', $fragment, $matches)) {
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
						$content[$k] = preg_replace('/DefineFragment\(' . $fragmentName . '\)/i', $fragment, $content[$k]);
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
				$this->createTag('td', array('colspan' => count($columns)), lang("no_result", "No items found!"))
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
		
		$this->form()->disableRestore();
		
		return $this->createTag('fieldset', array("class" => "tablefield-fieldset"), 
				$content['before'] .
				$this->createTag('table', $tableAttrs, $head."\n".$foot."\n".$body) .
				$content['after']
			);
	}

    /**
     * Custom request handler that will check component handlers before proceeding to the default implementation.
     *
     * @todo There is too much code copied from RequestHandler here.
     * @param   Request $request
     * @param   bool $subController
     * @return  false|mixed|null
     */
	public function handleRequest($request, $subController = false) {

		$this->request = $request;
		
		if($this->getParam("tableState")) {
			if(is_string($this->getParam("tableState"))) {
				$this->state = json_decode($this->getParam("tableState"));
			} else {
				$this->state = $this->getParam("tableState");
			}
		}
		
		foreach($this->getComponents() as $component) {
            $action = $this->getActionFromComponent($component, $request);
            if($action !== false) {
                $content = $this->executeAction($component, $action, $request);
                if($content !== false) {
                    return $content;
                }
            }
		}
		
		return parent::handleRequest($request, $subController);
	}

    /**
     * gets url handlers from component and trys to get an action.
     * returns false when no action was found.
     *
     * @param   Object $component
     * @param   Request $request
     * @return  string|false
     */
    public function getActionFromComponent($component, $request) {
        if($component instanceof TableField_URLHandler) {

            $urlHandlers = $component->getURLHandlers($this);

            if ($urlHandlers) foreach ($urlHandlers as $rule => $action) {
                if ($params = $request->match($rule, true)) {
                    // Actions can reference URL parameters, eg, '$Action/$ID/$OtherID' => '$Action',
                    if ($action[0] == '$') $action = $params[substr(strtolower($action), 1)];
                    return $action;
                }
            }
        }

        return false;
    }

    /**
     * checks if access for method is available and gives return of method back if calable.
     *
     * @param   object component
     * @param   string $action
     * @return  string
     */
    public function executeAction($component, $action, $request) {
        if(!method_exists($component, 'checkAccessAction') || $component->checkAccessAction($action)) {
            if(!$action) {
               return $component->index($this, $request);
            } else if(is_string($action)) {
                return $component->$action($this, $request);
            } else {
                throw new LogicException( 'Non-string method name: ' . var_export($action, true));
            }
        }

        return false;
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
	public function _handleAction($actionName, $args, $data) {
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

    /**
     * used for deserialization of objects in goma.
     */
    public function __wakeup() {
		parent::__wakeup();
		
		if(is_object($this->data)) {
			$this->data->__wakeup();
		}
	}
}