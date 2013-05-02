<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 20.03.2013
  * $Version 1.1.9
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FileUploadSet extends FormField {
	/**
	 * url-handlers
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		"ajaxUpload"		=> "ajaxUpload",
		"frameUpload"		=> "frameUpload",
		"POST remove/\$id"	=> "removeFile"
	);
	
	/**
	 * actions
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array(
		"ajaxUpload",
		"frameUpload",
		"removeFile"
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
	 *@name max_filesize
	*/
	public $max_filesize = 10485760; // 10 Mib
	
	/**
	 * collection
	 *
	 *@name collection
	 *@access public
	*/
	public $collection = "FormUploadSet";
	
	/**
	 * upload-class
	*/
	protected $uploadClass = "Uploads";
	
	/**
	 * unique key of this dataset
	 *
	 *@name key
	 *@access protected
	*/
	protected $key;
	
	/**
	 * main view
	 *
	 *@name view
	 *@access public
	*/
	public $view;
	
	/**
	 * table-body for files
	 *
	 *@name tbody
	 *@access public
	*/
	public $tbody;
	
	/**
	 * defines whether set a link to the file or not
	 *
	 *@name link
	 *@access public
	*/
	public $link = true;
	
	/**
	 * this field needs to have the full width
	 *
	 *@name fullSizedField
	*/
	protected $fullSizedField = true;
	
	/**
	 *@name __construct
	 *@access public
	*/
	public function __construct($name = null, $title = null, $file_types = null, $value = "", $collection = null, &$form = null) {
		parent::__construct($name, $title, $value, $form);
		if($file_types !== null && (is_array($file_types) || $file_types == "*"))
			$this->allowed_file_types = $file_types;
			
		if(isset($collection))
			$this->collection = $collection;
	}
	
	/**
	 * handles the request and saves the data to the session
	 *
	 *@handleRequest
	 *@access public
	 *@param request-object
	*/
	public function handleRequest($request, $subController = false)
	{
		$data = parent::handleRequest($request, $subController);
		$this->storeData();
		return $data;
	}
	
	/**
	 * stores the data
	 *
	 *@name storeData
	 *@access public
	*/
	public function storeData() {
		session_store("FileUploadSet_" . $this->key, $this->value);
		$this->Form()->saveToSession();
	}
	
	/**
	 * gets the current value
	 *
	 *@name getValue
	 *@access public
	*/
	public function getValue() {
		
		if(is_object($this->form()->result) && isset($this->form()->result->many_many_tables[$this->name])) {
			$this->uploadClass = substr($this->form()->result->many_many_tables[$this->name]["extfield"], 0, -2);
		}
		
		if(isset($this->form()->post[$this->PostName() . "__key"]) && session_store_exists("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"])) {
			$this->value = session_restore("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"]);
			$this->key = $this->form()->post[$this->PostName() . "__key"];
		} else if(isset($this->form()->result[$this->name])) {
			$this->key = randomString(10);
			$this->value = $this->form()->result[$this->name];
			if(!is_object($this->value)) {
				$this->value = new ManyMany_DataObjectSet($this->uploadClass);
			}
		} else if(isset($this->form()->result[$this->name . "ids"])) {
			$this->value = new ManyMany_DataObjectSet($this->uploadClass, array("id" => $this->form()->result[$this->name . "ids"]));
			$this->value->forceData();
		} else {
			$this->value = new ManyMany_DataObjectSet($this->uploadClass);
		}
		
		if(isset($_FILES[$this->PostName() . "_upload"]) && !empty($_FILES[$this->PostName() . "_upload"]["name"])) {
			$response = $this->handleUpload($_FILES[$this->PostName() . "_upload"]);
			if($response === false) {
				AddContent::addNotice(lang("files.upload_failure"));
			} else if(is_string($response)) {
				AddContent::addNotice($response);
			} else {
				AddContent::addSuccess(lang("files.upload_success"));
			}
		}
		
		foreach($this->value as $record) {
			if(isset($this->form()->post[$this->PostName() . "__delete_" . $record["id"]])) {
				$record->disconnect($this->value);
			}
		}
	}

	/**
	 * ajax upload
	 *
	 *@name ajaxUpload
	 *@access public
	*/
	public function ajaxUpload() {
		if(!isset($_SERVER["HTTP_X_FILE_NAME"]))
				$_SERVER["HTTP_X_FILE_NAME"] = "";
			
		if($this->allowed_file_types == "*" || preg_match('/\.('.implode("|", $this->allowed_file_types).')$/i', $_SERVER["HTTP_X_FILE_NAME"])) {
			
			if(Core::$phpInputFile) {
				$tmp_name = Core::$phpInputFile;
				
				
				if(filesize($tmp_name) != $_SERVER["HTTP_X_FILE_SIZE"]) {
					HTTPResponse::setHeader("Content-Type", "text/x-json");
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
					exit;
				}
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
			// clean up
			if(isset($tmp_name))
				@unlink($tmp_name);
			if(is_object($response)) {
				HTTPResponse::setHeader("Content-Type", "text/x-json");
				HTTPResponse::sendHeader();
				
				$filedata = array("name" => $response->filename, "realpath" => $response->fieldGet("path"), "icon16" => $response->getIcon(16), "path" => $response->path, "id" => $response->id);
				if(!$this->link) {
					unset($filedata["realpath"]);
					unset($filedata["path"]);
				}
				
				echo json_encode(array("status" => 1, "file" => $filedata));
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
			if(is_array($_FILES["file"]["name"])) {
				$files = $this->handleUpload($_FILES["file"]);
				$filedata = array();
				foreach($files as $data) {
					$filedata[] = array("name" => $data->filename, "realpath" => $data->fieldGet("path"), "icon16" => $data->getIcon(16), "path" => $data->path, "id" => $data->id);
					if(!$this->link) {
						unset($filedata[count($filedata) - 1]["realpath"]);
						unset($filedata[count($filedata) - 1]["path"]);
					}
				}
				
				echo json_encode(array("status" => 1, "multiple" => true, "files" => $filedata));
				exit;
			} else {
				$response = $this->handleUpload($_FILES["file"]);
				if(is_object($response)) {
					HTTPResponse::sendHeader();
					$filedata = array("name" => $response->filename, "realpath" => $response->fieldGet("path"), "icon16" => $response->getIcon(16), "path" => $response->path, "id" => $response->id);
					if(!$this->link) {
						unset($filedata["realpath"]);
						unset($filedata["path"]);
					}
					
					echo json_encode(array("status" => 1, "file" => $filedata));
					exit;
				} else if(is_string($response)) {
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => $response));
					exit;
				} else {
					HTTPResponse::sendHeader();
					echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
					exit;
				}
			}
		} else {	
			HTTPResponse::sendHeader();
			echo json_encode(array("status" => 0, "errstring" => lang("files.upload_failure")));
			exit;
		}
	}
	
	/**
	 * removes a file from list
	 *
	 *@name removeFile
	 *@access public
	*/
	public function removeFile() {
		$id = $this->getParam("id");
		foreach($this->value as $record) {
			if($record["id"] == $id) {
				$record->disconnect($this->value);
			}
		}
		
		return true;
	}
	
	/**
	 * handles the upload(s)
	 *
	 *@name handleUpload
	 *@access public
	*/
	public function handleUpload($upload) {
		if(!isset($upload["name"])) {
			return "No Upload defined.";
		}
		
		// if are more than one file are given ;)
		if(is_array($upload["name"])) {
			// we make a error-stack
			$errStack = array();
			$fileStack = array();
			foreach($upload["name"]  as $key => $name) {
				
				if(GOMA_FREE_SPACE - $upload["size"][$key] < 10 * 1024 * 1024) {
					$errStack[] = lang("error_disk_space");
				}
				
				if($this->max_filesize == -1 || $upload["size"][$key] <= $this->max_filesize) {
					$ext = strtolower(substr($name, strrpos($name, ".") + 1));
					if($this->allowed_file_types == "*" || in_array($ext, $this->allowed_file_types)) {
						$name = preg_replace('/[^a-zA-Z0-9_\-\.]/i','_',$name);
						if($data = call_user_func_array(array($this->uploadClass, "addFile"), array($name, $upload["tmp_name"][$key], $this->collection, $this->uploadClass))) {
							$this->value->add($data);
							$fileStack[] = $data;
						} else {
							$errStack[] = lang("files.upload_failure") . "(".convert::raw2text($name).")";
						}
					} else {
						// not right filetype
						$errStack[] = lang("files.filetype_failure", "The filetype isn't allowed.") . "(".convert::raw2text($name).")";
					}
				} else {
					// file is too big								
					$errStack[] = lang('files.filesize_failure', "The file is too big.") . "(".convert::raw2text($name).")";
				}
			}
			if(count($errStack) == 0) {
				$this->storeData();
				return $fileStack;
			} else {
				$this->storeData();
				return '<ul>
					<li>'.implode('</li><li>', $errStack).'</li>
				</ul>';
			}
		
		// just one file
		} else {
			if(GOMA_FREE_SPACE - $upload["size"] < 10 * 1024 * 1024) {
				return lang("error_disk_space");
			}
		
			if($this->max_filesize == -1 || $upload["size"] <= $this->max_filesize) {
				$name = $upload["name"];
				$ext = strtolower(substr($name, strrpos($name, ".") + 1));
				if($this->allowed_file_types == "*" || in_array($ext, $this->allowed_file_types)) {
					$name = preg_replace('/[^a-zA-Z0-9_\-\.]/i','_',$name);
					if($data = call_user_func_array(array($this->uploadClass, "addFile"), array($name, $upload["tmp_name"], $this->collection, $this->uploadClass))) {
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
	 * renders the base-structure of the field
	 *
	 *@name renderAfterSetForm
	 *@access public
	*/
	public function renderAfterSetForm() {
		parent::renderAfterSetForm();
		
		$this->view = new HTMLNode('div', array("class" => "view"), array(
			new HTMLNode('div', array("class" => "subview table"), array(
				new HTMLNode('table', array("class" => "filetable"), array(
					new HTMLNode('thead', array(), array(
						new HTMLNode('tr', array(), array(
							new HTMLNode('td', array("class" => "icon")),
							new HTMLNode('td', array(),lang("files.filename")),
							new HTMLNode('th', array("class" => "no-js actions"), array(
								new HTMLNode('input', array(
									"type" 	=> "file", 
									"class" => "no-js-upload",
									"name"	=> $this->PostName() . "_upload"
								)),
								new HTMLNode('input', array(
									"type"	=> "submit",
									"class" => "no-js-upload button",
									"name"	=> $this->PostName() . "_uploadbutton",
									"value"	=> lang("files.upload")
								))
							))
						))
					)),
					$this->tbody = new HTMLNode('tbody', array()),
				))
			))
		));
		
		$files = $this->FileList();
		
		if(count($files) > 0) {
			$b = 0;
			$i = 0;
			foreach($files as $data) {
				if(strlen($data["filename"]) > 43) {
					$filename = mb_substr($data["filename"], 0, 35, "UTF-8") . "…" . mb_substr($data["filename"], -7, 7, "UTF-8");
				} else {
					$filename = $data["filename"];
				}
				$this->tbody->append(
					new HTMLNode('tr', array(
						"class" => ($i == 0) ? "white" : "grey",
						"id"	=> $this->name . "__upload_" . $b,
						"name"	=> $data["id"]
					), array(
						new HTMLNode('td', array("class" => "icon"), array(
							new HTMLNode('img', array("src" => $data["icon16"], "alt" => $data["filename"]))	
						)),
						new HTMLNode('td', array("class" => "filename", "title" => $data["filename"]), array(
							$a = new HTMLNode('a', array("href" => $data["path"], "target" => "_blank"), $filename)
						)),
						new HTMLNode('td', array("class" => "actions"), $this->renderActions($data))
					))
				);
				
				if(!$this->link)
					$a->removeAttr("href");
				
				$i = ($i == 0) ? 1 : 0;
				$b++;
			}
		} else {
			$this->tbody->append(
				new HTMLNode('tr', array(
					
				), array(
					new HTMLNode('th', array("colspan" => 3, "class" => "empty"), lang("files.no_file"))
				))
			);
		}
		
	}
	
	/**
	 * renders the actions
	 *
	 *@name renderActions
	 *@access public
	*/
	public function renderActions($data) {
		return array(
			new HTMLNode('div', array(
				"class" => "delete"
			), array(
				new HTMLNode('input', array("type" => "checkbox", "id" => $this->ID() . "__delete_" . $data["id"], "name" => $this->PostName() . "__delete_" . $data["id"], "value" => 1)),
				new HTMLNode('label', array("for" => $this->ID() . "__delete_" . $data["id"]), lang("delete"))
			))
		);
	}
	
	/**
	 * sets the right enctype for the form
	 *@name field
	 *@access public
	*/
	public function field()
	{
			if(PROFILE) Profiler::mark("FormField::field");
			
			$this->storeData();
			
			Resources::addData("var filelist_".$this->name." = ".json_encode($this->FileList()).";");
			gloader::load("ajaxupload");
			Resources::add("system/form/FileUploadSet.js", "js", "tpl");
			Resources::add("FileUpload.css", "css");
			Resources::addJS("$(function(){new FileUploadSet('".$this->name."',$('#".$this->divID()." .view'), '".$this->externalURL()."');});");			// modify form for right datatype
			$this->form()->form->enctype = "multipart/form-data";
			
			$this->callExtending("beforeField");
			
			$this->container->append(new HTMLNode(
				"label",
				array("for"	=> $this->ID(), "style" => "display: block;"),
				$this->title
			));
			
			$this->container->append($this->view);
			$this->container->append(new HTMLNode('input', array("type" => "hidden", "value" => $this->key, "name" => $this->PostName() . "__key")));
			$this->container->append(new HTMLNode("div", array("class" => "clear")));
			
			$this->callExtending("afterField");
			
			if(PROFILE) Profiler::unmark("FormField::field");
			
			return $this->container;
	}
	
	/**
	 * returns a file list
	 *
	 *@name FileList
	 *@access public
	*/
	public function FileList() {
		$list = array();
		foreach($this->value as $file) {
			$list[$file->id] = array(
				"filename"	=> $file->filename,
				"realfile"	=> $file->realfile,
				"path"		=> $file->path,
				"icon128"	=> $file->getIcon(128),
				"icon16"	=> $file->getIcon(16),
				"icon32"	=> $file->getIcon(32),
				"icon64"	=> $file->getIcon(64),
				"id"		=> $file->id
			);
		}
		return $list;
	}
	/**
	 * returns the result
	 *
	 *@name result
	 *@access public
	*/
	public function result() {
		$this->getValue();
		
		if(isset($this->form()->post[$this->PostName() . "__key"]) && session_store_exists("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"])) {
			$this->value = session_restore("FileUploadSet_" . $this->form()->post[$this->PostName() . "__key"]);
			$this->key = $this->form()->post[$this->PostName() . "__key"];
		}
		
		foreach($this->value as $record) {
			if(isset($this->form()->post[$this->PostName() . "__delete_" . $record["id"]])) {
				$record->disconnect($this->value);
			}
		}
		
		return $this->value;
	}
}