<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 11.05.201
  * $Version: 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

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
		if(isset($this->owner->autocomplete) && $this->owner->autocomplete) {
			if(is_a($this->owner->autocomplete, "DataSet")) {
				$this->data = $this->owner->autocomplete;
			} else if(is_a($this->owner->Form()->result, "ViewAccessableData")) {
				//echo $this->owner->Form()->result->dataClass;
				$this->data = DataObject::get($this->owner->Form()->result->dataClass);
			}
		}
		
		gloader::load("autocomplete");
		Resources::addJS('$(function(){
			$("#'.$this->owner->ID().'").autocomplete({
				minLength: 1,
				source: "'.$this->owner->externalURL().'/autocomplete_search"
			});
		});');
	}
	
	/**
	 * returns the result
	*/
	public function autocomplete_search() {
		HTTPResponse::setHeader("content-type", "text/x-json");
		
		if(isset($_GET["term"])) {
			$arr = array();
			$filtered = $this->data->filter(array($this->owner->name => array("LIKE", $_GET["term"] . "%")))->groupBy($this->owner->name);
			foreach($filtered as $record) {
				$arr[] = array("id" => $record->id, "label" => convert::raw2text($record[$this->owner->name]), "value" => $record[$this->owner->name]);
			}
			
			return json_encode($arr);
		}
		
		return "";
	}
}

Object::extend("FormField", "AutoCompleterField");