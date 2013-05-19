<?php
/**
 * Global Action, which will be visible in the footer of the tablefield.
 *
 * @package     Goma\Form-Framework\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */

defined("IN_GOMA") OR die();

class TableFieldGlobalAction implements TableField_HTMLProvider, TableField_ActionProvider {
	/**
	 * Constructor.
	 *
	 * @param String $name internal name
	 * @param String $title Title of the Button
	 * @param callback $callback Method to call, when button was pushed
	*/
	public function __construct($name, $title, $callback) {
		$this->name = $name;
		$this->title = $title;
		
		if(is_callable($callback))
			$this->callback = $callback;
		else
			throwError(6, "Invalid Argument Error", '$callback must be a valid Argument for TableFieldGlobalAction::__construct.');
	}
	
	
	/**
	 * provides HTML-fragments
	 *
	 *@name provideFragments
	*/
	public function provideFragments($tableField) {
		
		$action = new TableField_FormAction($tableField, $this->name, $this->title, "globalaction_".$this->name."_callback");
		$view = new ViewAccessableData();
		if($tableField->getConfig()->getComponentByType('TableFieldPaginator')) {
			return array(
				"pagination-footer-right" => $view->customise(array("field" => $action->field()))->renderWith("form/tableField/globalAction.html")
			);
		} else {
			return array("footer" => $view->customise(array("field" => $action->field()))->renderWith("form/tableField/globalActionWithFooter.html"));
		}
	}
	
	/**
	 * provide some actions of this tablefield
	 *
	 *@name getActions
	 *@access public
	*/
	public function getActions($tableField) {
		return array("globalaction_" . $this->name . "_callback");
	}
	
	/**
	 * handles the actions
	*/
	public function handleAction($tableField, $actionName, $arguments, $data) {
		if($actionName == "globalaction_" . $this->name . "_callback") {
			return call_user_func_array($callback, array($tableField, $actionName, $data));
		}
		return false;
	}
}