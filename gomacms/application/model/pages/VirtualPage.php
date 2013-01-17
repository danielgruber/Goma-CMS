<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 09.01.2013
  * $Version 1.0.2
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
		
		$form->add($dropdown = new HasOneDropDown("regardingPage", lang("virtual_page")));
		
		$form->addValidator(new RequiredFields(array("regardingPage")), "validateRegarding");
		$form->addValidator(new FormValidator(array($this, "validate")), "validateSelf");
		$dropdown->info_field = "url";
	}
	
	/**
	 * validate self
	*/
	public function validate($obj) {
		$data = $obj->form->result;
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
	 *@name index
	*/
	public function index() {
		$model = $this->modelInst()->regardingPage();
		$model->title = $this->modelInst()->title;
		if(is_object($model->controller())) {
			return $model->controller()->index();
		} else {
			throwError(6, "Unknowen Error", "VirtualPage must have an regarding Page, but regarding page seems missing or imcompabible.");
		}
	}
}