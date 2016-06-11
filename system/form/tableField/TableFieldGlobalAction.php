<?php defined("IN_GOMA") OR die();

/**
 * Global Action, which will be visible in the footer of the tablefield.
 *
 * @package     Goma\Form\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0.1
 */
class TableFieldGlobalAction implements TableField_HTMLProvider, TableField_ActionProvider {

	protected $name;
	protected $title;
	protected $callback;

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

		if(is_callable($callback)) {
			$this->callback = $callback;
		} else {
			throw new InvalidArgumentException('$callback must be a valid Argument for TableFieldGlobalAction::__construct.');
		}
	}


	/**
	 * provides HTML-fragments
	 *
	 * @return array
	 */
	public function provideFragments($tableField) {

		$action = new TableField_FormAction($tableField, $this->name, $this->title, "globalaction_".strtolower($this->name)."_callback");
		$view = new ViewAccessableData();
		if($tableField->getConfig()->getComponentByType('TableFieldPaginator')) {
			return array(
				"pagination-footer-right" => $view->customise(array("field" => $action->exportFieldInfo()->ToRestArray(true)))->renderWith("form/tableField/globalAction.html")
			);
		} else {
			return array("footer" => $view->customise(array("field" => $action->exportFieldInfo()->ToRestArray(true)))->renderWith("form/tableField/globalActionWithFooter.html"));
		}
	}

	/**
	 * provide some actions of this tablefield
	 *
	 * @return array
	 */
	public function getActions($tableField) {
		return array("globalaction_" . strtolower($this->name) . "_callback");
	}

	/**
	 * handles the actions
	 */
	public function handleAction($tableField, $actionName, $arguments, $data) {
		if($actionName == "globalaction_" . strtolower($this->name) . "_callback") {
			return call_user_func_array($this->callback, array($tableField, $actionName, $data));
		}
		return false;
	}
}