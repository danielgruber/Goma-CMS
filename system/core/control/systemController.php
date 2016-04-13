<?php defined("IN_GOMA") OR die();

/**
 * Basic Class some system behaviour.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class systemController extends Controller {
	/**
	 * js-debug-limit in KB
	 *
	 *@name JS_DEBUG_LIMIT
	 *@access public
	*/
	const JS_DEBUG_LIMIT = 2048;

	/**
	 * string for adminAsUser.
	 *
	 * @var string
	 */
	const ADMIN_AS_USER = "adminAsUser";

	/**
	 * @var array
	 */
	public $url_handlers = array(
		"setUserView/\$bool!"	=> "setUserView",
		"switchView",
		"getLang/\$lang"		=> "getLang",
		"indexSearch/\$max"		=> "indexSearch"
	);
	
	public $allowed_actions = array(
		"setUserView",
		"switchView",
		"getLang",
		"indexSearch"
	);

	/**
	 * @return bool|string
	 */
	public function index() {
		return false;
	}

	/**
	 * sets the user view
	*/
	public function setUserView() {
		if($this->getParam("bool") == 1) {
			GlobalSessionManager::globalSession()->set(self::ADMIN_AS_USER, true);
		} else {
			GlobalSessionManager::globalSession()->remove(self::ADMIN_AS_USER);
		}
		return $this->redirectback();
	}

	/**
	 * switches the view
	*/
	public function switchView() {
		if(GlobalSessionManager::globalSession()->hasKey(self::ADMIN_AS_USER)) {
			GlobalSessionManager::globalSession()->remove(self::ADMIN_AS_USER);
		} else {
			GlobalSessionManager::globalSession()->set(self::ADMIN_AS_USER, true);
		}
		
		HTTPResponse::unsetCacheable();
		
		return $this->redirectBack();
	}
	
	/**
	 * sends language as json to the user
	 *
	 *@name getLang
	 *@access public
	*/
	public function getLang() {
		$lang = $this->getParam("lang");
		$output = array();
		$outputNull = false;
		if(empty($lang) || $lang == "*") {
			$output = $GLOBALS["lang"];
		} else {
			if(is_array($lang) && count($lang) > 0) {
				foreach($lang as $value) {
					$value = strtoupper($value);
					if(isset($GLOBALS["lang"][$value])) {
						$output[$value] = $GLOBALS["lang"][$value];
					} else {
						$output[$value] = null;
						$outputNull = true;
					}
				}
			} else if(is_string($lang)) {
				$lang = strtoupper($lang);
				if(isset($GLOBALS["lang"][$lang])) {
						$output[$lang] = $GLOBALS["lang"][$lang];
					} else {
						$output[$lang] = null;
						$outputNull = true;
					}
			}
		}
		
		$expCount = isset(ClassInfo::$appENV["expansion"]) ? count(ClassInfo::$appENV["expansion"]) : 0;
		$cacher = new Cacher("lang_" . Core::$lang . count(i18n::$languagefiles) . $expCount);
		$mtime = $cacher->created;
		$etag = strtolower(md5("lang_" . var_export($this->getParam("lang"),true) . var_export($output, true)));
		if($outputNull === false) {
			HTTPResponse::addHeader('Cache-Control','public, max-age=5511045');
			HTTPResponse::addHeader("pragma","Public");
		}
		
		HTTPResponse::addHeader("Etag", '"'.$etag.'"');
		
		// 304 by HTTP_IF_MODIFIED_SINCE
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{					
				if(strtolower(gmdate('D, d M Y H:i:s', $mtime).' GMT') == strtolower($_SERVER['HTTP_IF_MODIFIED_SINCE']))
				{
						HTTPResponse::setResHeader(304);
						HTTPResponse::sendHeader();
						if(PROFILE)
							Profiler::End();
							
						exit;
				}
		}
		
		// 304 by ETAG
		if(isset($_SERVER["HTTP_IF_NONE_MATCH"]))
		{
				if($_SERVER["HTTP_IF_NONE_MATCH"] == '"' . $etag . '"')
				{
						HTTPResponse::setResHeader(304);
						HTTPResponse::sendHeader();
						
						if(PROFILE)
							Profiler::End();
						
						exit;
				}
		}
		
		$expiresAdd = defined("DEV_MODE") ? 3 * 60 * 60 : 48 * 60 * 60;
		if($outputNull === false) {
			HTTPResponse::setCachable(NOW + $expiresAdd, $mtime, true);
		}
		
		HTTPResponse::setHeader("content-type", "text/x-json");
		HTTPResponse::output('('.json_encode($output).')');
		exit;
	}

	/**
	 * indexes some records for search.
	*/
	public function indexSearch() {
		if(!Permission::check("ADMIN"))
			return false;
		
		GlobalSessionManager::globalSession()->stopSession();
		$manipulation = array();
		foreach(ClassInfo::getChildren("DataObject") as $class) {
			
			
			if (in_array("searchindex", gObject::$extensions[$class])) {
				$notIndexed = DataObject::get($class, "indexversion = 0 OR indexversion < '".SearchIndex::VERSION."'", array(), $max);
				foreach($notIndexed as $record) {
					if(microtime(true) - EXEC_START_TIME > 2.0)
						return true;
					
					SearchIndex::indexRecord($record);
					$manipulation[] = array(
							"command"		=> "update",
							"table_name"	=> $record->table(),
							"id"			=> $record->versionid,
							array(
								"indexversion"	=> SearchIndex::VERSION
							)
						);
				}
				
				SQL::manipulate($manipulation);
			}
		}
		
		return 1;
	}
}