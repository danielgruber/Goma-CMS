<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 22.02.2013
  * $Version 2.6.6
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

define("GFS_DIR_TYPE", "goma_dir");
defined("NOW") OR define("NOW", time());
define("FILESIZE_SAVE_IN_DB", 3072);
// be careful with json, it's not binary-safe
define("GFS_DB_TYPE", "serialize");

// flags
define("GFS_READONLY", 2);
define("GFS_READWRITE", 1);

class GFS extends Object {
	
	/**
	 * required version for this class
	 *
	 *@name REQUIRED_VERSION
	 *@access public
	*/
	const REQUIRED_VERSION = "2.0";
	
	/**
	 * version for this class
	 *
	 *@name VERSION
	 *@access public
	*/
	const VERSION = "2.6";
	
	/**
	 * version of the GFS-Library the opeded file was created with
	 *
	 *@name version
	 *@access public
	*/
	public $version;
	
	/**
	 * filepointer
	 *
	 *@name pointer
	 *@access protected
	*/
	protected $pointer;
	
	/**
	 * file
	 *
	 *@name file
	 *@access public
	*/
	public $file;
	
	/**
	 * database
	 *
	 *@name database
	 *@access protected 
	*/
	protected $db;
	
	/**
	 * valid
	 *
	 *@name valid
	 *@access public
	*/
	public $valid;
	
	/**
	 * fileposition
	 *
	 *@name position
	*/
	public $position = 0;
	
	/**
	 * endofcontentposition
	 *
	 *@name endOfContentPos
	 *@access public
	*/
	 protected $endOfContentPos = 0;
	 
	/**
	 * files
	 *
	 *@name files
	 *@access protected
	*/
	protected $files = array();
	
	/**
	 * offsetcache
	 *
	 *@name offsetcache
	 *@access protected
	 *@var array
	*/
	protected $offsetcache = array();
	
	/**
	 * old-db-size
	 *
	 *@name oldDBSize
	 *@access protected
	*/
	protected $oldDBSize;
	
	/**
	 * error on opening
	 *
	 *@name error
	*/
	public $error = 0;
	
	/**
	 * if this archive is opened as readonly
	 *
	 *@name readonly
	 *@access public
	 *@var bool
	*/
	protected $readonly;
	
	/**
	 * encrypted certificate-string
	 *
	 *@name certificate
	 *@access protected
	*/
	protected $certificate;
	
	/**
	 * private-key-cache for signing
	 *
	 *@name private
	 *@access private
	*/
	private $private;
	
	/**
	 * certificate-validate-cache
	 *
	 *@name certValidCache
	 *@access private
	*/
	private $certValidCache;
	
	/**
	 *@name __construct
	 *@access public
	 *@param filename
	*/
	public function __construct($filename, $flag = null) {
		parent::__construct();
		
		$this->file = $filename;
		if(is_dir($this->file)) {
			$this->valid = false;
			$this->error = 8;
			return false;
		}
		
		$filesize = @filesize($this->file);
		if(file_exists($this->file)) {
			$this->file = realpath($this->file);
			if($flag == GFS_READONLY) {
				$this->pointer = @fopen($this->file, "r");
				$this->readonly = true;
			} else if($flag == GFS_READWRITE) {
				$this->pointer = @fopen($this->file, "rb+");
			} else {
				if($this->pointer = @fopen($this->file, "rb+")) {
					
				} else {
					$this->pointer = @fopen($this->file, "r");
					$this->readonly = true;
				}
			}
			
			if($this->pointer) {
				if(fread($this->pointer, 4) == "!GFS") {
					$this->valid = true;
					// find version
					$this->position = 5;
					$this->setPosition();
					$v = fread($this->pointer, 5);
					if(preg_match("/^V([0-9]+)\.([0-9]+)/", $v, $match)) {
						$version = $match[1] . "." . $match[2];
						unset($match);
						// compare
						if(version_compare($version, self::REQUIRED_VERSION, ">=")) {
							$this->valid = true;
							$this->version = $version;
						} else {
							$this->error = 6;
							log_error("Could not open GFS ".$filename.". Version not supported.");
							$this->valid = false;
							return false;
						}
						
						if(version_compare($version, self::VERSION, ">")) {
							$this->error = 6;
							log_error("Could not open GFS ".$filename.". Version not supported.");
							$this->valid = false;
							return false;
						}
						
						$this->setPosition(6 + strlen($version));
						if(fread($this->pointer, 2) != "\n\n") {
							$this->error = 2;
							log_error("Could not open GFS ".$filename.". Malformed file.");
							$this->valid = false;
							return false;
						}
						
						// check if this is a signed archive
						$this->setPosition($filesize - 5);
						$flag = fread($this->pointer, 5);
						if($flag == "!SIGN") {
							$this->setPosition($filesize - 16);
							$certSize = fread($this->pointer, 11);
							if(preg_match("/\n([0-9]+)$/", $certSize, $match)) {
								$certSize = $match[1];
								$this->setPosition($filesize - 6 - strlen((string)$certSize) - $certSize);
								$this->certificate = trim(fread($this->pointer, $certSize));
								
								// reset filesize
								$filesize = $filesize - 8 - strlen((string)$certSize) - $certSize;
							} else {
								$this->error = 7;
								$this->valid = false;
								return false;
							}
						}
						
						// set filepointer to next line
						$this->setPosition($filesize - 11);
						$dbsize = fread($this->pointer, 11);
						if(preg_match("/\n([0-9]+)$/", $dbsize, $match)) {
							$dbsize = $match[1];
							unset($match);
							// set filepointer to next line
							$this->position = $filesize - 1 - strlen((string)$dbsize) - $dbsize;
							$this->setPosition();
							// read db
							$this->oldDBSize = $dbsize;
							$db = trim(fread($this->pointer, $dbsize));
							
							if(preg_match('/^\{/', $db) || preg_match('/^\[/', $db)) {
								$data = json_decode($db, true);
								if($data !== false) {
									$this->db = $data;
								} else {
									log_error("Could not open GFS ".$filename.". Could not decode json-Database.");
									$this->error = 5;
									$this->valid = false;
									return false;
								}
							} else {
								$data = unserialize($db);
								if($data !== false) {
									$this->db = $data;
								} else {
									echo $data;
									log_error("Could not open GFS ".$filename.". Could not decode serialize-Database.");
									$this->valid = false;
									$this->error = 5;
									return false;
								}
							}
							
							
							unset($data, $db);
							$this->position = strlen("!GFS;V" . $version) + 2;
							$this->endOfContentPos = $filesize - 1 - strlen((string)$dbsize) - $dbsize - 2;
							$this->setPosition();
							$this->valid = true;
						} else {
							log_error("Could not open GFS ".$filename.". Damaged database.");
							$this->valid = false;
							$this->error = 4;
							return false;
						}
					} else {
						log_error("Could not open GFS ".$filename.". Malformed version number.");
						$this->valid = false;
						$this->error = 3;
						return false;
					}
				} else {
					log_error("Could not open GFS ".$filename.". Malformed file.");
					$this->valid = false;
					$this->error = 2;
					return false;
				}
			} else {
				$this->valid = false;
				$this->error = 1;
			}
		} else {
			$this->pointer = fopen($filename, "wb+");
			fwrite($this->pointer, "!GFS;V".self::VERSION."\n\n\n\n[]\n2");
			$this->db = array();
			$this->position = strlen("!GFS;V".self::VERSION."\n\n");
			$this->endOfContentPos = $this->position;
			$this->setPosition();
			
			clearstatcache(true);
			$this->file = realpath($this->file);
		}
	}
	
	/**
	 * sets the Position of the pointer
	 *
	 *@name setPosition
	*/
	public function setPosition($position = null) {
		if($position !== null) $this->position = $position;
		fseek($this->pointer, $this->position);
	}
	
	/**
	 * adds a file
	 *
	 *@name addFromFile
	 *@access public
	 *@param string - file
	 *@param string - path in container
	*/
	public function addFromFile($file, $path, $not_add_if_dir = array()) {
		
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		// parse path
		$path = $this->parsePath($path);
		
		if(realpath($file) == $this->file || in_array($path,$not_add_if_dir)) 
				return true;
		
		if(!file_exists($file)) {
			return -4;
		}
		
		// check if you can create the path
		if(!isset($this->db[$path])) {
			if(strpos($path, "/")) {
				$pathparts = preg_split("/\//",$path, -1, PREG_SPLIT_NO_EMPTY);
				$i = 1;
				$currpath = "";
				foreach($pathparts as $part) {
					if(count($pathparts) == $i) {
						// do nothing
					} else {
						$currpath = $part . "/";
						if(!$this->exists($currpath)) {
							$this->addDir($currpath);
						}
					}
					$i++;
				}
				unset($pathparts, $i, $currpath);
			}
		} else {
			return -1;
		}
		
		if(is_file($file)) {
			if(filesize($file) > FILESIZE_SAVE_IN_DB) {
				$this->setPosition($this->endOfContentPos);
				// read and save memory
				if($filehandle = @fopen($file, "r")) {
					while (!feof($filehandle)) {
						$content = fgets($filehandle);
						fwrite($this->pointer, $content);
						unset($content);
					}
					fclose($filehandle);
					unset($filehandle);
					$this->db[$path] = array(
						"type"	 			=> $this->getFileType($file),
						"size"	 			=> filesize($file),
						"lastModfied"		=> filemtime($file),
						"checksum"			=> "GFS" . md5_file($file),
						"startChunk"		=> $this->endOfContentPos
					);
					$this->endOfContentPos += filesize($file);
				} else {
					return false;
				}
			} else {
				$this->db[$path] = array(
					"type"	 			=> $this->getFileType($file),
					"size"	 			=> filesize($file),
					"lastModfied"		=> filemtime($file),
					"contents"			=> file_get_contents($file)
				);
			}
			return $this->updateDB();
		} else if(is_dir($file)) {
			$this->db[$path] = array(
				"type"	 		=> GFS_DIR_TYPE,
				"lastModfied"	=> filemtime($file)
			);
			foreach(scandir($file) as $_file) {
				if($_file != "." && $_file != "..") {
					$this->addFromFileHelper($file . "/" . $_file, $path . "/" . $_file, $not_add_if_dir);
				}
			}
			return $this->updateDB();
		} else {
			return -4;
		}
	}
	
	/**
	 * adds a file without writing db
	 *
	 *@name addFromFileHelper
	 *@access protected
	 *@param string - file
	 *@param string - path in container
	*/
	protected function addFromFileHelper($file, $path, $not_add_if_dir = array()) {
		
		if(!$this->valid)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		// parse path
		$path = $this->parsePath($path);
				
		if(basename($file) == basename($this->file) || in_array($path,$not_add_if_dir)) 
				return true;
		
		// check if you can create the path
		if(!isset($this->db[$path])) {
			if(strpos($path, "/")) {
				$pathparts = preg_split("/\//",$path, -1, PREG_SPLIT_NO_EMPTY);
				$i = 1;
				$currpath = "";
				foreach($pathparts as $part) {
					if(count($pathparts) == $i) {
						// do nothing
					} else {
						$currpath = $currpath . "/" . $part;
						if(!$this->exists($currpath)) {
							$this->addDir($currpath);
						}
					}
					$i++;
				}
				unset($pathparts, $i, $currpath);
				
			}
		} else {
			return -1;
		}
		
		
		
		if(is_file($file)) {

			if(filesize($file) > FILESIZE_SAVE_IN_DB) {
				$this->setPosition($this->endOfContentPos);
				// read and save memory
				if($filehandle = @fopen($file, "r")) {
					while (!feof($filehandle)) {
						fwrite($this->pointer, fgets($filehandle));
					}
					fclose($filehandle);
					unset($filehandle);
					$this->db[$path] = array(
						"type"	 			=> $this->getFileType($file),
						"size"	 			=> filesize($file),
						"lastModfied"		=> filemtime($file),
						"checksum"			=> "GFS" . md5_file($file),
						"startChunk"		=> $this->endOfContentPos
					);
					$this->endOfContentPos += filesize($file);
				} else {
					return false;
				}
			} else {
				$this->db[$path] = array(
					"type"	 			=> $this->getFileType($file),
					"size"	 			=> filesize($file),
					"lastModfied"		=> filemtime($file),
					"contents"			=> file_get_contents($file)
				);
			}
			unset($file, $path);
			return true;
		} else if(is_dir($file)) {
			$this->db[$path] = array(
				"type"	 			=> GFS_DIR_TYPE,
				"lastModfied"		=> filemtime($file),
				"size"				=> 0
			);
			foreach(scandir($file) as $_file) {
				if($_file != "." && $_file != "..") {
					$this->addFromFileHelper($file . "/" . $_file, $path . "/" . $_file, $not_add_if_dir);
				}
			}
			unset($file, $path);
			return true;
		} else {
			return -4;
		}
	}
	
	/**
	 * adds a Directory
	 *
	 *@name addDir
	 *@access public
	 *@param string - path
	*/
	public function addDir($path) {
		
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		// parse path
		$path = $this->parsePath($path);
		
		
		// check if you can create the path
		if(!isset($this->db[$path])) {
			if(strpos($path, "/")) {
				$pathparts = preg_split("/\//",$path, -1, PREG_SPLIT_NO_EMPTY);
				$i = 1;
				$currpath = "";
				foreach($pathparts as $part) {
					if(count($pathparts) == $i) {
						// do nothing
					} else {
						$currpath = $part . "/";
						if(!$this->exists($currpath)) {
							return -4;
						}
					}
					$i++;
				}
				
			}
		} else {
			return -1;
		}
		
		$this->db[$path] = array(
			"type"  	 	=> GFS_DIR_TYPE,
			"lastModfied"	=> TIME
		);
		return $this->updateDB();
	}
	
	/**
	 * adds a file
	 *
	 *@name addFile
	 *@access public
	 *@param string - path
	 *@param string - content
	*/
	public function addFile($path, $content) {
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		// parse path
		$path = $this->parsePath($path);
		
		// check if you can create the path
		if(!isset($this->db[$path])) {
			if(strpos($path, "/")) {
				$pathparts = preg_split("/\//",$path, -1, PREG_SPLIT_NO_EMPTY);
				$i = 1;
				$currpath = "";
				foreach($pathparts as $part) {
					if(count($pathparts) == $i) {
						// do nothing
					} else {
						$currpath = $part . "/";
						if(!$this->exists($currpath)) {
							$this->addDir($currpath);
						}
					}
					$i++;
				}
				
			}
		} else {
			return -1;
		}
		if(strlen($content) > FILESIZE_SAVE_IN_DB) {
			$this->db[$path] = array(
				"type"	 			=> $this->getFileType($path),
				"size"	 			=> strlen($content),
				"lastModfied"		=> time(),
				"checksum"			=> "GFS" . md5($content),
				"startChunk"		=> $this->endOfContentPos
			);
			$this->setPosition($this->endOfContentPos);
			fwrite($this->pointer, $content);
			$this->endOfContentPos += strlen($content);
		} else {
			$this->db[$path] = array(
				"type"	 			=> $this->getFileType($path),
				"size"	 			=> strlen($content),
				"lastModfied"		=> time(),
				"contents"			=> $content
			);
		}
		return $this->updateDB();

	}
	
	/**
	 * gets the filetype = extension
	 *
	 *@name getFileType
	 *@access public
	 *@param string - file
	*/
	public function getFileType($file) {
		return substr($file, strrpos($file, ".") + 1);
	}
	
	/**
	 * gets contents of a file
	 *
	 *@name getFileContents
	 *@access public
	 *@param string - path
	*/
	public function getFileContents($path) {
		if($this->valid === false)
			return false;
		
		// parse path
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path])) {
			if($this->db[$path]["size"] == 0) {
				return "";
			}
			// get position of file
			if(isset($this->db[$path]["startChunk"])) {
				$offset = $this->db[$path]["startChunk"];
			} else if(isset($this->db[$path]["contents"])) {
				return $this->db[$path]["contents"];
			} else {
				return false;
			}

			// go to position of file
			fseek($this->pointer, $offset);
			$content = fread($this->pointer, $this->db[$path]["size"]);
			if("GFS" . md5($content) == $this->db[$path]["checksum"]) {
				return $content;
			} else {
				return -3;
			}
		} else {
			return -4;
		}
	}
	
	/**
	 * getDBInfo
	 *
	 *@name getDBInfo
	 *@access public
	 *@param string - path
	*/
	public function getDBInfo($path) {
		if($this->valid === false)
			return false;
		
		$path = $this->parsePath($path);
		
		return isset($this->db[$path]) ? $this->db[$path] : -4;
	}
	
	/**
	 * returns the complete database
	 *
	 *@name getDB
	 *@access public
	*/
	public function getDB() {
		return $this->db;
	}
	
	/**
	 * checks if file exists
	 *
	 *@name exists
	 *@access public
	*/
	public function exists($realfile) {
		if($this->valid === false)
			return false;
		
		// parse path
		$file = $this->parsePath($realfile);
		
		return isset($this->db[$file]);
	}
	
	/**
	 * gets contents of a directory
	 *
	 *@name scanDir
	 *@access public
	*/
	public function scanDir($path) {
		if($this->valid === false)
			return false;
		
		// parse path
		$path = $this->parsePath($path);
		
		if($path == "" || (isset($this->db[$path]) && $this->db[$path]["type"] == GFS_DIR_TYPE)) {
			// first filter keys
			// then run trough basename for just filename
			if($path != "") {
				$path = $path . "/";
			}
			return array_map("basename", array_filter(array_keys($this->db), create_function("\$val", "return preg_match('/^".preg_quote($path, "/")."([^\/]+)$/', \$val);")));
		} else {
			return -4;
		}
	}
	
	/**
	 * remove
	 * we just unset the data in db
	 *
	 *@name unlink
	 *@access public
	 *@param string - path
	*/
	public function unlink($path) {
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		// parse path
		$path = $this->parsePath($path);
		
		
		if(isset($this->db[$path])) {
			if($this->db[$path]["type"] == GFS_DIR_TYPE) {
				return $this->rmdir($path);
			} else {
				unset($this->db[$path]);
				return $this->updateDB();
			}
		} else {
			return -4;
		}
	}
	
	/**
	 * deletes a directory recursivly
	 *
	 *@name rmdir
	 *@access public
	 *@param string - path
	*/
	public function rmdir($path) {
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		$path = $this->parsePath($path);
		if(!isset($this->db[$path]) || $this->db[$path]["type"] != GFS_DIR_TYPE) {
			return false;
		}
		// first find files in path, but go from last to first to have better caches
		$db = array_reverse($this->db);
		
		// close before read
		fclose($this->pointer);
		$files_to_delete = array();
		foreach($db as $file => $data) {
			if(preg_match("/^".preg_quote($path, "/")."\//", $file)) {
				unset($db[$file], $file, $data);
			}
		}
		
		return $this->updateDB();

		
	}
	/**
	 * gets last modfied of given file
	 *
	 *@name filemTime
	 *@access public
	 *@param string - path
	*/
	public function filemTime($path) {
		if(!$this->valid) {
			return false;
		}
		
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path]["last_modfied"])) {
			return $this->db[$path]["last_modfied"];
		} else {
			return -4;
		}
	}
	
	/**
	 * writes the database
	 *
	 *@name UpdateDB
	 *@access public
	*/
	public function updateDB() {
		if(PROFILE) Profiler::mark("updateDB");
		if($this->valid === false) {
			return false;
		}
		if($this->readonly)
			return false;
			
		
		$this->setPosition($this->endOfContentPos);
		if(GFS_DB_TYPE == "serialize")
  	 		$db = serialize($this->db);
  	 	else
  	 		$db = json_encode($this->db);
  	 	
		if($this->oldDBSize > strlen($db)) {
			$db = str_repeat("\n", $this->oldDBSize - strlen($db)) . $db;
		}
		fwrite($this->pointer, "\n\n" . $db . "\n" . strlen($db));
		$this->oldDBSize = strlen($db);
		$this->certValidCache = null;
		$this->certificate = null;
		unset($db);
		if(PROFILE) Profiler::unmark("updateDB");
		return true;
	}
	
	/**
	 * rename
	 *
	 *@name rename
	 *@access public
	*/
	public function rename($path, $new) {
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		$path = $this->parsePath($path);
		
		$new = basename($new);
		if(isset($this->db[$path])) {
			if($this->db[$path]["type"] == GFS_DIR_TYPE) {
				$oldpath = $path;
				$newpath = substr($path, 0, strrpos("/", $path)) . "/" . $new;
				foreach($this->db as $key => $value) {
					if(substr($key, 0, strlen($oldpath)) == $oldpath) {
						$this->db[$newpath . substr($key, strlen($oldpath) + 1)] = $value;
						unset($this->db[$key]);
					}
				}
				return $this->updateDB();
			} else {
				$new = substr($path, 0, strrpos($path, "/")) . "/" . $new;
				if(substr($new, 0, 1) == "/")
					$new = substr($new, 1);

				if(!isset($this->db[$new])) {
					$this->db[$new] = $this->db[$path];
					$this->db[$new]["type"] = $this->getFileType($new);
					unset($this->db[$path]);
					return $this->updateDB();
				} else {
					return -1;
				}
			}
		} else {
			return -4;
		}
	}
	
	/**
	 * sets the last modfied-meta-tag to the current time
	 *
	 *@name touch
	 *@access public
	 *@param string - path
	*/
	public function touch($path, $time = NOW) {
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path])) {
			if($this->db[$path][$type] != GFS_DIR_TYPE) {
				$this->db[$path]["last_modified"] = $time;
			}
			unset($path);
			return $this->updateDB();
		} else {
			return -4;
		}
	}
	
	/**
	 * Writes $text in $path
	 *@name write
	 *@param string - text
	 *@param string - path
	 *@access public
	 *@return bool
	**/
	public function write($path, $text)
	{
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		$path = $this->parsePath($path);
			
		if(isset($this->db[$path])) {
			if($err = $this->addFile($path . ".tmp", $text) === true) {
				$this->unlink($path);
				return $this->rename($path . ".tmp", $path);
			} else {
				return $err;
			}
		} else {
			return $this->addFile($path, $text);
		} 	 
	}
	
	/**
	 * unpacks the archive
	 *
	 *@name unpack
	 *@access public
	 *@param string - aim directory
	 *@param string - path to start
	*/
	public function unpack($aim, $path = "") {
		FileSystem::requireDir($aim);
		if($path == "" || $this->exists($path)) {
			foreach($this->scandir($path) as $file) {
				$fileinfo = $this->getDBInfo($path . "/" . $file);
				if(is_array($fileinfo) && $fileinfo["type"] == GFS_DIR_TYPE) {
					if($this->unpack($aim . "/" . $file,$path . "/" . $file) !== true) {
						return false;
					}
				} else {
					if(!$this->writeToFileSystem($path . "/" . $file, $aim . "/" . $file)) {
						return false;
					}
				}
				unset($file, $fileinfo);
			}
			return true;
		} else 
			return -4;
	}
	
	/**
	 * writes a file from the archive to the real filesystem
	 * you should use this function if you write files directly to the filesystem, because with this function you can also write files larger than your RAM-amount
	 *
	 *@name writeToFileSystem
	 *@access public
	 *@param string - file in archive
	 *@param string - destination file
	*/
	public function writeToFileSystem($path, $aim) {
		if($this->valid === false) {
			return false;
		}
		
		// parse path
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path])) {
			if($this->db[$path]["size"] == 0) {
				return "";
			}
			// get position of file
			if(isset($this->db[$path]["startChunk"])) {
				$offset = $this->db[$path]["startChunk"];
			} else if(isset($this->db[$path]["contents"]) || $this->db[$path]["contents"] === null) {
				if($pointer = @fopen($aim, "w")) {
					fwrite($pointer, $this->db[$path]["contents"]);
					fclose($pointer);
					chmod($aim, 0777);
					unset($pointer);
					return true;
				} else {
					return -3;
				}
			} else {
				return false;
			}
			$this->setPosition($offset);
			if($pointer = @fopen($aim . ".tmp", "w")) {
				$currentchunk = $offset;
				while($currentchunk - $offset < $this->db[$path]["size"]) {
					if($this->db[$path]["size"] - ($currentchunk - $offset) < 500000) {
						$readsize = $this->db[$path]["size"] - ($currentchunk - $offset);
					} else {
						$readsize = 500000;
					}
					fwrite($pointer, fread($this->pointer, $readsize));
					$currentchunk += 500000;
				}
				fclose($pointer);
				unset($pointer, $readsize);
				@chmod($aim, 0777);
				if("GFS" . md5_file($aim . ".tmp") == $this->db[$path]["checksum"]) {
					if(file_exists($aim)) @unlink($aim);
					return rename($aim . ".tmp", $aim);
				} else {
					@unlink($aim . ".tmp");
					return -3;
				}
			}
			
		} else {
			return -4;
		}
	}
	
	/**
	 * parses a given plist-file and gives back the result as an array
	 *
	 *@name parsePlist
	 *@access public
	*/
	public function parsePlist($file) {
		if($this->exists($file)) {
			$plist = new CFPropertyList();
			$plist->parse($this->getFileContents($file));
			return $plist->ToArray();
		}
		
		
		return array();
	}
	
	/**
	 * writes given data to given plist-file
	 *
	 *@name writePlist
	 *@access public
	*/
	public function writePlist($file, $data) {
		if($this->valid === false)
			return false;
		
		if($this->readonly) {
			return -5;
		}
		
		$plist = new CFPropertyList();
		
		$td = new CFTypeDetector();  
		$guessedStructure = $td->toCFType($data);
		
		$plist->add($guessedStructure);
		
		return $this->write($file, $plist->toXML());
	}
	
	/**
	 * parses paths
	 *
	 *@name parsePath
	 *@access public
	 *@param string - path
	 *@return string - parsed path
	*/
	public function parsePath($path) {
		if(substr($path, 0, 1) == "/") {
			$path = substr($path, 1);
		}
		
		if($path == ".." || $path == "." OR $path == "") {
			return "";
		}
		
		$pathparts = array();
		$parts = explode("/", $path);
		
		foreach($parts as $part) {
			if($part == ".") {
				continue;
			}
			if($part == "..") {
				array_pop($pathparts);
			}
			if($path == "") {
				continue;
			}
			$pathparts[] = $part;
		}
		
		
		$path = implode("/", $pathparts);
		unset($parts, $pathparts, $part);
		
		$path = str_replace('//', '/', $path);
		
		if(substr($path, -1) == "/") {
			$path = substr($path, 0, -1);
		}
		
		return $path;
	}
	
	/**
	 * returns if this archive is signed
	 *
	 *@name isSigned
	 *@access public
	*/
	public function isSigned($publicKey) {
		if($this->valid === false)
			return false;
		
		if(isset($this->certValidCache)) {
			return $this->certValidCache;
		}
		
		
		
		if(isset($this->certificate)) {
			// generate data to encrypt
			$data = "";
			foreach($this->db as $path => $entry) {
				$data .= $path;
				if(isset($entry["checksum"])) {
					$data .= $entry["checksum"];
				} else if(isset($entry["contents"])) {
					$data .= md5($entry["contents"]);
				}
			}
			
			$data = md5($data);
			if(openssl_public_decrypt($this->certificate, $decrypted, $publicKey)) {
				if($decrypted == $data) {
					$this->certValidCache = true;
					return true;
				}
			}
		}
		
		$this->certValidCache = false;
		return false;
	}
	
	/**
	 * signs the archive
	 *
	 *@name sign
	 *@access public
	 *@param string - privateKey
	*/
	public function sign($privateKey) {
		if($this->valid === false)
			return false;
		
		if($this->readonly)
			return false;
		
		$this->certificate = null;
		$this->private = $privateKey;
		$this->certValidCache = null;
				
		return true;
	}
	
	/**
	 * closes a GFS-Archive
	 *
	 *@name close
	 *@access public
	*/
	public function close() {
		if(!$this->readonly) {
			$this->updateDB();
		}
		
		if($this->private && !$this->readonly) {
			$enc = "";
		
			// generate data to encrypt
			$data = "";
			foreach($this->db as $path => $entry) {
				$data .= $path;
				if(isset($entry["checksum"])) {
					$data .= $entry["checksum"];
				} else if(isset($entry["contents"])) {
					$data .= md5($entry["contents"]);
				}
			}
		
			$data = md5($data);
			
			if(openssl_private_encrypt($data, $enc, $this->private)) {
				$this->certificate = $enc;
				$this->private = null;
			}
			
			fseek($this->pointer, filesize($this->file) - 5);
			if(@fread($this->pointer, 5) != "!SIGN") {
				@fwrite($this->pointer, "\n\n" . $this->certificate . "\n" . strlen($this->certificate) . "!SIGN");
			}
		}
		
		if(isset($this->pointer)) {
			@fclose($this->pointer);
			@chmod($this->file, 0777);
		}
		unset($this->db, $this->pointer);
		$this->valid = false;
	}
	
	/**
	 * destruct
	 *
	 *@name __destruct
	 *@access public
	*/
	public function __destruct() {
		$this->close();
	}
	
}

class GFS_Package_installer extends GFS {
	public $status;
	public $current;
	public $progress;
	public $remaining;
	
	/**
	 * already unpacked files
	 *
	 *@name unpacked
	*/
	public static $unpacked = array();
	
	/**
	 * construct with read-only
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($filename) {
		parent::__construct($filename, GFS_READONLY);
	}
	
	/**
	 * unpack
	 *
	 *@name unpack
	 *@access public
	 *@param string - directory to which we unpack
	*/
	public function unpack($destination, $path = "") {
		if($path != "") {	
			//! TODO: Support Subfolders!
			throwError(6, "Wrong-Argument-Error", "GFS_Package_Installer doesn't support subfolders.");
		}
		if(!$this->valid) {
			return false;
		}
		
		// first we write everything to a temporary folder
		$tempfolder = ROOT . CACHE_DIRECTORY . "/" . basename($this->file);
		
		// write files
		$this->status = "Writing files...";
		$this->current = "";
		
		// we get time, if it is over 2, we reload ;)
		$start = microtime(true);
		$number = count($this->db);
		if(file_exists($tempfolder . "/.gfsprogess")) {
			
			$data = file_get_contents($tempfolder . "/.gfsprogess");
			if(preg_match('/^[0-9]+$/i', $data)) {
				$i = $data;
				$count = 1;
			} else {
				$data = unserialize($data);
				$i = $data["i"];
				$count = $data["count"];
			}
			
		} else {
  	 		FileSystem::requireDir($tempfolder);
			$i = 0;
			$count = 1;
		}
		
		$db = array_values($this->db);
		$paths = array_keys($this->db);
		
		// let's go
		while($i < count($db)) {
			
			$path = $paths[$i];
			$data = $db[$i];
			if($data["type"] == GFS_DIR_TYPE) {
				FileSystem::requireDir($tempfolder . "/" . $path);
			} else {
				if(!file_exists($tempfolder . "/" . $path)) {
					FileSystem::RequireDir(substr($tempfolder . "/" . $path, 0, strrpos($tempfolder . "/" . $path, "/")));
					$this->writeToFileSystem($path, $tempfolder . "/" . $path);
					@chmod($tempfolder . "/" . $path, 0777);
				}
			}
			$this->current = basename($path);
			
			// maximum 0.5 second
			if(microtime(true) - $start > 2.0) {
				$i++;
				$count++;
				file_put_contents($tempfolder . "/.gfsprogess", serialize(array("i" => $i, "count" => $count)));
				$this->progress = ($i / count($this->db) * 100) * 0.7;
				$perhit = $i / $count;
				$remaining = round((round((count($this->db) - $i) / $perhit * 3) + 3) * 1.42);
				if($remaining > 60) {
					$remaining = round($remaining / 60);
					if($remaining > 60) {
						$remaining = round($remaining / 60);
						$this->remaining = "More than ".$remaining." hours remaining";
					} else {
						$this->remaining = "More than ".$remaining." minutes remaining";
					}
				} else {
					$this->remaining = "More than ".$remaining." seconds remaining";
				}
				if(defined("IN_GFS_EXTERNAL")) {
					$this->showUI();
				} else {
					$file = $this->buildFile($destination);
					$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&unpack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?unpack[]=".urlencode($this->file)."";
					if(count(self::$unpacked)) {
						foreach(self::$unpacked as $file) {
							$uri .= "&unpack[]=" . urlencode($file);
						}
					}
					$this->showUI($file . "?redirect=" . urlencode($uri));
				}
			}
			$i++;
			unset($data, $path);
		}
		
		// now move all files
		if(file_exists($tempfolder . "/.gfsrprogess")) {
			
			$data = file_get_contents($tempfolder . "/.gfsrprogess");
			if(preg_match('/^[0-9]+$/i', $data)) {
				$i = $data;
				$count = 1;
			} else {
				$data = unserialize($data);
				$i = $data["i"];
				$count = $data["count"];
			}
			
		} else {
			$i = 0;
			$count = 1;
		}
		
		// let's go
		while($i < count($db)) {
			$path = $paths[$i];
			$data = $db[$i];
			if($data["type"] == GFS_DIR_TYPE) {
					FileSystem::requireDir($destination . "/" . $path);
			} else {
				FileSystem::requireDir(substr($destination . "/" . $path, 0, strrpos($destination . "/" . $path, "/")));
				// helps in some cases ;)
				@unlink($destination . "/" . $path);
				if(@rename($tempfolder . "/" . $path, $destination . "/" . $path))
					chmod($destination . "/" . $path, 0777);
			
			}
			
			$this->status = "Renaming files...";
			$this->current = basename($path);
			
			// maximum of 0.5 seconds
			if(microtime(true) - $start > 2.0) {
				$i++;
				$count++;
				file_put_contents($tempfolder . "/.gfsrprogess", serialize(array("i" => $i, "count" => $count)));
				$this->progress = 70 + ($i / count($this->db) * 100) * 0.3;
				$perhit = $i / $count;
				$remaining = round((round((count($this->db) - $i) / $perhit * 3) + 3) * 0.40);
				if($remaining > 60) {
					$remaining = round($remaining / 60);
					if($remaining > 60) {
						$remaining = round($remaining / 60);
						$this->remaining = "More than ".$remaining." hours remaining";
					} else {
						$this->remaining = "More than ".$remaining." minutes remaining";
					}
				} else {
					$this->remaining = "More than ".$remaining." seconds remaining";
				}
				if(defined("IN_GFS_EXTERNAL")) {
					$this->showUI();
				} else {
					$file = $this->buildFile($destination);
					
					$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&unpack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?unpack[]=".urlencode($this->file)."";
					if(count(self::$unpacked)) {
						foreach(self::$unpacked as $file) {
							$uri .= "&unpack[]=" . urlencode($file);
						}
					}
					$this->showUI($file . "?redirect=" . urlencode($uri));
				}
				
			}
			$i++;
			unset($data, $path);
		}
		
		self::$unpacked[] = $this->file;
		
		// clean up
		
		FileSystem::delete($tempfolder);
		
		if(defined("IN_GFS_EXTERNAL")) {
			if(isset($_GET["redirect"]))
				header("Location:" . $_GET["redirect"]);
			exit; 
		}
		return true;
		
	}
	
	/**
	 * if a specific file was unpacked
	 *
	 *@name wasUnpacked
	 *@access public
	*/
	public static function wasUnpacked($file = null) {
		if(isset($file)) {
			$file = realpath($file);
			if(isset($_GET["unpack"]))
				return in_array($file, $_GET["unpack"]);
			else
				return false;
		} else {
			if(isset($_GET["unpack"]))
				return true;
			else
				return false;
		}
	}
	
	/**
	 * builds the Code for the external file
	 *
	 *@name buildFile
	 *@access public
	*/
	public function buildFile($destination) {
		$file = CACHE_DIRECTORY . "gfs.unpack." . md5($this->file) . ".php";
		$code = '<?php define("TIME", time()); define("NOW", TIME); define("IN_GOMA", true); define("PROFILE", false); define("CACHE_DIRECTORY", '.var_export(CACHE_DIRECTORY, true).'); define("ROOT", '.var_export(ROOT, true).'); define("ROOT_PATH", '.var_export(ROOT_PATH, true).');  define("BASE_URI", '.var_export(BASE_URI, true).'); define("FRAMEWORK_ROOT", ROOT . "system/"); define("IN_GFS_EXTERNAL", true); chdir(ROOT); error_reporting(E_ALL); defined("INSTALL") OR define("INSTALL", true); define("DEV_MODE", '.var_export(DEV_MODE, true).'); define("EXEC_START_TIME", microtime(true)); define("LOG_FOLDER", '.var_export(LOG_FOLDER, true).'); ';
		
		$code .= 'define("CURRENT_PROJECT", '.var_export(CURRENT_PROJECT, true).'); define("APPLICATION", CURRENT_PROJECT); define("STATUS_ACTIVE", '.var_export(STATUS_ACTIVE, true).'); define("IN_SAFE_MODE", '.var_export(IN_SAFE_MODE, true).'); define("SYSTEM_TPL_PATH", '.var_export(SYSTEM_TPL_PATH, true).'); define("APPLICATION_TPL_PATH", '.var_export(APPLICATION_TPL_PATH, true).');';
		
		// copy some files
		copy(FRAMEWORK_ROOT . "core/Object.php", ROOT . CACHE_DIRECTORY . "gfs.Object.php");
		copy(FRAMEWORK_ROOT . "core/ClassInfo.php", ROOT . CACHE_DIRECTORY . "gfs.ClassInfo.php");
		copy(FRAMEWORK_ROOT . "core/ClassManifest.php", ROOT . CACHE_DIRECTORY . "gfs.ClassManifest.php");
		copy(FRAMEWORK_ROOT . "libs/GFS/gfs.php", ROOT . CACHE_DIRECTORY . "gfs.gfs.php");
		copy(FRAMEWORK_ROOT . "libs/file/FileSystem.php", ROOT . CACHE_DIRECTORY . "filesystem.gfs.php");
		copy(FRAMEWORK_ROOT . "libs/template/tpl.php", ROOT . CACHE_DIRECTORY . "tpl.gfs.php");
		copy(FRAMEWORK_ROOT . "libs/template/template.php", ROOT . CACHE_DIRECTORY . "template.gfs.php");
		copy(FRAMEWORK_ROOT . "core/viewaccessabledata.php", ROOT . CACHE_DIRECTORY . "viewaccess.gfs.php");
		copy(FRAMEWORK_ROOT . "core/Core.php", ROOT . CACHE_DIRECTORY . "core.gfs.php");
		copy(FRAMEWORK_ROOT . "core/requesthandler.php", ROOT . CACHE_DIRECTORY . "requesthandler.gfs.php");
		copy(FRAMEWORK_ROOT . 'libs/http/httpresponse.php', ROOT . CACHE_DIRECTORY . "httpresponse.gfs.php");
		copy(FRAMEWORK_ROOT . 'libs/array/arraylib.php', ROOT . CACHE_DIRECTORY . "arraylib.gfs.php");
		copy(FRAMEWORK_ROOT . 'core/fields/DBField.php', ROOT . CACHE_DIRECTORY . "field.gfs.php");
		// includes
		$code .= 'if(!class_exists("Object")) include_once(ROOT . CACHE_DIRECTORY . "gfs.Object.php");';
		$code .= 'if(!class_exists("ClassInfo")) include_once(ROOT . CACHE_DIRECTORY . "gfs.ClassInfo.php");';
		$code .= 'if(!class_exists("ClassManifest")) include_once(ROOT . CACHE_DIRECTORY . "gfs.ClassManifest.php");';
		$code .= 'if(!class_exists("RequestHandler")) include_once(ROOT . CACHE_DIRECTORY . "requesthandler.gfs.php");';
		$code .= 'if(!class_exists("Core")) include_once(ROOT . CACHE_DIRECTORY . "core.gfs.php");';
		$code .= 'if(!class_exists("viewaccessabledata")) include_once(ROOT . CACHE_DIRECTORY . "viewaccess.gfs.php");';
		$code .= 'if(!class_exists("GFS")) include_once(ROOT . CACHE_DIRECTORY . "gfs.gfs.php");';
		$code .= 'if(!class_exists("FileSystem")) include_once(ROOT . CACHE_DIRECTORY . "filesystem.gfs.php");';
		$code .= 'if(!class_exists("tpl")) include_once(ROOT . CACHE_DIRECTORY . "tpl.gfs.php");';
		$code .= 'if(!class_exists("template")) include_once(ROOT . CACHE_DIRECTORY . "template.gfs.php");';
		$code .= 'if(!class_exists("httpresponse")) include_once(ROOT . CACHE_DIRECTORY . "httpresponse.gfs.php");';
		$code .= 'if(!class_exists("arraylib")) include_once(ROOT . CACHE_DIRECTORY . "arraylib.gfs.php");';
		$code .= 'if(!class_exists("DBField")) include_once(ROOT . CACHE_DIRECTORY . "field.gfs.php");';
		$code .= '$gfs = new GFS_Package_Installer('.var_export($this->file, true).');';
		$code .= '$gfs->unpack('.var_export($destination, true).');';
		FileSystem::write(ROOT . $file, $code);
		return $file;
 
	}
	
	/**
	 * shows the ui
	 *
	 *@name showUI
	 *@access public
	*/
	public function showUI($file = "",$reload = true) {
		if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)
		
		$template = new Template;
		$template->assign("destination", $file);
		$template->assign("reload", $reload);
		$template->assign("archive", basename($this->file));
		$template->assign("progress", $this->progress);
		$template->assign("status", $this->status);
		$template->assign("current", $this->current);
		$template->assign("remaining", $this->remaining);
		echo $template->display("/system/templates/GFSUnpacker.html");
		exit;
	}
}

class GFS_Package_Creator extends GFS {
	public $status;
	public $current;
	public $progress;
	public $remaining;
	
	// packed files for evantually later reload
	static public $packed = array();
	
	/**
	 * defines if we commit changes after adding files
	 *
	 *@name autoCommit
	 *@access public
	*/
	public $autoCommit = true;
	
	/**
	 * index of files of the next operation
	 *
	 *@name fileIndex
	 *@access protected
	*/
	protected $fileIndex = array();
	
	/**
	 * construct with read-only
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($filename) {
		parent::__construct($filename, GFS_READWRITE);
	}
	
	/**
	 * adds a folder
	 *
	 *@name add
	 *@access public
	 *@param string - directory which we add
	 *@param string - path to which we write
	 *@param array - subfolder, we want to exclude
	*/
	public function add($file, $path = "", $excludeList = array()){
	
		
		
		// create index
		
		$this->indexHelper($file, $this->fileIndex, $path, $excludeList);
		
		if($this->autoCommit) {
			$this->commit();
		}
		
		return true;
		
	}
	
	/**
	 * sets the value of auto-commit
	 *
	 *@name setAutoCommit
	 *@access public
	 *@param bool
	*/
	public function setAutoCommit($commit) {
		$this->autoCommit = $commit;
	}
	
	/**
	 * commits the changes
	 *
	 *@name commit
	 *@access public
	*/
	public function commit($inFile = null, $index = null) {
		if(isset($index)) {
			$this->fileIndex = $index;
		}
		
		// Adding files...
		$this->status = "Adding files...";
		$this->current = "";
		
		// for reloading early enough
		$start = microtime(true);
		if($start - EXEC_START_TIME > 5) {
			$start += 0.9;
		}
		
		// create index-progress-file
		if($this->exists("/gfsprogress" . count($this->fileIndex))) {
			$data = $this->getFileContents("/gfsprogress" . count($this->fileIndex));
			$data = unserialize($data);
			$i = $data["i"];
			$count = $data["count"];
		} else {
			$count = 1;
			$i = 0;
			$this->addFile("/gfsprogress" . count($this->fileIndex), serialize(array("i" => $i, "count" => $count)));
		}
		
		$realfiles = array_keys($this->fileIndex);
		$paths = array_values($this->fileIndex);
		
		// iterate through the index
		while($i < count($this->fileIndex)){
			// maximum of 1.0 seconds
			if(microtime(true) - $start < 1.0) {
				if(!$this->exists($paths[$i])) {
					$this->addFromFile($realfiles[$i], $paths[$i]);
				}
			} else {
				$count++;
				$this->write("/gfsprogress" . count($this->fileIndex), serialize(array("i" => $i, "count" => $count)));
				$this->close();
				$this->progress = ($i / count($this->fileIndex) * 100);
				$perhit = $i / $count;
				$remaining = (round((count($index) - $i) / $perhit * 3) + 3);
				$this->current = $paths[$i];
				if($remaining > 60) {
					$remaining = round($remaining / 60);
					if($remaining > 60) {
						$remaining = round($remaining / 60);
						$this->remaining = "More than ".$remaining." hours remaining";
					} else {
						$this->remaining = "More than ".$remaining." minutes remaining";
					}
				} else {
					$this->remaining = "More than ".$remaining." seconds remaining";
				}
				
				if(!isset($inFile)) {
					// build the external file and redirect-uri
					$file = $this->buildFile($this->fileIndex);
					$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&pack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?pack[]=".urlencode($this->file)."";
					if(count(self::$packed)) {
						foreach(self::$packed as $file) {
							$uri .= "&pack[]=" . urlencode($file);
						}
					}
					$this->showUI($file . "?redirect=" . urlencode($uri));
				} else {
					// if we are in the external file
				 $this->showUI();	
			}
			}
			$i++;
		}
		
		self::$packed[$this->file] = $this->file;
		$this->unlink("/gfsprogress" . count($this->fileIndex));
		$this->fileIndex = array();
		
		// if we are in the external file
		if(isset($inFile)) {
			@unlink($inFile);
			if(isset($_GET["redirect"])) {
				header("Location:" . $_GET["redirect"]);
				exit;
			} else {
				header("Location:" . ROOT_PATH);
				exit;
			}
		}
	}
	
	 /**
	 * if a specific file was packed
	 *
	 *@name wasPacked
	 *@access public
	*/
	public static function wasPacked($file = null) {
		if(isset($file)) {
			if(isset($_GET["pack"])) {
				$file = realpath($file);
				return in_array($file, $_GET["pack"]);
			} else {
				return false;
			}
		} else {
			if(isset($_GET["pack"]))
				return true;
			else
				return false;
		}
	}
	
	/**
	 * builds the Code for the external file
	 *
	 *@name buildFile
	 *@access public
	*/
	public function buildFile($index) {
		$file = CACHE_DIRECTORY . "gfs." . md5($this->file) . ".php";
		$code = '<?php define("TIME", time()); define("NOW", TIME); define("IN_GOMA", true); define("PROFILE", false); define("CACHE_DIRECTORY", '.var_export(CACHE_DIRECTORY, true).'); define("ROOT", '.var_export(ROOT, true).'); define("ROOT_PATH", '.var_export(ROOT_PATH, true).');  define("BASE_URI", '.var_export(BASE_URI, true).'); define("FRAMEWORK_ROOT", ROOT . "system/"); define("IN_GFS_EXTERNAL", true); chdir(ROOT); error_reporting(E_ALL); defined("INSTALL") OR define("INSTALL", true); define("DEV_MODE", '.var_export(DEV_MODE, true).'); define("EXEC_START_TIME", microtime(true)); define("LOG_FOLDER", '.var_export(LOG_FOLDER, true).'); ';
		
		$code .= 'define("CURRENT_PROJECT", '.var_export(CURRENT_PROJECT, true).'); define("APPLICATION", CURRENT_PROJECT); define("STATUS_ACTIVE", '.var_export(STATUS_ACTIVE, true).'); define("IN_SAFE_MODE", '.var_export(IN_SAFE_MODE, true).'); define("SYSTEM_TPL_PATH", '.var_export(SYSTEM_TPL_PATH, true).'); define("APPLICATION_TPL_PATH", '.var_export(APPLICATION_TPL_PATH, true).');';
		
  	 	// copy some files
		copy(FRAMEWORK_ROOT . "core/Object.php", ROOT . CACHE_DIRECTORY . "gfs.Object.php");
		copy(FRAMEWORK_ROOT . "core/ClassInfo.php", ROOT . CACHE_DIRECTORY . "gfs.ClassInfo.php");
		copy(FRAMEWORK_ROOT . "core/ClassManifest.php", ROOT . CACHE_DIRECTORY . "gfs.ClassManifest.php");
		copy(FRAMEWORK_ROOT . "libs/GFS/gfs.php", ROOT . CACHE_DIRECTORY . "gfs.gfs.php");
		copy(FRAMEWORK_ROOT . "libs/file/FileSystem.php", ROOT . CACHE_DIRECTORY . "filesystem.gfs.php");
		copy(FRAMEWORK_ROOT . "libs/template/tpl.php", ROOT . CACHE_DIRECTORY . "tpl.gfs.php");
		copy(FRAMEWORK_ROOT . "libs/template/template.php", ROOT . CACHE_DIRECTORY . "template.gfs.php");
		copy(FRAMEWORK_ROOT . "core/viewaccessabledata.php", ROOT . CACHE_DIRECTORY . "viewaccess.gfs.php");
		copy(FRAMEWORK_ROOT . "core/Core.php", ROOT . CACHE_DIRECTORY . "core.gfs.php");
		copy(FRAMEWORK_ROOT . "core/requesthandler.php", ROOT . CACHE_DIRECTORY . "requesthandler.gfs.php");
		copy(FRAMEWORK_ROOT . 'libs/http/httpresponse.php', ROOT . CACHE_DIRECTORY . "httpresponse.gfs.php");
		copy(FRAMEWORK_ROOT . 'libs/array/arraylib.php', ROOT . CACHE_DIRECTORY . "arraylib.gfs.php");
		copy(FRAMEWORK_ROOT . 'core/fields/DBField.php', ROOT . CACHE_DIRECTORY . "field.gfs.php");
		// includes
		$code .= 'if(!class_exists("Object")) include_once(ROOT . CACHE_DIRECTORY . "gfs.Object.php");';
		$code .= 'if(!class_exists("ClassInfo")) include_once(ROOT . CACHE_DIRECTORY . "gfs.ClassInfo.php");';
		$code .= 'if(!class_exists("ClassManifest")) include_once(ROOT . CACHE_DIRECTORY . "gfs.ClassManifest.php");';
		$code .= 'if(!class_exists("RequestHandler")) include_once(ROOT . CACHE_DIRECTORY . "requesthandler.gfs.php");';
		$code .= 'if(!class_exists("Core")) include_once(ROOT . CACHE_DIRECTORY . "core.gfs.php");';
		$code .= 'if(!class_exists("viewaccessabledata")) include_once(ROOT . CACHE_DIRECTORY . "viewaccess.gfs.php");';
		$code .= 'if(!class_exists("GFS")) include_once(ROOT . CACHE_DIRECTORY . "gfs.gfs.php");';
		$code .= 'if(!class_exists("FileSystem")) include_once(ROOT . CACHE_DIRECTORY . "filesystem.gfs.php");';
		$code .= 'if(!class_exists("tpl")) include_once(ROOT . CACHE_DIRECTORY . "tpl.gfs.php");';
		$code .= 'if(!class_exists("template")) include_once(ROOT . CACHE_DIRECTORY . "template.gfs.php");';
		$code .= 'if(!class_exists("httpresponse")) include_once(ROOT . CACHE_DIRECTORY . "httpresponse.gfs.php");';
		$code .= 'if(!class_exists("arraylib")) include_once(ROOT . CACHE_DIRECTORY . "arraylib.gfs.php");';
		$code .= 'if(!class_exists("DBField")) include_once(ROOT . CACHE_DIRECTORY . "field.gfs.php");';
		$code .= '$gfs = new GFS_Package_Creator('.var_export($this->file, true).');';
		$code .= '$gfs->commit(__FILE__, '.var_export($index, true).');';
		FileSystem::write(ROOT . $file, $code);
		return $file;

	}
	
	/**
	 * creates the index
	 *
	 *@name indexHelper
	 *@access public
	*/ 
	public function indexHelper($folder, &$index, $path, $excludeList = array(), $internalPath = "") {
		foreach(scandir($folder) as $file) {
			if($file != "." && $file != "..") {
				if(in_array($file, $excludeList) || in_array($internalPath . "/" . $file, $excludeList)) {
					continue;
				}
				if(is_dir($folder . "/" . $file)) {
					$this->indexHelper($folder . "/" . $file, $index, $path . "/" . $file, $excludeList, $internalPath . "/" . $file);
				} else {
					$index[$folder . "/" . $file] = $path . "/" . $file;
				}
			}
		}
	}
	/**
	 * shows the ui
	 *
	 *@name showUI
	 *@access public
	*/
	public function showUI($file = null, $reload = true) {
		if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)
		
		$template = new Template;
		$template->assign("destination", $file);
		$template->assign("reload", $reload);
		$template->assign("archive", basename($this->file));
		$template->assign("progress", $this->progress);
		$template->assign("status", $this->status);
		$template->assign("current", $this->current);
		$template->assign("remaining", $this->remaining);
		echo $template->display("/system/templates/GFSUnpacker.html");
		exit;
	}
}