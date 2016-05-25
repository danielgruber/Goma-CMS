<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for Request-Handling.
 *
 * @package		Goma\System\Core
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */
class Director extends gObject {

    /**
     * addon urls by modules or others
     *@name urls
     *@var array
     */
    public static $rules = array();

    /**
     * Controllers used in this Request
     *@name Controllers
     */
    public static $controller = array();

    /**
     * the current active controller
     *
     * @var RequestHandler|Controller
     */
    public static $requestController;

    /**
     * sorted rules.
     *
     * @var array
     */
    private static $sortedRules;

    /**
     * Init.
     */
    public static function Init() {

    }

    /**
     * adds some rules to controller
     *@param array $rules
     *@param int $priority
     */
    public static function addRules($rules, $priority = 50) {
        self::$sortedRules = null;

        if(isset(self::$rules[$priority])) {
            self::$rules[$priority] = array_merge(self::$rules[$priority], $rules);
        } else {
            self::$rules[$priority] = $rules;
        }
    }

    /**
     * gets all active rules sorted.
     */
    public static function getSortedRules() {
        if(!isset(self::$sortedRules)) {
            self::$sortedRules = self::$rules;
            krsort(self::$sortedRules);
        }

        return self::$sortedRules;
    }

    /**
     * serves the output given
     *
     *@param string - content
     */
    public static function serve($output) {
        if(PROFILE)
            Profiler::unmark("render");

        if(PROFILE)
            Profiler::mark("serve");

        Core::callHook("serve", $output);

        if(isset(self::$requestController)) {
            if(is_a($output, "GomaResponse")) {
                /** @var GomaResponse $output */
                if($output->shouldServe()) {
                    $output->setBodyString(self::$requestController->serve($output->getResponseBodyString()));
                }
            } else {
                $output = self::$requestController->serve($output);
            }
        }

        if(PROFILE)
            Profiler::unmark("serve");

        Core::callHook("onBeforeServe", $output);

        if(!is_a($output, "GomaResponse")) {
            $output = new GomaResponse(HTTPResponse::gomaResponse()->getHeader(), $output);
            $output->setStatus(HTTPResponse::gomaResponse()->getStatus());

            $output->getBody()->setIncludeResourcesInBody(!Core::is_ajax());
        } else {
            $output->merge(HTTPResponse::gomaResponse());
        }

        $output->output();

        Core::callHook("onBeforeShutdown");
    }

    /**
     * renders the page
     */
    public static function direct($url) {

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

        $request = new Request(
            (isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
            $url,
            $_GET,
            array_merge((array)$_POST, (array)$_FILES),
            getallheaders(),
            $_SERVER["SERVER_NAME"],
            $_SERVER["SERVER_PORT"],
            (isset($_SERVER["HTTPS"])) && $_SERVER["HTTPS"] != "off",
            isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : ""
        );

        $ruleMatcher = RuleMatcher::initWithRulesAndRequest(self::getSortedRules(), $request);
        while($nextController = $ruleMatcher->matchNext()) {
            if(!ClassInfo::exists($nextController)) {
                ClassInfo::delete();
                throw new LogicException("Controller $nextController does not exist.");
            }

            $inst = new $nextController;
            self::$requestController = $inst;
            self::$controller = array($inst);

            /** @var RequestHandler $inst */
            $data = $inst->handleRequest($ruleMatcher->getCurrentRequest());
            if($data !== false) {
                self::serve($data);
                return;
            }
        }

        return "404";
    }
}
