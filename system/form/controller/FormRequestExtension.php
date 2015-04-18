<?php defined("IN_GOMA") OR die();

/**
 * extends Request-Handler to have namespace-support.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */

class FormRequestExtension extends Extension {

    /**
     * hooks in handleAction and returns content of Form-Field if it is able to.
     *
     * @param $action
     * @param $content
     * @param $handleWithMethod
     */
    public function onBeforeHandleAction($action, &$content, &$handleWithMethod) {
        if($action == "forms" && $this->getOwner()->request->getParam("id") == "form") {

            $handleWithMethod = false;

            $externalForm = new ExternalFormController();

            $request = $this->getOwner()->request;

            // used to check if Controller supports form-namespacing.
            if(isset($request->fakeRequest)) {
                $content = $request->fakeRequest;
                return;
            }


            if($arguments = $request->match('$form!/$field!', true)) {
                $content = $externalForm->handleRequest($request);
                if(!$content) {
                    $content = $this->getOwner()->index();
                }
            } else {
                $content = $this->getOwner()->index();
            }
        }
    }

    /**
     * registers action
     *
     * @param $action
     * @param $hasAction
     */
    public function extendHasAction( $action, &$hasAction) {
        if($action == "forms" && $this->getOwner()->request->getParam("id") == "form") {
            $hasAction = true;
        }
    }
}

Object::extend("RequestHandler", "FormRequestExtension");