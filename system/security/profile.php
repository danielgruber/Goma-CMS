<?php
/**y
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 23.08.2011
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
	public $allowed_actions = array("edit", "login", "logout");
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
		
		$userdata = DataObject::_get("user", array("id" => member::$id));
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
		
		if((isset($_SESSION["user_id"]) && $id == $_SESSION["user_id"])) {
			$this->profile_actions->append(new HTMLNode("li", array(), new HTMLNode("a", array("href" => "profile/edit/", "rel" => "dropdownDialog", "class" => "noAutoHide"), lang("edit_profile"))));
		}
		
		// get info-tab
		$userdata = DataObject::get("user", array("id" => $id));	
		$userdata->editable = ((isset($_SESSION["user_id"]) && $id == $_SESSION["user_id"])) ? true : false;
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
			if(isset($_GET["redirect"]))
				HTTPResponse::redirect($_GET["redirect"]);
			if(isset($_POST["redirect"]))
				HTTPResponse::redirect($_POST["redirect"]);
				
				
			HTTPResponse::redirect(BASE_URI);
		}
			
			
		// if no login and pwd and username isset, we login
		if(isset($_POST['user'], $_POST['pwd']))
		{
				if(member::doLogin($_POST['user'], $_POST['pwd']))
				{
						if(isset($_GET["redirect"]))
								HTTPResponse::redirect($_GET["redirect"]);
						if(isset($_POST["redirect"]))
								HTTPResponse::redirect($_POST["redirect"]);
				
				
						HTTPResponse::redirect(BASE_URI);
				} else
				{
						addcontent::add(member::$error);
				}
		}
		
		// else we show template
		
		return tpl::render("boxes/login.html");
	}
	/**
	 * logout-method
	*/
	public function	logout()
	{
			member::doLogout();
							
			if(isset($_GET["redirect"]))
					HTTPResponse::redirect($_GET["redirect"]);
			if(isset($_POST["redirect"]))
					HTTPResponse::redirect($_POST["redirect"]);
	
	
			HTTPResponse::redirect(BASE_URI);
	}
}