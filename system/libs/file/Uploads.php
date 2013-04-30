<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 30.04.2013
  * $Version 1.5.7
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
	 * max cache lifetime
	 *
	 *@name cacheLifeTime
	 *@access public
	*/
	static $cache_life_time = 5356800; // 62 days = 5356800

	/**
	 * database-table
	 *
	 *@name db
	 *@access public
	*/
	static $db = array(
		"filename"	=> "varchar(100)",
		"realfile"	=> "varchar(300)",
		"path"		=> "varchar(200)",
		"type"		=> "enum('collection','file')",
		"deletable"	=> "enum('0', '1')",
		"md5"		=> "text"
	);
	
	/**
	 * extensions in this files are by default handled by this class
	 *
	 *@name file_extensions
	 *@access public
	*/
	static $file_extensions = array();
	
	/**
	 * relations
	 *
	 *@name has_one
	 *@access public
	*/
	static $has_one = array(
		"collection"		=> "Uploads"
	);
	
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
					"realfile"		=> UPLOAD_DIR . "/" . md5($collectionPath) . "/" . randomString(8) . $filename,
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
				"realfile"		=> UPLOAD_DIR . "/" . md5($collectionPath) . "/" . randomString(8) . $filename,
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
	
	/**
	 * checks for the permission to show this file
	 *
	 *@name checkPermission
	*/
	public function checkPermission() {
		$check = true;
		$this->callExtendig("checkPermission", $check);
		return $check;
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
		if($this->modelInst()->checkPermission()) {
			if(preg_match('/\.(pdf)$/i', $this->modelInst()->filename)) {
				HTTPResponse::setHeader("content-type", "application/pdf");
				HTTPResponse::sendHeader();
				readfile($this->modelInst()->realfile);
				exit;
			}
			FileSystem::sendFile($this->modelInst()->realfile, $this->modelInst()->filename);
		}
	}
	
	/**
	 * checks for the permission to do anything
	 *
	 *@name checkPermission
	*/
	public function checkPermission($action) {
		return (parent::checkPermission($action) && $this->modelInst()->checkPermission());
	}
}


class ImageUploads extends Uploads {
	/**
	 * add some db-fields
	 * inherits fields from Uploads
	 *
	 *@name db
	 *@access public
	*/
	static $db = array(
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
	static $file_extensions = array(
		"png",
		"jpeg",
		"jpg",
		"gif",
		"bmp"
	);
	
	/**
	 * some defaults
	*/
	static $default = array(
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
		return $this->path;
	}
	
	/**
	 * to string
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString() {
		if(preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $this->filename)) {
			$file = $this->raw().'/index'.substr($this->filename, strrpos($this->filename, "."));
			if(!file_exists($file)) {
				FileSystem::requireDir(dirname($file));
				FileSystem::write($file . ".permit", 1);
			}
			
			return '<img src="'.$file.'" height="'.$this->height.'" width="'.$this->width.'" alt="'.$this->filename.'" />';
		} else
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
	 * authenticates a specific url and removes cache-files if necessary
	 *
	 *@name manageURL
	*/
	public function manageURL($file) {
		FileSystem::requireDir(dirname($file));
		FileSystem::write($file . ".permit", 1);
		if(file_exists($file) && filemtime($file) < NOW - Uploads::$cache_life_time) {
			@unlink($file);
		}
	}
	
	/**
	 * sets the height
	 *
	 *@name setHeight
	 *@access public
	*/
	public function setHeight($height, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path . "/setHeight/" . $height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/setHeight/" . ($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file . '" height="'.$height.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the width
	 *
	 *@name setWidth
	 *@access public
	*/
	public function setWidth($width, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path . "/setWidth/" . $width . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/setWidth/" . ($width * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
				
		return '<img src="' . $file . '" width="'.$width.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the Size
	 *
	 *@name setSize
	 *@access public
	*/
	public function setSize($width, $height, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path .'/setSize/'.$width.'/'.$height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path .'/setSize/'.($width * 2).'/'.($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 *@name orgSetSize
	 *@access public
	*/
	public function orgSetSize($width, $height, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path .'/orgSetSize/'.$width.'/'.$height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);		
		
		// retina
		$fileRetina = $this->path .'/orgSetSize/'.($width * 2).'/'.($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 *@name orgSetWidth
	 *@access public
	*/
	public function orgSetWidth($width, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path . "/orgSetWidth/" . $width . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/orgSetWidth/" . ($width * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" width="'.$width.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 *@name orgSetHeight
	 *@access public
	*/
	public function orgSetHeight($height, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path . "/orgSetHeight/" . $height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/orgSetHeight/" . ($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" height="'.$height.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 *@name noCropSetSize
	 *@access public
	*/
	public function noCropSetSize($width, $height, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path .'/noCropSetSize/'.$width.'/'.$height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path .'/noCropSetSize/'.($width * 2).'/'.($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 *@name noCropSetWidth
	 *@access public
	*/
	public function noCropSetWidth($width, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path . "/noCropSetWidth/" . $width . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/noCropSetWidth/" . ($width * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" width="'.$width.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 *@name noCropSetHeight
	 *@access public
	*/
	public function noCropSetHeight($height, $absolute = false, $html = "", $style = "") {
		// normal URL Cache
		$file = $this->path . "/noCropSetHeight/" . $height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/noCropSetHeight/" . ($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		if($absolute) {
			if(file_exists($file)) {
				$file = BASE_URI . $file;
			} else {
				$file = BASE_URI . BASE_SCRIPT . $file;
			}
			
			if(file_exists($fileRetina)) {
				$fileRetina = BASE_URI . $fileRetina;
			} else {
				$fileRetina = BASE_URI . BASE_SCRIPT . $fileRetina;
			}
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" height="'.$height.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
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
		"setWidth/\$width" 					=> "setWidth",
		"setHeight/\$height"				=> "setHeight",
		"setSize/\$width/\$height"			=> "setSize",
		"orgSetWidth/\$width" 				=> "orgSetWidth",
		"orgSetHeight/\$height"				=> "orgSetHeight",
		"orgSetSize/\$width/\$height"		=> "orgSetSize",
		"noCropSetWidth/\$width" 			=> "orgSetWidth",
		"noCropSetHeight/\$height"			=> "orgSetHeight",
		"noCropSetSize/\$width/\$height"	=> "orgSetSize"
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
		"orgSetHeight",
		"noCropSetSize",
		"noCropSetWidth",
		"nocropSetHeight"
	);
	/**
	 * sends the image to the browser
	 *
	 *@name index
	 *@access public
	*/
	public function index() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			$cacheDir = substr(ROOT . URL,0,strrpos(ROOT . URL, "/"));
			
			// generate
			$image = new RootImage($this->modelInst()->realfile);
			
			// write to cache
			if(preg_match('/index\.(jpg|jpeg|png|bmp|gif)$/', URL)) {
				FileSystem::requireDir($cacheDir);
				$image->toFile(ROOT . URL);
			}
			
			// output
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
			
			$width = (int) $this->getParam("width");
			
			$cacheDir = substr(ROOT . URL,0,strrpos(ROOT . URL, "/"));
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			// create
			$image = new RootImage($this->modelInst()->realfile);
			
			$img = $image->createThumb($width, null, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight);
			
			// write to cache
			FileSystem::requireDir($cacheDir);
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
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
			
			$height = (int) $this->getParam("height");
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->createThumb(null, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
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
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$height = (int) $this->getParam("height");
			$width = (int) $this->getParam("width");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->createThumb($width, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
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
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$height = (int) $this->getParam("height");
			$width = (int) $this->getParam("width");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->createThumb($width, $height, 0, 0, 100, 100);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
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
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$width = (int) $this->getParam("width");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->createThumb($width, null, 0, 0, 100, 100);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
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
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$height = (int) $this->getParam("height");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->createThumb(null, $height, 0, 0, 100, 100);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the size on the original 
	 *
	 *@name noCropSetSize
	 *@åccess public
	*/
	public function noCropSetSize() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$height = (int) $this->getParam("height");
			$width = (int) $this->getParam("width");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->resize($width, $height, true);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the width on the original 
	 *
	 *@name orgSetWidth
	 *@åccess public
	*/
	public function noCropSetWidth() {
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$width = (int) $this->getParam("width");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->resize($width, null, true);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
		}
		
		exit;
	}
	
	/**
	 * sets the height on the original
	 *
	 *@name noCropSetHeight
	 *@access public
	*/
	public function noCropSetHeight() {	
		if(preg_match('/\.(jpg|jpeg|png|gif|bmp)/i', $this->modelInst()->filename)) {
			
			if(!file_exists(ROOT . URL . ".permit"))
				return false;
			
			$height = (int) $this->getParam("height");
			
			// create image
			$image = new RootImage($this->modelInst()->realfile);
			$img = $image->resize(null, $height, true);
			
			// write to cache
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
			
			// output
			$img->Output();
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
		return false;
	}
	
	/**
	 * handles a file
	 *
	 *@name handleFile
	 *@access public
	*/
	public function handleFile() {
		$data = DataObject::Get("Uploads", array("path" => $this->getParam("collection") . "/" . $this->getParam("hash") . "/" . $this->getParam("filename")));
		
		if($data->count() == 0) {
			return false;
		}
		
		if(!file_exists($data->first()->realfile)) {
			$data->first()->remove(true);
			return false;
		}
		
		session_write_close();
		
		return $data->first()->controller()->handleRequest($this->request);
	}	
}

class GravatarImageHandler extends ImageUploads {
	
	/**
	 * add db-fields for email
	 *
	 *@name db
	*/
	static $db = array(
		"email"	=> "varchar(200)"
	);
	
	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param boole $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array(), $html = "", $style = "" ) {
		if(isset($_SERVER["HTTPS"])) {
			$url = 'https://secure.gravatar.com/avatar/';	
		} else {
			$url = 'http://www.gravatar.com/avatar/';
		}
		$url .= md5( strtolower( trim( $email ) ) );
		$urlRetina = $url;
		
		$url .= "?s=$s&d=$d&r=$r&.jpg";
		$sR = $s * 2;
		$urlRetina .= "?s=$sR&d=$d&r=$r&.jpg";
		
		if ( $img ) {
			$url = '<img src="' . $url . '" data-retina="'.$urlRetina.'"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' style="'.$style.'" '.$html.' />';
		}
		return $url;
	}
	
	/**
	 * returns the raw-path
	 *
	 *@name raw
	 *@access public
	*/
	public function raw() {
		return self::get_gravatar($this->email, 500);
	}
	/**
	 * to string
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString() {
		return '<img src="'.$this->raw().'" alt="'.$this->filename.'" />';
	}
	
	/**
	 * returns the path to the icon of the file
	 *
	 *@name getIcon
	 *@access public
	 *@param int - size; support for 16, 32, 64 and 128
	*/
	public function getIcon($size = 128, $retina = false) {
		if($retina) {
			$size = $size * 2;
		}
		
		return self::get_gravatar($this->email, $size);
	}
	
	/**
	 * sets the height
	 *
	 *@name setHeight
	 *@access public
	*/
	public function setHeight($height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $height, "mm", "g", true, array("height" => $height), $html, $style);
	}
	
	/**
	 * sets the width
	 *
	 *@name setWidth
	 *@access public
	*/
	public function setWidth($width, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array("width" => $width), $html, $style);
	}
	
	/**
	 * sets the Size
	 *
	 *@name setSize
	 *@access public
	*/
	public function setSize($width, $height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array("height" => $height, "width" => $width), $html, $style);
	}
	
	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 *@name orgSetSize
	 *@access public
	*/
	public function orgSetSize($width, $height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array(), $html, $style);
	}
	
	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 *@name orgSetWidth
	 *@access public
	*/
	public function orgSetWidth($width, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array(), $html, $style);
	}
	
	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 *@name orgSetHeight
	 *@access public
	*/
	public function orgSetHeight($height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $height, "mm", "g", true, array(), $html, $style);
	}
	
	/**
	 * returns width
	 *
	 *@name width
	 *@access public
	*/
	public function width() {
		return 500;
	}
	
	/**
	 * returns height
	 *
	 *@name height
	 *@access public
	*/
	public function height() {
		return 500;
	}
}