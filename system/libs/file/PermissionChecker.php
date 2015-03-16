<?php defined("IN_GOMA") OR die();

/**
 * class that checks all permissions required by Framework.
 *
 * @package		Goma\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class PermissionChecker {
	/**
	 * folders.
	*/
	protected $folders = array();

	/**
	 * trys to set this permission-mode before trying to write.
	*/
	protected $permissionMode = 0777;

	/**
	 * constructor.
	 *
	 * you can give the folders as array here.
	*/
	public function __construct($folders = null, $permissionMode = null) {
		if(isset($folders)) {
			$this->folders = (array) $folders;
		}

		if(isset($permissionMode)) {
			$this->setPermissionMode($permissionMode);
		}
	}

	/**
	 * validates and returns an array of folders which are not writable.
	 * if everything goes right it returns and empty array.
	*/
	public function tryWrite() {
		$error = array();

		foreach($this->folders as $folder) {
			if(file_exists($folder)) {

				if(!self::checkWriteable($folder, $this->permissionMode)) {
					$error[] = $folder;
				}

			} else {
				mkdir($folder, 0777, true);

				if($this->permissionMode !== false) {
					@chmod($folder, $this->permissionMode);
				}

				if(!file_exists($folder)) {
					$error[] = $folder;
				}
			}
		}

		return $error;
	}

	/**
	 * returns current permission-mode.
	*/
	public function getPermissionMode() {
		return $this->permissionMode;
	}

	/**
	 * sets current permission-mode.
	*/
	public function setPermissionMode($mode) {
		if(self::isValidPermission($mode) || $mode === false) {
			$this->permissionMode = $mode;
		} else {
			throw new InvalidArgumentException("Mode must be a valid Unix-Filemode or false. '$mode' given.");
		}
	}

	/**
	 * sets the folders.
	*/
	public function setFolders($folders) {
		$this->folders = (array) $folders;
	}

	/**
	 * adds some folders.
	*/
	public function addFolders($folders) {
		$this->folders = array_merge($this->folders, (array) $folders);
	}

	/**
	 * returns all current folders.
	*/
	public function getFolders() {
		return $this->folders;
	}

	/**
	 * checks if a specified folder is writable.
	 * the folder must exist.
	 *
	 * @name 	checkWriteable
	*/
	public static function checkWriteable($folder, $permissionMode = false) {
		if(!file_exists($folder)) {
			throw new LogicException("Folder must exist for PermissionChecker::checkWritable.");
		}

		if($permissionMode !== false) {
			if(self::isValidPermission($permissionMode)) {
				@chmod($folder, $permissionMode);
			} else {
				throw new InvalidArgumentException("Mode must be a valid Unix-Filemode or false. '$mode' given.");
			}
		}

		

		if(@fopen($folder . "/write.test", "w")) {
			@unlink($folder . "/write.test");
			return true;
		} else {
			return false;
		}
	}

	/**
	 * validates if a permisssion-flag is valid.
	*/
	public static function isValidPermission($mode) {
		return (boolean) preg_match('/^(1|0)?[0-7]{1,3}+$/', sprintf("%o", $mode));
	}
}