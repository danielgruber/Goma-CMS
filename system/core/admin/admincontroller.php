<?php defined("IN_GOMA") OR die();

/**
 * The base controller for the admin-panel.
 *
 * @package     Goma\Core\Admin
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5.2
 */
class adminController extends Controller
{
    /**
     * current title
     *
     * @name title
     */
    static $title;

    /**
     * object of current admin-view
     *
     * @name activeController
     * @access protected
     */
    protected static $activeController;

    /**
     * some default url-handlers for this controller
     *
     * @name url_handkers
     * @access public
     */
    public $url_handlers = array(
        "switchlang"              => "switchlang",
        "update"                  => "handleUpdate",
        "flushLog"                => "flushLog",
        "history"                 => "history",
        "admincontroller:\$item!" => "handleItem"
    );

    /**
     * we allow those actions
     *
     * @name allowed_actions
     * @access public
     */
    public $allowed_actions = array("handleItem", "switchlang", "handleUpdate", "flushLog", "history");

    /**
     * this var contains the templatefile
     * the str {admintpl} will be replaced with the current admintpl
     *
     * @name template
     * @var string
     */
    public $template = "admin/index.html";

    /**
     * tpl-vars
     */
    public $tplVars = array(
        "BASEURI" => BASE_URI
    );

    static $less_vars = "admin.less";

    /**
     * returns current controller
     *
     * @return adminController
     */
    static function activeController()
    {
        return (self::$activeController) ? self::$activeController : new adminController;
    }

    /**
     * @param null $keyChain
     */
    public function __construct($keyChain = null)
    {
        parent::__construct($keyChain);

        Resources::addData("goma.ENV.is_backend = true;");
        defined("IS_BACKEND") OR define("IS_BACKEND", true);
        Core::setHeader("robots", "noindex, nofollow");
    }

    /**
     * global admin-enabling
     *
     * @name handleRequest
     * @access public
     * @return string|false
     */
    public function handleRequest($request, $subController = false)
    {
        if (isset(ClassInfo::$appENV["app"]["enableAdmin"]) && !ClassInfo::$appENV["app"]["enableAdmin"]) {
            HTTPResponse::redirect(BASE_URI);
        }

        HTTPResponse::unsetCacheable();

        return parent::handleRequest($request, $subController);
    }

    /**
     * hands the control to admin-controller
     *
     * @name handleItem
     * @access public
     * @return mixed
     */
    public function handleItem()
    {
        if (!Permission::check("ADMIN"))
            return $this->modelInst()->renderWith("admin/index_not_permitted.html");

        $class = $this->request->getParam("item") . "admin";

        if (ClassInfo::exists($class)) {
            /** @var RequestHandler $controller */
            $controller = new $class;

            Core::$favicon = ClassInfo::getClassIcon($class);

            if (Permission::check($controller->rights)) {
                self::$activeController = $controller;

                return $controller->handleRequest($this->request);
            }
        }
    }

    /**
     * title
     *
     * @name title
     * @return string
     */
    public function title()
    {
        return "";
    }

    /**
     * returns title, alias for title
     *
     * @name adminTitle
     * @access public
     * @return string
     */
    final public function adminTitle()
    {
        return $this->Title();
    }

    /**
     * returns the URL for the View Website-Button
     *
     * @name PreviewURL
     * @access public
     * @return string
     */
    public function PreviewURL()
    {
        return BASE_URI;
    }

    /**
     * switch-lang-template
     *
     * @name switchLang
     * @access public
     * @return string
     */
    public function switchLang()
    {
        return tpl::render("switchlang.html");
    }

    /**
     * flushes all log-files
     *
     * @name flushLog
     * @return mixed|string
     */
    public function flushLog($count = 40) {
        $count = $this->getParam("count") ? $this->getParam("count") : $count;

        if (Permission::check("superadmin")) {
            PushController::enablePush();
            GlobalSessionManager::globalSession()->stopSession();
            ignore_user_abort(true);
            // we delete all logs that are older than 30 days
            Core::CleanUpLog($count);

            if (!Core::is_ajax()) {
                AddContent::addSuccess(lang("flush_log_success"));
                return $this->redirectBack();
            } else {
                HTTPResponse::setHeader("content-type", "text/x-json");
                HTTPResponse::sendHeader();

                Notification::notify($this->classname, lang("flush_log_success"), null, "PushNotification");

                GlobalSessionManager::Init();
                PushController::disablePush();

                echo json_encode(1);
                exit;
            }
        }

        $this->template = "admin/index_not_permitted.html";

        return parent::index();
    }

    /**
     * post in own structure
     */
    public function serve($content)
    {
        Core::setHeader("robots", "noindex,nofollow");
        if (!Permission::check("ADMIN") && Core::is_ajax()) {
            Resources::addJS("location.reload();");
        }

        if (Permission::check("ADMIN")) {
            $data = $this->helpData();
            $data["#help-button a"] = lang("HELP.HELP");
            Resources::addJS("addHelp(" . json_encode($data) . ");");
        }

        if (!Core::is_ajax()) {
            if (!preg_match('/<\/html/i', $content)) {
                if (!Permission::check("ADMIN")) {
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
     *
     * @name index
     * @return bool|string
     */
    public function index()
    {
        if (Permission::check("ADMIN")) {

            if (isset($_GET["flush"])) {
                Core::deleteCache(true);

                AddContent::addSuccess(lang("cache_deleted"));
            }

            return parent::index();
        } else {
            $this->template = "admin/index_not_permitted.html";

            return parent::index();
        }
    }

    /**
     * update algorythm
     *
     * @name handleUpdate
     * @access public
     */
    public function handleUpdate()
    {

        if (Permission::check("superadmin")) {
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
     * @return bool|string
     */
    public function history()
    {
        if (Permission::check("ADMIN")) {
            $controller = new HistoryController();

            return $controller->handleRequest($this->request, true);
        }

        $this->template = "admin/index_not_permitted.html";

        return false;
    }

    /**
     * extends the userbar
     *
     * @name userbar
     * @access public
     */
    public function userbar(&$bar)
    {

    }

    /**
     * here you can modify classes content-div
     *
     * @return string
     */
    public function contentClass()
    {
        return $this->classname;
    }

    /**
     * history-url
     *
     * @return string
     */
    public function historyURL()
    {
        return "admin/history";
    }

    /**
     * help-texts.
     */
    public function helpData()
    {
        return array(
            "#navi-toggle span span" => array(
                "text" => lang("HELP.SHOW-MENU")
            ),
            "#history"          => array(
                "text"     => lang("HELP.HISTORY"),
                "position" => "left"
            )
        );
    }
}

/**
 * The base model for the admin-panel.
 *
 * @package     Goma\Core\Admin
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5
 */
class admin extends ViewAccessableData implements PermProvider
{
    /**
     * user-bar
     *
     * @return array|string
     */
    public function userbar()
    {
        $userbar = new HTMLNode("div");
        $this->callExtending("userbar");
        adminController::activeController()->userbar($userbar);

        return $userbar->html();
    }

    /**
     * history-url
     *
     * @name historyURL
     * @access public
     */
    public function historyURL()
    {
        return adminController::activeController()->historyURL();
    }

    public function TooManyLogs()
    {
        if (file_exists(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log")) {
            $count = count(scandir(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/log"));
            if ($count > 45) {
                return $count;
            }

            return false;
        }

        return false;
    }

    /**
     * returns title
     */
    public function title()
    {
        $adminTitle = adminController::activeController()->Title();
        if ($adminTitle) {
            if (Core::$title)
                return $adminTitle . " / " . Core::$title;

            return $adminTitle;
        }

        if (Core::$title)
            return Core::$title;

        return false;
    }

    /**
     * returns content-classes
     */
    public function content_class()
    {
        return adminController::activeController()->ContentClass();
    }

    /**
     * returns the URL for the view Website button
     *
     * @name PreviewURL
     */
    public function PreviewURL()
    {
        return adminController::activeController()->PreviewURL();
    }

    /**
     * provies all permissions of this dataobject
     */
    public function providePerms()
    {
        return array(
            "ADMIN"         => array(
                "title"       => '{$_lang_administration}',
                'default'     => array(
                    "type" => "admins"
                ),
                "description" => '{$_lang_permission_administration}'
            ),
            "ADMIN_HISTORY" => array(
                "title"    => '{$_lang_history}',
                "default"  => array(
                    "type" => "admins"
                ),
                "category" => "ADMIN"
            )
        );
    }

    /**
     * gets data fpr available points
     *
     * @return DataSet
     */
    public function this()
    {

        $data = new DataSet();
        foreach (ClassInfo::getChildren("adminitem") as $child) {
            $class = new $child;
            if ($class->text) {
                if (Permission::check($class->rights) && $class->visible()) {
                    if (adminController::activeController()->classname == $child)
                        $active = true;
                    else
                        $active = false;

                    $data->push(array('text'   => parse_lang($class->text),
                                      'uname'  => substr($class->classname, 0, -5),
                                      'sort'   => $class->sort,
                                      "active" => $active,
                                      "icon"   => ClassInfo::getClassIcon($class->classname)));
                }
            }
        }
        $data->sort("sort", "DESC");

        return $data;
    }

    /**
     * gets addcontent
     *
     * @return string
     */
    public function getAddContent()
    {
        return addcontent::get();
    }

    /**
     * lost_password
     *
     * @name getLost_password
     * @access public
     */
    public function getLost_password()
    {
        $profile = new ProfileController();
        return $profile->lost_password();
    }

    /**
     * returns a list of installed software at a given maximum number
     *
     * @return ViewAccessableData
     */
    public function Software($number = 7)
    {
        return G_SoftwareType::listAllSoftware();
    }

    /**
     * returns if store is available
     *
     * @return bool
     */
    public function isStoreAvailable()
    {
        return G_SoftwareType::isStoreAvailable();
    }

    /**
     * returns updatable packages
     *
     * @return DataSet
     */
    public function getUpdatables()
    {
        return new DataSet(G_SoftwareType::listUpdatablePackages());
    }

    /**
     * returns updatables as json
     *
     * @return string
     */
    public function getUpdatables_JSON()
    {
        return json_encode(G_SoftwareType::listUpdatablePackages());
    }
}