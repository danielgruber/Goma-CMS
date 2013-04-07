<?php
/**
  * starts parsing urls for normal content-pages
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 07.04.2013
  * $Version 2.0.8
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
			array_unshift(self::$activeids, $this->modelInst()->id);
			

			if($this->modelInst()->meta_keywords) {
				Core::setHeader("keywords", $this->modelInst()->meta_keywords);
			}
			
			if($this->modelInst()->meta_description) {
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
					if($this->KeyChainCheck($password)) {
						;
					} else {
						foreach($passwords as $pwd) {
							if($this->KeyChainCheck($pwd)) {
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
								$this->keyChainAdd($pwd);
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
			define("PAGE_ORG_PATH", $this->modelInst()->orgurl);
			
			if($this->modelInst()->parentid == 0 && $this->modelInst()->sort == 0) {
				defined("HOMEPAGE") OR define("HOMEPAGE", true);
				Core::setTitle($this->modelInst()->windowtitle);
			} else {
				defined("HOMEPAGE") OR define("HOMEPAGE", false);
				Core::setTitle($this->modelInst()->windowtitle);
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

			if($content !== null) {
				if($this->modelInst()->meta_keywords == "") {
					
					$index = array();
					foreach(HTMLParser::list_words($content) as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)]++;
						} else {
							$index[strtolower($word)] = 1;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "h1") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 7;
						} else {
							$index[strtolower($word)] = 7;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "h2") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 6;
						} else {
							$index[strtolower($word)] = 6;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "h3") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 5;
						} else {
							$index[strtolower($word)] = 5;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "h4") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 4;
						} else {
							$index[strtolower($word)] = 4;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "h5") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 3;
						} else {
							$index[strtolower($word)] = 3;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "h6") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 2;
						} else {
							$index[strtolower($word)] = 2;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "b") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 1;
						} else {
							$index[strtolower($word)] = 1;
						}
					}
					
					foreach(HTMLParser::list_words_in_tag($content, "strong") as $word) {
						if(isset($index[strtolower($word)])) {
							$index[strtolower($word)] += 1;
						} else {
							$index[strtolower($word)] = 1;
						}
					}
					
					arsort($index);
					$str = "";
					$i = 0;
					foreach($index as $word => $prio) {
						if($i == 0) {
							$i++;
						} else if($i == 5) {
							break;
						} else {
							$i++;
							$str .= ", ";
						}
						$str .= $word;
					}
					Core::setHeader("keywords", $str);
				}
			}
		}
		
		/**
		 * output-hook
		 *
		 *@name outputHook
		*/
		public static function outputHook($content) {
			if(is_a(Core::$requestController, "contentController")) {
				$uploadObjects = array();
				$uploadHash = "";
				
				// a-tags
				preg_match_all('/<a([^>]+)href="([^">]+)"([^>]*)>/Usi', $content, $links);
				foreach($links[2] as $key => $href)
				{
					if(strpos($href, "Uploads/") !== false && preg_match('/Uploads\/([^\/]+)\/([a-zA-Z0-9]+)\/([^\/]+)/', $href, $match)) {
						if($data = DataObject::Get_One("Uploads", array("path" => $match[1] . "/" . $match[2] . "/" . $match[3]))) {
							if(file_exists($data->path) && filemtime(ROOT . "Uploads/" . $match[1] . "/" . $match[2] . "/" . $match[3]) < NOW - Uploads::$cache_life_time && file_exists($data->realfile)) {
								@unlink($data->path);
							}
							
							$uploadObjects[] = $data;
							$uploadHash .= $data->realfile;
						}
					}
				}
				
				// img-tags
				preg_match_all('/<img([^>]+)src="([^">]+)"([^>]*)>/Usi', $content, $links);
				foreach($links[2] as $key => $href)
				{
					if(strpos($href, "Uploads/") !== false && preg_match('/Uploads\/([^\/]+)\/([a-zA-Z0-9]+)\/([^\/]+)/', $href, $match)) {
						if($data = DataObject::Get_One("Uploads", array("path" => $match[1] . "/" . $match[2] . "/" . $match[3]))) {
							$uploadObjects[] = $data;
							$uploadHash .= $data->realfile;
						}
					}
				}
				
				if(count($uploadObjects) > 0) {

					$hash = md5($uploadHash);
					$cacher = new Cacher("track_" . Core::$requestController->modelInst()->versionid . "_" . $hash);
					if($cacher->checkValid())
						return true;
					else {
						Core::$requestController->modelInst()->UploadTracking()->setData(array());
						foreach($uploadObjects as $upload)
							Core::$requestController->modelInst()->UploadTracking()->push($upload);
						
						Core::$requestController->modelInst()->UploadTracking()->write(false, true);
						$cacher->write(1, 14 * 86400);
					}
				}
				
			}
		}
}

class UploadsPageLinkExtension extends DataObjectExtension {
	/**
	 * many-many
	*/
	static $belongs_many_many = array(
		"pagelinks"	=> "pages"
	);
}

class UploadsPageBacktraceController extends ControllerExtension {
	/**
	 * on before handle an action we redirect if needed
	 *
	 *@name onBeforeHandleAction
	*/
	public function onBeforeHandleAction($action, &$content, &$handleWithMethod) {
		$data = $this->getOwner()->modelInst()->linkingPages();
		$data->setVersion(false);
		if($data->Count() > 0) {
			foreach($data as $page) {
				if($page->isPublished() || $page->can("Write", $page) || $page->can("Publish", $page)) {
					return true;
				}
			}
			
			$handleWithMethod = false;
			$content = false;
		}
	}
}

Object::extend("UploadsController", "UploadsPageBacktraceController");
Core::addToHook("onBeforeServe", array("contentController", "outputHook"));