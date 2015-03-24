<?php defined("IN_GOMA") OR die();
/**
 * moves a list of files or a complete folder to another point in filesystem.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0.1
 *
 * last modified: 16.03.2015
*/
class FileMover {
	/**
	 * list of files that should be moved.
	 * 
	 * @name 	filelist
	 * @access 	protected
	*/
	protected $fileList = array();

	/**
  	 * source-root.
	*/
	protected $source = "";

	/**
	 * destination-path.
	*/
	protected $destination;

	/**
	 * if this object has been checked.
	*/
	protected $isValid;

	/**
	 * last file-error.
	*/
	protected $fileError;

	/**
	 * callback after each file executes.
	*/
	protected $callback;

	/**
	 * constant used as return-value for callback-functions,
	 * that indicates to stop current execution.
	*/
	const STOP_EXECUTION = -1;

	/**
 	 * generates file-mover.
 	 * 
 	 * @name 	__construct
 	 * @access 	public
 	 * @param 	array - files
	*/
	public function __construct($files = null, $source = null, $destination = null) {
		if(isset($files)) {
			$this->setFileList($files);
		}

		if(isset($source)) {
			$this->setSource($source);
		}

		if(isset($destination)) {
			$this->setDestination($destination);
		}

		$this->isValid = false;
	}

	/**
	 * sets file-list.
	*/
	public function setFileList($files) {
		$this->isValid = false;
		$this->fileList = ArrayLib::key_value((array) $files);
	}

	/**
	 * returns current File-List.
	*/
	public function getFileList() {
		return array_values($this->fileList);
	}

	/**
	 * adds some files.
	*/
	public function addFiles($files) {
		foreach((array) $files as $k => $v) {
			if(!is_array($v)) {
				$this->fileList[$v] = $v;
			} else {
				throw new InvalidArgumentException("File-Path must be a string.");
			}
		}

		$this->isValid = false;
	}

	/**
	 * adds a complete folder with all files in it to this mover.
	*/
	public function addFolder($folder) {
		if(file_exists($folder)) {
			foreach(scandir($folder) as $file) {
				if($file != "." && $file != "..") {
					if(is_file($folder . "/" . $file)) {
						$this->fileList[$folder . "/" . $file] = $folder . "/" . $file;
					} else {
						$this->addFolder($folder . "/" . $file);
					}
				}
			}
		} else {
			throw new InvalidArgumentException("Folder must exist. FileMover::addFolder.");
		}

		$this->isValid = false;
	}

	/**
	 * sets destination.
	*/
	public function setDestination($destination) {
		if(!file_exists($destination)) {
			throw new InvalidArgumentException("Destination not found. It must be an existing file-path.");
		}

		$this->destination = $destination;
		$this->isValid = false;
	}

	/**
	 * returns current Destination.
	*/
	public function getDestination() {
		return $this->destination;
	}

	/**
	 * sets source.
	*/
	public function setSource($source) {
		if(!file_exists($source)) {
			throw new InvalidArgumentException("Source not found. It must be an existing file-path.");
		}

		$this->source = $source;
		$this->isValid = false;
	}

	/**
	 * returns current source.
	*/
	public function getSource() {
		if($this->source == "" || substr($this->source, -1) == "/") {
			return $this->source;
		}

		return $this->source . "/";
	}

	/**
	 * returns last file-error.
	 * you can use this after checkValid().
	*/
	public function getLastFileError() {
		return $this->fileError;
	}

	/**
	 * sets validation. this is not recommended to be used
	 * if you're not sure if it will execute.
	*/
	public function setValid($valid) {
		$this->isValid = $valid;
	}

	/**
	 * checks if we can move the files.
	 * it checks for file-permissions basically.
	 *
	 * @name 	checkValid
	 * @param 	boolean if check with source-files: requires existance
	 * @param 	boolean if true, it returns an array with all problematic files or if false a boolean
	 * @return 	boolean|array
	*/
	public function checkValid($checkSources = false, $returnList = false) {
		if($this->isValid === true && !$returnList) {
			return $returnList ? array() : true;
		}

		// init list which contains all problematic files
		$list = array();

		$this->fileError = null;
		foreach($this->fileList as $file) {
			if($checkSources) {
				if(!self::canMoveFileTo($this->getSource() . $file, $this->destination . "/" . $file)) {
					$this->fileError = $file;
					
					if(!$returnList) {
						return false;
					}

					$list[] = $file;
				}
			} else {
				if(!self::canMoveAnyFileTo($this->destination . "/" . $file)) {
					$this->fileError = $file;
					
					if(!$returnList) {
						return false;
					}

					$list[] = $file;
				}
			}
		}

		if($checkSources && count($list) == 0) {
			$this->isValid = true;
		}

		return $returnList ? $list : true;
	}

	/**
	 * executes operation.
	 *
	 * @name 	execute
	 * @param 	int start at file-index.
	 * @param 	int end at file-index
	 * @return 	int current file-index.
	*/
	public function execute($startFromIndex = 0, $doUntilIndex = null) {
		
		// check for complete validation.
		if(!$this->isValid && !$this->checkValid(true)) {
			throw new FileMoverCannotMoveException("Cannot execute FileMover-Operation cause it will not pass cause of file-permissions.", $this->fileError);
		}

		// we need an array with indexes from 0 to n
		$files = array_values($this->fileList);
		$doUntilIndex = (isset($doUntilIndex) && $doUntilIndex < count($files)) ? $doUntilIndex : count($files);
		// start moving files.
		for($i = $startFromIndex; $i < $doUntilIndex; $i++) {
			$file = $files[$i];
			
			$bool = self::move($this->getSource() . $file, $this->destination . "/" . $file);

			// call callback
			if(is_callable($this->callback)) {

				// if callback response with FileMover::STOP_EXECUTION, stop and return current position.
				$response = call_user_func_array($this->callback, array($file, $bool));
				if($response == self::STOP_EXECUTION) {
					break;
				}
			}
		}

		return $i;
	}

	/**
	 * trys to move a file or folder to destination.
	*/
	protected function move($source, $dest) {
		if(is_dir($source)) {
			return $this->checkForDirectoryMoving($dest);
		}

		if(file_exists($source)) {
			if(file_exists($dest)) {
				if(is_writable($dest)) {
					if(!@unlink($dest) || !@rename($source, $dest)) {
						return false;
					}
					return true;
				} else {
					return false;
				}
			} else {
				// create folder
				self::requireFolderForFile($dest);
				return @rename($source, $dest);
			}
		}

		return false;
	}

	/**
	 * checks for directory-moving.
	*/
	protected function checkForDirectoryMoving($dest) {
		if(is_dir($dest)) {
			return true;
		} 

		if(!file_exists($dest)) {
			FileSystem::requireDir($dest);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * creates parent folder for given path.
	*/
	protected function requireFolderForFile($path) {
		FileSystem::requireDir(substr($path, 0, strrpos($path, "/")));
	}

	/**
	 * returns if you can move any file to a specified destination. 
	 * it also checks the possibility if we can or have to create a folder to 
	 * move the file to the destination.
	 *
	 * @name 	canMoveAnyFileTo
	 * @param 	string destination
	 * @param 	boolean return
	 * @return 	boolean if return false when its a folder.
	*/
	public static function canMoveAnyFileTo($destination, $isFolder = false) {
		
		if(file_exists($destination)) {
			if(is_dir($destination)) {
				return $isFolder;
			} else if(is_file($destination) && $isFolder) {
				return false;
			} else {
				return is_writable($destination);
			}
		}

		// now check parent folder writable, so first find parent folder.
		$path = self::findFirstExistingFolderForPath($destination);
		if($path === false) {
			return false;
		}

		return PermissionChecker::checkWriteable($path);
	}

	/**
	 * finds first existing folder which is existing for path.
	 *
	 * @name 	findFirstExistingFolderForPath
	 * @param 	string - path
	 * @param 	string - optional root, which is prepended to path.
	 * @return 	string - folder without root
	*/
	public static function findFirstExistingFolderForPath($path, $root = "") {
		if(file_exists($root . $path)) {
			return $path;
		}

		$file = substr($path, 0, strrpos($path, "/"));
		while(!file_exists($root . $file)) {
			if($file == "") {
				return false;
			}

			if(strpos($file, "/") === false) {
				$file = "";
			} else {
				$file = substr($file, 0, strrpos($file, "/"));
			}
		}

		return $file;
	}

	/**
	 * returns if we can move a specific file to a specified destination.
	 * it checks if source is a folder and also checks if file is the same.
	 *
	 * @name 	canMoveFileTo
	 * @param 	string source
	 * @param 	string destination
	 * @return 	boolean
	*/
	public static function canMoveFileTo($source, $destination) {
		if(!file_exists($source)) {
			throw new FileMoverCannotMoveException("Source must exist in FileMover::canMoveFileTo. $source", $source);
		}

		if(self::canMoveAnyFileTo($destination, is_dir($source))) {
			return true;
		}

		if(file_exists($destination) && md5_file($source) == md5_file($destination)) {
			return true;
		}

		return false;
	}

}

/**
 * error when a file cannot be moved.
*/
class FileMoverCannotMoveException extends LogicException {

	/**
	 * cannot move this file.
	*/
	protected $fileError;

	public function __construct($message, $file) {
		parent::__construct($message);
		$this->fileError = $file;
	}

	public function getFileWithError() {
		return $this->fileError;
	}

	public function setFileWithError($file) {
		$this->fileError = $file;
	}
}