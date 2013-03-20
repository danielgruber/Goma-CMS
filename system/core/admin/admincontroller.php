<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 18.12.2012
  * $Version 1.4.6
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class adminController extends Controller
{
		/**
		 * current title
		 *
		 *@name title
		*/
		public static $title;
		
		/**
		 * object of current admin-view
		 *
		 *@name activeController
		 *@access protected
		*/
		protected static $activeController;
		
		/**
		 * some default url-handlers for this controller
		 *
		 *@name url_handkers
		 *@access public
		*/
		public $url_handlers = array(
			"switchlang"				=> "switchlang",
			"update"					=> "handleUpdate",
			"flushLog"					=> "flushLog",
			"history"					=> "history",
			"admincontroller:\$item!"	=> "handleItem"
		);
		
		/**
		 * we allow those actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array("handleItem", "switchlang", "handleUpdate", "flushLog", "history");
		
		/**
		 * this var contains the templatefile
		 * the str {admintpl} will be replaced with the current admintpl
		 *@name template
		 *@var string
		*/
		public $template = "admin/index.html";
		
		/**
		 * tpl-vars
		*/
		public $tplVars = array(
			"BASEURI"	=> BASE_URI
		);
		
		/**
		 * returns current controller
		 *
		 *@name activeController
		 *@access public
		*/
		public static function activeController() {
			return (self::$activeController) ? self::$activeController : new adminController;
		}
		
		/**
		 *__construct
		*/
		public function __construct()
		{
				Resources::addData("goma.ui.is_backend = true;");
				defined("IS_BACKEND") OR define("IS_BACKEND", true);
				Core::setHeader("robots", "noindex, nofollow");
				parent::__construct();
		}
		
		/**
		 * global admin-enabling
		 *
		 *@name handleRequest
		 *@access public
		*/
		public function handleRequest($request, $subController = false) {
			if(isset(ClassInfo::$appENV["app"]["enableAdmin"]) && !ClassInfo::$appENV["app"]["enableAdmin"]) {
				HTTPResponse::redirect(BASE_URI);
			}
			
			return parent::handleRequest($request, $subController);
		}
		
		/**
		 * hands the control to admin-controller
		 *
		 *@name handleItem
		 *@access public
		*/
		public function handleItem() {
			if(!Permission::check("ADMIN")) 
				return $this->modelInst()->renderWith("admin/index_not_permitted.html");
			
			$class = $this->request->getParam("item") . "admin";
			
			if(classinfo::exists($class)) {
				$c = new $class;
				
				if(Permission::check($c->rights))
				{
						self::$activeController = $c;
						return $c->handleRequest($this->request);
				}
			}
		}
		
		/**
		 * title
		 *
		 *@name title
		*/
		public function title() {
			return "";
		}
		
		/**
		 * returns title, alias for title
		 *
		 *@name adminTitle
		 *@access public
		*/
		final public function adminTitle() {
			return $this->Title();
		}
		
		/**
		 * returns the URL for the View Website-Button
		 *
		 *@name PreviewURL
		 *@access public
		*/
		public function PreviewURL() {
			return BASE_URI;
		}
		
		/**
		 * switch-lang-template
		 *
		 *@name switchLang
		 *@access public
		*/
		public function switchLang() {
			return tpl::render("switchlang.html");
		}
		
		/**
		 * flushes all log-files
		 *
		 *@name flushLog
		*/
		public function flushLog() {
			if(Permission::check("superadmin")) {
				FileSystem::delete(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER);
				AddContent::addSuccess(lang("flush_log_success"));
				$this->redirectBack();
			}
			
			$this->template = "admin/index_not_permitted.html";
			return parent::index();
		}
		
		/**
		 * post in own structure
		*/
		public function serve($content) {
			Core::setHeader("robots", "noindex,nofollow");
			if(!Permission::check("ADMIN") && Core::is_ajax()) {
				Resources::addJS("location.reload();");
			}
			if(!Core::is_ajax()) {
				if(!_eregi('</html', $content)) {
					if(!Permission::check("ADMIN")) {
						$admin = new Admin();
						return $admin->customise(array("content" => $content))->renderWith("admin/index_not_permitted.html");
					 } else {
						$admin = new Admin();
						return $admin->customise(array("content" => $content))->renderWith("admin/index.html");
					}
				}
			}
			return $content;
			
		}
		
		/**
		 * loads content and then loads page
		 *@name index
		*/
		public function index()
		{
				if(isset($_GET["flush"])) {
					AddContent::addSuccess(lang("cache_deleted"));
				}
				
				if(Permission::check("ADMIN"))
					return parent::index();
				else {
					$this->template = "admin/index_not_permitted.html";
					return parent::index();
				}
		}
		
		/**
		 * update algorythm
		 *
		 *@name handleUpdate
		 *@access public
		*/
		public function handleUpdate() {
			
			if(Permission::check("superadmin")) {
				$controller = new UpdateController();
				self::$activeController = $controller;
				return $controller->handleRequest($this->request);
			}
			
			$this->template = "admin/index_not_permitted.html";
			return parent::index();
		}
		
		/**
		 * history
		 *
		 *@name history
		 *@access public
		*/
		public function history() {
			if(Permission::check("ADMIN")) {
				$controller = new HistoryController();
				return $controller->handleRequest($this->request, true);
			}
			
			$this->template = "admin/index_not_permitted.html";
			return parent::index();
		}
		
		/**
		 * extends the userbar
		 *
		 *@name userbar
		 *@access public
		*/
		public function userbar(&$bar) {
			
		}
		
		/**
		 * here you can modify classes content-div
		 *
		 *@name contentClass
		 *@access public
		*/
		public function contentClass() {
			return $this->class;
		}
		
		/**
		 * history-url
		 *
		 *@name historyURL
		 *@access public
		*/
		public function historyURL() {
			return "admin/history";
		}
}

class admin extends ViewAccessableData implements PermProvider
{
		/**
		 * user-bar
		 *
		 *@name userbar
		 *@access public
		*/
		public function userbar() {
			$userbar = new HTMLNode("div");
			$this->callExtending("userbar");
			adminController::activeController()->userbar($userbar);
			
			return $userbar->html();
		}
		
		/**
		 * history-url
		 *
		 *@name historyURL
		 *@access public
		*/
		public function historyURL() {
			return adminController::activeController()->historyURL();
		}
		
		/**
		 * headers
		 *@name header
		 *@access public
		*/
		public function header()
		{
				return Core::GetHeaderHTML();
		}
		
		/**
		 * returns title
		*/
		public function title() {
			$adminTitle = adminController::activeController()->Title();
			if($adminTitle) {
				if(Core::$title)
					return $adminTitle . " / " . Core::$title;
				return $adminTitle;
			}
			
			if(Core::$title)
				return Core::$title;
			
			return false;
		}
		
		/**
		 * returns content-classes
		*/
		public function content_class() {
			return adminController::activeController()->ContentClass();
		}
		
		/**
		 * returns the URL for the view Website button
		 *
		 *@name PreviewURL
		*/
		public function PreviewURL() {
			return adminController::activeController()->PreviewURL();
		}
		
		/**
		 * provies all permissions of this dataobject
		*/
		public function providePerms()
		{
				return array(
					"ADMIN"	=> array(
						"title" 		=> '{$_lang_administration}',
						'default'		=> array(
							"type" 		=> "admins"
						),
						"description"	=> '{$_lang_permission_administration}'
					),
					"ADMIN_HISTORY"	=> array(
						"title"		=> '{$_lang_history}',
						"default"	=> array(
							"type"	=> "admins"
						),
						"category"	=> "ADMIN"
					)
				);
		}
		
		/**
		 * Statistics
		 *
		 *@name statistics
		 *@access public
		*/
		public function statistics($month = true, $page = 1) {
			if($month) {
				return livecounterController::statisticsByMonth(10, $page);
			} else {
				return livecounterController::statisticsByDay(10, 1, $page);
			}
		}
		
		/**
		 * gets data fpr available points
		 *@name this
		 *@access public
		*/
		public function this()
		{
				
				$data = new DataSet();
				foreach(ClassInfo::getChildren("adminitem") as $child)
				{
						$class = new $child;
						if($class->text) {
								if(right($class->rights) && $class->visible())
								{
										if(adminController::activeController()->class == $child)
											$active = true;
										else
											$active = false;
										$data->push(array(	'text' 	=> parse_lang($class->text), 
															'uname' => substr($class->class, 0, -5),
															'sort'	=> $class->sort,
															"active"=> $active));
								}
						}
				}
				$data->sort("sort", "DESC");
				return $data;
		}
		
		/**
		 * gets addcontent
		 *@name getAddContent
		 *@access public
		*/
		public function getAddContent()
		{
				return addcontent::get();
		}
		
		/**
		 * lost_password
		 *@name getLost_password
		 *@access public
		*/
		public function getLost_password()
		{
				$controller = new lost_password();
				return $controller->render();
		}
		
		/**
		 * returns a list of installed software at a given maximum number
		 *
		 *@name Software
		 *@access public
		*/
		public function Software($number = 7) {
			return G_SoftwareType::listAllSoftware();
		}
		
		/**
		 * lists local updates
		 *
		 *@name getUpdates
		*/			
		public function getUpdates() {
			$updates = G_SoftwareType::listUpdatePackages();
			foreach($updates as $name => $data) {
				$data["secret"] = randomString(20);
				if(!isset($data["AppStore"])) {
					$_SESSION["updates"][$data["file"]] = $data["secret"];
				} else {
					$_SESSION["AppStore_updates"][$data["AppStore"]] = $data["secret"];
				}
				$updates[$name] = $data;
			}
			
			return new DataSet($updates);
		}
		
		/**
		 * returns if store is available
		 *
		 *@name isStoreAvailable
		 *@access public
		*/
		public function isStoreAvailable() {
			return G_SoftwareType::isStoreAvailable();
		}
		
		/**
		 * returns updatable packages
		 *
		 *@name getUpdatables
		 *@access public
		*/
		public function getUpdatables() {
			return new DataSet(G_SoftwareType::listUpdatablePackages());
		}
		
		/**
		 * returns updatables as json
		 *
		 *@name getUpdatables_JSON
		*/
		public function getUpdatables_JSON() {
			return json_encode(G_SoftwareType::listUpdatablePackages());
		}
}