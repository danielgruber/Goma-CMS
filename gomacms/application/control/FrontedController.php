<?php
/**
 * @package goma cms
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified:  28.12.2012
 * $Version 2.1
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FrontedController extends Controller
{
    /**
     * activates the live-counter on this controller
     *
     * @name live_counter
     * @access public
     */
    public static $live_counter = true;

    /**
     * gets $view
     *
     * @name getView
     * @access public
     * @return string
     */
    public function View()
    {
        if (Core::globalSession()->hasKey(SystemController::ADMIN_AS_USER)) {
            return lang("user", "user");
        } else {
            return lang("admin", "admin");
        }
    }

    /**
     * gets addcontent
     *
     * @return string
     */
    public function addcontent()
    {
        return addcontent::get();
    }


    /**
     * title
     *
     * @return string
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
     *
     * @return null
     */
    public function own_css()
    {
        return settingsController::get('css_standard');
    }

    /**
     * fronted-bar for admins
     *
     * @name frontedBar
     * @access public
     * @return array
     */
    public function frontedBar()
    {
        return array();
    }

    /**
     * handles the request with showing as site
     */
    public function serve($content)
    {
        if (Core::is_ajax() && isset($_GET["dropdownDialog"])) {
            return $content;
        }

        $model = is_object($this->model_inst) ? $this->model_inst : new ViewAccessableData();

        $model->customise(array(
            "title"      => $this->Title(),
            "own_css"    => $this->own_css(),
            "addcontent" => $this->addcontent(),
            "view"       => $this->view(),
            "frontedbar" => new DataSet($this->frontedBar()),
            "content"    => $content
        ));

        if (SITE_MODE == STATUS_MAINTANANCE && !Permission::check("ADMIN")) {
            return $model->customise(array("content" => $content))->renderWith("page_maintenance.html");
        }


        return $this->renderWith("site.html", $model);
    }
}

class siteController extends Controller
{
    public $shiftOnSuccess = false;
    public static $keywords;
    public static $description;

    public function handleRequest($request, $subController = false)
    {

        if (SITE_MODE == STATUS_MAINTANANCE && !Permission::check("ADMIN")) {
            //HTTPResponse::setResHeader(503);
            $data = new ViewAccessAbleData();
            HTTPResponse::output($data->customise()->renderWith("page_maintenance.html"));
            exit;
        }

        return parent::handleRequest($request, $subController);
    }

    /**
     * gets the content
     *
     * @name index
     * @access public
     */
    public function index()
    {
        $path = $this->getParam("path");
        if ($path) {
            $data = DataObject::get_one("pages", array("path" => array("LIKE", $path), "parentid" => 0));

            if ($data) {
                return $data->controller()->handleRequest($this->request);
            } else {

                unset($data, $path);
                $error = DataObject::get_one("errorpage");
                if ($error) {
                    return $error->controller()->handleRequest($this->request);
                }
                unset($error);
            }

        }
    }


    /**
     * @access public
     * @param string - title of the link
     * @param string - href attribute of the link
     * @use: for adding breadcrumbs
     */
    public static function addbreadcrumb($title, $link)
    {
        if (DEV_MODE) {
            $trace = debug_backtrace();
            logging("SiteController::addBreadCrumb is deprecated. Call From " . $trace[0]["file"] . " on line " . $trace[0]["line"] . "");
        }
        Core::addbreadcrumb($title, $link);
    }

    /**
     * @access public
     * @param string - title of addtitle
     * @use: for adding title
     */
    public static function addtitle($title)
    {
        if (DEV_MODE) {
            $trace = debug_backtrace();
            logging("SiteController::addTitle is deprecated. Call From " . $trace[0]["file"] . " on line " . $trace[0]["line"] . "");
        }
        Core::setTitle($title);
    }

}

class HomePageController extends SiteController
{
    /**
     * shows the homepage of this page
     *
     * @name index
     * @access public
     * @return false|string
     */
    public function index()
    {

        defined("HOMEPAGE") OR define("HOMEPAGE", true);

        if (isset($_GET["r"])) {
            $redirect = DataObject::get_one("pages", array("id" => $_GET["r"]));
            if ($redirect) {
                $query = preg_replace('/\&?r\=' . preg_quote($_GET["r"]) . '/', '', $_SERVER["QUERY_STRING"]);
                HTTPResponse::redirect($redirect->url . "?" . $query);
            }
        }

        if ($data = DataObject::get_one("pages", array("parentid" => 0))) {
            return $data->controller()->handleRequest($this->request);
        } else {
            return false;
        }
    }
}