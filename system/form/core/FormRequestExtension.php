<?php defined("IN_GOMA") OR die();

/**
 * enables all kind of RequestHandlers to have a form-action.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.2
 */
class FormRequestExtension extends Extension {
    /**
     * called before handling action so we can hook in and create the 'form'-action.
     * @param string $action
     * @param string $content
     * @param bool $handleWithMethod
     */
    public function onBeforeHandleAction($action, &$content, &$handleWithMethod) {
        /** @var RequestHandler $owner */
        $owner = $this->getOwner();

        $params = array_values($owner->getRequest()->params);

        $parts = $owner->getRequest()->getUrlParts();
        if(isset($params[0]) && $params[0] == "forms" && ((isset($params[1]) && $params[1] == "form") || $parts[0] == "form")) {
            if(!isset($params[1]) && $parts[0] == "forms") {
                $owner->getRequest()->shift(1);
            }

            $formRequest = $owner->getRequest();
            if(count($params) > 2) {
                $formRequest = clone $formRequest;
                $urlParts = array_slice($params, 2);
                $formRequest->setUrlParts(array_merge($urlParts, $parts));
            }

            $handleWithMethod = false;

            $externalForm = new ExternalFormController();

            if($arguments = $formRequest->match('$form!/$field!', true)) {
                $content = $externalForm->handleRequest($formRequest, true);
                if(!$content) {
                    $content = $owner->index();
                }
            } else {
                $content = $owner->index();
            }
        }
    }

    /**
     * this action prohibits the controller from checking permission for 'form'-action.
     * @param string $action
     * @param bool $hasAction
     */
    public function extendHasAction($action, &$hasAction) {

        /** @var RequestHandler $owner */
        $owner = $this->getOwner();

        $params = array_values($owner->getRequest()->params);

        $parts = $owner->getRequest()->getUrlParts();
        if(isset($params[0]) && $params[0] == "forms" && ((isset($params[1]) && $params[1] == "form") || $parts[0] == "form")) {
            $hasAction = true;
        }
    }
}

gObject::extend("RequestHandler", "FormRequestExtension");
