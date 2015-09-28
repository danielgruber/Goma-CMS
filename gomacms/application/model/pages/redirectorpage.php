<?php
/**
 *@package goma cms
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 05.01.2015
 * $Version 1.1
 */

defined("IN_GOMA") OR die("");

class redirector extends Page
{
	/**
	 * title of the page
	 */
	static $cname = '{$_lang_redirect}';

	/**
	 * icon
	 */
	static $icon = "images/icons/fatcow16/page_link.png";

	/**
	 * generates the form
	 *
	 *@name getForm
	 *@access public
	 */
	public function getForm(&$form)
	{
		parent::getForm($form);
		$form->add(new textField('data', lang("URL")), null, "content");

		$form->addValidator(new requiredFields(array("data")), "requireURL");
	}

	/**
	 * returns URL
	 */
	public function getUrl()
	{
		return $this->data["data"];
	}
}

class redirectorController extends PageController
{
	public function index() {

		$url = $this->modelInst()->data;

		if(substr($url, -1) == "/") {
			$url = substr($url, 0, -1);
		}

		if($this->getParam("action")) {
			$url .= "/" . $this->getParam("action");
		}

		if($this->request->remaining()) {
			$url .= "/" . $this->request->remaining() . URLEND;
		}

		HTTPResponse::redirect($url, true);
	}
}

