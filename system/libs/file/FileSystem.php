<?php defined('IN_GOMA') OR die();
/**
 * File-System class to map all FileSystem calls with Goma-Specific updates.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.7.3
 *
 * last modified: 30.05.2015
*/

define('LANGUAGE_ROOT', ROOT . '/languages/');
define('IMAGE_ROOT', ROOT . '/images/');
define('UPLOADS_ROOT', ROOT . '/uploads/');
define('HTACCESS_FILE', ROOT . '.htaccess');

class FileSystem extends gObject {
	/**
	 * this is the last file which causes an error
	 *
	 * @name errFile
	 * @access public
	*/
	protected static $errFile;

	/**
	 * safe-mode.
	 * When enabled all files and folders are created with 0755.
	 * When disabled all files and folders are created with 0777.
	 * you can call applySafeMode() to update all existing files.
	 *
	 * @param boolean
	*/
	public static $safe_mode = false;

	/**
	 * folders on which safe-mode is applied.
	 *
	 * applySafeModeFolders
	*/
	public static $applySafeModeFolders = array(
		FRAMEWORK_ROOT,
		APP_FOLDER,
		LANGUAGE_ROOT,
		IMAGE_ROOT,
		UPLOADS_ROOT,
		HTACCESS_FILE
	);

	/**
	 * this is the last file which causes an error
	 *
	 * @name errFile
	 * @access public
	 * @return string
	 */
	public static function errFile() {
		$file = self::$errFile;
		if(substr($file, 0, strlen(ROOT)) == ROOT) {
			return substr($file, strlen(ROOT));
		} else {
			return $file;
		}
	}
	
	/**
	 * get mode from safe-mode.
	*/
	public static function getMode($mode = null) {
		if($mode === null) {
			$mode = (!self::$safe_mode) ? 0777 : 0755;
		}

		return $mode;
	}

	/**
	 * creates a directory and forces chmod safe-mode-specific or given mode
	 *
	 * @param string $dir
	 * @param int $mode
	 * @param bool $throwOnFail
	 * @return bool
	 * @throws FileNotPermittedException
	 */
	public static function requireDir($dir, $mode = null, $throwOnFail = true) {

		$mode = self::getMode($mode);

		clearstatcache();
		if(!file_exists($dir)) {
			if(mkdir($dir, $mode, true)) {
				@chmod($dir, $mode);
				return true;
			} else {
				if($throwOnFail) {
					throw new FileNotPermittedException("Could not create folder '" . $dir . "'.");
				}
				self::$errFile = $dir;
				return false;
			}
		} else {
			if(fileperms($dir) == $mode)
				return true;
			
			@chmod($dir, $mode);
			return true;
		}
	}

	/**
	 * alias for requireDir
	 *
	 * @param string $dir
	 * @param int $mode
	 * @return bool
	 */
	public static function requireFolder($dir, $mode = null) {
		return self::requireDir($dir, $mode);
	}

	/**
	 * createFile
	 *
	 * @param $file
	 * @return bool
	 * @throws FileExistsException
	 * @access public
	 */
	public static function createFile($file) {
		if(!file_exists($file)) {
			if($handle = @fopen($file, "w")) {
				fclose($handle);
				chmod($file, self::getMode());
				return true;
			} else {
				self::$errFile = $file;
				return false;
			}
		} else {
			throw new FileExistsException("Can't create file: File exists.");
		}
	}

    /**
     * writes file contentss
     *
     * @param string $file
     * @param string $content
     * @param int $modifier
     * @param int $mode permission of file
     * @return bool
     * @access public
     */
	public static function writeFileContents($file, $content, $modifier = null, $mode = null) {
		if(@file_put_contents($file, $content, $modifier)) {
			@chmod($file, self::getMode($mode));
			return true;
		} else {
			return false;
		}
	}

    /**
     * alias for writeFileContents
     *
     * @name write
     * @access public
     * @return bool
     */
	public static function write($file, $content, $modifier = null, $mode = null) {
		return self::writeFileContents($file, $content, $modifier, $mode);
	}

    /**
     * sets chmod recursively.
     *
     * @param    string $file path
     * @param    int $mode
     * @param    bool $breakOnFail if to break and return false on fail
     * @param    bool $tryOwn if try to own all files, that we can set perm-mode easy
     * @return bool
     */
	public static function chmod($file, $mode, $breakOnFail = true, $tryOwn = false) {
		$r = @chmod($file, $mode);

		// maybe try to own this file.
		if($tryOwn && fileowner($file) != getmyuid()) {
			if(self::tryOwn($file)) {
				$r = @chmod($file, $mode);
			}
		}

		if(!$r) {
			self::$errFile = $file;
		}

		if(is_dir($file) && ($r || !$breakOnFail)) {
			foreach(scandir($file) as $_file) {
				if($_file != "." && $_file != "..") {
					if(!self::chmod($file . "/" . $_file, $mode, $breakOnFail, $tryOwn) && $breakOnFail) {
						return false;
					}
				}
			}
		}

		return $r;
		
	}

    /**
     * removes recursively
     *
     * @name delete
     * @access public
     * @param string $file path
     * @param bool $breakOnFail if to break and return false on fail
     * @return bool
     */
	public static function delete($file, $breakOnFail = true) {
		if(is_dir($file)) {
			foreach(scandir($file) as $_file) {
				if($_file != "." && $_file != "..") {
                    if (!self::delete($file . "/" . $_file, $breakOnFail) && $breakOnFail) {
                        return false;
                    }
                }
			}
			return @rmdir($file);
		} else {
			return @unlink($file);
		}
	}

    /**
     * copies recursivly
     *
     * @param string $source
     * @param string $destination
     * @param bool $breakOnFail if to break and return false on fail
     * @return bool
     */
	public static function copy($source, $destination, $breakOnFail = true) {
		return self::executeFunction("copy", $source, $destination, $breakOnFail);
	}

    /**
     * moves files recursivly. it preserves the old folder-structure,
     * cause it will just require all folders and move all files.
     *
     * @param    string $source
     * @param    string $destination
     * @param    bool $breakOnFail if to break and return false on fail
     * @return bool
     */
    public static function move($source, $destination, $breakOnFail = true) {
        return self::executeFunction("rename", $source, $destination, $breakOnFail);
    }

    /**
     * executes given operation on files with source, destination and breakOnFail.
     *
     * @param callback $callback
     * @param string $source
     * @param string $destination
     * @param bool $breakOnFail
     * @return bool
     */
    protected static function executeFunction($callback, $source, $destination, $breakOnFail = true) {
        if(!$source || !$destination) {
            throw new InvalidArgumentException("Source and Destination are required for FileSystem::executeFunction.");
        }

        if(is_dir($source)) {
            if(!self::requireDir($destination, null, false) && $breakOnFail){
                return false;
            }

            foreach(scandir($source) as $file) {
                if($file != "." && $file != "..") {
                    if(!self::executeFunction($callback, $source . "/" . $file, $destination . "/" . $file, $breakOnFail) && $breakOnFail) {
                        return false;
                    }
                }
            }
            return true;
        } else {
            if(file_exists($destination) && !@unlink($destination)) {
                self::$errFile = $destination;
                return false;
            }

            if(call_user_func_array($callback, array($source, $destination))) {
                return true;
            } else {
                self::$errFile = $source;
                return false;
            }
        }
    }

    /**
     * moves recursivly with logging
     *
     * @name    moveLogged
     * @access    public
     * @param    string - source
     * @param    string - destination
     * @param    bool - if to break and return false on fail
     * @param    internal variable - for the log
     * @return bool|string
     */
	public static function moveLogged($source, $destination, $breakOnFail = true, $useLog = false) {
		$log = "#: ";
		
		if(!$source || !$destination) {
            throw new InvalidArgumentException("Source and Destination are required for FileSystem::moveLogged.");
		}
		
		if(is_dir($source)) {
			if(!self::requireDir($destination) && $breakOnFail){
				return false;
			}
			
			foreach(scandir($source) as $file) {
				if($file != "." && $file != "..") {
					if(($return = self::moveLogged($source . "/" . $file, $destination . "/" . $file, $breakOnFail)) === false && $breakOnFail) {
						return false;
					}
					
					if($return === false) {
						$log .= "Failed: {$source}/{$file} => {$destination}/{$file}\n";
					}
					
					if(is_string($return))
						$log .= $return;
				}
			}
			return $log;
		} else {
			if(file_exists($destination) && !@unlink($destination)) {
				self::$errFile = $destination;
				return false;
			}
			
			if(rename($source, $destination)) {
				$log .= "{$source} => {$destination} \n";
				return $log;
			} else {
				self::$errFile = $source;
				return false;
			}
		}
	}

	/**
	 * moves all files by rename within a folder.
	 * 
	 * @name 	moveFolderContents
	 * @param 	string source-folder
	 * @param 	string destination-folder
	 * @param 	boolean if to return an array of errors or a boolean
	 * @return 	boolean|array
	*/
	public static function moveFolderContents($source, $destination, $giveErrors = false) {
		if(!is_dir($source)) {
			return array($source);
		}

		FileSystem::requireDir($destination);

		$errors = array();
		foreach(scandir($source) as $file) {
			if($file != "." && $file != "..") {
				if(!@rename($source . "/" . $file, $destination . "/" . $file)) {
					$errors[] = $source . "/" . $file;
				}
			}
		}

		if($giveErrors) {
			return $errors;
		}

		return (count($errors) == 0);
	}

    /**
     * protects file-path
     *
     * @return mixed
     */
	public static function protect($path) {
		return str_replace("../", "", $path);
	}

    /**
     * sends a file to browser in chunks, because of less RAM-Usage
     *
     * @name readfile_chunked
     * @access public
     * @return bool
     */
	public static function readfile_chunked($filename) {
		$range = 0; 
		$size = filesize($filename); 
	
		if(isset($_SERVER['HTTP_RANGE'])) { 
			list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']); 
			str_replace($range, "-", $range); 
			$size2 = $size - 1; 
			$new_length = $size - $range; 
			HTTPResponse::setResHeader(206);
			HTTPResponse::addHeader("content-length", $new_length);
			HTTPResponse::addHeader("content-range", "bytes " . $range . $size2 . "/" . $size);
		} else { 
			$size2 = $size-1; 
			HTTPResponse::addHeader("content-range", "bytes 0-".$size2 . "/" . $size."");
			HTTPResponse::addHeader("content-length", $size);
		} 
		HTTPResponse::addHeader("Accept-Ranges", "bytes");		
		// send headers now
		HTTPResponse::sendHeader();
	
		ini_set('max_execution_time', '0');  
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$handle = fopen($filename, 'rb');
		
		fseek($handle,$range);
		
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			print $buffer;
			ob_flush();
			flush();
		}
		return fclose($handle);
	}

    /**
     * sends a specified file to the browser through the file-sender
     *
     * @param string $file
     * @param string $filename
     * @return bool
     */
	public static function sendFile($file, $filename = null) {
		if(!file_exists($file))
			return false;
		
		$hash = randomString(20);
		FileSystem::write(FRAMEWORK_ROOT . "temp/download." . $hash . ".goma", serialize(array("file" => realpath($file), "filename" => $filename)));
		HTTPResponse::redirect(ROOT_PATH . "system/libs/file/Sender/FileSender.php?downloadID=" . $hash);
		exit;
	}

    /**
     * compares two files
     *
     * @name compare
     * @access public
     * @return bool
     */
	public static function compare($file1, $file2) {
		$content1 = strtoupper(dechex(crc32(file_get_contents($file1))));
		$content2 = strtoupper(dechex(crc32(file_get_contents($file2))));

		if ($content1 != $content2) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * finds the first existing file in a path. it iterates upwards, so
	 * it first checks test/blah/blub.txt, then test/blah, and so on.
	 *
	 * @param 	getNearestFileInPath
	 * @return  string|false
	*/
	public static function getNearestFileInPath($path, $root = "./") {
		if(file_exists($root . $path)) {
			return $path;
		}

		$file = substr($path, 0, strrpos($path, "/"));
		while(!file_exists($root . $file)) {
			if(!strpos($file, "/")) {
				return false;
			}
			$file = substr($file, 0, strrpos($file, "/"));
		}

		return $file;
	}
	
	/**
	 * returns an index of all files in a directory and every subdirectory
	 *
	 *@name index
	 *@access public
	 *@param string - directory
	 *@param array - index
	*/
	public static function index($dir, &$index) {
		if(is_dir($dir)) {
			$dir = realpath($dir);
			foreach(scandir($dir) as $file) {
				if($file == "." || $file == "..")
					continue;
				
				if(is_dir($dir . "/" . $file)) {
					self::index($dir . "/" . $file, $index);
				} else {
					self::$index[] = $dir . "/" . $file;
				}
			}
		} else if(file_exists($dir)) {
			$index[] = $dir;
		}
		
	}

	/**
	 * apply-safe-mode.
	*/
	public static function applySafeMode($folders = null, $configFiles = null, $tryOwn = false) {
		if($folders === null) {
			$folders = self::$applySafeModeFolders;
		}

		foreach($folders as $folder) {
			if(file_exists($folder)) {
				self::chmod($folder, self::getMode(), false, $tryOwn);	
			}
		}
		
		chmod(ROOT, self::getMode());

		// reset config files
		if(self::$safe_mode) {
			$configFiles = $configFiles || array(ROOT . "_config.php", APP_FOLDER . "config.php");
			foreach($configFiles as $file) {
				self::chmod($file, 0600);
			}
		}
	}

	/**
	 * generate code for external system to preserve state of safe-mode.
	*/
	public static function codeForExternalSystem() {
		return 'FileSystem::$safe_mode = '.var_export(FileSystem::$safe_mode, true).';';
	}

	/**
	 * tries to own files.
	*/
	protected static function tryOwn($file) {
		if(file_exists($file)) {
			if(!function_exists("get_current_user") || (get_current_user() != fileowner($file))) {
				if(is_file($file)) {
					return self::tryOwnFileByCopy($file); 
				} else {
					return self::tryOwnFolderByCopy($file);
				}
			} else {
				return true;
			}
		}

		return false;
	}

	/**
	 * trys to own a file by making a copy of it and deleting the old copy.
	*/
	protected static function tryOwnFileByCopy($file) {
		$newName = $file . "_new";
		while(file_exists($newName)) {
			$newName .= "_2";
		}

		if(copy($file, $newName)) {
			if(@unlink($file)) {
				return rename($newName, $file);
			}

			unlink($newName);
		}
			
		return false;
	}

	/**
	 * trys to own a folder by making a copy of it and deleting the old copy.
	 * for folders its important that all files are moved in the new folder.
	*/
	protected static function tryOwnFolderByCopy($file) {

		while(substr($file, -1) == "/") {
			$file = substr($file, 0, -1);
		}

		$newName = $file . "_new";
		while(file_exists($newName)) {
			$newName .= "_2";
		}

		FileSystem::requireDir($newName);

		if(self::moveFolderContents($file, $newName) && rmdir($file)) {
			return rename($newName, $file);
		} else {
			self::moveFolderContents($newName, $file);

			rmdir($newName);
			return false;
		}
	}
}

class FileException extends GomaException {
	protected $standardCode = ExceptionManager::FILE_EXCEPTION;
	public function __construct($message = "Unknown File-Exception.", $code = null, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

class FileNotFoundException extends FileException {
	protected $standardCode = ExceptionManager::FILE_NOT_FOUND;
}

class FileNotPermittedException extends FileException {
	protected $standardCode = ExceptionManager::FILE_NOT_PERMITTED;
}

class FileExistsException extends FileException {
	protected $standardCode = ExceptionManager::FILE_ALREADY_EXISTING;
}
class FileCopyException extends FileException {
	protected $standardCode = ExceptionManager::FILE_COPY_FAIL;
}
