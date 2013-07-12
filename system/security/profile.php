<?php
/**y
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 02.04.2012
  * $Version 1.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

i18n::AddLang("/members");

class ProfileController extends FrontedController {
	
	/**
	 * allowed actions
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array("edit", "login", "logout", "switchlang");
	/**
	 * profile actions
	 *
	 *@name profile_actions
	 *@access public
	*/
	public $profile_actions;
	/**
	 * tabs
	 *
	 *@name tabs
	 *@access public
	*/
	public $tabs;
	/**
	 * shows the edit-screen
	 *
	 *@name edit
	 *@access public
	*/
	public function edit() {
		if(!member::login())
		{
				HTTPResponse::redirect(BASE_URI . "profile/login/?redirect=".urlencode(ROOT_PATH . BASE_SCRIPT . "profile/edit/")."");
				exit;
		}
		
		Core::addBreadCrumb(lang("profile"), "profile/");
		Core::addBreadCrumb(lang("edit_profile"), "profile/edit/");
		Core::setTitle(lang("edit_profile"));
		
		$userdata = DataObject::get("user", array("id" => member::$id))->first();
		return '<h1>'.lang("edit_profile").'</h1>' . $userdata->controller()->edit();
	}
	/**
	 * default screen
	 *
	 *@name index
	 *@access public
	*/
	public function index($id = null) {
		$id = ($id == null) ? $this->getParam("id") : $id;
		if(!$id && !member::login()) {
			HTTPResponse::redirect(BASE_URI . "profile/login/?redirect=".urlencode(ROOT_PATH . BASE_SCRIPT . "profile/")."");
			exit;
		}
		
		if($id == null) {
			$id = member::$id;
			Core::addBreadCrumb(lang("profile"), "profile/");
			Core::setTitle(lang("profile"));
		}
		
		
		
		$this->tabs = new Tabs("profile_tabs");
		$this->profile_actions = new HTMLNode("ul");
		
		if((isset(member::$id) && $id == member::$id)) {
			$this->profile_actions->append(new HTMLNode("li", array(), new HTMLNode("a", array("href" => "profile/edit/", "class" => "noAutoHide"), lang("edit_profile"))));
		}
		
		// get info-tab
		$userdata = DataObject::get("user", array("id" => $id));	
		$userdata->editable = ((isset(member::$id) && $id == member::$id)) ? true : false;
		$info = $userdata->renderWith("profile/info.html");
		$this->tabs->addTab(lang("general", "General Information"), $info, "info");
		
		Core::addBreadcrumb($userdata->nickname, URL . URLEND);
		Core::setTitle($userdata->nickname);
		
		$this->callExtending("beforeRender", $userdata);
		
		return $userdata->customise(array("tabs" => $this->tabs->render(), "profile_actions" => $this->profile_actions->render()))->renderWith("profile/profile.html");
	}
	
	/**
	 * login-method
	*/
	public function login() {
		
		Core::addBreadCrumb(lang("login"), "profile/login/");
		Core::setTitle(lang("login"), "profile/login/");

		
		// if login and a user want's to login as someone else, we should log him out
		if(member::login() && isset($_POST["pwd"]))
		{
			member::doLogout();
		// if a user goes to login and is logged in, we redirect him home
		} else if(member::login()) {
			HTTPResponse::redirect(getRedirect(true));
		}
			
		// if no login and pwd and username isset, we login
		if(isset($_POST['user'], $_POST['pwd']))
		{
				if(member::doLogin($_POST['user'], $_POST['pwd']))
				{
						HTTPResponse::redirect(getRedirect(true));
				}
		}
		
		// else we show template
		
		return tpl::render("profile/login.html");
	}
	
	/**
	 * switch-lang
	 *
	 *@name switchlang
	 *@access public
	*/
	public function switchlang() {
		return tpl::render("switchlang.html");
	}
	
	/**
	 * logout-method
	*/
	public function	logout()
	{
			if(ClassInfo::$appENV["app"]["name"] == "gomacms" && version_compare(ClassInfo::$appENV["app"]["version"] . "-" . ClassInfo::$appENV["app"]["build"], "2.0.0-030", "<")) {
				member::doLogout();
			} else if(isset($_POST["logout"])) {
				member::doLogout();
			}
							
			HTTPResponse::redirect(getRedirect(true));
	}
}