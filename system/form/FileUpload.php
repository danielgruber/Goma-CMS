<?php defined("IN_GOMA") OR die();

/**
 * a simple Upload form-field which supports Ajax-Upload + normal Framed upload.
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.2
 */
class FileUpload extends FormField {
	/**
	 * url-handlers. used for controller.
	 *
	 * @name 	url_handlers
	 * @access 	public
	 */
	public $url_handlers = array(
		"ajaxUpload" => "ajaxUpload",
		"frameUpload" => "frameUpload"
	);

	/**
	 * used for controller.
	 *
	 * @name 	allowed_actions
	 * @access 	public
	 */
	public $allowed_actions = array(
		"ajaxUpload",
		"frameUpload"
	);
	/**
	 * all allowed file-extensions for this field.
	 *
	 * @name 	allowed_file_types
	 * @access 	public
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
	 * @name 	max_filesize
	 */
	public $max_filesize = 5242880;
	// 5m = 2097152 = 1024 * 1024 * 2

	/**
	 * collection
	 *
	 * @name 	collection
	 * @access 	public
	 */
	public $collection = "FormUpload";

	/**
	 * upload-class
	 */
	protected $uploadClass = "Uploads";

	/**
	 * container on the left
	 *
	 * @name 	leftContainer
	 * @access 	public
	 */
	public $leftContainer;

	/**
	 * this field needs to have the full width
	 *
	 * @name fullSizedField
	 */
	protected $fullSizedField = true;

	/**
	 * default-icon.
	 */
	protected $default_icon = "images/icons/goma/128x128/file.png";

	/**
	 * @name 	__construct
	 * @access 	public
	 */
	public function __construct($name = null, $title = null, $file_types = null, $value = "", $collection = null, &$form = null) {
		parent::__construct($name, $title, $value, $form);
		if($file_types !== null && (is_array($file_types) || $file_types == "*"))
			$this->allowed_file_types = $file_types;

		if(isset($collection))
			$this->collection = $collection;
	}

	/**
	 * renders the base-structure of the field
	 *
	 * @name 	renderAfterSetForm
	 * @access 	public
	 */
	public function renderAfterSetForm() {
		parent::renderAfterSetForm();

		$this->leftContainer = new HTMLNode("div", array(
			"style" => "float: left;",
			"class" => "FileUpload_left"
		), array(
			new HTMLNode('div', array(
				"class" => "icon",
				"id" => $this->ID() . "_upload"
			), array($link = new HTMLNode("a", array("target" => "_blank"), array(
					new HTMLNode("div", array("class" => "img"), array(new HTMLNode("img", array(
							"src" 			=> $this->getIcon(),
							"data-retina" 	=> $this->getIcon(true),
							"alt" 			=> convert::raw2text($this->value ? $this->value->filename : "")
						)))),
					new HTMLNode("span", array(), convert::raw2text($this->value ? $this->value->filename : ""))
				)))),

			new HTMLNode("input", array(
				"name" => $this->PostName() . "_file",
				"id" => $this->ID() . "_file_hidden",
				"type" => "hidden",
				"value" => $this->value ? $this->value->fieldGet("path") : "",
				"class" => "FileUploadValue"
			))
		));

		if($this->value && $this->value->fieldGet("path") != "") {
			$link->href = $this->value->raw();
		}
	}

	/**
	 * returns icon.
	 * @param bool $retina
	 * @return string
	 */
	public function getIcon($retina = false) {
		return $this->value ? $this->value->getIcon(128, $retina) : $this->default_icon;
	}

	/**
	 * gets the current value
	 */
	public function getValue() {
		parent::getValue();

		if(isset($_FILES[$this->PostName()]) && !empty($_FILES[$this->PostName()]["name"])) {
			if(is_object($value = $this->handleUpload($_FILES[$this->PostName()]))) {
				$this->value = $value;
				return true;
			} else {
				if(is_string($value)) {
					AddContent::addNotice($value);
				} else {
					AddContent::addNotice(lang("files.upload_failure"));
				}
			}
		} else if(isset($this->form()->post[$this->PostName() . "__deletefile"])) {
			$this->value = "";
		} else if(isset($this->form()->post[$this->PostName() . "_file"])) {
			$this->value = $this->form()->post[$this->PostName() . "_file"];
		}

		if(!empty($this->value) && ($data = Uploads::getFile($this->value)) !== false) {
			$this->value = $data;
		} else {
			if(!empty($this->value)) {
				if($data = Uploads::addFile(basename($this->value), $this->value, $this->collection)) {
					$this->value = $data;
					return true;
				}
			}

			$this->value = null;
		}
	}

	/**
	 * ajax upload
	 *
	 * @name 	ajaxUpload
	 * @access 	public
	 */
	public function ajaxUpload() {
		if(!isset($_SERVER["HTTP_X_FILE_NAME"]))
			$_SERVER["HTTP_X_FILE_NAME"] = "";

		if($this->allowed_file_types == "*" || preg_match('/\.(' . implode("|", $this->allowed_file_types) . ')$/i', $_SERVER["HTTP_X_FILE_NAME"])) {
			if(Core::phpInputFile()) {
				$tmp_name = Core::phpInputFile();

				// filesize problem, file has not been uploaded completly or is corrupted.
				if(filesize($tmp_name) != $_SERVER["HTTP_X_FILE_SIZE"]) {
					$this->sendFailureJSON();
				}
			} else {

				// no file given
				$this->sendFailureJSON();
			}

			// prepare upload-information
			$upload = array(
				"name" => $_SERVER["HTTP_X_FILE_NAME"],
				"size" => $_SERVER["HTTP_X_FILE_SIZE"],
				"error" => 0,
				"tmp_name" => $tmp_name
			);
			$response = $this->handleUpload($upload);
			// clean up
			if(isset($tmp_name))
				@unlink($tmp_name);

			/** @var Uploads $response */
			if(is_object($response)) {
				HTTPResponse::setHeader("Content-Type", "text/x-json");
				HTTPResponse::sendHeader();

				echo json_encode(array(
					"status" => 1,
					"file" => $this->getFileResponse($response)
				));
				exit ;
			} else if(is_string($response)) {

				// we got an string error, so send it via JSON.
				$this->sendFailureJSON($response);
			} else {
				$this->sendFailureJSON();
			}
		} else {
			$this->sendFailureJSON(lang("files.filetype_failure", "The filetype isn't allowed."));
		}
	}

	/**
	 * renders response.
	 *
	 * @param Uploads $response
	 * @return array
	 */
	protected function getFileResponse($response) {
		return array(
			"name" => $response->filename,
			"realpath" => $response->fieldGet("path"),
			"icon16" => $response->getIcon(16),
			"path" => $response->path,
			"id" => $response->id,
			"icon128" => $response->getIcon(128),
			"icon128@2x" => $response->getIcon(128, true),
			"icon" => $response->getIcon()
		);
	}

	/**
	 * sends error with optional status-message in JSON-Format and sets JSON-Header.
	*/
	public function sendFailureJSON($error = null) {
		

		HTTPResponse::setHeader("Content-Type", "text/x-json");
		
		$this->printFailureJSON($error);
	}

	/**
	 * prints failure as JSON without JSON-Header.
	 * @param null $error
	 */
	public function printFailureJSON($error = null) {

		HTTPResponse::sendHeader();

		$error = isset($error) ? $error :  lang("files.upload_failure");

		echo json_encode(array(
			"status" => 0,
			"errstring" => $error
		));
		exit;
	}

	/**
	 * frame upload
	 *
	 *@name frameUpload
	 *@access public
	 */
	public function frameUpload() {
		if(isset($_FILES["file"])) {
			$response = $this->handleUpload($_FILES["file"]);
			/** @var Uploads $response */
			if(is_object($response)) {
				HTTPResponse::sendHeader();

				echo json_encode(array(
					"status" => 1,
					"file" => $this->getFileResponse($response)
				));
				exit ;
			} else if(is_string($response)) {
				$this->printFailureJSON($response);
			} else {
				$this->printFailureJSON();
			}
		} else {
			$this->printFailureJSON();
		}
	}

	/**
	 * this shouldn't do anything
	 *@name setValue
	 *@access public
	 */
	public function setValue() {
	}

	/**
	 * creates the file-upload-node
	 */
	public function createNode() {
		$node = parent::createNode();
		$node->type = "file";
		return $node;
	}

	/**
	 * javascript-variable.
	 */
	protected function jsVar() {
		return "fileupload_" . $this->ID();
	}

	/**
	 * sets the right enctype for the form.
	 * renders div.
	 */
	public function field() {
		if(PROFILE)
			Profiler::mark("FormField::field");

		gloader::load("ajaxupload");
		Resources::add("font-awsome/font-awesome.css", "css");
		Resources::add("system/form/FileUpload.js", "js", "tpl");
		Resources::add("FileUpload.less", "css");
		Resources::addJS("$(function(){ window[".var_export($this->jsVar(), true)."] = new FileUpload($('#" . $this->divID() . "'), '" . $this->externalURL() . "', " . var_export($this->max_filesize, true) . ", ".json_encode($this->allowed_file_types).");});");
		// modify form for right datatype
		$this->form()->form->enctype = "multipart/form-data";

		$this->callExtending("beforeField");

		$this->setValue();

		$this->container->append(new HTMLNode("label", array(
			"for" => $this->ID(),
			"style" => "display: block;"
		), $this->title));

		$this->container->append($this->leftContainer);

		$nojs = new HTMLNode("div", array("class" => "FileUpload_right"), array(new HTMLNode("div", array("class" => "wrapper"),
			new HTMLNode('div', array("class" => "actions"), array(
			new HTMLNode("button", array(
				"class" => "button show-on-js delete-file red"
			), new HTMLNode("i", array("class" => "fa fa-trash delete-icon", "type" => "button")))
		))),
			new HTMLNode('div', array("class" => "no-js-fallback"), array(
			new HTMLNode('h3', array(), lang("files.replace")),
			$this->input
		))));

		if($this->value && $this->value->realfile)
			$nojs->append(new HTMLNode('div', array("class" => "delete hide-on-js"), array(
				new HTMLNode('input', array(
					"id" => $this->ID() . "__delete",
					"name" => $this->PostName() . "__deletefile",
					"type" => "checkbox"
				)),
				new HTMLNode('label', array("for" => $this->ID() . "__delete"), lang("files.delete"))
			)));

		$this->container->append($nojs);

		$this->container->append(new HTMLNode("div", array("class" => "clear")));

		$this->callExtending("afterField");

		if(PROFILE)
			Profiler::unmark("FormField::field");

		return $this->container;
	}

	/**
	 * handles the upload
	*/
	public function handleUpload($upload) {
		if(!isset($upload["name"])) {
			return "No Upload defined.";
		}

		if(GOMA_FREE_SPACE - $upload["size"] < 10 * 1024 * 1024) {
			return lang("error_disk_space");
		}

		if($upload["size"] <= $this->max_filesize || $this->max_filesize == -1) {
			$name = $upload["name"];
			$ext = strtolower(substr($name, strrpos($name, ".") + 1));
			if($this->allowed_file_types == "*" || in_array($ext, $this->allowed_file_types)) {
				$name = preg_replace('/[^a-zA-Z0-9_\-\.]/i', '_', $name);
				if($data = call_user_func_array(array(
					$this->uploadClass,
					"addFile"
				), array(
					$name,
					$upload["tmp_name"],
					$this->collection,
					$this->uploadClass
				))) {
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

	/**
	 * the result is a Uploads-Object
	 *
	 * @return Uploads
	 */
	public function result() {
		$this->getValue();
		return $this->value;
	}
}
