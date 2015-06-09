<?php defined("IN_GOMA") OR die();

/**
 * Generic implementation for REST-API.
 *
 * @package     Goma\API
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */

class GomaRestfulService extends RequestHandler {
    public $url_handlers = array(
        '$ClassName!'	=> "handleWithDataType"
    );

    public $allowed_actions = array(
        "handleWithDataType"
    );

    public static $default_response = "json";

    /**
     * @return bool|string
     */
    public function handleWithDataType()
    {
        if($this->request->isPost() || $this->request->isPut() || $this->request->isDelete()) {
            HTTPResponse::setResHeader(405);

            return "405 - Not allowed";
        }
        $class = $this->getParam("ClassName");
        if(ClassInfo::exists($class) && ClassInfo::hasInterface($class, "IRestModel")) {
            $object = call_user_func_array(array($class, "getRESTObject"), array($this->request));

            if(!$object) {
                HTTPResponse::setResHeader(404);

                return false;
            }

            $array = call_user_func_array(array($class, "toRESTArray"), array($object));

            HTTPResponse::setHeader("content-type", "text/json");

            return json_encode($array);
        }
    }
}