<?php defined('IN_GOMA') OR die();

defined("UPLOAD_DIR") OR die('Constant UPLOAD_DIR not defined, Please define UPLOAD_DIR to proceed.');

loadlang("files");

/**
  *	@package 	goma framework
  *	@link 		http://goma-cms.org
  *	@license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.5.12
  *
  * last modified: 26.01.2015
*/
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
	* indexes
	*/
	static $index = array(
		array(
			"name"      => "pathlookup",
			"fields"    => "path,class_name",
			"type"      => "INDEX"
		)    
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
		
		if(!isset($deletable)) {
			$deletable = false;
		}
		
		// get collection info
		$collection = self::getCollection($collectionPath);

		// we need a collection, without SQL-DB this does not work, 
		// but you can always create Uploads-Object by your own.
		if($collection === null) {
			throw new LogicException("Collection must be set. A Database-Connection is required for Uploads::addFile.");
		}
		$collectionPath = $collection->hash();
		
		// determine file-position
		FileSystem::requireFolder(UPLOAD_DIR . "/" . md5($collectionPath));
		
		// generate instance of file.
		$file = self::getFileInstance($realfile, $collection, $filename, $deletable);
		
		// now reinit the file-object with maybe guessed class-name.
		$file = $file->getClassAs(self::getFileClass($class_name, $filename));
		
		if(copy($realfile, $file->realfile)) {

			if($deletable) {
				$file->forceDeletable = true;
			}
			
			if($file->write(true, true)) {
				return $file;
			} else {
				return false;
			}
		}
		
		return false;
	}

	/**
	 * get file class by class or filename.
	 *
	 * @name 	getFileClass
	*/
	public static function getFileClass($class_name, $filename) {
		// make it a valid class-name
		if(isset($class_name)) {
			$class_name = trim(strtolower($class_name));
		}
		
		// guess class-name
		$guessed_class_name = self::guessFileClass($filename);

		// if we dont have a given class-name, use guessed one.
		if(!isset($class_name)) {
			$class_name = $guessed_class_name;

		// if guessed classname is a specialisation of class-name, use guessed one.
		} else if(is_subclass_of($guessed_class_name, $class_name)) {
			$class_name = $guessed_class_name;
		}

		return $class_name;
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
	 * builds an instance of file.
	 * it checks if file with md5 already exists and creates it if required.
	 *
	 * @param 	string realfile
	 * @param 	string collection
	 * @param 	string filename
	 * @param 	boolean if file is auto-deletable or not
	*/
	public static function getFileInstance($realfile, $collection, $filename, $deletable) {
		
		// check for already existing file.
		if(filesize($realfile) < self::FILESIZE_MD5) {
			$md5 = md5_file($realfile);
			$object = DataObject::get("Uploads", array("md5" => $md5));
			if($object->Count() > 0 && file_exists($object->realfile)) {
				if(md5_file($object->realfile) == $md5) {

					// we found the same file, just create a new DB-Entry, cause we 
					// don't track where db-entry is used. one db entry is for one
					// connection to another model.
					$file = clone $object->first();
					$file->collectionid = $collection->id;
					$file->path = strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $collection->hash())) . "/" . randomString(6) . "/" . $filename;
					$file->filename = $filename;

					return $file;
				} else {

					// maybe file of object has changed and md5 is not valid anymore
					// so rewrite md5-hash of object
					$object->md5 = md5_file($object->realfile);
					$object->write(false, true);
				}
			}
		}

		// generate Uploads-Object.
		return new Uploads(array(
			"filename" 		=> $filename,
			"type"			=> "file",
			"realfile"		=> UPLOAD_DIR . "/" . md5($collection->hash()) . "/" . randomString(8) . $filename,
			"path"			=> strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $collection->hash())) . "/" . randomString(6) . "/" . $filename,
			"collectionid" 	=> $collection->id,
			"deletable"		=> $deletable,
			"md5"			=> isset($md5) ? $md5 : null
		));
	}

	/**
	 * returns object of file-collection by given collection-data. (string or object)
	 *
	 * @name 	getCollection
	 * @param 	mixed 		collection as string or object
	 * @param 	boolean		use cache to cache results.
	 * @return 	Uploads 	null if SQL not loaded up, else object of type Uploads
	*/
	public static function getCollection($collectionPath, $useCache = true) {
		if(!is_object($collectionPath)) {
			if(defined("SQL_LOADUP")) {

				$cacher = new Cacher("uploads_collection_" . $collectionPath);
				if($cacher->checkValid()) {
					return DataObject::get_by_id("Uploads", $cacher->getData());
				} else {

					$collection = self::generateCollectionTree($collectionPath);

					$cacher->write($collection->id, 86400);
				}
			} else {
				return null;
			}
		} else {
			$collection = $collectionPath;
		}

		return $collection;
	}

	/**
	 * checks if collection-tree exists and gets last generated or found location.
	 *
	 * @name 	generateCollectionTree
	 * @param 	string collectionPath
	 * @return 	Uploads
	*/
	public static function generateCollectionTree($collectionPath) {
		// determine id of collection
		$collectionTree = explode(".", $collectionPath);

		// check for each level of collection if it is existing.
		foreach($collectionTree as $collection) {
			$data = DataObject::get_one("Uploads", array("filename" => $collection, "type" => "collection"));
			if($data) {
				$collection = $data;
			} else {
				$collection = new Uploads(array("filename" => $collection, "type" => "collection", "collectionid" => isset($id) ? $id : 0));
				$collection->write(false, true);
			}
		}

		return $collection;
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
		
		$cacher = new Cacher("file_" . $this->fieldGet("path"));
		$cacher->delete();
		
		$cacher = new Cacher("file_" . $this->fieldGet("realfile"));
		$cacher->delete();
		
		parent::onAfterRemove();
	}
	
	/**
	 * gets the object for the given file-path
	 *
	 *@name getFile
	 *@access public
	*/
	public function getFile($path) {

		if(preg_match('/Uploads\/([^\/]+)\/([a-zA-Z0-9]+)\/([^\/]+)/', $path, $match)) {
			$path = $match[1] . "/" . $match[2] . "/" . $match[3];
		}

		$cacher = new Cacher("file_" . $path);
		if($cacher->checkValid()) {
			$data = $cacher->getData();
			return new $data["class_name"]($data);
		} else {
			if(($data = DataObject::get_one("Uploads", array("path" => $path))) !== false) {
				$cacher->write($data->toArray(), 86400);
				return $data;
			} else if(($data = DataObject::get_one("Uploads", array("realfile" => $path))) !== false) {
				$cacher->write($data->toArray(), 86400);
				return $data;
			} else {
				return false;
			}
		}
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
		
		$cacher = new Cacher("file_" . $this->fieldGet("path"));
		$cacher->delete();
		
		$cacher = new Cacher("file_" . $this->fieldGet("realfile"));
		$cacher->delete();
		
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