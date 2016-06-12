<?php defined("IN_GOMA") OR die();

/**
 * Disables FormFields and/or actions.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 2.4.2
 *
 * @method Form getOwner()
 */
class FormDisabler extends FormDecorator {
	
	/**
	 * if actions are not disabled
	*/
	public $actions = true;
	
	/**
	 * decorate methods
	*/ 
	public static $extra_methods = array(
		"reenable", "disableActions", "enableActions"
	);
	
	/**
	 * before render disable all fields if form is disabled
	*/
	public function beforeRender() {
		if($this->actions === false) {
			/** @var FormField $field */
			foreach ($this->getOwner()->fields as $field) {
				if (!is_a($field, "FormAction"))
					$field->disable();
			}
		}
	}

	/**
	 * decorate with new reenable-method
	*/
	public function reenable() {
		$this->getOwner()->enable();
	}

	/**
	 * sets if actions should not be disabled
	*/
	public function enableActions() {
		$this->actions = true;
	}
	/**
	 * sets if actions should be disabled
	*/
	public function disableActions() {
		$this->actions = false;
	}
}

gObject::extend("Form", "FormDisabler");
