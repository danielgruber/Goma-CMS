<?php defined("IN_GOMA") OR die();

/**
 * A file upload set.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.0
 *
 * @property DataObjectSet $model
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
     * gets the current value
     */
    public function getValue()
    {
        $this->model = $this->getModel();

        if(!is_a($this->model, DataObjectSet::ID)) {
            throw new InvalidArgumentException("FileUploadSet requires DataObjectSet as model.");
        }
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
        foreach ($this->model as $record) {
            if (isset($this->request->post_params[$this->PostName() . "__delete_" . $record->id])) {
                if(is_a($this->model, "RemoveStagingDataObjectSet")) {
                    /** @var ManyMany_DataObjectSet $manyManySet */
                    $manyManySet = $this->model;
                    $manyManySet->removeFromSet($record);
                } else {
                    $this->model->removeFromStage($record);
                }
            }
        }
    }

    /**
     * ajax upload
     */
    public function ajaxUpload()
    {
        try {
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

            return new JSONResponseBody(array("status" => 1, "file" => $this->getFileArray($this->handleUpload($upload))));
        } catch(Exception $e) {
            return $this->sendJSONError($e->getMessage());
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
     * @return string
     */
    public function frameUpload()
    {
        try {
            if (isset($this->getRequest()->post_params["file"])) {
                if (is_array($this->getRequest()->post_params["file"]["name"])) {
                    $files = $this->handleUpload($this->request->post_params["file"]);
                    $filedata = array();
                    /** @var Uploads $data */
                    foreach ($files as $data) {
                        if (is_a($data, "Uploads")) {
                            $filedata[] = array(
                                "status" => 1,
                                "file"   => $this->getFileArray($data)
                            );
                        } else {
                            $filedata[] = array(
                                "status"    => 0,
                                "errstring" => $data
                            );
                        }
                    }

                    return new JSONResponseBody(array("status" => 1, "multiple" => true, "files" => $filedata));
                } else {
                    $response = $this->handleUpload($this->request->post_params["file"]);
                    /** @var Uploads $response */
                    return new JSONResponseBody(array("status" => 1, "file" => $this->getFileArray($response)));
                }
            } else {
                return $this->sendJSONError(lang("files.upload_failure"), false);
            }
        } catch(Exception $e) {
            return $this->sendJSONError($e->getMessage(), false);
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
        foreach ($this->model as $record) {
            if ($record->id == $id) {
                if(is_a($this->model, RemoveStagingDataObjectSet::ID)) {
                    /** @var ManyMany_DataObjectSet $manyManySet */
                    $manyManySet = $this->model;
                    $manyManySet->removeFromSet($record);
                } else {
                    $this->model->removeFromStage($record);
                }
            }
        }

        return true;
    }

    /**
     * handles the upload(s)
     * @param array $upload
     * @return Uploads|Uploads[]|string[]
     * @throws Exception
     */
    public function handleUpload($upload)
    {
        if (!isset($upload["name"])) {
            throw new Exception(lang("files.upload_failure"));
        }

        // if are more than one file are given ;)
        if (is_array($upload["name"])) {
            // we make a error-stack
            $responseStack = array();
            foreach ($upload["name"] as $key => $name) {
                try {
                    $responseStack[] = $this->handleSingleUpload($upload["name"][$key], $upload["size"][$key], $upload["tmp_name"][$key]);
                } catch(Exception $e) {
                    $responseStack[] = $e->getMessage();
                }
            }

            return $responseStack;
        } else {
            return $this->handleSingleUpload($upload["name"], $upload["size"], $upload["tmp_name"]);
        }
    }

    /**
     * @param string $name
     * @param int $size
     * @param string $tmp_name
     * @return Uploads
     * @throws Exception
     */
    protected function handleSingleUpload($name, $size, $tmp_name) {
        if (GOMA_FREE_SPACE - $size < 10 * 1024 * 1024) {
            throw new Exception(lang("error_disk_space"));
        }

        if($this->max_filesize != -1 && $size > $this->max_filesize) {
            throw new Exception(lang('files.filesize_failure', "The file is too big."));
        }

        $ext = strtolower(substr($name, strrpos($name, ".") + 1));
        if ($this->allowed_file_types != "*" &&
            (
                (is_array($this->allowed_file_types) && !in_array($ext, $this->allowed_file_types)) ||
                (is_string($this->allowed_file_types) && !preg_match('/\.(' . implode("|", $this->allowed_file_types) . ')$/i', $name))
            )
        ) {
            throw new Exception(lang("files.filetype_failure", "The filetype isn't allowed."));
        }

        if ($data = call_user_func_array(array($this->uploadClass, "addFile"), array($name, $tmp_name, $this->collection, $this->uploadClass))) {
            $this->model->add($data);
            return $data;
        } else {
            throw new Exception(lang("files.upload_failure"));
        }
    }

    /**
     * saves sort.
     */
    public function saveSort()
    {
        // TODO: Implement it
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
    public function field($info)
    {
        if (PROFILE) Profiler::mark("FormField::field");

        $this->form()->form->enctype = "multipart/form-data";

        $this->callExtending("beforeField");

        $this->container->append(
            $this->templateView
                ->customise(
                    $info->setIncludeLink($this->link)
                        ->setDefaultIcon($this->defaultIcon)
                        ->setUploads($this->getModel())
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
        foreach ($this->model as $file) {
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

        $this->checkForEvents();

        return $this->model;
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
