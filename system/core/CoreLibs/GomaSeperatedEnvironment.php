<?php defined("IN_GOMA") OR die();

/**
 * Class which generates an environment in which goma runs in Temp-Folder with minimal requirements.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		1.1.1
 */

define("__APPLIB_FILE", FRAMEWORK_ROOT . "core/applibs.php");

class GomaSeperatedEnvironment {
	/**
	 * this var contains classes that are needed.
	*/
	protected $classes = array(
		gObject::ID,
        "StaticsManager",
		"ClassInfo",
		"ClassManifest",
		"RequestHandler",
		"Core",
		"ViewAccessableData",
		"FileSystem",
		"tpl",
		"template", 
		"httpresponse",
		"ArrayLib",
        "IDataBaseField",
		"DBField",
        "Varchar",
		"Convert"
	);

	/**
	 * additional files needed.
	*/
	protected $files = array(
		__APPLIB_FILE
	);

	/**
	 * constants needed.
	*/
	protected $constants = array(
		"IN_GOMA",
		"TIME",
		"NOW",
		"PROFILE",
		"CACHE_DIRECTORY",
		"ROOT",
		"ROOT_PATH",
		"BASE_URI",
		"FRAMEWORK_ROOT",
		"DEV_MODE",
		"LOG_FOLDER",
		"SYSTEM_TPL_PATH",
		"APPLICATION_TPL_PATH",
		"IN_SAFE_MODE",
		"STATUS_ACTIVE",
		"APPLICATION",
		"CURRENT_PROJECT",
		"APP_FOLDER"
	);

	/**
	 * generate class.
	*/
	public function __construct($files = array(), $classes = array(), $constants = array()) {
		$this->files = array_merge($this->files, $files);
		$this->classes = array_merge($this->classes, $classes);
		$this->constants = array_merge($this->constants, $constants);
	}

	/**
	 * add files.
	*/
	public function addFiles($file) {
		$this->files = array_merge($this->files, $files);
	}

	/**
	 * add classes.
	*/
	public function addClasses($classes) {
		$this->classes = array_merge($this->classes, $classes);
	}

	/**
	 * add constants.
	*/
	public function addConstants($constants) {
		$this->constants = array_merge($this->constants, $constants);
	}

	/**
	 * builds the file and returns the url to it.
	*/
	public function build($userCode) {
		$file = CACHE_DIRECTORY . "ext." . randomString(10) . ".php";

		$code = $this->buildCode() . $userCode;

		FileSystem::write(ROOT . $file, $code);
		return $file;
	}

	/**
	 * builds basic code.
	*/
	public function buildCode() {
		$code = '<?php ';

		$code .= $this->generateHeader();
		$code .= $this->copyAndInclude();

		return $code;
	}

	/**
	 * returns filepath to class.
	*/
	public function getClassPath($class) {
		$class = strtolower($class);

		if(isset(ClassInfo::$files[$class])) {
			return ClassInfo::$files[$class];
		}

		if($class == "classmanifest") {
			return "system/core/ClassManifest.php";
		}

		return false;
	}

	/**
	 * copy all classes and generate code to include them.
	*/
	public function copyAndInclude() {
		$code = '';

		foreach($this->files as $file) {
			$e = CACHE_DIRECTORY . "ext.goma." . basename($file);
			copy($file, $e);
			$code .= 'include_once('.var_export($e, true).');';
		}

		foreach($this->classes as $class) {

			if(self::getClassPath($class)) {
				$e = CACHE_DIRECTORY . "ext.goma." . $class . ".php";

				copy(self::getClassPath($class), $e);
				$code .= 'if(!class_exists('.var_export($class, true).')) include_once('.var_export($e, true).');';

				if(gObject::method_exists($class, "codeForExternalSystem")) {
					$code .= call_user_func_array(array($class, "codeForExternalSystem"), array());
				}
			} else {
				throw new LogicException("Class $class not found for external subsystem.");
			}
		}

		return $code;
	}

	/**
	 * generates header.
	 *
	 * this contains all base-constants and some code which is required to run goma.
	*/
	public function generateHeader() {
		$code = '';

		// add constants
		foreach($this->constants as $c) {
			$code .= 'define('.var_export($c, true).', '.var_export(constant($c), true).'); ';

		}

		// add some code which is important to run
		$code .= '	define("IN_GFS_EXTERNAL", true); 
					chdir(ROOT); 
					error_reporting(E_ALL); 
					defined("INSTALL") OR define("INSTALL", true); 
					define("EXEC_START_TIME", microtime(true));
					date_default_timezone_set('.var_export(DEFAULT_TIMEZONE, true).');';

		return $code;
	}
}
