<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 21.12.2011
  * $Version - 1.1
 */
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FormDisabler extends FormDecorator {
	/**
	 * if form is disabled
	 *
	 *@name disabled
	 *@access public
	*/
	public $disabled = false;
	
	/**
	 * if actions are not disabled
	 *
	 *@name actions
	 *@access public
	*/
	public $actions = false;
	
	/**
	 * decorate methods
	*/ 
	public static $extra_methods = array(
		"disable", "reenable", "disableActions", "enableActions"
	);
	
	/**
	 * before render disable all fields if form is disabled
	 *
	 *@name beforeRender
	*/
	public function beforeRender() {
		if($this->disabled) {
			foreach($this->getOwner()->fields as $field) {
				if($this->actions !== true || !is_a($field, "FormAction"))
					$field->disable();
			}
		}
	}
	
	/**
	 * decorate with new disable-method
	 *
	 *@name disable
	*/
	public function disable() {		
		$this->disabled = true;
	}
	/**
	 * decorate with new reenable-method
	 *
	 *@name reenable
	*/
	public function reenable() {
		$this->disabled = false;
	}
	/**
	 * sets the result
	 *
	 *@name getResult
	*/
	public function getResult(&$result) {
		if($this->disabled)
			$result = array();
	}
	/**
	 * sets if actions should not be disabled
	 *
	 *@name enableActions
	 *@access public
	*/
	public function enableActions() {
		$this->actions = true;
	}
	/**
	 * sets if actions should be disabled
	 *
	 *@name disableActions
	 *@access public
	*/
	public function disableActions() {
		$this->actions = false;
	}
}

Object::extend("Form", "FormDisabler");