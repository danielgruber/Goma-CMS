<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for Request-Handling.
 *
 * @package		Goma\System\Core
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */
class Director {

    /**
     * addon urls by modules or others
     *@name urls
     *@var array
     */
    public static $rules = array();

    /**
     * url.
     */
    public static $url;

    /**
     * Controllers used in this Request
     *@name Controllers
     */
    public static $controller = array();

    /**
     * the current active controller
     *
     *@var object
     */
    public static $requestController;

    /**
     * adds some rules to controller
     *@param array - rules
     *@param numeric - priority
     */
    public static function addRules($rules, $priority = 50) {
        if(isset(self::$rules[$priority])) {
            self::$rules[$priority] = array_merge(self::$rules[$priority], $rules);
        } else {
            self::$rules[$priority] = $rules;
        }
    }

    /**
     * serves the output given
     *
     *@param string - content
     */
    public static function serve($output) {

        if(isset($_GET["flush"]) && Permission::check("ADMIN"))
            Notification::notify("Core", lang("CACHE_DELETED"));

        if(PROFILE)
            Profiler::unmark("render");

        if(PROFILE)
            Profiler::mark("serve");

        Core::callHook("serve", $output);

        if(isset(self::$requestController))
            $output = self::$requestController->serve($output);

        if(PROFILE)
            Profiler::unmark("serve");

        Core::callHook("onBeforeServe", $output);

        HTTPResponse::setBody($output);
        HTTPResponse::output();

        Core::callHook("onBeforeShutdown");

        exit ;
    }

    /**
     * renders the page
     */
    public static function direct($url) {

        self::$url = $url;
        if(PROFILE)
            Profiler::mark("render");

        // we will merge $_POST with $_FILES, but before we validate $_FILES
        foreach($_FILES as $name => $arr) {
            if(is_array($arr["tmp_name"])) {
                foreach($arr["tmp_name"] as $tmp_file) {
                    if($tmp_file && !is_uploaded_file($tmp_file)) {
                        throw new LogicException($tmp_file . " is no valid upload! Please try again uploading the file.");
                    }
                }
            } else {
                if($arr["tmp_name"] && !is_uploaded_file($arr["tmp_name"])) {
                    throw new LogicException($arr["tmp_name"] . " is no valid upload! Please try again uploading the file.");
                }
            }
        }

        $orgrequest = new Request((isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'], $url, $_GET, array_merge((array)$_POST, (array)$_FILES));

        krsort(self::$rules);

        // get  current controller
        foreach(self::$rules as $priority => $rules) {
            foreach($rules as $rule => $controller) {
                $request = clone $orgrequest;
                if($args = $request->match($rule, true)) {
                    if($request->getParam("controller")) {
                        $controller = $request->getParam("controller");
                    }

                    if(!ClassInfo::exists($controller)) {
                        ClassInfo::delete();
                        throw new LogicException("Controller $controller does not exist.");
                    }

                    $inst = new $controller;
                    self::$requestController = $inst;
                    self::$controller = array($inst);

                    /** @var RequestHandler $inst */
                    $data = $inst->handleRequest($request);
                    if($data === false) {
                        continue;
                    }
                    self::serve($data);
                    break 2;
                }
            }
        }

    }
}