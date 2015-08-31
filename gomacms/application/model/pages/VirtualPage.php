<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.04.2013
  * $Version 1.0.3
*/ 

defined("IN_GOMA") OR die("");

class VirtualPage extends Page {
	/**
	 * title
	*/
	static $cname = '{$_lang_virtual_page}';
		
	/**
	 * icon
	*/
	static $icon = "images/icons/goma/16x16/clone.png";
	
	/**
	 * relations
	*/
	static $has_one = array(
		"regardingPage" => "pages"
	);
	
	/** 
	 * generates the form
	*/
	public function getForm(&$form) {
		parent::getForm($form);
		
		$form->add($dropdown = new HasOneDropDown("regardingPage", lang("virtual_page")), 0, "content");
		
		$form->addValidator(new RequiredFields(array("regardingPage")), "validateRegarding");
		$form->addValidator(new FormValidator(array($this, "validate")), "validateSelf");
		$dropdown->info_field = "url";
	}
	
	/**
	 * validate self
	*/
	public function validate($obj) {
		$data = $obj->getForm()->result;
		if(isset($data["recordid"]) && $data["regardingPageid"] == $data["recordid"]) {
			return lang("error_page_self");
		}
		
		return true;
	}
}

class VirtualPageController extends PageController {
	/**
	 * handles the action
	 *
	 * @name index
	 * @return bool|string
	 */
	public function index() {
		$model = $this->modelInst()->regardingPage();
		$model->title = $this->modelInst()->title;
		if(is_object($model->controller())) {
			return $model->controller()->index();
		} else {
			throw new LogicException("VirtualPage must have an regarding Page, but regarding page seems missing or incompabible.");
		}
	}
}