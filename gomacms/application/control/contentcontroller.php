<?php defined("IN_GOMA") OR die();


/**
 * the base controller for every page.
 *
 * It provides Hiearchy-Options and generates some content around.
 *
 * @package     Goma-CMS\Content
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.1.2
 */
class contentController extends FrontedController
{
    /**
     * this is for mainbar, so we know, which ids of site has to be marked as active
     *
     * @name activeids
     * @access public
     * @var array
     */
    public static $activeids = array();

    /**
     * default templte of a page
     *
     * @name template
     */
    public $template = "pages/page.html";

    /**
     * default-url-handlers
     *
     * @name url_handlers
     * @access public
     */
    public $url_handlers = array(
        '$Action//$id/$otherid' => '$Action'
    );

    static $activeNodes = array();

    static $enableBacktracking = true;

    /**
     * register meta-tags
     *
     * @name pagetitle
     * @access public
     */
    public function pagetitle()
    {
        // mark this id as active in mainbar
        array_push(self::$activeids, $this->modelInst()->id);

        if ($this->modelInst()->meta_description) {
            Core::setHeader("description", $this->modelInst()->meta_description);
        }

        // add breadcrumbs, if we are not on the homepage
        if ($this->modelInst()->parentid != 0 || $this->modelInst()->sort != 0) {
            return $this->modelInst()->title;
        }

        return null;
    }

    /**
     * extends hasAction for:
     * - Permission-checks with Password
     * - sub-pages
     *
     * @name extendHasAction
     * @access public
     * @param string - action
     * @return bool|void
     */
    public function extendHasAction($action, &$hasAction)
    {
        if(!$this->checkForReadPermission()) {
            return false;
        }

        array_push(self::$activeNodes, $this->modelInst()->id);

        // check for sub-page
        if($this->willHandleWithSubpage($action)) {
            $hasAction = true;
            return true;
        }

        // register a PAGE_PATH
        define("PAGE_PATH", $this->modelInst()->url);
        define("PAGE_ORG_PATH", $this->modelInst()->orgurl);

        if ($this->modelInst()->parentid == 0 && $this->modelInst()->sort == 0) {
            defined("HOMEPAGE") OR define("HOMEPAGE", true);
            Core::setTitle($this->modelInst()->windowtitle);
        } else {
            defined("HOMEPAGE") OR define("HOMEPAGE", false);
            Core::setTitle($this->modelInst()->windowtitle);
        }
    }

    /**
     * checks if we will handle the action with subpage.
     *
     * @param string $action
     * @return bool
     */
    public function willHandleWithSubpage($action) {
        if ($action != "") {
            $path = $action;
            if (preg_match('/^[a-zA-Z0-9_\-\/]+$/Usi', $path)) {
                if (DataObject::Count("pages", array("path" => array("LIKE", $path), "parentid" => $this->modelInst()->id)) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * checks for read action.
     *
     * @return boolean
     */
    public function checkForReadPermission() {

        /** @var Pages $model */
        $model = $this->modelInst();
        if ($model->read_permission && $model->read_permission->type == "password") {
            $passwords = array();
            $this->callExtending("providePasswords", $passwords);
            if ($model->read_permission->password != "" || $passwords) {
                $password = $model->read_permission->password;
                if (!$this->KeyChainCheck($password)) {
                    foreach ($passwords as $pwd) {
                        if ($this->KeyChainCheck($pwd)) {
                            return true;
                        }
                    }

                    return $this->showPasswordForm(array_merge(array($model->read_permission->password), $passwords));
                }
            }
        }
    }

    /**
     * shows password accept form. we need an array as given password.
     */
    protected function showPasswordForm($passwords) {
        $validator = new FormValidator(array($this, "validatePassword"), array($passwords));

        // set password + breadcrumb
        if ($pwd = $this->prompt(lang("password", "password"), array($validator), null, null, true)) {
            $this->keyChainAdd($pwd);
            return true;
        } else {
            return false;
        }
    }

    /**
     * for validating the password
     *
     * @name validatePassword
     * @access public
     * @param object - validator
     * @param string - password
     * @return bool|string
     */
    public function validatePassword($obj, $passwords)
    {
        foreach ($passwords as $password) {
            if ($obj->form->result["prompt_text"] == $password) {
                return true;
            }
        }

        return lang("captcha_wrong", "The Code was wrong.");

    }

    /**
     * action-handling
     *
     * @name extendHandleAction
     * @access public
     * @return bool|void
     */
    public function extendHandleAction($action, &$content)
    {
        if ($content === null && $action != "") {
            $path = $action;
            if (preg_match('/^[a-zA-Z0-9_\-\/]+$/Usi', $path)) {
                if ($data = DataObject::get_one("pages", array("path" => array("LIKE", $path), "parentid" => $this->modelInst()->id))) {
                    $content = $data->controller()->handleRequest($this->request);

                    return true;
                }
            }
        }

        if ($action == "index") {
            ContentTPLExtension::AppendContent($this->modelInst()->appendedContent);
            ContentTPLExtension::PrependContent($this->modelInst()->prependedContent);
        }
    }

    /**
     * output-hook
     *
     * @name outputHook
     * @return bool
     */
    public static function outputHook($content)
    {

        if (PROFILE) Profiler::mark("contentController checkupload");

        if (self::$enableBacktracking && is_a(Director::$requestController, "contentController")) {

            $contentmd5 = md5($content);
            $cache = new Cacher("uploadTracking_" . $contentmd5);
            if ($cache->checkValid()) {
                return true;
            } else {
                $uploadObjects = self::fetchUploadObjects($content, $uploadHash, $lowestmtime);

                $cache->write(1, Uploads::$cache_life_time - (NOW - $lowestmtime));

                if (count($uploadObjects) > 0) {
                    $hash = md5($uploadHash);
                    $cacher = new Cacher("track_" . Director::$requestController->modelInst()->versionid . "_" . $hash);
                    if ($cacher->checkValid()) {
                        return true;
                    } else {
                        Director::$requestController->modelInst()->UploadTracking()->setData(array());
                        foreach ($uploadObjects as $upload) {
                            Director::$requestController->modelInst()->UploadTracking()->push($upload);
                        }

                        Director::$requestController->modelInst()->UploadTracking()->write(false, true);
                        $cacher->write(1, 14 * 86400);
                    }
                }


            }
        }

        if (PROFILE) Profiler::unmark("contentController checkupload");
    }

    /**
     * checks for upload objects.
     *
     * @param string $content
     * @param string $uploadHash reference
     * @param int $lowestmtime reference
     * @return array
     */
    protected static function fetchUploadObjects($content, &$uploadHash, &$lowestmtime) {

        $uploadHash = "";
        $lowestmtime = NOW;
        $uploadObjects = array();

        // a-tags
        preg_match_all('/<a([^>]+)href="([^">]+)"([^>]*)>/Usi', $content, $links);
        foreach ($links[2] as $key => $href) {
            if (strpos($href, "Uploads/") !== false && preg_match('/Uploads\/([^\/]+)\/([a-zA-Z0-9]+)\/([^\/]+)/', $href, $match)) {
                if ($data = Uploads::getFile($match[1] . "/" . $match[2] . "/" . $match[3])) {
                    if (file_exists($data->path)) {
                        $mtime = filemtime(ROOT . "Uploads/" . $match[1] . "/" . $match[2] . "/" . $match[3]);
                        if ($mtime < $lowestmtime) {
                            $lowestmtime = $mtime;
                        }
                        if ($mtime < NOW - Uploads::$cache_life_time && file_exists($data->realfile)) {
                            @unlink($data->path);
                        }
                    }

                    $uploadObjects[] = $data;
                    $uploadHash .= $data->realfile;
                }
            }
        }

        // img-tags
        preg_match_all('/<img([^>]+)src="([^">]+)"([^>]*)>/Usi', $content, $links);
        foreach ($links[2] as $key => $href) {
            if (strpos($href, "Uploads/") !== false && preg_match('/Uploads\/([^\/]+)\/([a-zA-Z0-9]+)\/([^\/]+)/', $href, $match)) {
                if ($data = Uploads::getFile($match[1] . "/" . $match[2] . "/" . $match[3])) {
                    $uploadObjects[] = $data;
                    $uploadHash .= $data->realfile;
                }
            }
        }

        return $uploadObjects;
    }
}

class UploadsPageLinkExtension extends DataObjectExtension
{
    /**
     * many-many
     */
    static $belongs_many_many = array(
        "pagelinks" => "pages"
    );
}

class UploadsPageBacktraceController extends ControllerExtension
{
    /**
     * on before handle an action we redirect if needed
     *
     * @name onBeforeHandleAction
     * @return bool|void
     */
    public function onBeforeHandleAction($action, &$content, &$handleWithMethod)
    {
        if (contentController::$enableBacktracking) {
            $data = $this->getOwner()->modelInst()->linkingPages();
            $data->setVersion(false);
            if ($data->Count() > 0) {
                foreach ($data as $page) {
                    if ($page->isPublished() || $page->can("Write", $page) || $page->can("Publish", $page)) {
                        return true;
                    }
                }

                $handleWithMethod = false;
                $content = false;
            }
        }
    }
}

Object::extend("UploadsController", "UploadsPageBacktraceController");
Core::addToHook("onBeforeServe", array("contentController", "outputHook"));