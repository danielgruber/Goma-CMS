<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 31.10.2011
  * $Version 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

define("GFS_DIR_TYPE", "goma_dir");
defined("NOW") OR define("NOW", time());
define("FILESIZE_SAVE_IN_DB", 3072);
// be careful with json, it's not binary-safe
define("GFS_DB_TYPE", "serialize");

class GFS extends Object {
    /**
     * version of this gfs-library
    */
    var $version = "2.0";
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
     *@name __construct
     *@access public
     *@param filename
    */
    public function __construct($filename) {
        parent::__construct();
        
        $this->file = $filename;
        $filesize = @filesize($this->file);
        if(file_exists($filename)) {
        	if($this->pointer = fopen($filename, "rb+")) {
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
	                    if(version_compare($version, $this->version, ">=")) {
	                        $this->valid = true;
	                    } else {
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
	                    
	                    $this->position = $filesize - 11;
	                    // set filepointer to next line
	                    $this->setPosition();
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
            fwrite($this->pointer, "!GFS;V".$this->version."\n\n\n\n[]\n2");
            $this->db = array();
            $this->position = strlen("!GFS;V".$this->version."\n\n");
            $this->endOfContentPos = $this->position;
            $this->setPosition();
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
        
        // parse path
        $path = $this->parsePath($path);
        
        if(basename($file) == $this->file || in_array($path,$not_add_if_dir)) 
                return true;
        
        if(!file_exists($file)) {
        	return -4;
        }
        
        // check if you can create the path
        if(!isset($this->db[$path])) {
            if(strpos($path, "/")) {
                $pathparts = explode("/",$path);
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
	            		fwrite($this->pointer, fgets($filehandle));
	            	}
	            	fclose($filehandle);
	            	unset($filehandle);
	           		$this->db[$path] = array(
	               	 	"type"             	=> $this->getFileType($file),
	                	"size"             	=> filesize($file),
	                	"lastModfied"    	=> filemtime($file),
	                	"checksum"        	=> "GFS" . md5_file($file),
	                	"startChunk"		=> $this->endOfContentPos
	            	);
	            	$this->endOfContentPos += filesize($file);
	           	} else {
	           		return false;
	           	}
	        } else {
	        	$this->db[$path] = array(
               	 	"type"             	=> $this->getFileType($file),
                	"size"             	=> filesize($file),
                	"lastModfied"    	=> filemtime($file),
                	"contents"			=> file_get_contents($file)
            	);
	        }
            return $this->updateDB();
        } else if(is_dir($file)) {
            $this->db[$path] = array(
                "type"             => GFS_DIR_TYPE,
                "lastModfied"    => filemtime($file),
                "size"            => 0
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
        
        
        // parse path
        $path = $this->parsePath($path);
                
                
        if(basename($file) == basename($this->file) || in_array($path,$not_add_if_dir)) 
                return true;
        
        // check if you can create the path
        if(!isset($this->db[$path])) {
            if(strpos($path, "/")) {
                $pathparts = explode("/",$path);
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
	               	 	"type"             	=> $this->getFileType($file),
	                	"size"             	=> filesize($file),
	                	"lastModfied"    	=> filemtime($file),
	                	"checksum"        	=> "GFS" . md5_file($file),
	                	"startChunk"		=> $this->endOfContentPos
	            	);
	            	$this->endOfContentPos += filesize($file);
	           	} else {
	           		return false;
	           	}
	        } else {
	        	$this->db[$path] = array(
               	 	"type"             	=> $this->getFileType($file),
                	"size"             	=> filesize($file),
                	"lastModfied"    	=> filemtime($file),
                	"contents"			=> file_get_contents($file)
            	);
	        }
            unset($file, $path);
            return true;
        } else if(is_dir($file)) {
            $this->db[$path] = array(
                "type"             	=> GFS_DIR_TYPE,
                "lastModfied"    	=> filemtime($file),
                "size"            	=> 0
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
        
        // parse path
        $path = $this->parsePath($path);
        
        // check if you can create the path
        if(!isset($this->db[$path])) {
            if(strpos($path, "/")) {
                $pathparts = explode("/",$path);
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
            "type"           => GFS_DIR_TYPE,
            "lastModfied"    => TIME
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
        
        // parse path
        $path = $this->parsePath($path);
        
        // check if you can create the path
        if(!isset($this->db[$path])) {
            if(strpos($path, "/")) {
                $pathparts = explode("/",$path);
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
	            "type"             	=> $this->getFileType($path),
	            "size"             	=> strlen($content),
	            "lastModfied"    	=> time(),
	            "checksum"        	=> "GFS" . md5($content),
	            "startChunk"		=> $this->endOfContentPos
	        );
	        $this->setPosition($this->endOfContentPos);
	        fwrite($this->pointer, $content);
	        $this->endOfContentPos += strlen($content);
        } else {
        	$this->db[$path] = array(
	            "type"             	=> $this->getFileType($path),
	            "size"             	=> strlen($content),
	            "lastModfied"    	=> time(),
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
    	if($this->valid === false) {
    		return false;
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
            	$new = substr($path, 0, strrpos("/", $path)) . "/" . $new;
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
            $path = $this->parsePath($path);
            
            if(isset($this->db[$path])) {
                if($err = $this->addFile($path . ".tmp", $text) === true) {
                    $this->unlink($path);
                    return $this->rename($path . ".tmp", $path);
                } else {
                    return $err;
                }
            } else {
                return -4;
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
                	return $this->writeToFileSystem($path . "/" . $file, $aim . "/" . $file);
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
        
        return $path;
    }
    /**
     * closes a GFS-Archive
     *
     *@name close
     *@access public
    */
    public function close() {
        $this->updateDB();
        if(isset($this->pointer))
            fclose($this->pointer);
        unset($this->db, $this->pointer);
        $this->valid = false;
    }
    
    /**
     * desruct
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
     * unpack
     *
     *@name unpack
     *@access public
     *@param string - directory to which we unpack
    */
    public function unpack($destination) {
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
            if(_ereg('^[0-9]+$', $data)) {
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
            if(microtime(true) - $start > 0.5) {
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
                $this->showUI();
            }
            $i++;
            unset($data, $path);
        }
        
        // now move all files
        if(file_exists($tempfolder . "/.gfsrprogess")) {
            
            $data = file_get_contents($tempfolder . "/.gfsrprogess");
            if(_ereg('^[0-9]+$', $data)) {
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
            if(microtime(true) - $start > 0.5) {
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
                $this->showUI();
            }
            $i++;
            unset($data, $path);
        }
        
        // clean up
        
        FileSystem::delete($tempfolder);
        
        return true;
        
    }
   
    /**
     * shows the ui
     *
     *@name showUI
     *@access public
    */
    public function showUI($reload = true) {
        if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)
        
        $template = new Template;
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
    /**
     * adds a folder
     *
     *@name add
     *@access public
     *@param string - directory which we add
     *@param string - path to which we write
     *@param array - subfolder, we want to exclude
    */
    public function add($file, $path = "", $excludeList = array()) {
      
        
        // Adding files...
        $this->status = "Adding files...";
        $this->current = "";
        
        // we get time, if it is over 2, we reload ;)
        $start = microtime(true);
        $number = count($this->db);
        if($this->exists("/gfsprogress")) {
            
            $data = $this->getFileContents("/gfsprogress");
            $data = unserialize($data);
            $i = $data["i"];
            $count = $data["count"];
            
            
        } else {
        	$count = 1;
        	$i = 0;
          	$this->addFile("/gfsprogress", serialize(array("i" => $i, "count" => $count)));
        }
        
        // create index
        $cacher = new Cacher("gfs.index.".md5($file)."");
        if($cacher->checkvalid()) {
        	$index = $cacher->getData();
        } else {
        	$index = array();
        	$this->indexHelper($file, $index, "", $excludeList);
        	$cacher->write($index, 60);
        }
        
        $realfiles = array_keys($index);
        $paths = array_values($index);
        
        while($i < count($index)){
        	// maximum of 0.5 seconds
            if(microtime(true) - $start < 1.0) {
            	if(!$this->exists($path . $paths[$i])) {
            		$this->addFromFile($realfiles[$i], $path . $paths[$i]);
            	}
            } else {
            	$count++;
                $this->write("/gfsprogress", serialize(array("i" => $i, "count" => $count)));
                $this->close();
                $this->progress = ($i / count($index) * 100);
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
                $this->showUI();
            }
            $i++;
        }
        $this->unlink("/gfsprogress");
        
        return true;
        
    }
    /**
     * creates the index
     *
     *@name indexHelper
     *@access public
    */ 
    public function indexHelper($folder, &$index, $path, $excludeList = array()) {
    	foreach(scandir($folder) as $file) {
    		if($file != "." && $file != "..") {
    			if(in_array($file, $excludeList) || in_array($path . "/" . $file, $excludeList)) {
    				continue;
    			}
    			if(is_dir($folder . "/" . $file)) {
    				$index[$folder . "/" . $file] = $path . "/" . $file;
    				$this->indexHelper($folder . "/" . $file, $index, $path . "/" . $file, $excludeList);
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
    public function showUI($reload = true) {
        if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)
        
        $template = new Template;
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