<?php defined("IN_GOMA") OR die();

/**
 * Controller for Dev-Mode of Goma-Framework. Handles stuff like rebuilding DB or
 * building versions
 *
 * @package		Goma\Core
 * @version		2.0
 */
class Dev extends RequestHandler {
	/**
	 * title of current view
	 */
	public static $title = "Creating new Database";

	public $url_handlers = array(	"build" => "builddev", 
									"rebuildcaches" => "rebuild", 
									"flush" => "flush", 
									"buildDistro/\$name!/\$subname" => "buildAppDistro", 
									"buildDistro" => "buildDistro", 
									"cleanUpVersions" => "cleanUpVersions", 
									"setChmod777" => "setChmod777", 
									"test" => "test");

	public $allowed_actions = array("builddev", 
									"rebuild", 
									"flush", 
									"buildDistro" 		=> "->isDev", 
									"buildAppDistro" 	=> "->isDev", 
									"buildExpDistro" 	=> "->isDev", 
									"cleanUpVersions" 	=> "->isDev", 
									"setChmod777", 
									"test");

	/**
	 * runs dev and redirects back to REDIRECT
	 *
	 */
	public static function redirectToDev() {
		@session_start();
		$_SESSION["dev_without_perms"] = true;
		HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "/dev?redirect=" . getredirect(false));
		exit ;
	}

	/**
	 * shows dev-site or not
	 */
	public function handleRequest($request, $subController = false) {

		define("DEV_CONTROLLER", true);

		HTTPResponse::unsetCacheable();

		if(!isset($_SESSION["dev_without_perms"]) && !Permission::check("ADMIN")) {
			makeProjectAvailable();

			throw new PermissionException();
		}

		return parent::handleRequest($request, $subController);

	}

	/**
	 * serves data
	 *
	 */
	public function serve($content) {
		$viewabledata = new ViewAccessableData();
		$viewabledata->content = $content;
		$viewabledata->title = self::$title;

		return $viewabledata->renderWith("framework/dev.html");
	}

	/**
	 * sets chmod 0777 to the whole system
	 *
	 */
	public function setChmod777() {
		FileSystem::chmod(ROOT, 0777, false);
		return "Okay";
	}

	/**
	 * returns if we are in dev-mode
	 *
	 */
	public function isDev() {

		return DEV_MODE;
	}

	/**
	 * the index site of the dev-mode
	 *
	 *@name index
	 */
	public function index() {

		// make 503
		makeProjectUnavailable();

		ClassInfo::delete();
		Core::callHook("deleteCachesInDev");

		// check if dev-without-perms, so redirect directly
		if(isset($_SESSION["dev_without_perms"])) {
			$url = ROOT_PATH . BASE_SCRIPT . "dev/rebuildcaches" . URLEND . "?redirect=" . urlencode(getredirect(true));
			header("Location: " . $url);
			echo "<script>location.href = '" . $url . "';</script><br /> Redirecting to: <a href='" . $url . "'>'.$url.'</a>";
			Core::callHook("onBeforeShutDown");
			exit ;
		}

		return '<h3>Creating new Database</h3>
			<script type="text/javascript">
				setTimeout(function(){ location.href = "' . ROOT_PATH . BASE_SCRIPT . 'dev/rebuildcaches/"; }, 500);
			</script>
			
			<img src="images/16x16/loading.gif" alt="Loading..." /> Rebuilding Caches... <br /><br />If it doesn\'t reload within 15 seconds, please click <a href="' . ROOT_PATH . 'dev/rebuildcaches">here</a>.
			<noscript>Please click <a href="' . ROOT_PATH . BASE_SCRIPT . 'dev/rebuildcaches/">here</a>.</noscript>';
	}

	/**
	 * this step regenerates the cache
	 *
	 *@name rebuild
	 */
	public function rebuild() {
		// 503
		makeProjectUnavailable();

		Core::callHook("rebuildCachesInDev");

		// generate class-info
		defined('GENERATE_CLASS_INFO') OR define('GENERATE_CLASS_INFO', true);
		define("DEV_BUILD", true);

		// redirect if needed
		if(isset($_SESSION["dev_without_perms"])) {
			$url = ROOT_PATH . BASE_SCRIPT . "dev/builddev" . URLEND . "?redirect=" . urlencode(getredirect(true));
			header("Location: " . $url);
			echo "<script>location.href = '" . $url . "';</script><br /> Redirecting to: <a href='" . $url . "'>'.$url.'</a>";
			Core::callHook("onBeforeShutDown");
			exit;
		}

		return '<h3>Creating new Database</h3>
			<script type="text/javascript">
				setTimeout(function(){ location.href = "' . ROOT_PATH . BASE_SCRIPT . 'dev/builddev/"; }, 500);
			</script>
			<div><img src="images/success.png" height="16" alt="Loading..." /> Rebuilding Caches...</div>
			<noscript>Please click <a href="' . ROOT_PATH . BASE_SCRIPT . 'dev/builddev/">here</a>.<br /></noscript>
			<div><img src="images/16x16/loading.gif"  alt="Loading..." /> Rebuilding Database...</div>If it doesn\'t reload within 15 seconds, please click <a href="' . ROOT_PATH . BASE_SCRIPT . 'dev/builddev">here</a>.';
	}

	/**
	 * this step regenerates the db
	 */
	public function builddev() {
		// 503
		makeProjectUnavailable();

		// patch
		Object::$cache_singleton_classes = array();

		// show progress
		$data = '<h3>Creating new Database</h3>
			<div><img src="images/success.png" height="16" alt="Loading..." /> Rebuilding Caches...</div>
			<div><img src="images/success.png" height="16" alt="Success" />  Rebuilding Database...</div>';

		if(defined("SQL_LOADUP")) {
			// remake db
			foreach(classinfo::getChildren("dataobject") as $value) {
				$obj = new $value;

				$data .= nl2br($obj->buildDB(DB_PREFIX));
			}
		}

		logging(strip_tags(preg_replace("/(\<br\s*\\\>|\<\/div\>)/", "\n", $data)));

		// after that rewrite classinfo
		ClassInfo::write();

		unset($obj);
		$data .= "<br />";

		Core::callHook("rebuildDBInDev");

		// restore page, so delete 503
		makeProjectAvailable();

		self::checkForRedirect();

		return $data;
	}

	/**
	 * checks for redirect without dev-permissions or normal redirect.
	 *
	 * @name checkForRedirect
	*/
	public static function checkForRedirect() {
		// redirect if needed
		if(isset($_GET["redirect"])) {
			if(isset($_SESSION["dev_without_perms"])) {
				unset($_SESSION["dev_without_perms"]);
			}
			HTTPResponse::redirect($_GET["redirect"]);
			exit ;
		}

		// redirect to BASE if needed
		if(isset($_SESSION["dev_without_perms"])) {
			unset($_SESSION["dev_without_perms"]);
			header("Location: " . ROOT_PATH);
			Core::callHook("onBeforeShutDown");
			exit;
		}
	}

	/**
	 * just for flushing the whole (!) cache
	 *
	 *@name flush
	 */
	public function flush() {
		defined('GENERATE_CLASS_INFO') OR define('GENERATE_CLASS_INFO', true);
		define("DEV_BUILD", true);

		classinfo::delete();
		classinfo::loadfile();

		header("Location: " . ROOT_PATH . "");
		Core::callHook("onBeforeShutDown");
		exit ;
	}

	/**
	 * builds a distributable of the application
	 *
	 */
	public function buildDistro() {
		self::$title = lang("DISTRO_BUILD");
		return g_SoftwareType::listAllSoftware()->renderWith("framework/buildDistro.html");
	}

	/**
	 * builds an app-distro
	 *
	 */
	public function buildAppDistro($name = null, $subname = null) {
		if(!isset($name)) {
			$name = $this->getParam("name");
		}

		if(!isset($subname))
			$subname = $this->getParam("subname");

		self::$title = lang("DISTRO_BUILD");

		if(!$name)
			return false;

		if(ClassInfo::exists("G_" . $name . "SoftwareType") && is_subclass_of("G_" . $name . "SoftwareType", "G_SoftwareType")) {
			$filename = call_user_func_array(array("G_" . $name . "SoftwareType", "generateDistroFileName"), array($subname));
			if($filename === false)
				return false;
			$file = ROOT . CACHE_DIRECTORY . "/" . $filename;

			$return = call_user_func_array(array("G_" . $name . "SoftwareType", "buildDistro"), array($file, $subname));
			if(is_string($return))
				return $return;

			FileSystem::sendFile($file);
			exit;
		}

		return false;
	}

	/**
	 * cleans up versions
	 *
	 */
	public function cleanUpVersions() {
		$log = "";
		foreach(ClassInfo::getChildren("DataObject") as $child) {
			if(ClassInfo::getParentClass($child) == "dataobject") {
				$c = new $child;
				if(DataObject::versioned($child)) {

					$baseTable = ClassInfo::$class_info[$child]["table"];
					if(isset(ClassInfo::$database[$child . "_state"])) {
						// first get ids NOT to delete

						$recordids = array();
						$ids = array();
						// first recordids
						$sql = "SELECT * FROM " . DB_PREFIX . $child . "_state";
						if($result = SQL::Query($sql)) {
							while($row = SQL::fetch_object($result)) {
								$recordids[$row->id] = $row->id;
								$ids[$row->publishedid] = $row->publishedid;
								$ids[$row->stateid] = $row->stateid;
							}
						}

						$deleteids = array();
						// now generate ids to delete
						$sql = "SELECT id FROM " . DB_PREFIX . $baseTable . " WHERE id NOT IN('" . implode("','", $ids) . "') OR recordid NOT IN ('" . implode("','", $recordids) . "')";
						if($result = SQL::Query($sql)) {
							while($row = SQL::fetch_object($result)) {
								$deleteids[] = $row->id;
							}
						}

						// now delete

						// first generate tables
						$tables = array(ClassInfo::$class_info[$child]["table"]);
						foreach(ClassInfo::dataClasses($child) as $class => $table) {
							if($baseTable != $table && isset(ClassInfo::$database[$table])) {
								$tables[] = $table;
							}
						}

						foreach($tables as $table) {
							$sql = "DELETE FROM " . DB_PREFIX . $table . " WHERE id IN('" . implode("','", $deleteids) . "')";
							if(SQL::Query($sql))
								$log .= '<div><img src="images/success.png" height="16" alt="Loading..." /> Delete versions of ' . $table . '</div>';
							else
								$log .= '<div><img src="images/16x16/del.png" height="16" alt="Loading..." /> Failed to delete versions of ' . $table . '</div>';
						}
					}

				}
			}
		}

		return '<h3>DB-Cleanup</h3>' . $log;
	}

	/**
	 * test-implementation
	 */
	public function test() {
		if(DEV_MODE) {
			$c = new GomaTestController();
			return $c->handleRequest($this->request);
		} else {
			return false;
		}
	}

}