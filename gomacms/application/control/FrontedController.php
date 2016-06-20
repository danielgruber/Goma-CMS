<?php defined("IN_GOMA") OR die();

/**
 * @package goma cms
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified:  28.12.2012
 * $Version 2.1
 */
class FrontedController extends Controller
{
    /**
     * activates the live-counter on this controller
     */
    protected static $live_counter = true;

    /**
     * gets $view
     *
     * @return string
     */
    public function View()
    {
        if (GlobalSessionManager::globalSession()->hasKey(SystemController::ADMIN_AS_USER)) {
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
     * @param string $content
     * @return mixed|string
     */
    public function serve($content)
    {
        if (Core::is_ajax() && isset($_GET["dropdownDialog"])) {
            return $content;
        }

        if (SITE_MODE == STATUS_MAINTANANCE && !Permission::check("ADMIN")) {
            return $this->getServeModel($content)->renderWith("page_maintenance.html");
        }


        return $this->renderWith("site.html", $this->getServeModel($content));
    }

    /**
     * serve-model.
     * @param string $content
     * @return ViewAccessableData
     */
    protected function getServeModel($content) {
        $model = is_object($this->model_inst) ? $this->model_inst : new ViewAccessableData();

        $model->customise(array(
            "title"      => $this->Title(),
            "own_css"    => $this->own_css(),
            "addcontent" => $this->addcontent(),
            "view"       => $this->view(),
            "frontedbar" => new DataSet($this->frontedBar()),
            "content"    => $content
        ));

        return $model;
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
     * @return bool|false|mixed|null|string
     */
    public function index()
    {
        $path = $this->getParam("path");
        if ($path) {
            /** @var Page $data */
            $data = DataObject::get_one("pages", array("path" => array("LIKE", $path), "parentid" => 0));

            if ($data) {
                return ControllerResolver::instanceForModel($data)->handleRequest($this->request);
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
}
