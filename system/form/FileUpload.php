<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 11.12.2011
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FileUpload extends FormField
{
		/**
		 * url-handlers
		 *
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array(
			"ajaxUpload"	=> "ajaxUpload",
			"frameUpload"	=> "frameUpload"
		);
		/**
		 * actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array(
			"ajaxUpload",
			"frameUpload"
		);	
		/**
		 * all allowed file-extensions
		 *@name allowed_file_types
		 *@access public
		*/
		public $allowed_file_types = array(
			"jpg",
			"png",
			"bmp",
			"zip",
			"rar",
			"doc",
			"txt",
			"text",
			"pdf"
		);
		/**
		 * max filesize
		 *@name max_filesize
		*/
		public $max_filesize = 5242880; // 5m = 2097152 = 1024 * 1024 * 2
		/**
		 * collection
		 *
		 *@name collection
		 *@access public
		*/
		public $collection = "FormUpload";
		/**
		 * upload-class
		*/
		protected $uploadClass = "Uploads";
		/**
		 * container on the left
		 *
		 *@name leftContainer
		 *@access public
		*/
		public $leftContainer;
		/**
		 *@name __construct
		 *@access public
		*/
		public function __construct($name = null, $title = null, $file_types = null, $value = "", $collection = null, $form = null) {
			parent::__construct($name, $title, $value, $form);
			if($file_types !== null)
				$this->allowed_file_types = $file_types;
				
			if(isset($collection))
				$this->collection = $collection;
				
			Resources::add("system/form/FileUpload.js", "js", "tpl");
			Resources::addData("var lang_browse = '".lang("files.browse")."';");
		}
		
		/**
		 * renders after setForm the whole field
		*/
		public function renderAfterSetForm() {
			parent::renderAfterSetForm();
			
			$this->leftContainer = new HTMLNode("div", array(
				"style"	=> "float: left;",
				"class"	=> "FileUpload_left"
			), array(
				new HTMLNode('div', array(
					"class" => "icon",
					"id"	=> $this->ID() . "_upload"
				), array(
					new HTMLNode("a", array(
						"href"	=> $this->value->raw(),
						"target"=> "_blank"
					), array(
						new HTMLNode("div", array("class" => "img"), array(
							new HTMLNode("img", array(
								"src"	=> $this->value->getIcon(),
								"alt"	=> text::protect($this->value->filename)
							))
						)),
						new HTMLNode("span", array(), text::protect($this->value->filename)),
						
					))
				)),
				new HTMLNode('div', array(
					"class" => "actions"
				)),
				new HTMLNode("input", array(
					"name" 	=> $this->name . "_file",
					"id" 	=> $this->ID() . "_file_hidden",
					"type"	=> "hidden",
					"value"	=> $this->value->fieldGet("path"),
					"class"	=> "FileUploadValue"
				))
			));
		}
		
		/**
		 * gets the current value
		 *
		 *@name getValue
		 *@access public
		*/
		public function getValue() {
			parent::getValue();
			
			if(isset($_FILES[$this->name]) && !empty($_FILES[$this->name]["name"])) {
				if(is_object($value = $this->handleUpload($_FILES[$this->name]))) {
					$this->value = $value;
					return true;
				} else {
					if(is_string($value)) {
						AddContent::addNotice($value);
					} else {
						AddContent::addNotice(lang("files.upload_failure"));
					}
				}
			} else if(isset($_POST[$this->name . "_file"])) {
				$this->value = $_POST[$this->name . "_file"];
			}
			
			if(($data = DataObject::get_one("Uploads", array("path" => $this->value))) !== false) {
				$this->value = $data;
			} else {
				if(!empty($this->value)) {
					if($data = Uploads::addFile(basename($this->value), $this->value, $this->collection)) {
						$this->value = $data;
						return true;
					}
				}
				$this->value = new Uploads(array("path" => $this->value, "filename" => basename($this->value)));
				
			}
		}
		
		/**
		 * ajax upload
		 *
		 *@name ajaxUpload
		 *@access public
		*/
		public function ajaxUpload() {
			if(preg_match('/\.('.implode("|", $this->allowed_file_types).')$/i', $_SERVER["HTTP_X_FILE_NAME"])) {
				if($handle = @fopen('php://input', "r")) {
					$tmp_name = ROOT . CACHE_DIRECTORY . "upload_" . md5($_SERVER["HTTP_X_FILE_NAME"]);
					file_put_contents($tmp_name, $handle);
				} else {
					HTTPResponse::setHeader("Content-Type", "text/x-json");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
					exit;
				}
				$upload = array(
					"name" 		=> $_SERVER["HTTP_X_FILE_NAME"],
					"size"		=> $_SERVER["HTTP_X_FILE_SIZE"],
					"error" 	=> 0,
					"tmp_name" 	=> $tmp_name
				);
				$response = $this->handleUpload($upload);
				if(is_object($response)) {
					HTTPResponse::setHeader("Content-Type", "text/x-json");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 1, "file" => array("name" => $response->filename, "realpath" => $response->fieldGet("path"), "icon" => $response->getIcon(), "path" => $response->path)));
					exit;
				} else if(is_string($response)) {
					HTTPResponse::setHeader("Content-Type", "text/x-json");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => $response));
					exit;
				} else {
					HTTPResponse::setHeader("Content-Type", "text/x-json");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
					exit;
				}
			} else {	
				HTTPResponse::setHeader("Content-Type", "text/x-json");
				HTTPResponse::sendHeader();
				echo json_encode(array("status" => 0, "errstring" => lang("files.filetype_failure", "The filetype isn't allowed.")));
				exit;
			}
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
				if(is_object($response)) {
					//HTTPResponse::setHeader("Content-Type", "text/plain");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 1, "file" => array("name" => $response->filename, "realpath" => $response->fieldGet("path"), "icon" => $response->getIcon(), "path" => $response->path)));
					exit;
				} else if(is_string($response)) {
					//HTTPResponse::setHeader("Content-Type", "text/plain");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => $response));
					exit;
				} else {
					//HTTPResponse::setHeader("Content-Type", "text/plain");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
					exit;
				}
			} else {	
				//HTTPResponse::setHeader("Content-Type", "text/plain");
				HTTPResponse::sendHeader();
				echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
				exit;
			}
		}
		
		/**
		 * this shouldn't do anything
		 *@name setValue
		 *@access public
		*/
		public function setValue() {}
		
		/**
		 * creates the file-upload-node
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->type = "file";
				return $node;
		}
		/**
		 * sets the right enctype for the form
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FormField::field");
				Resources::addJS("$(function(){new AjaxfiedUpload($('#".$this->divID()."'), '".$this->externalURL()."');});");
				// modify form for right datatype
				$this->form()->form->enctype = "multipart/form-data";
				
				$this->callExtending("beforeField");
				
				$this->setValue();
				
				$this->container->append(new HTMLNode(
					"label",
					array("for"	=> $this->ID(), "style" => "display: block;"),
					$this->title
				));
				
				$this->container->append($this->leftContainer);
				
				$this->container->append(new HTMLNode("div", array("class" => "rightContainer"), array($this->input)));
				
				$this->container->append(new HTMLNode("div", array("class" => "clear")));
				
				$this->callExtending("afterField");
				
				if(PROFILE) Profiler::unmark("FormField::field");
				
				return $this->container;
		}
		
		/**
		 * handles the upload
		 *
		 *@name handleUpload
		 *@access public
		*/
		public function handleUpload($upload) {
			if(!isset($upload["name"])) {
				return "No Upload defined.";
			}
			
			if($upload["size"] <= $this->max_filesize) {
				$name = $upload["name"];
				$ext = strtolower(substr($name, strrpos($name, ".") + 1));
				if(in_array($ext, $this->allowed_file_types)) {
					$name = preg_replace('/[^a-zA-Z0-9_\-\.]/i','_',$name);
					if($data = Uploads::addFile($name, $upload["tmp_name"], $this->collection)) {
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
		 * the result is a filename
		 *@name result
		 *@access public
		*/
		public function result()
		{
			return $this->value;
		}
}