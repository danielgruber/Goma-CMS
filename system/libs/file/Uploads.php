<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 07.12.2011
  * $Version 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

defined("UPLOAD_DIR") OR die('Constant UPLOAD_DIR not defined, Please define UPLOAD_DIR to proceed.');

loadlang("files");

class Uploads extends DataObject {
	/**
	 * database-table
	 *
	 *@name db_fields
	 *@access public
	*/
	public $db_fields = array(
		"filename"	=> "varchar(100)",
		"realfile"	=> "varchar(300)",
		"path"		=> "varchar(200)",
		"type"		=> "enum('collection','file')"
	);
	/**
	 * relations
	 *
	 *@name has_one
	 *@access public
	*/
	public $has_one = array(
		"collection"		=> "Uploads"
	);
	/**
	 * extensions in this files are by default handled by this class
	 *
	 *@name file_extensions
	 *@access public
	*/
	public static $file_extensions = array();
	
	/**
	 * adds a file to the upload-folder
	 *
	 *@name addFile
	 *@access public
	*/
	public static function addFile($filename, $realfile, $collectionPath, $class_name = null) {
		if(!file_exists($realfile) || empty($collectionPath)) {
			return false;
		}
		
		if(!is_object($collectionPath)) {
			// determine id of collection
			$collectionTree = explode(".", $collectionPath);
			foreach($collectionTree as $collection) {
				$data = DataObject::get_one("Uploads", array("filename" => $collection, "type" => "collection"));
				if($data) {
					$id = $data->id;
				} else {
					$collection = new Uploads(array("filename" => $collection, "type" => "collection", "collectionid" => isset($id) ? $id : 0));
					$collection->write();
					$id = $collection->id;
				}
			}
		} else {
			$collection = $collectionPath;
			$collectionPath = $collection->hash();
			$id = $collection->id;
		}
		
		if(!isset($id))
			return false;
		
		// determine file-position
		FileSystem::requireFolder(UPLOAD_DIR . "/" . md5($collectionPath));
		
		$hash = strtolower(randomString(1));
		while(file_exists(UPLOAD_DIR . "/" . md5($collectionPath) . "/" . $hash . $filename) && 
				(	(filesize($realfile) > 1000000) || 
					(filesize(UPLOAD_DIR . "/" . md5($collectionPath) . "/" . $hash . $filename) > 1000000) || 
					(md5_file($realfile) != md5_file(UPLOAD_DIR . "/" . md5($collectionPath) . "/" . $hash . $filename))
				)
			) {
			$hash .= strtolower(randomString(1));
		}
		
		$file = new Uploads(array(
			"filename" 		=> $filename,
			"type"			=> "file",
			"realfile"		=> UPLOAD_DIR . "/" . md5($collectionPath) . "/" . $hash . $filename,
			"path"			=> strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $collectionPath)) . "/" . $hash . "/" . $filename,
			"collectionid" => $id
		));
		
		if(!isset($class_name)) {
			$class_name = self::guessFileClass($filename);
		}

		$file = $file->getClassAs($class_name);
		
		if(copy($realfile, UPLOAD_DIR . "/" . md5($collectionPath) . "/" . $hash . $filename)) {
			if($file->write()) {
				return $file;
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * guesses the file-class
	 *
	 *@name guessFileClass
	 *@access public
	*/
	public static function guessFileClass($filename) {
		$ext = strtolower(substr($filename, strrpos($filename, ".") + 1));
		foreach(ClassInfo::getChildren("Uploads") as $child) {
			if(in_array($ext,ClassInfo::getStatic($child, "file_extensions"))) {
				return $child;
			}
		}
		
		return "Uploads";
	}
	
	/**
	 * returns files in the collection
	 *
	 *@name getCollectionFiles
	 *@access public
	*/
	public function getCollectionFiles() {
		if($this->type == "file") {
			return DataObject::get("Uploads", array("collectionid" => $this->collectionid));
		} else {
			return DataObject::get("Uploads", array("collectionid" => $this->id));
		}
	}
	
	/**
	 * gets a subcollection with given name
	 *
	 *@name getSubCollection
	 *@access public
	 *@param string - name
	*/
	public function getSubCollection($name) {
		if($this->type == "file") {
			return $this->collection()->getSubCollection($name);
		} else {
			$data = DataObject::get_one("Uploads", array("collectionid" => $this->id, "filename" => $name));
			if($data) {
				return $data;
			} else {
				$collection = new Uploads(array(
					"filename" 		=> $name,
					"collectionid" 	=> $name,
					"type" 			=> "collection"
				));
				$collection->write(true, true);
				return $collection;
			}
		}
	}
	
	/**
	 * generates unique path for this collection
	 *
	 *@name hash
	 *@access public
	*/
	public function hash() {
		if(empty($this->realfile)) {
			$this->realfile = md5($this->identifier);
		}
		
		$this->write(false, true);
		return $this->realfile;
	}
	
	/**
	 * generates identifier for collection
	 *
	 *@name identifier
	 *@access public
	*/
	public function identifier() {
		if($this->collection()) {
			return $this->collection()->identifier() . "." . $this->filename;
		} else {
			return $this->filename;
		}
	}
	
	/**
	 * returns the raw-path
	 *
	 *@name raw
	 *@access public
	*/
	public function raw() {
		return $this->path;
	}
	
	/**
	 * returns the path
	 *
	 *@name getPath
	 *@access public
	*/
	public function getPath(){
		return 'Uploads/' . $this->fieldGET("path");
	}
	
	/**
	 * to string
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString() {
		return '<a href="'.$this->raw().'">' . $this->filename . '</a>';
	}
	
	/**
	 * returns the path to the icon of the file
	 *
	 *@name getIcon
	 *@access public
	 *@param int - size; support for 16, 32, 64 and 128
	*/
	public function getIcon() {
		return "images/icons/goma/128x128/file.png";
	}
	
	/**
	 * local argument Query
	 *
	 *@name argumentQuery
	 *@access public
	*/
	
	public function argumentQuery(&$query) {
		parent::argumentQuery($query);
		
		if(isset($query->filter["path"])) {
			if(substr($query->filter["path"],0,strlen("Uploads")) == "Uploads") {
				$query->filter["path"] = substr($query->filter["path"], strlen("Uploads") + 1);
			}
		}
	}
	
	
}


class UploadsController extends Controller {
	/**
	 * index
	 *
	 *@name index
	 *@access public
	*/
	public function index() {
		HTTPResponse::sendFile($this->modelInst()->realfile);
		FileSystem::readfile_chunked($this->modelInst()->realfile);
	}
}


class ImageUploads extends Uploads {
	/**
	 * add some db-fields
	 * inherits fields from Uploads
	 *
	 *@name db_fields
	 *@access public
	*/
	public $db_fields = array(
		"width"				=> "int(5)",
		"height"			=> "int(5)",
		"thumbLeft"			=> "int(3)",
		"thumbTop"			=> "int(3)",
		"thumbWidth"		=> "int(3)",
		"thumbHeight"		=> "int(3)"
	);
	
	/**
	 * extensions in this files are by default handled by this class
	 *
	 *@name file_extensions
	 *@access public
	*/
	public static $file_extensions = array(
		"png",
		"jpeg",
		"jpg",
		"gif",
		"bmp"
	);
	
	/**
	 * some defaults
	*/
	public $defaults = array(
		"thumbLeft"		=> 0,
		"thumbTop"		=> 0,
		"thumbWidth"	=> 100,
		"thumbHeight"	=> 100
	);
	
	/**
	 * returns the raw-path
	 *
	 *@name raw
	 *@access public
	*/
	public function raw() {
		if(preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $this->filename)) {
			return $this->realfile;
		}
		
		return $this->path;
	}
	/**
	 * to string
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString() {
		if(preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $this->filename))
			return '<img src="'.$this->raw().'" height="'.$this->height.'" width="'.$this->width.'" alt="'.$this->filename.'" />';
		else
			return '<a href="'.$this->raw().'">' . $this->filename . '</a>';
	}
	
	
}

class ImageUploadsController extends UploadsController {
	/**
	 * handlers
	 *
	 *@name handlers
	 *@access public
	*/
	public $handlers = array(
		"setWidth/\$width" 			=> "setWidth",
		"setHeight/\$height"		=> "setHeight",
		"setSize/\$width/\$height"	=> "setSize"
	);
	/**
	 * sends the image to the browser
	 *
	 *@name index
	 *@access public
	*/
	public function index() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$image = new RootImage($this->modelInst()->realfile);
			$image->output();
		}
		
		exit;
	}
	/**
	 * sets the width
	 *
	 *@name setWidth
	 *@access public
	*/
	public function setWidth() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$width = $this->getParam("width");
			$image = new RootImage($this->modelInst()->realfile);
			$image->createThumb($width, null, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight)->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the height
	 *
	 *@name setHeight
	 *@access public
	*/
	public function setHeight() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$height = $this->getParam("height");
			$image = new RootImage($this->modelInst()->realfile);
			$image->createThumb(null, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight)->Output();
		}
		
		exit;
	}
}

class UploadController extends Controller {
	/**
	 * handler
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		"\$collection/\$hash/\$filename" => "handleFile"
	);
	
	/**
	 * allow action
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array(
		"handleFile"
	);
	
	/**
	 * index
	*/
	public function index() {
		exit;
	}
	
	/**
	 * handles a file
	 *
	 *@name handleFile
	 *@access public
	*/
	public function handleFile() {
		$data = DataObject::Get("Uploads", array("path" => $this->getParam("collection") . "/" . $this->getParam("hash") . "/" . $this->getParam("filename")));
		return $data->first()->controller()->handleRequest($this->request);
	}
	
}