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
     * showform if no edit right
     *
     * @var bool
     * @default false
     */
    public static $showWithoutRight = false;

    /**
     * activates the live-counter on this controller
     *
     * @var bool
     */
    protected static $live_counter = false;

    /**
     * how much data is on one page?
     */
    public $perPage = null;

    /**
     * defines whether to use pages or not
     *
     * @var bool
     */
    public $pages = false;

    /**
     * defines which model is used for this controller
     *
     * @var bool|string
     */
    public $model = null;

    /**
     * instance of the model
     *
     * @var ViewAccessableData
     */
    public $model_inst = false;

    /**
     * allowed actions
     */
    public $allowed_actions = array(
        "edit",
        "delete",
        "record",
        "version"
    );

    /**
     * template for this controller
     */
    public $template = "";

    /**
     * some vars for the template
     */
    public $tplVars = array();

    /**
     * url-handlers
     */
    public $url_handlers = array(
        '$Action/$id' => '$Action',
    );

    /**
     * @var Keychain
     */
    protected $keychain;

    /**
     * Controller constructor.
     * @param KeyChain|null $keychain
     */
    public function __construct($keychain = null)
    {
        parent::__construct();

        $this->keychain = $keychain;
    }

    /**
     * @param ViewAccessableData $model
     * @return static
     */
    public static function InitWithModel($model) {
        $controller = new static();
        $controller->setModelInst($model);

        return $controller;
    }

    /**
     * inits the controller:
     * - determining and loading model
     * - checking template
     * @param Request $request
     */
    public function Init($request = null)
    {
        parent::Init($request);

        if ($this->template == "") {
            $this->template = $this->model() . ".html";
        }

        if(!$this->subController) {
            if (static::$live_counter) {
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
     * @param ViewAccessableData $model
     * @param bool $name
     * @return $this
     */
    public function setModelInst($model, $name = false)
    {
        if(!is_a($model, "ViewAccessableData")) {
            throw new InvalidArgumentException("Argument must be type of ViewAccessableData.");
        }

        $this->model_inst = $model;
        $this->model = ($name !== false) ? $name : $model->DataClass();

        return $this;
    }

    /**
     * returns the model-object
     *
     * @param ViewAccessableData|string|null $model
     * @return ViewAccessableData|IDataSet
     */
    public function modelInst($model = null)
    {
        if (is_object($model) && is_a($model, "ViewAccessableData")) {
            $this->model_inst = $model;
            $this->model = $model->DataClass();
            return $this->model_inst;
        }

        if(!$this->createDefaultSetFromModel($model)) {
            if(!is_object($this->model_inst)) {
                $this->createDefaultSetFromModel($this->model) ||
                $this->createDefaultSetFromModel(substr($this->classname, 0, -10)) ||
                $this->createDefaultSetFromModel(substr($this->classname, 0, -11));
            } else {
                $this->model = $this->model_inst->DataClass();
            }
        }

        return (is_object($this->model_inst)) ? $this->model_inst : new ViewAccessAbleData();
    }

    /**
     * @param string $model
     * @return bool
     */
    public function createDefaultSetFromModel($model) {
        if(isset($model) && ClassInfo::exists($model)) {
            if(ClassInfo::hasInterface($model, "IDataObjectSetDataSource")) {
                $this->model_inst = DataObject::get($model);
                $this->model = $model;
                return true;
            } else if(is_subclass_of($model, "ViewAccessableData")) {
                $this->model_inst = gObject::instance($model);
                $this->model = $model;
                return true;
            }
        }
        return false;
    }

    /**
     * returns the controller-model
     *
     * @param string|null $model
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
                $this->model = $this->model_inst->DataClass();
            }
        }

        return $this->model;
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
        /** @var ControllerRedirectBackResponse $content */
        if(is_a($content, "ControllerRedirectBackResponse")) {
            if($content->getFromUrl() != $this->namespace && !$content->getHintUrl()) {
                $content->setHintUrl($this->namespace);
                $content->setParentControllerResolved(true);
            }
        }

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
     * gets this class with new model inst.
     * @param ViewAccessableData $model
     * @return Controller
     */
    public function getWithModel($model) {
        $class = clone $this;
        $class->model_inst = $model;
        $class->model = $model->DataClass();

        return $class;
    }

    /**
     * handles a request with a given record in it's controller
     *
     * @return string|false
     */
    public function record()
    {
        if (is_a($this->modelInst(), "IDataSet")) {
            $data = clone $this->modelInst();
            $data->addFilter(array("id" => $this->getParam("id")));
            $this->callExtending("decorateRecord", $model);
            $this->decorateRecord($data);
            if ($data->first() != null) {
                return $this->getWithModel($data->first())->handleRequest($this->request);
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
     * @return mixed|string
     */
    public function version()
    {
        if (is_a($this->modelInst(), "IDataSet")) {
            $data = clone $this->modelInst();
            $data->addFilter(array("versionid" => $this->getParam("id")));
            $this->callExtending("decorateRecord", $model);
            $this->decorateRecord($data);
            if ($data) {
                return $this->getWithModel($data)->handleRequest($this->request);
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
     * @return string
     */
    public function edit()
    {
        /** @var DataObject $model */
        if($model = $this->getSingleModel()) {
            if (!$model->can("Write")) {
                if (StaticsManager::getStatic($this->classname, "showWithoutRight") || $this->modelInst()->showWithoutRight) {
                    $disabled = true;
                } else {
                    return $this->actionComplete("less_rights");
                }
            } else {
                $disabled = false;
            }

            return $this->form("edit_" . $this->classname . $model->id, $model, array(), true, "safe", $disabled);
        }
    }

    /**
     * delete-function
     * this delete-function also implements ajax-functions
     *
     * @return bool|string
     */
    public function delete()
    {
        if($model = $this->getSingleModel()) {
            if(!$model->can("Delete")) {
                return $this->actionComplete("less_rights");
            }

            $description = $this->generateRepresentation($model);

            if ($this->confirm(lang("delete_confirm", "Do you really want to delete this record?"), null, null, $description)) {
                $preservedModel = clone $model;
                $model->remove();
                if ($this->getRequest()->isJSResponse() || isset($this->getRequest()->get_params["dropdownDialog"])) {
                    $response = new AjaxResponse();
                    $data = $this->hideDeletedObject($response, $preservedModel);

                    return $data;
                } else {
                    return $this->actionComplete("delete_success", $preservedModel);
                }
            }
        }
    }

    /**
     * finds single model if set or by id.
     *
     * @return DataObject|ViewAccessableData|null
     */
    protected function getSingleModel() {
        if(is_a($this->modelInst(), "IDataSet")) {
            return $this->getParam("id") ? $this->modelInst()->find("id", $this->getParam("id")) : null;
        } else {
            return $this->modelInst();
        }
    }

    /**
     * @param DataObject $model
     * @param bool $link
     * @return string
     */
    protected function generateRepresentation($model, $link = false) {
        $description = $model->generateRepresentation($link);

        // find link.
        if(!preg_match('/<a\s+/i', $description)) {
            $link = false;
        }

        if(!$link) {
            if ($this->modelInst() == $model) {
                return '<a href="' . $this->namespace . '/edit' . URLEND . '">' . $description . '</a>';
            }

            if (is_a($this->modelInst(), "DataObjectSet")) {
                if ($this->modelInst()->find("id", $model->id)) {
                    return '<a href="' . $this->namespace . '/edit/' . $model->id . URLEND . '">' . $description . '</a>';
                }
            }
        }

        return $description;
    }

    /**
     * hides the deleted object
     * @param AjaxResponse $response
     * @param array $data
     * @return AjaxResponse
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
        $givenModel = isset($form) ? $form->getModel() : null;
        if (($model = $this->save($data, $priority, false, false, $overrideCreated, $givenModel)) !== false) {
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
     * @param    array $data data
     * @param    integer $priority Defines what type of save it is: 0 = autosave, 1 = save, 2 = publish
     * @param    boolean $forceInsert forces the database to insert a new record of this data and neglect permissions
     * @param    boolean $forceWrite forces the database to write without involving permissions
     * @param bool $overrideCreated
     * @param null|DataObject $givenModel
     * @return bool|DataObject
     */
    public function save($data, $priority = 1, $forceInsert = false, $forceWrite = false, $overrideCreated = false, $givenModel = null)
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
     * @return ControllerRedirectBackResponse
     */
    public function redirectback($param = null, $value = null)
    {
        if (isset($this->request->get_params["redirect"])) {
            $redirect = $this->request->get_params["redirect"];
        } else if (isset($this->request->post_params["redirect"])) {
            $redirect = $this->request->post_params["redirect"];
        } else {
            $redirect = null;
        }

        return ControllerRedirectBackResponse::create(
            $redirect,
            $this->request ? $this->request->getShiftedPart() : null,
            $this->request ? $this->request->canReplyJavaScript() : false
        )->setParam($param, $value);
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
     * @deprecated
     */
    public function confirm($title, $btnokay = null, $redirectOnCancel = null, $description = null)
    {
        $data = $this->confirmByForm($title, function() {
            return true;
        }, function() use($redirectOnCancel) {
            if($redirectOnCancel) {
                return GomaResponse::redirect($redirectOnCancel);
            }

            return false;
        }, $btnokay, $description);
        if(!is_bool($data->getRawBody())) {
            Director::serve($data);
            exit;
        }

        return $data->getRawBody();
    }

    /**
     * @param string $title
     * @param Callable $successCallback
     * @param Callable $errorCallback
     * @param null $btnokay
     * @param null $description
     * @return GomaFormResponse
     */
    public function confirmByForm($title, $successCallback, $errorCallback = null, $btnokay = null, $description = null) {
        $form = new ConfirmationForm($this, "confirm_" . $this->classname, array(
            new HTMLField("confirm", '<div class="text">' . $title . '</div>')
        ), array(
            $cancel = new FormAction("cancel", lang("cancel"), array($this, "_confirmCancel")),
            new FormAction("save", $btnokay ? $btnokay : lang("yes"))
        ));
        $form->setSubmission(array($this, "_confirmSuccess"));
        $cancel->setSubmitWithoutData(true);

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

        self::$successCallback = $successCallback;
        self::$errorCallback = $errorCallback;

        $data = $form->render();
        $data->addRenderFunction(
            function($data){
                /** @var GomaFormResponse $data */
                if($data->shouldServe()) {
                    $data->setBodyString($this->showWithDialog($data->getResponseBodyString(), lang("confirm", "Confirm...")));
                }
            });
        return $data;
    }

    private static $errorCallback;
    private static $successCallback;


    /**
     * @internal
     * @return bool
     */
    public function _confirmSuccess() {
        return call_user_func_array(self::$successCallback, array());
    }

    /**
     * @internal
     * @return bool
     */
    public function _confirmCancel() {
        return self::$errorCallback ? call_user_func_array(self::$errorCallback, array()) : false;
    }

    /**
     * prompts the user
     *
     * @param $messsage
     * @param array $validators
     * @param string $defaultValue
     * @param null|bool $redirectOnCancel
     * @param null|bool $usePwdField
     * @return RequestForm
     * @deprecated
     */
    public function prompt($messsage, $validators = array(), $defaultValue = null, $redirectOnCancel = null, $usePwdField = null)
    {
        $data = $this->promptByForm($messsage, function($text) {
            return array($text);
        }, function() use($redirectOnCancel) {
            if($redirectOnCancel) {
                return GomaResponse::redirect($redirectOnCancel);
            }

            return false;
        }, $defaultValue, $validators, $usePwdField);
        if(is_array($data)) {
            return $data[0];
        }

        if(!is_bool($data->getRawBody())) {
            Director::serve($data);
            exit;
        }
        return $data->getRawBody();
    }

    private static $successPromptCallback;

    /**
     * @param $message
     * @param $successCallback
     * @param $errorCallback
     * @param null $defaultValue
     * @param array $validators
     * @param bool $usePwdField
     * @return GomaFormResponse
     */
    public function promptByForm($message, $successCallback, $errorCallback = null, $defaultValue = null, $validators = array(), $usePwdField = false) {
        $field = ($usePwdField) ? new PasswordField("prompt_text", $message, $defaultValue) :
            new TextField("prompt_text", $message, $defaultValue);
        $form = new ConfirmationForm($this, "prompt_" . $this->classname, array(
            $field
        ), array(
            $cancel = new FormAction("cancel", lang("cancel"), array($this, "_confirmCancel")),
            new FormAction("save", lang("ok"))
        ), $validators);
        $cancel->setSubmitWithoutData(true);
        $form->setSubmission(array($this, "_promptSuccess"));

        self::$successPromptCallback = $successCallback;
        self::$errorCallback = $errorCallback;

        $data = $form->render();
        $data->addRenderFunction(
            function($data){
                /** @var GomaFormResponse $data */
                if($data->shouldServe()) {
                    $data->setBodyString($this->showWithDialog($data->getResponseBodyString(), lang("prompt", "Insert Text...")));
                }
        });
        return $data;
    }

    /**
     * catches problem when $data is not a string.
     *
     * @param string|object $data
     * @param string $title
     * @return string
     */
    protected function showWithDialog($data, $title) {
        if(!is_string($data)) {
            return $data;
        }

        $view = new ViewAccessableData();
        return $view->customise(
            array("content" => $data, "title" => $title)
        )->renderWith("framework/dialog.html");
    }

    /**
     * @internal
     * @param array $data
     * @return mixed
     */
    public function _promptSuccess($data) {
        return call_user_func_array(self::$successPromptCallback, array($data["prompt_text"]));
    }

    /**
     * keychain
     */

    /**
     * adds a password to the keychain
     *
     * @deprecated
     * @param string $password
     * @param null $cookie
     * @param null $cookielt
     */
    public static function keyChainAdd($password, $cookie = null, $cookielt = null)
    {
        Core::Deprecate(2.0, "keychain()->add");
        Keychain::sharedInstance()->add($password);
    }

    /**
     * checks if a password is in keychain
     *
     * @deprecated
     * @param string $password
     * @return bool
     */
    public static function KeyChainCheck($password)
    {
        Core::Deprecate(2.0, "keychain()->check");
        return Keychain::sharedInstance()->check($password);
    }

    /**
     * removes a password from keychain
     *
     * @deprecated
     * @param string $password
     */
    public static function keyChainRemove($password)
    {
        Core::Deprecate(2.0, "keychain()->remove");
        Keychain::sharedInstance()->remove($password);
    }

    /**
     * @return Keychain
     */
    public function keychain() {
        return isset($this->keychain) ? $this->keychain : Keychain::sharedInstance();
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
