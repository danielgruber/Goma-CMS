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

        if($action == "forms" && $owner->getRequest()->getParam("id") == "form") {
            $handleWithMethod = false;

            $externalForm = new ExternalFormController();

            if($arguments = $owner->getRequest()->match('$form!/$field!', true)) {
                $content = $externalForm->handleRequest($owner->getRequest(), true);
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

        if($action == "forms" && $owner->getRequest()->getParam("id") == "form") {
            $hasAction = true;
        }
    }
}

Object::extend("RequestHandler", "FormRequestExtension");