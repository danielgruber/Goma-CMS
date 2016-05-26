<?php
defined("IN_GOMA") OR die();

/**
 * An auto complete field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0
 */

class AutoCompleterField extends ControllerExtension {
	/**
	 * extend allowed actions
	 */
	public $allowed_actions = array("autocomplete_search");

	/**
	 * extend methods
	 */
	static $extra_methods = array("autocomplete_search");

	/**
	 * data
	 */
	public $data;

	/**
	 * adds the info to the field
	 */
	public function beforeField() {
		if(isset($this->owner->autocomplete) && ($this->owner->autocomplete === true || is_object($this->owner->autocomplete))) {
			if(is_a($this->owner->autocomplete, "DataSet")) {
				$this->data = $this->owner->autocomplete;
			} else if(is_a($this->owner->Form()->result, "ViewAccessableData")) {
				$this->data = DataObject::get($this->owner->Form()->result->dataClass);
			}

			gloader::load("autocomplete");
			Resources::addJS('$(function(){
				$("#' . $this->owner->ID() . '").autocomplete({
					minLength: 1,
					source: "' . $this->owner->externalURL() . '/autocomplete_search",
					delay: 50
				});
			});');
		}
	}

	/**
	 * returns the result
	 */
	public function autocomplete_search() {
		HTTPResponse::setHeader("content-type", "text/x-json");

		if(isset($_GET["term"])) {
			$arr = array();
			$filtered = $this->data->filter(array($this->owner->name => array(
				"LIKE",
				trim($_GET["term"]) . "%"
			)))->groupBy($this->owner->name);
			/** @var DataObjectSet $record */
			foreach($filtered as $record) {
				$arr[] = array(
					"id" => $record->first()->id,
					"label" => $record->first()->{$this->owner->name},
					"value" => $record->first()->{$this->owner->name}
				);
			}

			return json_encode($arr);
		}

		return "";
	}

}

gObject::extend("FormField", "AutoCompleterField");
