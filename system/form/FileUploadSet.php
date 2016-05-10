<?php defined("IN_GOMA") OR die();

/**
 * A file upload set.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.0
 *
 * @property DataObjectSet $value
 */
class FileUploadSet extends FormField
{
    /**
     * url-handlers
     *
     * @name url_handlers
     * @access public
     */
    public $url_handlers = array(
        "ajaxUpload" => "ajaxUpload",
        "frameUpload" => "frameUpload",
        "POST remove/\$id" => "removeFile"
    );

    /**
     * actions
     *
     * @name allowed_actions
     * @access public
     */
    public $allowed_actions = array(
        "ajaxUpload",
        "frameUpload",
        "removeFile",
        "saveSort"
    );

    /**
     * all allowed file-extensions
     * @name allowed_file_types
     * @access public
     */
    public $allowed_file_types = array(
        "jpg",
        "png",
        "bmp",
        "jpeg",
        "zip",
        "rar",
        "doc",
        "txt",
        "text",
        "pdf",
        "dmg",
        "7z",
        "gif",
        "mp3",
        "xls"
    );

    /**
     * max filesize
     *
     * @var int
     */
    public $max_filesize = 10485760; // 10 Mib

    /**
     * collection
     *
     * @var string
     */
    public $collection = "FormUploadSet";

    /**
     * upload-class
     *
     * @var string
     */
    protected $uploadClass = "Uploads";

    /**
     * unique key of this dataset
     *
     * @var string
     */
    protected $key;

    /**
     * defines whether set a link to the file or not
     *
     * @var bool
     */
    public $link = true;

    /**
     * default-icon.
     */
    protected $defaultIcon = "images/icons/goma/128x128/file.png";

    /**
     * this field needs to have the full width
     *
     * @var bool
     */
    protected $fullSizedField = true;

    /**
     * template-view.
     */
    protected $template = "form/FileUploadSet.html";

    /**
     * @var ViewAccessableData
     */
    protected $templateView;

    /**
     * used for internal sort-function.
     */
    private $sortInfo;

    /**
     * @param string $name
     * @param string $title
     * @param array|null $file_types
     * @param DataObjectSet|null $value
     * @param string|null $collection
     * @param null $parent
     * @return static
     */
    public static function create($name, $title, $file_types = null, $value = null, $collection = null, $parent = null)
    {
        return new static($name, $title, $file_types, $value, $collection, $parent);
    }

    /**
     * @param string|null $name
     * @param string|null $title
     * @param array|null $file_types
     * @param string $value
     * @param null $collection
     * @param null $form
     */
    public function __construct($name = null, $title = null, $file_types = null, $value = "", $collection = null, &$form = null)
    {
        parent::__construct($name, $title, $value, $form);

        if (isset($file_types) && (is_array($file_types) || $file_types == "*"))
            $this->allowed_file_types = $file_types;

        if (isset($collection))
            $this->collection = $collection;

        $this->templateView = new ViewAccessableData();
    }

    /**
     * handles the request and saves the data to the session
     *
     * @param Request $request
     * @param bool $subController
     * @return false|string
     */
    public function handleRequest($request, $subController = false)
    {
        $data = parent::handleRequest($request, $subController);
        $this->storeData();
        return $data;
    }

    /**
     * stores the data
     */
    public function storeData()
    {
        Core::globalSession()->set("FileUploadSet_" . $this->key, $this->value);
        $this->Form()->saveToSession();
    }

    /**
     * gets the current value
     */
    public function getValue()
    {
        if(!isset($this->value)) {
            if (is_a($this->form()->result, "DataObject")) {
                /** @var DataObject $object */
                $object = $this->form()->result;
                $relationShip = $object->getManyManyInfo($this->name);
                $this->uploadClass = $relationShip->getTargetClass();
            }

            if (isset($this->form()->post[$this->PostName() . "__key"]) && Core::globalSession()->hasKey("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"])) {
                $this->value = Core::globalSession()->get("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"]);
                $this->key = $this->form()->post[$this->PostName() . "__key"];
            } else {
                if (!isset($this->uploadClass)) {
                    throw new LogicException("FileUploadSet only works with DataObjects and corresponding Many-Many-Connections.");
                }

                if (isset($this->form()->result->{$this->name})) {
                    $this->key = randomString(10);
                    $this->value = $this->form()->result->{$this->name};
                    if (!is_object($this->value)) {
                        throw new LogicException("ManyMany-Connection did not return Object.");
                    }
                } else {
                    $this->value = new ManyMany_DataObjectSet($this->uploadClass);
                    $this->value->setFetchMode(DataObjectSet::FETCH_MODE_CREATE_NEW);
                }
            }
        }

        if(!is_a($this->value, "DataObjectSet")) {
            throw new InvalidArgumentException("FileUploadSet requires DataObjectSet as source.");
        }

        $this->checkForEvents();
    }

    /**
     * checks if user wants to change something.
     */
    protected function checkForEvents() {
        if (isset($this->request->post_params[$this->PostName() . "_upload"]) && !empty($this->request->post_params[$this->PostName() . "_upload"]["name"])) {
            $response = $this->handleUpload($this->request->post_params[$this->PostName() . "_upload"]);
            if ($response === false) {
                AddContent::addNotice(lang("files.upload_failure"));
            } else if (is_string($response)) {
                AddContent::addNotice($response);
            } else {
                AddContent::addSuccess(lang("files.upload_success"));
            }
        }

        /** @var DataObject $record */
        foreach ($this->value as $record) {
            if (isset($this->request->post_params[$this->PostName() . "__delete_" . $record->id])) {
                if(is_a($this->value, "RemoveStagingDataObjectSet")) {
                    /** @var ManyMany_DataObjectSet $manyManySet */
                    $manyManySet = $this->value;
                    $manyManySet->removeFromSet($record);
                } else {
                    $this->value->removeFromStage($record);
                }
            }
        }
    }

    /**
     * ajax upload
     */
    public function ajaxUpload()
    {
        if ($this->allowed_file_types == "*" || preg_match('/\.(' . implode("|", $this->allowed_file_types) . ')$/i', $this->request->getHeader("x-file-name"))) {
            if ($this->request->inputStreamFile()) {
                $tmp_name = $this->request->inputStreamFile();

                if (filesize($tmp_name) != $this->request->getHeader("x-file-size")) {
                    return $this->sendJSONError(lang("files.upload_failure"));
                }
            } else {
                return $this->sendJSONError(lang("files.upload_failure"));
            }

            $upload = array(
                "name" => $this->request->getHeader("x-file-name"),
                "size" => $this->request->getHeader("x-file-size"),
                "error" => 0,
                "tmp_name" => $tmp_name
            );
            $response = $this->handleUpload($upload);
            // clean up
            if (isset($tmp_name))
                @unlink($tmp_name);

            /** @var Uploads $response */
            if (is_object($response)) {
                return new JSONResponseBody(array("status" => 1, "file" => $this->getFileArray($response)));
            } else if (is_string($response)) {
                return $this->sendJSONError($response);
            } else {
                return $this->sendJSONError(lang("files.upload_failure"));
            }
        } else {
            return $this->sendJSONError(lang("files.filetype_failure", "The filetype isn't allowed."));
        }
    }

    /**
     * gets file data from uploads object.
     * @param Uploads $file
     * @return array
     */
    protected function getFileArray($file) {
        return array(
            "name" => $file->filename,
            "realpath" => $this->link ? $file->fieldGet("path") : null,
            "icon128" => $file->getIcon(128),
            "icon16" => $file->getIcon(16),
            "icon32" => $file->getIcon(32),
            "icon64" => $file->getIcon(64),
            "path" => $this->link ? $file->path : null,
            "id" => $file->id
        );
    }

    /**
     * sends error to client.
     *
     * @param string $error
     * @param bool $sendContentType
     * @return JSONResponseBody
     */
    protected function sendJSONError($error, $sendContentType = true) {
        return new JSONResponseBody(array("status" => 0, "errstring" => $error));
    }

    /**
     * frame upload
     *
     * @name frameUpload
     * @access public
     * @return string
     */
    public function frameUpload()
    {
        if (isset($this->request->post_params["file"])) {
            if (is_array($this->request->post_params["file"]["name"])) {
                $files = $this->handleUpload($this->request->post_params["file"]);
                $filedata = array();
                /** @var Uploads $data */
                foreach ($files as $data) {
                    $filedata[] = $this->getFileArray($data);
                }

                return new JSONResponseBody(array("status" => 1, "multiple" => true, "files" => $filedata));
            } else {
                $response = $this->handleUpload($this->request->post_params["file"]);
                /** @var Uploads $response */
                if (is_object($response)) {
                    return new JSONResponseBody(array("status" => 1, "file" => $this->getFileArray($response)));
                } else if (is_string($response)) {
                    return $this->sendJSONError($response, false);
                } else {
                    return $this->sendJSONError(lang("files.upload_failure"), false);
                }
            }
        } else {
            return $this->sendJSONError(lang("files.upload_failure"), false);
        }
    }

    /**
     * removes a file from list
     *
     * @return bool
     */
    public function removeFile()
    {
        $id = $this->getParam("id");
        /** @var DataObject $record */
        foreach ($this->value as $record) {
            if ($record->id == $id) {
                if(is_a($this->value, "RemoveStagingDataObjectSet")) {
                    /** @var ManyMany_DataObjectSet $manyManySet */
                    $manyManySet = $this->value;
                    $manyManySet->removeFromSet($record);
                } else {
                    $this->value->removeFromStage($record);
                }
            }
        }

        return true;
    }

    /**
     * handles the upload(s)
     * @param array $upload
     * @return array|bool|string
     */
    public function handleUpload($upload)
    {
        if (!isset($upload["name"])) {
            return "No Upload defined.";
        }

        // if are more than one file are given ;)
        if (is_array($upload["name"])) {
            // we make a error-stack
            $errStack = array();
            $fileStack = array();
            foreach ($upload["name"] as $key => $name) {

                if (GOMA_FREE_SPACE - $upload["size"][$key] < 10 * 1024 * 1024) {
                    $errStack[] = lang("error_disk_space");
                }

                if ($this->max_filesize == -1 || $upload["size"][$key] <= $this->max_filesize) {
                    $ext = strtolower(substr($name, strrpos($name, ".") + 1));
                    if ($this->allowed_file_types == "*" || in_array($ext, $this->allowed_file_types)) {
                        $name = preg_replace('/[^a-zA-Z0-9_\-\.]/i', '_', $name);
                        if ($data = call_user_func_array(array($this->uploadClass, "addFile"), array($name, $upload["tmp_name"][$key], $this->collection, $this->uploadClass))) {
                            $this->value->add($data);
                            $fileStack[] = $data;
                        } else {
                            $errStack[] = lang("files.upload_failure") . "(" . convert::raw2text($name) . ")";
                        }
                    } else {
                        // not right filetype
                        $errStack[] = lang("files.filetype_failure", "The filetype isn't allowed.") . "(" . convert::raw2text($name) . ")";
                    }
                } else {
                    // file is too big
                    $errStack[] = lang('files.filesize_failure', "The file is too big.") . "(" . convert::raw2text($name) . ")";
                }
            }
            if (count($errStack) == 0) {
                $this->storeData();
                return $fileStack;
            } else {
                $this->storeData();
                return '<ul>
					<li>' . implode('</li><li>', $errStack) . '</li>
				</ul>';
            }

            // just one file
        } else {
            if (GOMA_FREE_SPACE - $upload["size"] < 10 * 1024 * 1024) {
                return lang("error_disk_space");
            }

            if ($this->max_filesize == -1 || $upload["size"] <= $this->max_filesize) {
                $name = $upload["name"];
                $ext = strtolower(substr($name, strrpos($name, ".") + 1));
                if ($this->allowed_file_types == "*" || in_array($ext, $this->allowed_file_types)) {
                    $name = preg_replace('/[^a-zA-Z0-9_\-\.]/i', '_', $name);
                    if ($data = call_user_func_array(array($this->uploadClass, "addFile"), array($name, $upload["tmp_name"], $this->collection, $this->uploadClass))) {
                        $this->value->add($data);
                        $this->storeData();
                        return $data;
                    } else {
                        return false;
                    }
                } else {
                    // not right filetype
                    return lang("files.filetype_failure", "The filetype isn't allowed.");
                }
            } else {
                // file is too big
                return lang('files.filesize_failure', "The file is too big.");
            }
        }
    }

    /**
     * saves sort.
     */
    public function saveSort()
    {
        $this->sortInfo = array();
        if (isset($this->request->post_params["sorted"])) {
            foreach ($this->request->post_params["sorted"] as $k => $id) {
                $this->sortInfo[$id] = $k;
            }
        }

        $this->value->sortWithCallback(array($this, "sort"));

        return 1;
    }

    /**
     * sort-function.
     */
    public function sort($a, $b)
    {
        $sortA = isset($this->sortInfo[$a->id]) ? $this->sortInfo[$a->id] : 10000;
        $sortB = isset($this->sortInfo[$b->id]) ? $this->sortInfo[$b->id] : 10000;

        if ($sortA == $sortB) {
            return 0;
        }

        return ($sortA < $sortB) ? -1 : 1;

    }

    /**
     * sets the right enctype for the form
     *
     * @param FileUploadSetRenderData|null $info
     * @return HTMLNode
     */
    public function field($info = null)
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->storeData();

        $this->form()->form->enctype = "multipart/form-data";

        $this->callExtending("beforeField");

        $this->container->append(
            $this->templateView
                ->customise(
                    $info->setIncludeLink($this->link)
                        ->setDefaultIcon($this->defaultIcon)
                        ->setUploads($this->value->ToArray())
                        ->ToRestArray(false, false)
                )->customise(
                    array(
                        "postname" => $this->PostName()
                    )
                )
                ->renderWith($this->template)
        );

        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FormField::field");

        return $this->container;
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        parent::addRenderData($info, $notifyField);

        gloader::load("ajaxupload");
        gloader::load("sortable");
        $info->addJSFile("system/form/FileUploadSet.js");
        $info->addCSSFile("FileUpload.less");
    }

    /**
     * @return string
     */
    public function JS() {
        return "$(function(){new FileUploadSet('" . $this->name . "',$('#" . $this->divID() . " .view'), '" . $this->externalURL() . "');});";
    }

    /**
     * returns a file list
     *
     * @return array
     */
    public function FileList()
    {
        $list = array();
        /** @var Uploads $file */
        foreach ($this->value as $file) {
            $list[$file->id] = $this->getFileArray($file);
        }
        return $list;
    }

    /**
     * @return FileUploadSetRenderData
     */
    public function createsRenderDataClass()
    {
        return new FileUploadSetRenderData($this->name, $this->classname, $this->ID(), $this->divID());
    }

    /**
     * returns the result
     *
     * @return ManyMany_DataObjectSet<Uploads>
     */
    public function result()
    {
        $this->getValue();

        if (isset($this->form()->post[$this->PostName() . "__key"]) && Core::globalSession()->hasKey("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"])) {
            $this->value = Core::globalSession()->get("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"]);
            $this->key = $this->form()->post[$this->PostName() . "__key"];
        }

        $this->checkForEvents();

        return $this->value;
    }

    /**
     * @return ViewAccessableData
     */
    public function getTemplateView()
    {
        return $this->templateView;
    }

    /**
     * @param ViewAccessableData $templateView
     */
    public function setTemplateView($templateView)
    {
        $this->templateView = $templateView;
    }
}
