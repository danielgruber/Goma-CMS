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
class ContentController extends FrontedController
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
     * @var Pages
     */
    protected $subPage;

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
     * @param string $action
     * @param bool $hasAction
     */
    public function extendHasAction($action, &$hasAction)
    {
        // check for sub-page
        if($this->willHandleWithSubpage($action)) {
            $hasAction = true;
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
                $this->subPage = DataObject::get_one("pages", array("path" => array("LIKE", $path), "parentid" => $this->modelInst()->id));
                if ($this->subPage != null) {
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
                if (!$this->keychain()->check($password)) {
                    foreach ($passwords as $pwd) {
                        if ($this->keychain()->check($pwd)) {
                            return true;
                        }
                    }

                    return array_merge(array($model->read_permission->password), $passwords);
                }
            }
        }

        return true;
    }

    /**
     * shows password accept form. we need an array as given password.
     *
     * @param array $passwords
     * @return GomaFormResponse
     */
    protected function showPasswordForm($passwords) {
        // set password + breadcrumb
        $object = $this;

        return $this->promptByForm(lang("password"), function($pwd) use($passwords, $object) {
            foreach ($passwords as $password) {
                if ($pwd == $password) {
                    $object->keychain()->add($pwd);
                    return true;
                }
            }

            throw new FormInvalidDataException("prompt_text", lang("captcha_wrong", "The Code was wrong."));
        }, function() {
            return GomaResponse::redirect(ROOT_PATH . BASE_SCRIPT . $this->namespace . "/../");
        }, null, array(), true);
    }

    /**
     * action-handling
     *
     * @param string $action
     * @param string $content
     * @return void
     * @throws Exception
     */
    public function extendHandleAction($action, &$content)
    {
        $check = $this->checkForReadPermission();
        if(is_array($check)) {
            $response = $this->showPasswordForm($check);
            if($response->getRawBody() !== true) {
                $content = $response;
                return;
            }
        }

        array_push(self::$activeNodes, $this->modelInst()->id);

        if ($content === null && $action != "" && $this->subPage != null) {
            $content = ControllerResolver::instanceForModel($this->subPage)->handleRequest($this->request);
            return;
        }

        if ($action == "index") {
            ContentTPLExtension::AppendContent($this->modelInst()->appendedContent);
            ContentTPLExtension::PrependContent($this->modelInst()->prependedContent);
        }

        if ($this->modelInst()->parentid == 0 && $this->modelInst()->sort == 0) {
            defined("HOMEPAGE") OR define("HOMEPAGE", true);
            Core::setTitle($this->modelInst()->windowtitle);
        } else {
            defined("HOMEPAGE") OR define("HOMEPAGE", false);
            Core::setTitle($this->modelInst()->windowtitle);
        }
    }

    /**
     * output-hook
     *
     * @param string|GomaResponse $content
     * @return bool
     */
    public static function outputHook($content)
    {
        if (PROFILE) Profiler::mark("contentController checkupload");

        if (self::$enableBacktracking && is_a(Director::$requestController, "contentController")) {
            $content = is_a($content, "GomaResponse") ? $content->getBody() : $content;

            $contentmd5 = md5($content);
            $cache = new Cacher("uploadTracking_" . $contentmd5 . "_" . Director::$requestController->modelInst()->versionid);
            if ($cache->checkValid()) {
                return true;
            } else {
                $uploadObjects = self::fetchUploadObjects($content, $uploadHash, $lowestmtime);

                if (count($uploadObjects) > 0) {
                    $hash = md5($uploadHash);
                    $cacher = new Cacher("track_" . Director::$requestController->modelInst()->versionid . "_" . $hash);
                    if ($cacher->checkValid()) {
                        return true;
                    } else {
                        /** @var ManyMany_DataObjectSet $uploadTracking */
                        $uploadTracking = Director::$requestController->modelInst()->UploadTracking();
                        /** @var Uploads $upload */
                        foreach ($uploadObjects as $upload) {
                            if(!$uploadTracking->find("versionid", $upload->versionid)) {
                                $uploadTracking->push($upload);
                            }
                        }
                        $uploadTracking->commitStaging(false, true);
                        $cacher->write(1, 14 * 86400);
                    }
                }

                $cache->write(1, Uploads::$cache_life_time - (NOW - $lowestmtime));
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

                    $uploadObjects[$data->id] = $data;
                    $uploadHash .= $data->realfile;
                }
            }
        }

        // img-tags
        preg_match_all('/<img([^>]+)src="([^">]+)"([^>]*)>/Usi', $content, $links);
        foreach ($links[2] as $key => $href) {
            if (strpos($href, "Uploads/") !== false && preg_match('/Uploads\/([^\/]+)\/([a-zA-Z0-9]+)\/([^\/]+)/', $href, $match)) {
                if ($data = Uploads::getFile($match[1] . "/" . $match[2] . "/" . $match[3])) {
                    $uploadObjects[$data->id] = $data;
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
            /** @var ManyMany_DataObjectSet $data */
            $data = $this->getOwner()->modelInst()->linkingPages();
            if ($data->Count() > 0) {
                return true;
            }

            if(Permission::check("admin")) {
                return true;
            }

            $content = null;
            $handleWithMethod = false;
        }
    }
}

gObject::extend("UploadsController", "UploadsPageBacktraceController");
Core::addToHook("onBeforeServe", array("contentController", "outputHook"));
