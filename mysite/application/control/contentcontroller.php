<?php
/**
  * starts parsing urls for normal content-pages
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 18.06.2011
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


class contentController extends FrontedController
{
		/**
		 * this is for mainbar, so we know, which ids of site has to be marked as active
		 *
		 *@name activeids
		 *@access public
		 *@var array
		*/
		public static $activeids = array();
		// default template
		public $template = "pages/page.html";
		// we just have one real good action: pagecomments
		public $allowed_actions = array("pagecomments");
		/**
		 * default-url-handlers
		*/
		public $url_handlers = array(
			'$Action//$id/$otherid' => '$Action'
		);
		/**
		 * we just return true on every action and check the rest in handleAction
		 * it's better for performance and for user experience, because of less 404-error-pages
		 *
		 *@name hasAction
		 *@access public
		 *@param string - action
		*/
		public function hasAction($action)
		{
				
			if(isset($this->modelInst()->viewer_type) && $this->modelInst()->viewer_type == "password") {
				if($this->modelInst()->readpassword != "") {
					if(isset($_SESSION["keychain"]) && in_array($this->modelInst()->readpassword, $_SESSION["keychain"])) {
						return true;
					} else {
						$validator = new FormValidator(array($this, "validatePassword"));
						$validator->args = array($this->modelInst()->readpassword);
						// set password + breadcrumb
						if($this->modelInst()->parentid != 0 || $this->modelInst()->sort != 0)
						{
							Core::addBreadCrumb($this->modelInst()->title, $this->modelInst()->url);
						}
						Core::settitle($this->modelInst()->title);
						
						
						if($pwd = $this->prompt(lang("captcha", "Please type in Code!"), array($validator))) {
							if(isset($_SESSION["keychain"])) {
								$_SESSION["keychain"][] = $pwd;
							} else {
								$_SESSION["keychain"] = array($pwd);
							}
							return true;
						} else {
							return false;
						}
					}
				}
			}
			return true;
		}
		
		/**
		 * for validating the password
		 *
		 *@name validatePassword
		 *@access public
		 *@param object - validator
		 *@param string - password
		*/
		public function validatePassword($obj, $password) {
			if($obj->form->result["prompt_text"] == $password)
				return true;
			else
				return lang("captcha_wrong", "The Code was wrong.");
		}
		
		/**
		 * handle-Action checks whether a action want to respond or the default action is the page
		 *
		 *@name handleAction
		 *@access public
		 *@param string - action
		*/
		public function handleAction($action)
		{
				// mark this id as active in mainbar
				self::$activeids[] = $this->modelInst()->id;
				
				// add breadcrumbs, if we are not on the homepage
				if($this->modelInst()->parentid != 0 || $this->modelInst()->sort != 0)
				{
						Core::addBreadCrumb($this->modelInst()->title, $this->modelInst()->url);
				} else { // this is the homepage
					defined("HOMEPAGE") OR define("HOMEPAGE", true);
				}
				defined("HOMEPAGE") OR define("HOMEPAGE", false);
				
				// first we check "normal" actions
				if(parent::hasAction($action))
				{
						Core::setTitle($this->modelInst()->title);
						define("PAGE_PATH", $this->modelInst()->url);
						if(settingsController::get("livecounter") == 1 || !isset($_SESSION["user_counted"]) || member::login()) {

							// livecounter
							Profiler::mark("livecounter");			
							livecounterController::run();				
							Profiler::unmark("livecounter");
							$_SESSION["user_counted"] = TIME;
						}
						
						$this->callExtending("beforeHandlePage");
						
						return parent::handleAction($action);
				}
				
				
				// check if this site has a subsite, which is called
				if($action != "")
				{					
						$path = $action;
						if(preg_match('/^[a-zA-Z0-9_\-\/]+$/Usi', $path))
						{
								$data =  DataObject::get_one("pages", array("path" => array("LIKE", $path), "parentid" => $this->modelInst()->id));
								if($data)
								{
										return $data->controller()->handleRequest($this->request);
								}
						}
				}
				
				// run the livecounter (statistics), just if it is activated or the visitor wasn't tracked already
				if(settingsController::get("livecounter") == 1 || !isset($_SESSION["user_counted"])  || member::login()) {
					// livecounter
					Profiler::mark("livecounter");			
					livecounterController::run();				
					Profiler::unmark("livecounter");
					$_SESSION["user_counted"] = TIME; 
				}
				
				// current path
				define("PAGE_PATH", $this->modelInst()->url);
				// add the title of this page
				core::setTitle($this->modelInst()->title);
				
				if(!empty($this->modelInst()->meta_keywords)) {
					Core::setHeader("keywords", $this->modelInst()->meta_keywords);
				}
				
				if(!empty($this->modelInst()->meta_description)) {
					Core::setHeader("description", $this->modelInst()->meta_description);
				}
				
				
				
				$this->callExtending("beforeHandlePage");
				
			
				
				// and show the page
				return $this->index();
		}
		/**
		 * pagecomments
		 *@name pagecomments
		 *@access public
		*/
		public function pagecomments()
		{
				if(is_object($this->modelInst())) {
					if(is_object($this->modelInst()->comments()->controller())) {
						SiteController::addBreadCrumb(lang("co_comments", "comments"), BASE_SCRIPT . $this->modelInst()->path . "/pagecomments" . URLEND);
						return $this->modelInst()->comments()->controller()->handleRequest($this->request);
					}
				}
				return null;
		}
		
}

