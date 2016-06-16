<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for GFS Archive-Managment.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		2.8
 */

define("GFS_DIR_TYPE", "goma_dir");
defined("NOW") OR define("NOW", time());
define("FILESIZE_SAVE_IN_DB", 50);
// be careful with json, it's not binary-safe
define("GFS_DB_TYPE", "serialize");

// flags
define("GFS_READONLY", 2);
define("GFS_READWRITE", 1);

class GFS {
	/**
	 * required version for this class
	*/
	const REQUIRED_VERSION = "2.0";
	
	/**
	 * version for this class
	*/
	const VERSION = "2.6";

	/**
	 * contains whether PHP supports Open-SSL or not.
	*/
	static $openssl_problems = false;
	
	/**
	 * version of the GFS-Library the opeded file was created with
	*/
	public $version;
	
	/**
	 * filepointer
	*/
	protected $pointer;
	
	/**
	 * file
	*/
	public $file;
	
	/**
	 * database
	*/
	protected $db;
	
	/**
	 * valid
	*/
	public $valid;
	
	/**
	 * fileposition
	*/
	public $position = 0;
	
	/**
	 * endofcontentposition
	*/
	protected $endOfContentPos = 0;
	 
	/**
	 * files
	*/
	protected $files = array();
	
	/**
	 * offsetcache
	 * @var array
	*/
	protected $offsetcache = array();
	
	/**
	 * old-db-size
	*/
	protected $oldDBSize;
	
	/**
	 * if this archive is opened as readonly
	 *
	 * @var bool
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
	*/
	private $private;
	
	/**
	 * certificate-validate-cache
	*/
	private $certValidCache;

	/**
	 * file-permissions for files that should be written.
	*/
	protected $writeMode = 0777;

	/**
	 * opens GFS-Archive. It throws some exceptions when something fails.
	 *
	 * @param string $filename
	 * @param int $flag
	 * @param int $writeMode
	 * @throws GFSDBException
	 * @throws GFSFileException
	 * @throws GFSVersionException
	 */
	public function __construct($filename, $flag = null, $writeMode = 0777) {
		$this->file = $filename;
		$this->writeMode = $writeMode;
		if(is_dir($this->file)) {
			$this->valid = false;
			throw new InvalidArgumentException("GFS-File is a Folder.");
		}
		
		if(file_exists($this->file)) {
			clearstatcache(true, $this->file);
			$filesize = filesize($this->file);
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
							$this->valid = false;
							throw new GFSVersionException("Could not open GFS ".$filename.". Version is not supported.");
						}
						
						if(version_compare($version, self::VERSION, ">")) {
							$this->valid = false;
							throw new GFSVersionException("Could not open GFS ".$filename.". Version is not supported.");
						}
						
						$this->setPosition(6 + strlen($version));
						if(fread($this->pointer, 2) != "\n\n") {
							$this->valid = false;
							throw new GFSFileException("Could not open GFS ".$filename.". Malformed file at Start.");
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
								$this->valid = false;
								throw new GFSFileException("Could not open signed GFS ".$filename.". Malformed file at signing.");
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
									$this->valid = false;
									throw new GFSDBException("Could not open GFS ".$filename.". Failed to decode JSON-DB.");
								}
							} else {
								try {
									$data = unserialize($db);
									if($data === false) {
										throw new Exception("Could not unserialize content.");
									}

									$this->db = $data;
								} catch(Exception $e) {
									$this->valid = false;
									throw new GFSDBException("Could not open GFS ".$filename.". Failed to decode Serialized DB. " . print_r($db, true), null, $e);
								}
							}

							unset($data, $db);
							$this->position = strlen("!GFS;V" . $version) + 2;
							$this->endOfContentPos = $filesize - 1 - strlen((string)$dbsize) - $dbsize - 2;
							$this->setPosition();
							$this->valid = true;
						} else {
							$this->valid = false;
							throw new GFSFileException("Could not open GFS ".$filename.". Malformed DataBase.");
						}
					} else {
						$this->valid = false;
						throw new GFSVersionException("Could not open GFS ".$filename.". Malformed Version-Number,");
					}
				} else {
					$this->valid = false;
					throw new GFSFileException("Could not open GFS ".$filename.". Malformed File.");
				}
			} else {
				$this->valid = false;
				throw new LogicException("Could not open GFS " . $filename . ". FileSystem returns an error.");
			}
		} else {
			$this->pointer = fopen($this->file, "wb+");
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
	 * sets the write-mode.
	*/
	public function setWriteMode($mode) {
		$this->writeMode = $mode;
	}

	/**
	 * sets the Position of the pointer.
	 *
	 * @name 	setPosition
	*/
	public function setPosition($position = null) {
		if($position !== null) {
			$this->position = $position;
		}

		fseek($this->pointer, $this->position);
	}

	/**
	 * adds a file.
	 *
	 * @param string $file
	 * @param string $path
	 * @param array $not_add_if_dir
	 * @return bool|int
	 * @throws GFSException
	 * @throws GFSFileExistsException
	 * @throws GFSReadonlyException
	 * @throws GFSRealFileNotFoundException
	 * @throws GFSRealFilePermissionException
	 */
	public function addFromFile($file, $path, $not_add_if_dir = array()) {
		$this->checkValidOrThrow();

		// check if is dir.
		if(is_dir($file)) {
			$this->addDirectoryAndFiles($file, $path, $not_add_if_dir);
			$this->updateDB();
			return;
		}

		// parse path
		$path = $this->parsePath($path);
		
		if(realpath($file) == $this->file || in_array($path, $not_add_if_dir)) {
			return;
		}

		// check if file exists and add it.
		if(is_file($file)) {
			// check if you can create the path
			if(!isset($this->db[$path])) {
				$this->createPath($path);
			} else {
				throw new GFSFileExistsException($path);
			}

			$this->addFileToArchiveWithoutChecks($file, $path);
		} else {
			throw new GFSRealFileNotFoundException($file);
		}
	}

	/**
	 * adds a file without call updateDB. it updates the local copy of db in $this->db.
	 *
	 * @param    string $file
	 * @param    string $path in container
	 * @param     array $not_add_if_dir
	 * @throws GFSException
	 * @throws GFSFileExistsException
	 * @throws GFSReadonlyException
	 * @throws GFSRealFileNotFoundException
	 * @throws GFSRealFilePermissionException
	 */
	protected function addFromFileHelper($file, $path, $not_add_if_dir = array()) {
		$this->checkValidOrThrow();

		// check if is dir.
		if(is_dir($file)) {
			$this->addDirectoryAndFiles($file, $path, $not_add_if_dir);
			return;
		}

		// parse path
		$path = $this->parsePath($path);
				
		if(basename($file) == basename($this->file) || in_array($path,$not_add_if_dir)) { 
			return;
		}
		
		// check if you can create the path
		if(!isset($this->db[$path])) {
			$this->createPath($path);
		} else {
			throw new GFSFileExistsException($path);
		}
		
		if(file_exists($file)) {
			$this->addFileToArchiveWithoutChecks($file, $path);
		} else {
			throw new GFSRealFileNotFoundException($file);
		}
	}

	/**
	 * adds an file to archive.
	 *
	 * @param string $file
	 * @param string $path
	 * @throws GFSRealFileNotFoundException
	 * @throws GFSRealFilePermissionException
	 */
	protected function addFileToArchiveWithoutChecks($file, $path) {
		if(file_exists($file)) {
			if(filesize($file) > FILESIZE_SAVE_IN_DB) {
				$this->setPosition($this->endOfContentPos);
				// read and save memory
				if($filehandle = @fopen($file, "r")) {
					try {
						while (!feof($filehandle)) {
							$content = fgets($filehandle);
							if (fwrite($this->pointer, $content) !== strlen($content)) {
								throw new GFSRealFilePermissionException();
							}
							unset($content);
						}
						$this->db[$path] = array(
							"type"	 			=> $this->getFileType($file),
							"size"	 			=> filesize($file),
							"lastModified"		=> filemtime($file),
							"checksum"			=> "GFS" . md5_file($file),
							"startChunk"		=> $this->endOfContentPos
						);
						$this->endOfContentPos += filesize($file);
					} finally {
						fclose($filehandle);
						unset($filehandle);
						$this->updateDB();
					}
				} else {
					throw new GFSRealFilePermissionException();
				}
			} else {
				$this->db[$path] = array(
					"type"	 			=> $this->getFileType($file),
					"size"	 			=> filesize($file),
					"lastModified"		=> filemtime($file),
					"contents"			=> file_get_contents($file)
				);
				$this->updateDB();
			}
		} else {
			throw new GFSRealFileNotFoundException($file);
		}
	}

	/**
	 * adds content to archive.
	 *
	 * @param string $content
	 * @param string $path
	 * @param null $lastModified
	 * @throws GFSRealFilePermissionException
	 */
	protected function addContentToArchiveWithoutChecks($content, $path, $lastModified = null) {
		if(!isset($lastModified)) {
			$lastModified = time();
		}

		try {
			if (strlen($content) > FILESIZE_SAVE_IN_DB) {
				$this->setPosition($this->endOfContentPos);
				// write and save memory
				if (fwrite($this->pointer, $content) !== strlen($content)) {
					throw new GFSRealFilePermissionException();
				}

				$this->db[$path] = array(
					"type"         => $this->getFileType($path),
					"size"         => strlen($content),
					"lastModified" => $lastModified,
					"checksum"     => "GFS" . md5($content),
					"startChunk"   => $this->endOfContentPos
				);
				$this->endOfContentPos += strlen($content);
			} else {
				$this->db[$path] = array(
					"type"         => $this->getFileType($path),
					"size"         => strlen($content),
					"lastModified" => $lastModified,
					"contents"     => $content
				);
			}
		} finally {
			$this->updateDB();
		}
	}

	/**
	 * adds all files of a directory.
	 * @param string $file
	 * @param string $path
	 * @param array $not_add_if_dir
	 */
	public function addDirectoryAndFiles($file, $path, $not_add_if_dir = array()) {
		$path = $this->parsePath($path);

		// create folder.
		if(!isset($this->db[$path])) {
			// create sub-path.
			$this->createPath($path);

			$this->db[$path] = array(
				"type"	 			=> GFS_DIR_TYPE,
				"lastModified"		=> filemtime($file)
			);
		}

		foreach(scandir($file) as $_file) {
			if($_file != "." && $_file != "..") {
				$this->addFromFileHelper($file . "/" . $_file, $path . "/" . $_file, $not_add_if_dir);
			}
		}
	}

	/**
	 * adds a Directory
	 *
	 * @param string $path
	 * @return bool|int
	 * @throws GFSException
	 */
	public function addDir($path) {
		$this->checkValidOrThrow();

		// parse path
		$path = $this->parsePath($path);
		
		
		// check if you can create the path
		if(!isset($this->db[$path])) {
			$this->createPath($path);
		} else {
			throw new GFSFileNotFoundException();
		}
		
		$this->db[$path] = array(
			"type"  	 	=> GFS_DIR_TYPE,
			"lastModified"	=> TIME
		);
		$this->updateDB();
	}

	/**
	 * returns filesize of given file.
	 *
	 * @param    string $path
	 * @return int
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 */
	public function getFileSize($path) {
		$this->checkValidOrThrow(false);

		// parse path
		$path = $this->parsePath($path);

		if(isset($this->db[$path])) {
			if(isset($this->db[$path]["size"])) {
				return $this->db[$path]["size"];
			} else {
				// folders have size 0.
				return 0;
			}

		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * returns when a file was last modified.
	 *
	 * @param  string $path
	 * @return int
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 */
	public function getLastModified($path) {
		$this->checkValidOrThrow(false);

		// parse path
		$path = $this->parsePath($path);

		if(isset($this->db[$path])) {
			if(isset($this->db[$path]["lastModified"])) {
				return $this->db[$path]["lastModified"];
			} else if(isset($this->db[$path]["lastModfied"])) {
				return $this->db[$path]["lastModfied"];
			} else {
				throw new LogicException("Every file-entry needs last modified.");
			}

		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * adds a file
	 *
	 * @param string $path
	 * @param string $content
	 * @param int $lastModified
	 * @return bool|int
	 * @throws GFSException
	 */
	public function addFile($path, $content, $lastModified = null) {
		$this->checkValidOrThrow();

		// parse path
		$path = $this->parsePath($path);
		
		// check if file is already existing.
		if(!isset($this->db[$path])) {
			$this->createPath($path);
		} else {
			throw new GFSFileExistsException();
		}

		$this->addContentToArchiveWithoutChecks($content, $path, $lastModified);
	}

	/**
	 * create all folders until the given file-path.
	 *
	 * @name createPath
	*/
	protected function createPath($path) {
		// parse path
		$path = $this->parsePath($path);

		if(strpos($path, "/")) {
			$pathparts = preg_split("/\//",$path, -1, PREG_SPLIT_NO_EMPTY);
			$i = 1;
			$currpath = "";
			foreach($pathparts as $part) {
				if(count($pathparts) != $i) {
					$currpath = $currpath . "/" . $part;
					if(!$this->exists($currpath)) {
						$this->addDir($currpath);
					}
				}
				$i++;
			}
		}
	}

	/**
	 * gets the filetype = extension
	 *
	 * @param string $file
	 * @return null|string
	 */
	public function getFileType($file) {
		if(strpos($file, ".") !== false)
			return substr($file, strrpos($file, ".") + 1);
		
		return null;
	}

	/**
	 * gets contents of a file
	 *
	 * @param string $path
	 * @return int|string
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSFileNotValidException
	 * @throws GFSReadonlyException
	 */
	public function getFileContents($path) {
		$this->checkValidOrThrow(false);

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
				throw new GFSFileNotFoundException();
			}

			// go to position of file
			fseek($this->pointer, $offset);
			$content = fread($this->pointer, $this->db[$path]["size"]);
			if("GFS" . md5($content) == $this->db[$path]["checksum"]) {
				return $content;
			} else {
				throw new GFSFileNotValidException();
			}
		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * getDBInfo
	 *
	 * @param string $path
	 * @return array
	 * @throws FileNotFoundException
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function getDBInfo($path) {
		$this->checkValidOrThrow(false);
		
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path])) {
			return $this->db[$path];
		}

		throw new FileNotFoundException();
	}

	/**
	 * returns if file exists.
	 * @param string $path
	 * @return bool
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function exists($path) {
		$this->checkValidOrThrow(false);
		
		$path = $this->parsePath($path);
		
		return isset($this->db[$path]) ? true : false;
	}

	/**
	 * returns if file is folder.
	 * @param string $path
	 * @return bool
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function isDir($path) {
		$this->checkValidOrThrow(false);
		
		$path = $this->parsePath($path);
		
		return (isset($this->db[$path]) && $this->db[$path]["type"] == GFS_DIR_TYPE) ? true : false;
	}

	/**
	 * returns md5-hash of file.
	 * @param $path
	 * @return bool|string
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function getMd5($path) {
		$this->checkValidOrThrow(false);
		
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path]) && isset($this->db[$path]["checksum"])) {
			return substr($this->db[$path]["checksum"], 3);
		} else if(isset($this->db[$path]["contents"])) {
			return md5($this->db[$path]["contents"]);
		} else {
			throw new GFSFileNotFoundException();
		}
	}
	
	/**
	 * returns the complete database
	*/
	public function getDB() {
		return $this->db;
	}

	/**
	 * gets contents of a directory
	 * @param string $path
	 * @return array
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 */
	public function scanDir($path) {
		$this->checkValidOrThrow(false);
		
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
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * remove
	 * we just unset the data in db
	 * @param string $path
	 * @return bool|int|void
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 * @throws GFSRealFilePermissionException
	 */
	public function unlink($path) {
		$this->checkValidOrThrow();

		// parse path
		$path = $this->parsePath($path);

		if(isset($this->db[$path])) {
			if($this->db[$path]["type"] == GFS_DIR_TYPE) {
				$this->rmdir($path);
			} else {
				if(isset($this->db[$path]["startChunk"])) {
					if($this->db[$path]["startChunk"] + $this->db[$path]["size"] == $this->endOfContentPos) {
						$this->endOfContentPos = $this->db[$path]["size"];
					}
				}
				
				unset($this->db[$path]);
				$this->updateDB();
			}
		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * deletes a directory recursivly
	 *
	 * @param string $path
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 * @throws GFSRealFilePermissionException
	 */
	public function rmdir($path) {
		$this->checkValidOrThrow();
		
		$path = $this->parsePath($path);
		if(!isset($this->db[$path]) || $this->db[$path]["type"] != GFS_DIR_TYPE) {
			throw new GFSFileNotFoundException();
		}
		// first find files in path, but go from last to first to have better caches
		$db = array_reverse($this->db);
		
		// close before read
		fclose($this->pointer);
		foreach($db as $file => $data) {
			if(preg_match("/^".preg_quote($path, "/")."\//", $file)) {
				unset($db[$file], $file, $data);
			}
		}
		
		$this->updateDB();
	}

	/**
	 * gets last modfied of given file
	 * @param string $path
	 * @return int
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function filemTime($path) {
		$this->checkValidOrThrow(false);
		
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path]["last_modfied"])) {
			return $this->db[$path]["last_modfied"];
		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * rename
	 * @param string $path
	 * @param string $new
	 * @throws GFSException
	 * @throws GFSFileExistsException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 * @throws GFSRealFilePermissionException
	 */
	public function rename($path, $new) {
		$this->checkValidOrThrow();

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
				$this->updateDB();
			} else {
				$new = substr($path, 0, strrpos($path, "/")) . "/" . $new;
				if(substr($new, 0, 1) == "/")
					$new = substr($new, 1);

				if(!isset($this->db[$new])) {
					$this->db[$new] = $this->db[$path];
					$this->db[$new]["type"] = $this->getFileType($new);
					unset($this->db[$path]);
					$this->updateDB();
				} else {
					throw new GFSFileExistsException();
				}
			}
		} else {
			throw new GFSFileNotFoundException();
		}
	}
	
	/**
	 * sets the last modfied-meta-tag to the current time
	 *
	 * @param string $path
	 * @param int $time
	*/
	public function touch($path, $time = NOW) {
		$this->checkValidOrThrow();
		
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path])) {
			if($this->db[$path]["type"] != GFS_DIR_TYPE) {
				$this->db[$path]["lastModified"] = $time;
			}
			unset($path);
			$this->updateDB();
		} else {
			$this->addFile($path, "", $time);
		}
	}

	/**
	 * Writes $text in $path
	 *
	 * @param string $path
	 * @param string $text
	 * @throws GFSReadonlyException
	 */
	public function write($path, $text)
	{
		$this->checkValidOrThrow();
		
		$path = $this->parsePath($path);
			
		if(isset($this->db[$path])) {
			$this->addFile($path . ".tmp", $text);
			$this->unlink($path);
			$this->rename($path . ".tmp", $path);
		} else {
			$this->addFile($path, $text);
		} 	 
	}

	/**
	 * unpacks the archive
	 *
	 * @param string $aim directory
	 * @param string $path to start
	 * @throws FileNotPermittedException
	 * @throws GFSFileNotFoundException
	 * @throws GFSRealFilePermissionException
	 */
	public function unpack($aim, $path = "") {
		FileSystem::requireDir($aim);
		if($path == "" || $this->exists($path)) {
			foreach($this->scandir($path) as $file) {
				$fileinfo = $this->getDBInfo($path . "/" . $file);
				if(is_array($fileinfo) && $fileinfo["type"] == GFS_DIR_TYPE) {
					$this->unpack($aim . "/" . $file,$path . "/" . $file);
				} else {
					$this->writeToFileSystem($path . "/" . $file, $aim . "/" . $file);
				}
				unset($file, $fileinfo);
			}
		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * writes a file from the archive to the real filesystem
	 * you should use this function if you write files directly to the filesystem, because with this function you can also write files larger than your RAM-amount
	 *
	 * @param string $path
	 * @param string $aim
	 * @throws GFSException
	 * @throws GFSFileNotFoundException
	 * @throws GFSReadonlyException
	 * @throws GFSRealFilePermissionException
	 */
	public function writeToFileSystem($path, $aim) {
		$this->checkValidOrThrow(false);
		
		// parse path
		$path = $this->parsePath($path);
		
		if(isset($this->db[$path])) {
			// get position of file
			if(isset($this->db[$path]["startChunk"])) {
				$offset = $this->db[$path]["startChunk"];
			} else if(isset($this->db[$path]["contents"]) || $this->db[$path]["contents"] === null) {
				if($pointer = @fopen($aim, "w")) {
					try {
						if (fwrite($pointer, (string)$this->db[$path]["contents"]) !== strlen((string)$this->db[$path]["contents"])) {
							throw new GFSRealFilePermissionException();
						}
					} finally {
						fclose($pointer);
						@chmod($aim, $this->writeMode);
						unset($pointer);
					}
					return;
				} else {
					throw new GFSRealFilePermissionException();
				}
			} else {
				throw new GFSFileNotFoundException();
			}

			$this->setPosition($offset);
			if($pointer = @fopen($aim . ".tmp", "w")) {
				$this->readChunked($pointer, $offset, $offset + $this->db[$path]["size"]);
				fclose($pointer);

				@chmod($aim, $this->writeMode);
				if("GFS" . md5_file($aim . ".tmp") == $this->db[$path]["checksum"]) {
					if(file_exists($aim))
						@unlink($aim);

					if(!rename($aim . ".tmp", $aim)) {
						throw new GFSRealFilePermissionException();
					}
				} else {
					@unlink($aim . ".tmp");
					throw new GFSRealFilePermissionException();
				}
			}
			
		} else {
			throw new GFSFileNotFoundException();
		}
	}

	/**
	 * reads data chunked from this archived and writes it to a pointer.
	 * @param $pointer
	 * @param $start
	 * @param $end
	 * @param int $chunkSize
	 * @return bool
	 */
	public function readChunked($pointer, $start, $end, $chunkSize = 50000) {
		$currentchunk = $start;
		while($currentchunk < $end) {
			if($end - $currentchunk < 500000) {
				$readsize = $end - $currentchunk;
			} else {
				$readsize = 500000;
			}
			fwrite($pointer, fread($this->pointer, $readsize));
			$currentchunk += 500000;
		}
		return true;
	}

	/**
	 * parses a given plist-file and gives back the result as an array
	 * @param string $file
	 * @return array|mixed
	 * @throws DOMException
	 * @throws IOException
	 * @throws PListException
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
	 * @param string $file
	 * @param array $data
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 * @throws PListException
	 */
	public function writePlist($file, $data) {
		$this->checkValidOrThrow();

		$plist = new CFPropertyList();
		
		$td = new CFTypeDetector();  
		$guessedStructure = $td->toCFType($data);
		
		$plist->add($guessedStructure);
		
		$this->write($file, $plist->toXML());
	}
	
	/**
	 * parses paths
	 *
	 * @param string $path
	 * @return string - parsed path
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
	 * writes the database
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function updateDB() {
		$this->checkValidOrThrow();

		$this->setPosition(0);
		if(GFS_DB_TYPE == "serialize") {
			$db = serialize($this->db);
		} else {
			$db = json_encode($this->db);
		}

		ftruncate($this->pointer, $this->endOfContentPos);
		$this->setPosition($this->endOfContentPos);

		if(fwrite($this->pointer, "\n\n" . $db . "\n" . strlen($db)) === false) {
			throw new GFSRealFilePermissionException();
		}

		$this->oldDBSize = strlen($db);
		$this->certValidCache = null;
		$this->certificate = null;
		unset($db);
	}

	/**
	 * returns if this archive is signed
	 *
	 * @param string $publicKey
	 * @return bool
	 */
	public function isSigned($publicKey) {
		$this->checkValidOrThrow(false);
		
		if(isset($this->certValidCache)) {
			return $this->certValidCache;
		}

		if(isset($this->certificate)) {
			if(function_exists("openssl_public_decrypt")) {
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
			} else {
				self::$openssl_problems = true;
				return false;
			}
		}
		
		$this->certValidCache = false;
		return false;
	}

	/**
	 * signs the archive
	 *
	 * @param string $privateKey
	 * @return bool|int
	 * @throws GFSException
	 * @throws GFSReadonlyException
	 */
	public function sign($privateKey) {
		$this->checkValidOrThrow();
		
		$this->certificate = null;
		$this->private = $privateKey;
		$this->certValidCache = null;
				
		return true;
	}

	/**
	 * closes a GFS-Archive
	 * @return bool
	 * @throws GFSRealFilePermissionException
	 */
	public function close() {
		if($this->valid === false) {
			return false;
		}

		if(!$this->readonly) {
			$this->updateDB();
		}
		
		if($this->private && !$this->readonly && function_exists("openssl_private_encrypt")) {
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
				if(fwrite($this->pointer, "\n\n" . $this->certificate . "\n" . strlen($this->certificate) . "!SIGN") === false) {
					throw new GFSRealFilePermissionException();
				}
			}
		} else if(!function_exists("openssl_private_encrypt")) {
			self::$openssl_problems = true;
		}
		
		if(isset($this->pointer)) {
			@fclose($this->pointer);
			@chmod($this->file, $this->writeMode);
		}
		unset($this->db, $this->pointer);
		$this->valid = false;
	}
	
	/**
	 * checks if valid and when not throws exception.
	 *
	 * @throws GFSException
	*/
	protected function checkValidOrThrow($checkReadonly = true) {
		if($this->valid === false) {
			throw new GFSException("GFS-Archive ".$this->file." corrupted.");
		}

		if($this->readonly && $checkReadonly) {
			throw new GFSReadonlyException();
		}

		return true;
	}

	/**
	 * destruct
	*/
	public function __destruct() {
		$this->close();
	}
	
}

class GFSException extends Exception {
	protected $internalCode = ExceptionManager::GFSException;
	public function __construct($m = "", $code = null, Exception $previous = null) {
		parent::__construct($m, $code ? $code : $this->internalCode, $previous);
	}
}
class GFSVersionException extends GFSException {
	protected $internalCode = ExceptionManager::GFSVersionException;
}
class GFSFileException extends GFSException {
	protected $internalCode = ExceptionManager::GFSFileException;
}
class GFSDBException extends GFSException {
	protected $internalCode = ExceptionManager::GFSDBException;
}
class GFSReadonlyException extends GFSException {
	protected $internalCode = ExceptionManager::GFSReadOnlyException;
}
class GFSFileNotFoundException extends GFSException {
	protected $internalCode = ExceptionManager::GFSFileNotFoundException;
}
class GFSFileNotValidException extends GFSException {
	protected $internalCode = ExceptionManager::GFSFileNotValidException;
}
class GFSFileExistsException extends GFSException {
	protected $internalCode = ExceptionManager::GFSFileExistsException;
}
class GFSRealFileNotFoundException extends GFSException {
	protected $internalCode = ExceptionManager::GFSRealFileNotExistsException;
}
class GFSRealFilePermissionException extends GFSException {
	protected $internalCode = ExceptionManager::GFSRealFilePermissionException;
}
