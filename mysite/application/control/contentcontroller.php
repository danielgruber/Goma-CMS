<?php
/**
  * starts parsing urls for normal content-pages
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 08.07.2012
  * $Version 2.0.1
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
		
		/**
		 * default templte of a page
		 *
		 *@name template
		*/
		public $template = "pages/page.html";
		
		/**
		 * default-url-handlers
		 *
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array(
			'$Action//$id/$otherid' => '$Action'
		);
		
		/**
		 * register meta-tags
		 *
		 *@name pagetitle
		 *@access public
		*/
		public function pagetitle() {
			// mark this id as active in mainbar
			self::$activeids[] = $this->modelInst()->id;
			
			if(!empty($this->modelInst()->meta_keywords)) {
				Core::setHeader("keywords", $this->modelInst()->meta_keywords);
			}
			
			if(!empty($this->modelInst()->meta_description)) {
				Core::setHeader("description", $this->modelInst()->meta_description);
			}
			
			// add breadcrumbs, if we are not on the homepage
			if($this->modelInst()->parentid != 0 || $this->modelInst()->sort != 0) {
				return $this->modelInst()->title;
			}
			return null;
		}
		
		/**
		 * extends hasAction for:
		 * - Permission-checks with Password
		 * - sub-pages
		 *
		 *@name extendHasAction
		 *@access public
		 *@param string - action
		*/
		public function extendHasAction($action, &$hasAction)
		{
			if($this->modelInst()->read_permission && $this->modelInst()->read_permission->type == "password") {
				$passwords = array();
				$this->callExtending("providePasswords", $passwords);
				if($this->modelInst()->read_permission->password != "" || $passwords) {
					$password = $this->modelInst()->read_permission->password;
					if((isset($_SESSION["keychain"]) && in_array($this->modelInst()->read_permission->password, $_SESSION["keychain"])) || (isset($_COOKIE["keychain_" . md5(md5($password))]) && $_COOKIE["keychain_" . md5(md5($password))] == md5($password))) {
						;
					} else {
						foreach($passwords as $pwd) {
							if((isset($_SESSION["keychain"]) && in_array($pwd, $_SESSION["keychain"])) ||  (isset($_COOKIE["keychain_" . md5(md5($pwd))]) && $_COOKIE["keychain_" . md5(md5($pwd))] == md5($pwd))) {
								$found = true;
							}
						}
						
						if(!isset($found)) {
							$validator = new FormValidator(array($this, "validatePassword"));
							$validator->args = array(array_merge(array($this->modelInst()->read_permission->password), $passwords));
							// set password + breadcrumb
							/*if($this->modelInst()->parentid != 0 || $this->modelInst()->sort != 0)
							{
								Core::addBreadCrumb($this->modelInst()->title, $this->modelInst()->url);
							}
							Core::settitle($this->modelInst()->title);*/
							
							if($pwd = $this->prompt(lang("password", "password"), array($validator), null, null, true)) {
								setCookie("keychain_" . md5(md5($pwd)), md5($pwd), time() + 60 * 60 * 24 * 181, "/");
								if(isset($_SESSION["keychain"])) {
									$_SESSION["keychain"][] = $pwd;
								} else {
									$_SESSION["keychain"] = array($pwd);
								}
								//$hasAction = true;
							} else {
								return 0;
							}
						}
					}
				}
			}
			
			// check for sub-page
			if($action != "")
			{					
				$path = $action;
				if(preg_match('/^[a-zA-Z0-9_\-\/]+$/Usi', $path))
				{
					if(DataObject::Count("pages", array("path" => array("LIKE", $path), "parentid" => $this->modelInst()->id)) > 0) {
						$hasAction = true;
						return true;
					}
				}
			}
			
			// register a PAGE_PATH
			define("PAGE_PATH", $this->modelInst()->url);
			
			if($this->modelInst()->parentid == 0 && $this->modelInst()->sort == 0) {
				defined("HOMEPAGE") OR define("HOMEPAGE", true);
				Core::setTitle($this->modelInst()->title);
			} else {
				defined("HOMEPAGE") OR define("HOMEPAGE", false);
			}
		}
		
		/**
		 * for validating the password
		 *
		 *@name validatePassword
		 *@access public
		 *@param object - validator
		 *@param string - password
		*/
		public function validatePassword($obj, $passwords) {
			foreach($passwords as $password) {
				if($obj->form->result["prompt_text"] == $password)
					return true;
			}
			return lang("captcha_wrong", "The Code was wrong.");
			
		}
		
		/**
		 * action-handling
		 *
		 *@name extendHandleAction
		 *@access public
		*/
		public function extendHandleAction($action, &$content) {
			if($content === null && $action != "") {
				$path = $action;
				if(preg_match('/^[a-zA-Z0-9_\-\/]+$/Usi', $path))
				{
					if($data = DataObject::get_one("pages", array("path" => array("LIKE", $path), "parentid" => $this->modelInst()->id))) {
						$content = $data->controller()->handleRequest($this->request);
						return true;
					}
				}
			}
			
			// livecounter
			if(PROFILE) Profiler::mark("livecounter");			
			livecounterController::run();				
			if(PROFILE) Profiler::unmark("livecounter");
			$_SESSION["user_counted"] = TIME;
			
			if($action == "index") {
				ContentTPLExtension::AppendContent($this->modelInst()->appendedContent);
				ContentTPLExtension::PrependContent($this->modelInst()->prependedContent);
			}
		}
		
}

