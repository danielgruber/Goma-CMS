<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 24.04.2012
  * $Version 2.0.5
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FrontedController extends Controller
{
		/**
		 * activates the live-counter on this controller
		 *
		 *@name live_counter
		 *@access public
		*/
		public static $live_counter = true;
		/**
         * gets $view
         *@name getView
         *@access public
        */
        public function View()
        {
                if(isset($_SESSION['sites_ansicht']) && $_SESSION['sites_ansicht'] == $GLOBALS['lang']['user'])
                {
                        return lang("user", "user");
                } else
                {
                        return lang("admin", "admin");
                }
        }
        
        /**
         * gets addcontent
         *
         *@name addcontent
         *@access public
        */
        public function addcontent() {
            return addcontent::get();
        }
        
        
        /**
         * title
         *@name title
         *@access public
        */
        public function Title()
        {
                return Core::$title . TITLE_SEPERATOR . Core::getCMSVar("ptitle");
        }
        
        /**
         * meta-data
        */
        /**
         * own css-code
         *@name own_css
         *@access public
        */
        public function own_css()
        {
                return settingsController::get('css_standard');
        }
        
		/**
		 * fronted-bar for admins
		 *
		 *@name frontedBar
		 *@access public
		 *@return array
		*/
		public function frontedBar() {
			return array();
		}
		
		/**
		 * handles the request with showing as site
		*/
		public function serve($content)
		{
			if(Core::is_ajax() && isset($_GET["dropdownDialog"]))
				return $content;
			
			$this->areaData["content"] = $content;
			
			$model = is_object($this->model_inst) ? $this->model_inst : new ViewAccessableData();
			
			$model->customise(array(
				"title"		=> $this->Title(),
				"own_css"	=> $this->own_css(),
				"addcontent"=> $this->addcontent(),
				"view"		=> $this->view(),
				"frontedbar"=> new DataSet($this->frontedBar())
			));
			
			
			
			return $this->renderWithAreas("site.html", $model);
		}
}

class siteController extends Controller
{       
		public $shiftOnSuccess = false;
		public static $keywords;
		public static $description;
        
        public function handleRequest(request $request)
        {
                
                if(SITE_MODE == STATUS_MAINTANANCE && !Permission::check("ADMIN"))
                {
                       $data = new ViewAccessAbleData();
                       HTTPResponse::output($data->customise()->renderWith("page_maintenance.html"));
                       exit;
                }
                
                return parent::handleRequest($request);
        }
        
        /**
         * gets the content
         *
         *@name index
         *@access public
        */
        public function index() {
            $path = $this->getParam("path");
            if($path) {
                $data = DataObject::get_one("pages",array("path" => array("LIKE", $path), "parentid" => 0));
               	
                if($data) {
                    return $data->controller()->handleRequest($this->request);
                } else {
                    
                    unset($data, $path);
                    $error = DataObject::get_one("errorpage");
                    if($error) {
                        return $error->controller()->handleRequest($this->request);
                    }
                    unset($error);
                }
                
            }
        }
        
        
        /**
         *@access public
         *@param string - title of the link
         *@param string - href attribute of the link
         *@use: for adding breadcrumbs
         */
        public static function addbreadcrumb($title, $link)
        {
        	if(DEV_MODE) {
        		$trace = debug_backtrace();
        		logging("SiteController::addBreadCrumb is deprecated. Call From ".$trace[0]["file"]." on line ".$trace[0]["line"]."");
        	}
            Core::addbreadcrumb($title, $link);
        }
        /**
         *@access public
         *@param string - title of addtitle
         *@use: for adding title
         */
        public static function addtitle($title)
        {
        		if(DEV_MODE) {
        			$trace = debug_backtrace();
        			logging("SiteController::addTitle is deprecated. Call From ".$trace[0]["file"]." on line ".$trace[0]["line"]."");
        		}
                Core::setTitle($title);
        }
        
}

class HomePageController extends SiteController
{
		/**
		 * shows the homepage of this page
		 *
		 *@name index
		 *@access public
		*/
		public function index() {
			
			define("HOMEPAGE", true);
			
			if(isset($_GET["r"])) {
				$redirect = DataObject::get_one("pages", array("id" => $_GET["r"]));
				if($redirect) {
					$query = preg_replace('/\&?r\='.preg_quote($_GET["r"]).'/','',$_SERVER["QUERY_STRING"]);
					HTTPResponse::redirect($redirect->url . "?" . $query);
				} else
				{
					HTTPResponse::Redirect(ROOT_PATH);
				}
			}
			
			if($data = DataObject::get_one("pages",array("parentid" => 0))) {
				return $data->controller()->handleRequest($this->request);
			} else {
				return 'No Homepage found!';
			}
		}
}