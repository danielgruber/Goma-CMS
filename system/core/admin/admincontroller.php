<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 14.09.2011
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class adminController extends Controller
{
		public static $title;
		public $url_handlers = array(
			"switchlang"				=> "switchlang",
			"admincontroller:\$item!"	=> "handleItem"
		);
		public $allowed_actions = array("handleItem", "switchlang");
		/**
		 *__construct
		*/
		public function __construct()
		{
				defined("IS_BACKEND") OR define("IS_BACKEND", true);
				parent::__construct();
		}
		public function handleItem() {
			
			
			if(!Permission::check("ADMIN_ALL")) 
				return $this->modelInst()->renderWith("admin/index_not_permitted.html");
			
			$class = $this->request->getParam("item") . "admin";
			
			if(classinfo::exists($class)) {
				$c = new $class;
				if(Permission::check($c->rights))
				{
						self::$title = parse_lang($c->text);
						
						return $c->handleRequest($this->request);
						
				} else
				{
						self::$title = parse_lang("less_rights");
						if(Core::is_ajax()) {
							if(member::login()) {
								return lang("less_rights", "You don't have permissions to enter this page.") . "<br /><a href=\"".BASE_URI."\">".lang("back")."</a>";
							} else {
								return lang("less_rights", "You don't have permissions to enter this page.") . tpl::render("boxes/login.html");
							}
						}
				}
			}
		}
		/**
		 * switch-lang-template
		 *
		 *@name switchLang
		 *@access public
		*/
		public function switchLang() {
			return tpl::render("admin/switchlang.html");
		}
		/**
		 * post in own structure
		*/
		public function serve($content) {
			Core::setHeader("robots", "noindex,nofollow");
			if(!_eregi('</html', $content)) {
				if(!Permission::check("ADMIN_ALL"))
					return $content;
				else {
					$admin = new Admin();
					return $admin->customise(array("content" => $content))->renderWith("admin/index.html");
				}
			}
			return $content;
			
		}
		/**
		 * this var contains the templatefile
		 * the str {admintpl} will be replaced with the current admintpl
		 *@name template
		 *@var string
		*/
		public $template = "admin/index.html";
		/**
		 * loads content and then loads page
		 *@name index
		*/
		public function index()
		{
				if(Permission::check("ADMIN_ALL"))
					return parent::index();
				else {
					$this->template = "admin/index_not_permitted.html";
					return parent::index();
				}
		}
		
}

class admin extends ViewAccessableData implements PermissionProvider
{
		
		/**
		 * ajaxbar
		*/
		public function ajaxbar()
		{
				return (settingsController::get('ajaxbar') == 1);
		}
		/**
		 * of login
		 *@name login
		*/
		public function login()
		{
				return member::login();
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
		 * overview
		 *@name overview
		*/
		public function overview()
		{
				$template = new template;
				// tempalte
				$template->assign('switchlang',Core::loadlangs());
				return $template->display('overview.html');
		}
		/**
		 * returns title
		*/
		public function title() {
			return adminController::$title;
		}
		/**
		 * provies all permissions of this dataobject
		*/
		public function providePermissions()
		{
				$arr = array(
					"ADMIN_ALL"	=> array(
						"title" 	=> '{$_lang_administration}',
						'default'	=> 7,
						"implements"=> array(
							"BOXES_ALL"
						)
					)
				);
				
				foreach(classinfo::getChildren("adminitem") as $class)
				{
						$c = new $class();
						$arr = array_merge($arr, $c->providePermissions());
				}
				
				return $arr;
		}
		/**
		 * Statistics
		 *
		 *@name statistics
		 *@access public
		*/
		public function statistics($month = true) {
			if($month) {
				return livecounterController::statisticsByMonth();
			} else {
				return livecounterController::statisticsByDay();
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
				foreach(classinfo::getChildren("adminitem") as $child)
				{
						$class = new $child;
						if($class->text)
								if(right($class->rights) && $class->visible())
								{
										if(_ereg('^admin/'.preg_quote(urlencode(str_replace('admin', '',$class->class))).'', URL))
											$active = true;
										else
											$active = false;
										$data->push(array('text' 	=> parse_lang($class->text), 
															'uname' => urlencode(str_replace('admin', '',$class->class)),
															'sort'	=> $class->sort,
															"active"=> $active));
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
		
		
				
}

class adminRedirectController extends RequestHandler {
	public function handleRequest($request) {
		HTTPResponse::redirect(ROOT_PATH . "admin/" . $request->remaining());
	}
}
