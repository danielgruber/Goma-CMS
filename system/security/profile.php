<?php defined("IN_GOMA") OR die();

i18n::AddLang("/members");

/**
 * this class provides Profile-Views for User.
 *
 * @package     goma framework
 * @link        http://goma-cms.org
 * @license:    LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author      Goma-Team
 * @version     1.0
 *
 * last modified: 16.12.2015
 */
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
	*/
	protected $tabs;
	
	/**
	 * define right model.	
	*/
	public $model = "user";

	/**
	 * shows the edit-screen
	 *
	 * @name edit
	 * @access public
	 * @return string
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
		$controller = ControllerResolver::instanceForModel($userdata);

		$data = $controller->edit();
		if(is_object($data)) {
			return $data;
		}
		return '<h1>'.lang("edit_profile").'</h1>' . $data;
	}

	/**
	 * default screen
	 *
	 * @name index
	 * @access public
	 * @return bool|string
	 */
	public function index($id = null) {
		$id = ($id == null) ? $this->getParam("id") : $id;
		if(!$id && !member::login()) {
			HTTPResponse::redirect(BASE_URI);
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
		$userdata = DataObject::get_one("user", array("id" => $id));
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
		if(member::login() && isset($this->getRequest()->post_params["pwd"]))
		{
			AuthenticationService::doLogout();
		// if a user goes to login and is logged in, we redirect him home
		} else if(member::login()) {
			return GomaResponse::redirect(getRedirect(true));
		}
			
		// if no login and pwd and username isset, we login
		if(isset($this->getRequest()->post_params["user"], $this->getRequest()->post_params["pwd"]))
		{
			if(member::doLogin($this->getRequest()->post_params["user"], $this->getRequest()->post_params["pwd"]))
			{
				return GomaResponse::redirect(getRedirect(true));
			}
		}
		
		// else we show template
		
		return tpl::render("profile/login.html");
	}

	/**
	 * switch-lang view
	 *
	 * @return string
	 */
	public function switchlang() {
		return tpl::render("switchlang.html");
	}
	
	/**
	 * logout-method
	*/
	public function	logout()
	{
		if(isset($this->getRequest()->post_params["logout"])) {
			AuthenticationService::doLogout();
		}

		return GomaResponse::redirect(getRedirect(true));
	}
}
