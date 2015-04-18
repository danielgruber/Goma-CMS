<?php defined("IN_GOMA") OR die();

/**
 * manages connection to controller of form like action triggering and using its namespace.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class FormControllerConnection {
    /**
     * current controller.
     *
     * @var RequestHandler
     */
    protected $controller;

    /**
     * cache for supported controllers.
     */
    protected static $supported = array();

    /**
     * creates the object.
     * @param RequestHandler $controller
     */
    public function __construct(RequestHandler $controller) {
        $this->controller = $controller;
    }

    /**
     * tests if controller supports form-namespacing.
     *
     * @return bool
     */
    protected function supportsNamespacing() {
        $className = ClassManifest::resolveClassName($this->controller);
        if(isset(self::$supported[$className])) {
            return self::$supported[$className];
        }

        $fakeRequest = new Request("get", "forms/test/1");
        $fakeRequest->fakeRequest = randomString(20);

        /** @var RequestHandler $controller */
        $controller = new $className();
        $content = $controller->handleRequest($fakeRequest);

        return ($content == $fakeRequest);
    }

    /**
     * external url based on namespace-support.
     *
     * @return bool
     */
    public function shouldUseNamespacing() {
        return (isset($this->controller->originalNamespace) && $this->controller->originalNamespace && $this->supportsNamespacing());
    }
}