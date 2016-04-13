<?php
defined("IN_GOMA") OR die();

/**
 * the basic class for each goma-controller, which handles models.
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package        Goma\Controller
 * @version        2.3.6
 */
class Controller extends RequestHandler
{
    /**
     * keychain constant for session.
     */
    const SESSION_KEYCHAIN = "c_keychain";

    /**
     * showform if no edit right
     *
     * @name showWithoutRight
     * @access public
     * @var bool
     * @default false
     */
    public static $showWithoutRight = false;

    /**
     * activates the live-counter on this controller
     *
     * @name live_counter
     * @access public
     */
    public static $live_counter = false;

    /**
     * how much data is on one page?
     *
     * @name perPage
     * @access public
     */
    public $perPage = null;

    /**
     * defines whether to use pages or not
     *
     * @name pages
     * @access public
     * @var bool
     */
    public $pages = false;

    /**
     * defines which model is used for this controller
     *
     * @name model
     * @access public
     * @var bool|string
     */
    public $model = null;

    /**
     * instance of the model
     *
     * @var RequestHandler|Controller
     */
    public $model_inst = false;

    /**
     * where for the model_inst
     * @name where
     * @access public
     */
    public $where = array();

    /**
     * allowed actions
     * @name allowed_actions
     * @access public
     */
    public $allowed_actions = array(
        "edit",
        "delete",
        "record",
        "version"
    );

    /**
     * template for this controller
     *
     * @name template
     * @acceess public
     */
    public $template = "";

    /**
     * some vars for the template
     * @name tplVars
     * @access public
     */
    public $tplVars = array();

    /**
     * url-handlers
     * @name url_handlers
     */
    public $url_handlers = array(
        '$Action/$id' => '$Action',
    );

    /**
     * inits the controller:
     * - determining and loading model
     * - checking template
     *
     *
     * @name init
     * @access public
     */
    public function Init($request = null)
    {
        parent::Init($request);

        if ($this->template == "") {
            $this->template = $this->model() . ".html";
        }

        if(!$this->subController) {
            if (StaticsManager::getStatic($this->classname, "live_counter")) {
                // run the livecounter (statistics), just if it is activated or the visitor wasn't tracked already

                if (PROFILE) Profiler::mark("livecounter");
                livecounter::run();
                if (PROFILE) Profiler::unmark("livecounter");

                GlobalSessionManager::globalSession()->set(livecounter::SESSION_USER_COUNTED, TIME);
            }

            if ($title = $this->PageTitle()) {
                Core::setTitle($title);
                Core::addBreadCrumb($title, $this->namespace . URLEND);
            }
        }
    }

    /**
     * if this method returns a title automatic title and breadcrumb will be set
     */
    public function PageTitle()
    {
        return null;
    }

    /**
     * returns an array of the wiki-article and youtube-video for this controller
     *
     * @name helpArticle
     * @access public
     * @return array
     */
    public function helpArticle()
    {
        return array();
    }

    /**
     * sets the model.
     * @param  ViewAccessableData $model
     * @param bool $name
     */
    public function setModelInst($model, $name = false)
    {
        if(!is_a($model, "ViewAccessableData")) {
            throw new InvalidArgumentException("Argument must be type of ViewAccessableData.");
        }

        $this->model_inst = $model;
        $this->model = ($name !== false) ? $name : $model->dataClass;

        $model->controller = $this;
    }

    /**
     * returns the model-object
     *
     * @param ViewAccessableData|null $model
     * @return ViewAccessableData
     */
    public function modelInst($model = null)
    {
        if (is_object($model) && is_a($model, "ViewAccessableData")) {
            $this->model_inst = $model;
            $this->model = $model->dataClass;
        } else if (isset($model) && ClassInfo::exists($model)) {
            $this->model = $model;
        }

        if (!is_object($this->model_inst) || (isset($model) && ClassInfo::exists($model))) {
            if (isset($this->model)) {
                $this->model_inst = gObject::instance($this->model);
            } else {
                if (ClassInfo::exists($model = substr($this->classname, 0, -10))) {
                    $this->model = $model;
                    $this->model_inst = gObject::instance($this->model);
                } else if (ClassInfo::exists($model = substr($this->classname, 0, -11))) {
                    $this->model = $model;
                    $this->model_inst = gObject::instance($this->model);
                }
            }
        } else if (!isset($this->model)) {
            $this->model = $this->model_inst->dataClass;
        }

        if (isset($this->model_inst) && is_object($this->model_inst) && is_a($this->model_inst, "DataSet") && !$this->model_inst->isPagination() && $this->pages && $this->perPage) {
            $page = isset($_GET["pa"]) ? $_GET["pa"] : null;
            if ($this->perPage)
                $this->model_inst->activatePagination($page, $this->perPage);
            else
                $this->model_inst->activatePagination($page);
        }

        return (is_object($this->model_inst)) ? $this->model_inst : new ViewAccessAbleData();
    }

    /**
     * returns the controller-model
     *
     * @name model
     * @access public
     * @return null|string
     */
    public function model($model = null)
    {
        if (isset($model) && ClassInfo::exists($model)) {
            $this->model = $model;
            return $model;
        }

        if (!isset($this->model)) {
            if (!is_object($this->model_inst)) {
                if (ClassInfo::exists($model = substr($this->classname, 0, -10))) {
                    $this->model = $model;
                } else if (ClassInfo::exists($model = substr($this->classname, 0, -11))) {
                    $this->model = $model;
                }
            } else {
                $this->model = $this->model_inst->dataClass;
            }
        }

        return $this->model;
    }

    /**
     * returns the count of records in the model according to this controller
     *
     * @name countModelRecords
     * @access public
     * @return int
     */
    public function countModelRecords()
    {
        if (is_a($this->modelInst(), "DataObjectSet"))
            return $this->modelInst()->count();
        else {
            if ($this->modelInst()->bool())
                return 1;
        }

        return 0;
    }

    /**
     * handles requests
     *
     * @param Request $request
     * @param bool $subController
     * @return false|mixed|null|string
     * @throws Exception
     */
    public function handleRequest($request, $subController = false)
    {
        try {
            if (StaticsManager::hasStatic($this->classname, "less_vars")) {
                Resources::$lessVars = StaticsManager::getStatic($this->classname, "less_vars");
            }

            $data = $this->__output(parent::handleRequest($request, $subController));

            if ($this->helpArticle()) {
                Resources::addData("goma.help.initWithParams(" . json_encode($this->helpArticle()) . ");");
            }

            return $data;
        } catch(Exception $e) {
            if($subController) throw $e;

            return $this->handleException($e);
        }
    }

    /**
     * output-layer
     * @param string|GomaResponse $content
     * @return string|GomaResponse
     */
    public function __output($content) {
        $this->callExtending("handleOutput", $content);

        return $content;
    }

    /**
     * this action will be called if no other action was found
     *
     * @return string|bool
     */
    public function index() {
        if ($this->template) {
            $this->tplVars["namespace"] = $this->namespace;
            return $this->modelInst()->customise($this->tplVars)->renderWith($this->template, $this->inExpansion);
        } else {
            throw new LogicException("No Template for Controller " . $this->classname . ". Please define \$template to activate the index-method.");
        }
    }

    /**
     * renders with given view
     *
     * @param string $template
     * @param ViewAccessableData|null $model
     * @return mixed
     */
    public function renderWith($template, $model = null)
    {
        if (!isset($model))
            $model = $this->modelInst();

        return $model->customise($this->tplVars)->renderWith($template);
    }

    /**
     * handles a request with a given record in it's controller
     *
     * @name record
     * @access public
     * @return string|false
     */
    public function record()
    {
        $id = $this->getParam("id");
        if ($model = $this->model()) {
            $data = DataObject::get_one($model, array("id" => $id));
            $this->callExtending("decorateRecord", $model);
            $this->decorateRecord($data);
            if ($data) {
                $controller = $data->controller();
                return $controller->handleRequest($this->request);
            } else {
                return $this->index();
            }
        } else {
            return $this->index();
        }
    }

    /**
     * handles a request with a given versionid in it's controller
     *
     * @name version
     * @access public
     * @return mixed|string
     */
    public function version()
    {
        $id = $this->getParam("id");
        if ($model = $this->model()) {
            $data = DataObject::get_one($model, array("versionid" => $id));
            $this->callExtending("decorateRecord", $model);
            $this->decorateRecord($data);
            if ($data) {
                return $data->controller()->handleRequest($this->request);
            } else {
                return $this->index();
            }
        } else {
            return $this->index();
        }
    }

    /**
     * hook in this function to decorate a created record of record()-method
     *
     * @name decorateRecord
     * @access public
     */
    public function decorateRecord(&$record)
    {

    }

    /**
     * generates a form
     *
     * @name form
     * @access public
     * @param string $name
     * @param ViewAccessableData|null $model
     * @param array $fields
     * @param bool $edit if calling getEditForm or getForm on model
     * @param string $submission
     * @param bool $disabled
     * @return string
     */
    public function form($name = null, $model = null, $fields = array(), $edit = false, $submission = null, $disabled = false)
    {
        return $this->buildForm($name, $model, $fields, $edit, $submission, $disabled)->render();
    }

    /**
     * builds the form
     *
     * @param string|null $name
     * @param ViewAccessableData|null $model
     * @param array $fields
     * @param bool $edit
     * @param callback|null $submission
     * @param bool $disabled
     * @return Form
     */
    public function buildForm($name = null, $model = null, $fields = array(), $edit = false, $submission = null, $disabled = false)
    {
        if (!isset($model) || !$model) {
            $model = clone $this->modelInst();
        }

        if(!isset($submission)) {
            $submission = "submit_form";
        }

        if (!gObject::method_exists($model, "generateForm")) {
            throw new LogicException("No Method generateForm for Model " . get_class($model));
        }

        // add the right controller
        /** @var DataObject $model */
        $form = $model->generateForm($name, $edit, $disabled, isset($this->request) ? $this->request : null, $this);
        $form->setSubmission($submission);

        foreach($fields as $field) {
            $form->add($field);
        }

        // we add where to the form
        foreach ($this->where as $key => $value) {
            $form->add(new HiddenField($key, $value));
        }

        $this->callExtending("afterForm", $form);

        return $form;
    }

    /**
     * renders the form for this model
     * @param bool $name
     * @param array $fields
     * @param string $submission
     * @param bool $disabled
     * @param null|ViewAccessableData $model
     * @return string
     */
    public function renderForm($name = false, $fields = array(), $submission = "safe", $disabled = false, $model = null)
    {
        if (!isset($model))
            $model = $this->modelInst();

        return $this->form($name, $model, $fields, true, $submission, $disabled);
    }

    /**
     * edit-function
     *
     * @name edit
     * @access public
     * @return string
     */
    public function edit()
    {
        if ($this->countModelRecords() == 1 && (!$this->getParam("id") || !is_a($this->modelInst(), "DataObjectSet")) && (!$this->getParam("id") || $this->ModelInst()->id == $this->getParam("id"))) {
            if (!$this->modelInst()->can("Write")) {
                if (StaticsManager::getStatic($this->classname, "showWithoutRight") || $this->modelInst()->showWithoutRight) {
                    $disabled = true;
                } else {
                    return $this->actionComplete("less_rights");
                }
            } else {
                $disabled = false;
            }

            return $this->form("edit_" . $this->classname . $this->modelInst()->id, $this->modelInst(), array(), true, "safe", $disabled);
        } else if ($this->getParam("id")) {
            if (preg_match('/^[0-9]+$/', $this->getParam("id"))) {
                $model = DataObject::get_one($this->model(), array_merge($this->where, array("id" => $this->getParam("id"))));
                if ($model) {
                    return $model->controller(clone $this)->edit();
                } else {
                    throw new InvalidArgumentException("No data found for ID " . $this->getParam("id"));
                }
            } else {
                log_error("Warning: Param ID for Action edit is not an integer: " . print_r($this->request, true));
                return $this->redirectBack();
            }
        } else {
            throw new InvalidArgumentException("Controller::Edit should be called if you just have one Record or a given ID in URL.");
        }
    }

    /**
     * delete-function
     * this delete-function also implements ajax-functions
     *
     * @name delete
     * @access public
     * @param object - object for hideDeletedObject Function
     * @return bool|string
     */
    public function delete($object = null)
    {
        if ($this->countModelRecords() == 1) {
            if (!$this->modelInst()->can("Delete")) {
                return $this->actionComplete("less_rights");
            }

            if (is_a($this->modelInst(), "DataObjectSet")) {
                $toDelete = $this->modelInst()->first();
            } else {
                $toDelete = $this->modelInst();
            }

            // generate description for data to delete
            $description = $toDelete->generateRepresentation(false);
            if (isset($description))
                $description = '<a href="' . $this->namespace . '/edit/' . $toDelete->id . URLEND . '" target="_blank">' . $description . '</a>';

            if ($this->confirm(lang("delete_confirm", "Do you really want to delete this record?"), null, null, $description)) {

                $data = clone $toDelete;
                $toDelete->remove();
                if ($this->getRequest()->isJSResponse() || isset($this->getRequest()->get_params["dropdownDialog"])) {
                    $response = new AjaxResponse();
                    if ($object !== null)
                        $data = $object->hideDeletedObject($response, $data);
                    else
                        $data = $this->hideDeletedObject($response, $data);

                    if (is_object($data))
                        $data = $data->render();

                    HTTPResponse::setBody($data);
                    HTTPResponse::output();
                    exit;
                } else {
                    return $this->actionComplete("delete_success", $data);
                }
            }
        } else {
            if (preg_match('/^[0-9]+$/', $this->getParam("id"))) {
                $model = DataObject::get_one($this->model(), array_merge($this->where, array("id" => $this->getParam("id"))));
                if ($model) {
                    return $model->controller(clone $this)->delete();
                } else {
                    return false;
                }
            } else {
                log_error("Warning: Param ID for Action delete is not an integer: " . print_r($this->request, true));
                return $this->redirectBack();
            }
        }
    }

    /**
     * hides the deleted object
     *
     * @name hideDeletedObject
     * @access public
     */
    public function hideDeletedObject($response, $data)
    {
        $response->exec("location.reload();");
        return $response;
    }

    /**
     * Alias for Controller::submit_form.
     *
     * @param array $data
     * @param Form $form
     * @param gObject $controller
     * @param bool $overrideCreated
     * @param int $priority
     * @param string $action
     * @return string
     * @throws Exception
     */
    public function safe($data, $form = null, $controller = null, $overrideCreated = false, $priority = 1, $action = 'save_success')
    {
        $givenModel = isset($form) ? $form->model : null;
        if ($model = $this->save($data, $priority, false, false, $overrideCreated, $givenModel) !== false) {
            return $this->actionComplete($action, $model);
        } else {
            throw new Exception('Could not save data');
        }
    }

    /**
     * saves data to database and marks the record as draft if versions are enabled.
     *
     * Saves data to the database. It decides if to create a new record or not whether an id is set or not.
     * It marks the record as draft if versions are enabled on this model.
     *
     * @access    public
     * @param    array $data
     * @param Form $form
     * @param gObject $controller
     * @param bool $overrideCreated
     * @return string
     * @throws Exception
     */
    public function submit_form($data, $form = null, $controller = null, $overrideCreated = false)
    {
        return $this->safe($data, $form, $controller, $overrideCreated);
    }

    /**
     * global save method for the database.
     *
     * it saves data to the database. you can define which priority should be selected and if permissions are relevant.
     *
     * @access    public
     * @param    array $data data
     * @param    integer $priority Defines what type of save it is: 0 = autosave, 1 = save, 2 = publish
     * @param    boolean $forceInsert forces the database to insert a new record of this data and neglect permissions
     * @param    boolean $forceWrite forces the database to write without involving permissions
     * @return bool|DataObject
     */
    public function save($data, $priority = 1, $forceInsert = false, $forceWrite = false, $overrideCreated = false, DataObject $givenModel = null)
    {
        if (PROFILE) Profiler::mark("Controller::save");

        $this->callExtending("onBeforeSave", $data, $priority);

        /** @var DataObject $model */
        $model = $this->getSafableModel($data, $givenModel);

        $model->writeToDB($forceInsert, $forceWrite, $priority, false, true, false, $overrideCreated);

        $this->callExtending("onAfterSave", $model, $priority);

        if (!isset($givenModel)) {
            $this->model_inst = $model;
            $model->controller = clone $this;
        }

        if (PROFILE) Profiler::unmark("Controller::save");

        return $model;
    }

    /**
     * returns a model which is writable with given data and optional given model.
     * if no model was given, an instance of the controlled model is generated.
     *
     * @param    array|gObject $data Data or Object of Data
     * @param    ViewAccessableData $givenModel
     * @return ViewAccessableData
     */
    public function getSafableModel($data, ViewAccessableData $givenModel = null)
    {
        $model = isset($givenModel) ? $givenModel->_clone() : $this->modelInst()->_clone();

        if(isset($data["class_name"])) {
            if(!ClassManifest::classesRelated($data["class_name"], $model)) {
                $model = gObject::instance($data["class_name"]);
            }
        }

        if (is_object($data) && is_subclass_of($data, "ViewaccessableData")) {
            $data = $data->ToArray();
        }

        foreach ($data as $key => $value) {
            $model->$key = $value;
        }

        return $model;
    }

    /**
     * saves data to database and marks the record published.
     *
     * Saves data to the database. It decides if to create a new record or not whether an id is set or not.
     * It marks the record as published.
     *
     * @access    public
     * @param    array $data
     * @param Form $form
     * @param null $controller
     * @param bool $overrideCreated
     * @return string
     * @throws Exception
     */
    public function publish($data, $form = null, $controller = null, $overrideCreated = false)
    {
        return $this->safe($data, $form, $controller, $overrideCreated, 2, 'publish_success');
    }

    /**
     * this is the method, which is called when a action was completed successfully or not.
     *
     * it is called when actions of this controller are completed and the user should be notified. For example if the user saves data and it was successfully saved, this method is called with the param save_success. It is also called if an error occurs.
     *
     * @param    string $action the action called
     * @param    gObject $record optional: record if available
     * @access    public
     * @return string
     */
    public function actionComplete($action, $record = null)
    {
        switch ($action) {
            case "publish_success":
                AddContent::addSuccess(lang("successful_published", "The entry was successfully published."));
                return $this->redirectback();
            case "save_success":
                AddContent::addSuccess(lang("successful_saved", "The data was successfully saved."));
                return $this->redirectback();
            case "less_rights":
                return '<div class="error">' . lang("less_rights", "You are not allowed to visit this page or perform this action.") . '</div>';
            case "delete_success":
                return $this->redirectback();
        }

        throw new InvalidArgumentException("Action $action not supported by actionComplete.");
    }

    /**
     * redirects back to the page before based on some information by the user.
     *
     * it detects redirect-params with GET and POST-Vars. It uses the Referer and as a last instance it redirects to homepage.
     * you can define params to add to the redirect if you want.
     *
     * @access    public
     * @param    string $param get-parameter
     * @param    string $value value of the get-parameter
     * @return GomaResponse
     */
    public function redirectback($param = null, $value = null)
    {

        if (isset($this->request->get_params["redirect"])) {
            $redirect = $this->request->get_params["redirect"];
        } else if (isset($this->request->post_params["redirect"])) {
            $redirect = $this->request->post_params["redirect"];
        } else {
            $redirect = BASE_URI . BASE_SCRIPT . $this->originalNamespace;
        }

        if (isset($param) && isset($value))
            $redirect = self::addParamToURL($redirect, $param, $value);

        return GomaResponse::redirect($redirect);
    }

    /**
     * asks the user if he want's to do sth
     *
     * @name confirm
     * @access public
     * @param string - question
     * @param string - title of the okay-button, if you want to set it, default: "yes"
     * @param string|object|null - redirect on cancel button
     * @return bool
     */
    public function confirm($title, $btnokay = null, $redirectOnCancel = null, $description = null)
    {
        $form = new RequestForm($this, array(
            new HTMLField("confirm", '<div class="text">' . $title . '</div>')
        ), lang("confirm", "Confirm..."), md5("confirm_" . $title . $this->classname), array(), ($btnokay === null) ? lang("yes") : $btnokay, $redirectOnCancel);

        if (isset($description)) {
            if(is_object($description)) {
                if(gObject::method_exists($description, "generateRepresentation")) {
                    /** @var DataObject $description */
                    $description = $description->generateRepresentation(true);
                } else {
                    throw new LogicException("Description-Object must have generateRepresentation-Method.");
                }
            }

            $form->add(new HTMLField("description", '<div class="confirmDescription">' . $description . '</div>'));
        }
        $form->get();
        return true;

    }

    /**
     * prompts the user
     *
     * @name prompt
     * @param string - message
     * @param array - validators
     * @param string - default value
     * @param string|null - redirect on cancel button
     */
    public function prompt($title, $validators = array(), $value = null, $redirectOnCancel = null, $usePwdField = null)
    {
        $field = ($usePwdField) ? new PasswordField("prompt_text", $title, $value) : new TextField("prompt_text", $title, $value);
        $form = new RequestForm($this, array(
            $field
        ), lang("prompt", "Insert Text..."), md5("prompt_" . $title . $this->classname), $validators, null, $redirectOnCancel);
        $data = $form->get();
        return $data["prompt_text"];
    }

    /**
     * keychain
     */

    /**
     * adds a password to the keychain
     *
     * @name keyChainAdd
     * @access public
     * @param string - password
     * @param bool - use cookie
     * @param int - cookie-livetime
     */
    public static function keyChainAdd($password, $cookie = null, $cookielt = null)
    {
        if (!isset($cookie)) {
            $cookie = false;
        }

        if (!isset($cookielt)) {
            $cookielt = 14 * 24 * 60 * 60;
        }

        $keychain = self::getCurrentKeychain();
        $keychain[] = $password;

        GlobalSessionManager::globalSession()->set(self::SESSION_KEYCHAIN, $keychain);

        if ($cookie) {
            setCookie("keychain_" . md5(md5($password)), md5($password), NOW + $cookielt);
        }
    }

    /**
     * checks if a password is in keychain
     *
     * @name keyChainCheck
     * @access public
     * @return bool
     */
    public static function KeyChainCheck($password)
    {
        $keychain = self::getCurrentKeychain();
        if ((in_array($password, $keychain)) ||
            (
                isset($_COOKIE["keychain_" . md5(md5($password))]) &&
                $_COOKIE["keychain_" . md5(md5($password))] == md5($password)
            ) ||
            isset($_GET[getPrivateKey()])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * removes a password from keychain
     *
     * @name keyChainRemove
     * @access public
     */
    public static function keyChainRemove($password)
    {
        $keychain = self::getCurrentKeychain();

        if ($key = array_search($password, $keychain)) {
            unset($keychain[$key]);
        }

        GlobalSessionManager::globalSession()->set(self::SESSION_KEYCHAIN, $keychain);

        setCookie("keychain_" . md5(md5($password)), null, -1);
    }

    /**
     * returns current keychain-array.
     */
    protected static function getCurrentKeychain() {
        if(GlobalSessionManager::globalSession()->hasKey(self::SESSION_KEYCHAIN)) {
            return GlobalSessionManager::globalSession()->get(self::SESSION_KEYCHAIN);
        }

        return array();
    }

    /**
     * adds a get-param to the query-string of given url.
     *
     * @param string $url
     * @param string $param
     * @param string $value
     * @return string
     */
    public static function addParamToUrl($url, $param, $value)
    {
        if (!strpos($url, "?")) {
            $modified = $url . "?" . $param . "=" . urlencode($value);
        } else {
            $url = preg_replace('/' . preg_quote($param, "/") . '\=([^\&]+)\&/Usi', "", $url);
            $url = preg_replace('/' . preg_quote($param, "/") . '\=([^\&]+)$/Usi', "", $url);
            $modified = str_replace(array("?&", "&&"), array('?', "&"), $url . "&" . $param . "=" . urlencode($value));
        }

        return convert::raw2text($modified);
    }
}
