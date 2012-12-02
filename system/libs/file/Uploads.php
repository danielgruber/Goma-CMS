<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 02.12.2012
  * $Version 1.4.2
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

defined("UPLOAD_DIR") OR die('Constant UPLOAD_DIR not defined, Please define UPLOAD_DIR to proceed.');

loadlang("files");

class Uploads extends DataObject {
	/**
	 * max-filesize for md5
	 *
	 *@name FILESIZE_MD5
	 *@access public
	*/
	const FILESIZE_MD5 = 52428800; // 50 MB

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
		"type"		=> "enum('collection','file')",
		"deletable"	=> "enum('0', '1')",
		"md5"		=> "text"
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
	public static function addFile($filename, $realfile, $collectionPath, $class_name = null, $deletable = null) {
		if(!file_exists($realfile) || empty($collectionPath)) {
			return false;
		}
		
		if(!isset($deletable))
			$deletable = false;
		
		if(!is_object($collectionPath)) {
			if(defined("SQL_LOADUP")) {
				// determine id of collection
				$collectionTree = explode(".", $collectionPath);
				foreach($collectionTree as $collection) {
					$data = DataObject::get_one("Uploads", array("filename" => $collection, "type" => "collection"));
					if($data) {
						$id = $data->id;
					} else {
						$collection = new Uploads(array("filename" => $collection, "type" => "collection", "collectionid" => isset($id) ? $id : 0));
						$collection->write(false, true);
						$id = $collection->id;
					}
				}	
			} else {
				$id = 0;
			}
		} else {
			$collection = $collectionPath;
			$collectionPath = $collection->hash();
			$id = $collection->id;
		}
		
		if(!isset($id)) {
			return false;
		}
		
		
		// determine file-position
		FileSystem::requireFolder(UPLOAD_DIR . "/" . md5($collectionPath));
		
		if(filesize($realfile) < self::FILESIZE_MD5) {
			$md5 = md5_file($realfile);
			$object = DataObject::get("Uploads", array("md5" => $md5));
			if($object->Count() > 0 && file_exists($object->realfile) && md5_file($object->realfile) == $md5) {
				$file = clone $object->first();
				$file->collectionid = $id;
				$file->path = strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $collectionPath)) . "/" . randomString(6) . "/" . $filename;
			} else {
				// check if md5 is old
				if($object->Count() > 0 && file_exists($object->realfile) && md5_file($object->realfile) != $md5) {
					$object->md5 = md5_file($object->realfile);
					$object->write(false, true);
				}
				$file = new Uploads(array(
					"filename" 		=> $filename,
					"type"			=> "file",
					"realfile"		=> UPLOAD_DIR . "/" . md5($collectionPath) . "/" . randomString(6) . $filename,
					"path"			=> strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $collectionPath)) . "/" . randomString(6) . "/" . $filename,
					"collectionid" 	=> $id,
					"deletable"		=> $deletable,
					"md5"			=> md5_file($realfile)
				));
			}
		} else {
			$file = new Uploads(array(
				"filename" 		=> $filename,
				"type"			=> "file",
				"realfile"		=> UPLOAD_DIR . "/" . md5($collectionPath) . "/" . randomString(6) . $filename,
				"path"			=> strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $collectionPath)) . "/" . randomString(6) . "/" . $filename,
				"collectionid" 	=> $id,
				"deletable"		=> $deletable
			));
		}
		
		// make it a valid class-name
		if(isset($class_name)) {
			$class_name = trim(strtolower($class_name));
		}
		
		// guess class-name
		$guessed_class_name = self::guessFileClass($filename);
		if(!isset($class_name)) {
			$class_name = $guessed_class_name;
		} else if(is_subclass_of($guessed_class_name, $class_name)) {
			$class_name = $guessed_class_name;
		}
		
		// now reinit the file-object
		$file = $file->getClassAs($class_name);
		
		if(copy($realfile, $file->realfile)) {
			if(!defined("SQL_LOADUP")) {
				$file->path = $file->realfile;
				return $file;
			}
			
			if($deletable)
				$file->forceDeletable = true;
			
			if($file->write(true, true)) {
				return $file;
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * removes the file after remvoing from Database
	 *
	 *@name onAfterRemove
	 *@access public
	*/
	public function onAfterRemove() {
		if(file_exists($this->realfile)) {
			$data = DataObject::get("Uploads", array("realfile" => $this->realfile));
			if($data->Count() == 0) {
				@unlink($this->realfile);
			}
		}
		parent::onAfterRemove();
	}
	
	/**
	 * gets the object for the given file-path
	 *
	 *@name getFile
	 *@access public
	*/
	public function getFile($path) {
		if(($data = DataObject::get_one("Uploads", array("path" => $this->value))) !== false) {
			return $data;
		} else if(($data = DataObject::get_one("Uploads", array("realfile" => $this->value))) !== false) {
			return $data;
		} else {
			return false;
		}
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
	 * event on before write
	 *
	 *@name onBeforeWrite
	 *@access public
	*/
	public function onBeforeWrite() {
		if(!$this->forceDeletable)
			$this->deletable = true;
	}
	
	/**
	 * clean up DB
	 *
	 *@name cleanUpDB
	 *@åccess public
	*/
	public function cleanUpDB($prefix = DB_PREFIX, &$log) {
		parent::cleanUpDB($prefix, $log);
		
		$data = DataObject::get("Uploads", array("deletable" => 1, "last_modified" => array(">", NOW - 60 * 60 * 24 * 14)));
		foreach($data as $record) {
			if(!file_exists($record->realfile)) {
				$record->remove(true);
				continue;
			}
			// in test
			//@unlink($record->realfile);
			logging("removing file ".$record->realfile."");
			//$record->remove(true);
		}
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
		if($this->deletable) {
			$this->deletable = true;
			$this->write(false, true);
		}
		
		return $this->path;
	}
	
	/**
	 * returns the path
	 *
	 *@name getPath
	 *@access public
	*/
	public function getPath(){
		if(!$this->fieldGET("path") || $this->fieldGet("path") == "Uploads/" || $this->fieldGet("path") == "Uploads")
			return $this->fieldGET("path");
		
		return BASE_SCRIPT . 'Uploads/' . $this->fieldGET("path");
	}
	
	/**
	 * sets the path
	 *
	 *@name setPath
	 *@access public
	*/
	public function setPath($path) {
		if(substr($path, 0, strlen(BASE_SCRIPT)) == BASE_SCRIPT) {
			$path = substr($path, strlen(BASE_SCRIPT));
		}
		
		if(substr($path, 0, strlen("index.php/")) == "index.php/") {
			$path = substr($path, strlen("index.php/"));
		}
		
		if(substr($path, 0, 8) == "Uploads/") {
			$this->setField("path", substr($path, 8));
		} else {
			$this->setField("path", $path);
		}
	}
	
	/**
	 * to string
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString() {
		if($this->bool()) {
			return '<a href="'.$this->raw().'">' . $this->filename . '</a>';
		} else {
			return null;
		}
	}
	
	/**
	 * returns the path to the icon of the file
	 *
	 *@name getIcon
	 *@access public
	 *@param int - size; support for 16, 32, 64 and 128
	*/
	public function getIcon($size = 128, $retina = false) {
		switch($size) {
			case 16:
				if($retina)
					return "images/icons/goma16/file@2x.png";
				else
					return "images/icons/goma16/file.png";
			break;
			case 32:
				if($retina)
					return "images/icons/goma32/file@2x.png";
				else
					return "images/icons/goma32/file.png";
			break;
			case 64:
				if($retina)
					return "images/icons/goma64/file@2x.png";
				else
					return "images/icons/goma64/file.png";
			break;
			case 128:
				return "images/icons/goma/128x128/file.png";
			break;
		}
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
			if(substr($query->filter["path"], 0, strlen(BASE_SCRIPT)) == BASE_SCRIPT) {
				$query->filter["path"] = substr($query->filter["path"], strlen(BASE_SCRIPT));
			}
			
			if(substr($query->filter["path"], 0, strlen("index.php/")) == "index.php/") {
				$query->filter["path"] = substr($query->filter["path"], strlen("index.php/"));
			}

			if(substr($query->filter["path"],0,strlen("Uploads")) == "Uploads") {
				$query->filter["path"] = substr($query->filter["path"], strlen("Uploads") + 1);
			}
		}
	}
	
	/**
	 * gets the file-size nice written
	 *
	 *@name filesize
	 *@access public
	*/
	public function filesize() {
		return FileSystem::filesize_nice($this->realfile);
	}
	
	/**
	 * returns if this dataobject is valid
	 *
	 *@name bool
	 *@access public
	*/
	public function bool() {
		if(parent::bool()) {
			return ($this->realfile !== "" && is_file($this->realfile));
		} else {
			return false;
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
		if(preg_match('/\.(pdf)$/i', $this->modelInst()->filename)) {
			HTTPResponse::setHeader("content-type", "application/pdf");
			HTTPResponse::sendHeader();
			readfile($this->modelInst()->realfile);
			exit;
		}
		FileSystem::sendFile($this->modelInst()->realfile, $this->modelInst()->filename);
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
		if(preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $this->realfile)) {
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
	
	/**
	 * returns the path to the icon of the file
	 *
	 *@name getIcon
	 *@access public
	 *@param int - size; support for 16, 32, 64 and 128
	*/
	public function getIcon($size = 128, $retina = false) {
		switch($size) {
			case 16:
				if($this->width > 15) {
					if($retina && $this->width > 31) {
						return $this->path . "/setWidth/32";
					}
					return $this->path . "/setWidth/16";
				} else {
					if($retina) {
						return "images/icons/goma16/image@2x.png";
					}
					return "images/icons/goma16/image.png";
				}
			break;
			case 32:
				if($this->width > 31) {	
					if($retina && $this->width > 63) {
						return $this->path . "/setWidth/64";
					}
					return $this->path . "/setWidth/32";
				} else {
					if($retina) {
						return "images/icons/goma32/image@2x.png";
					}
					return "images/icons/goma32/image.png";
				}
			break;
			case 64:
				if($this->width > 63) {
					if($retina && $this->width > 127) {
						return $this->path . "/setWidth/128";
					}
					return $this->path . "/setWidth/64";
				} else {
					if($retina) {
						return "images/icons/goma64/image@2x.png";
					}
					return "images/icons/goma64/image.png";
				}
			break;
			case 128:
				if($this->width > 127) {
					if($retina && $this->width > 255) {
						return $this->path . "/setWidth/256";
					}
					return $this->path . "/setWidth/128";
				} else {
					return "images/icons/goma/128x128/image.png";
				}
			break;
		}
		return "images/icons/goma/128x128/image.png";
	}
	
	/**
	 * sets the height
	 *
	 *@name setHeight
	 *@access public
	*/
	public function setHeight($height) {
		return '<img src="' . $this->path . "/setHeight/" . $height . '" data-retina="' . $this->path . "/setHeight/" . ($height * 2) . '" alt="'.$this->filename.'" />';
	}
	
	/**
	 * sets the width
	 *
	 *@name setWidth
	 *@access public
	*/
	public function setWidth($width) {
		return '<img src="' . $this->path . "/setWidth/" . $width . '" data-retina="' . $this->path . "/setWidth/" . ($width * 2) . '" alt="'.$this->filename.'" />';
	}
	
	/**
	 * sets the Size
	 *
	 *@name setSize
	 *@access public
	*/
	public function setSize($width, $height) {
		return '<img src="' . $this->path .'/setSize/'.$width.'/'.$height.'" data-retina="' . $this->path .'/setSize/'.($width * 2).'/'.($height * 2).'" alt="'.$this->filename.'" />';
	}
	
	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 *@name orgSetSize
	 *@access public
	*/
	public function orgSetSize($width, $height) {
		return '<img src="' . $this->path .'/orgSetSize/'.$width.'/'.$height.'" data-retina="' . $this->path .'/orgSetSize/'.($width*2).'/'.($height*2).'" alt="'.$this->filename.'" />';
	}
	
	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 *@name orgSetWidth
	 *@access public
	*/
	public function orgSetWidth($width) {
		return '<img src="' . $this->path . "/orgSetWidth/" . $width . '" alt="'.$this->filename.'" />';
	}
	
	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 *@name orgSetHeight
	 *@access public
	*/
	public function orgSetHeight($height) {
		return '<img src="' . $this->path . "/orgSetHeight/" . $height . '" alt="'.$this->filename.'" />';
	}
	
	/**
	 * returns width
	 *
	 *@name width
	 *@access public
	*/
	public function width() {
		if(preg_match('/^[0-9]+$/', $this->fieldGET("width")) && $this->fieldGET("width") != 0) {
			return $this->fieldGet("width");
		}
		
		$image = new RootImage($this->realfile);
		$this->setField("width", $image->width);
		$this->write(false, true);
		return $image->width;
	}
	
	/**
	 * returns height
	 *
	 *@name height
	 *@access public
	*/
	public function height() {
		if(preg_match('/^[0-9]+$/', $this->fieldGET("height"))  && $this->fieldGET("height") != 0) {
			return $this->fieldGet("height");
		}
		
		$image = new RootImage($this->realfile);
		$this->setField("height", $image->height);
		$this->write(false, true);
		return $image->height;
	}

}

class ImageUploadsController extends UploadsController {
	/**
	 * handlers
	 *
	 *@name handlers
	 *@access public
	*/
	public $url_handlers = array(
		"setWidth/\$width" 				=> "setWidth",
		"setHeight/\$height"			=> "setHeight",
		"setSize/\$width/\$height"		=> "setSize",
		"orgSetWidth/\$width" 			=> "orgSetWidth",
		"orgSetHeight/\$height"			=> "orgSetHeight",
		"orgSetSize/\$width/\$height"	=> "orgSetSize"
	);
	
	/**
	 * allowed actions
	*/
	
	public $allowed_actions = array(
		"setWidth",
		"setHeight",
		"setSize",
		"orgSetSize",
		"orgSetWidth",
		"orgSetHeight"
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
	
	/**
	 * sets the size
	 *
	 *@name setSize
	 *@access public
	*/
	public function setSize() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$height = $this->getParam("height");
			$width = $this->getParam("width");
			$image = new RootImage($this->modelInst()->realfile);
			$image->createThumb($width, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight)->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the size on the original 
	 *
	 *@name orgSetSize
	 *@åccess public
	*/
	public function orgSetSize() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$height = $this->getParam("height");
			$width = $this->getParam("width");
			$image = new RootImage($this->modelInst()->realfile);
			$image->createThumb($width, $height, 0, 0, 100, 100)->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the width on the original 
	 *
	 *@name orgSetWidth
	 *@åccess public
	*/
	public function orgSetWidth() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$width = $this->getParam("width");
			$image = new RootImage($this->modelInst()->realfile);
			$image->createThumb($width, null, 0, 0, 100, 100)->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the height on the original
	 *
	 *@name orgSetHeight
	 *@access public
	*/
	public function orgSetHeight() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$height = $this->getParam("height");
			$image = new RootImage($this->modelInst()->realfile);
			$image->createThumb(null, $height,0,0,100,100)->Output();
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
		
		if(!file_exists($data->first()->realfile)) {
			$data->first()->remove(true);
			exit;
		}
		
		session_write_close();
		
		return $data->first()->controller()->handleRequest($this->request);
	}
	
}