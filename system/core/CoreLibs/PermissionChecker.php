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

				if($this->permissionMode !== false) {
					@chmod($folder, $this->permissionMode);
				}

				if(@fopen($folder . "/write.test", "w")) {
					@unlink($folder . "/write.test");
				} else {
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
			throw InvalidArgumentException("Mode must be a valid Unix-Filemode or false.");
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
	 * validates if a permisssion-flag is valid.
	*/
	public static function isValidPermission($mode) {
		return preg_match('/^0[0-7]{3}$/', $mode);
	}
}