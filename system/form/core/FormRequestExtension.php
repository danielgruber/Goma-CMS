<?php defined("IN_GOMA") OR die();

/**
 * enables all kind of RequestHandlers to have a form-action.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.2
 *
 * @method RequestHandler getOwner()
 */
class FormRequestExtension extends Extension {

    /**
     * external form controller.
     */
    protected $externalFormController;

    /**
     * FormRequestExtension constructor.
     * @param ExternalFormController|null $externalFormController
     */
    public function __construct($externalFormController = null)
    {
        parent::__construct();

        $this->externalFormController = isset($externalFormController) ? $externalFormController : new ExternalFormController();
    }

    /**
     * called before handling action so we can hook in and create the 'form'-action.
     * @param string $action
     * @param string $content
     * @param bool $handleWithMethod
     */
    public function onBeforeHandleAction($action, &$content, &$handleWithMethod) {
        if($request = $this->getOwner()->getRequest()) {
            $params = array_values($request->params);

            $parts = $request->getUrlParts();
            if (isset($params[0]) && $params[0] == "forms" && ((isset($params[1]) && $params[1] == "form") || $parts[0] == "form")) {
                if (!isset($params[1]) && $parts[0] == "forms") {
                    $request->shift(1);
                }
                if($parts[0] == "forms" && $parts[1] == "form") {
                    $request->shift(2);
                }

                $formRequest = $request;
                if (count($params) > 2) {
                    $formRequest = clone $formRequest;
                    $urlParts = array_slice($params, 2);
                    $formRequest->setUrlParts(array_merge($urlParts, $parts));
                } else if($parts[0] == "form" && !isset($params[1])) {
                    $request->shift(1);
                }

                $handleWithMethod = false;

                if ($arguments = $formRequest->match('$form!/$field!', true)) {
                    $content = $this->externalFormController->handleRequest($formRequest, true);
                    if (!$content) {
                        $content = $this->getOwner()->index();
                    }
                } else {
                    $content = $this->getOwner()->index();
                }
            }
        }
    }

    /**
     * this action prohibits the controller from checking permission for 'form'-action.
     * @param string $action
     * @param bool $hasAction
     */
    public function extendHasAction($action, &$hasAction) {
        if($request = $this->getOwner()->getRequest()) {
            $params = array_values($request->params);

            $parts = $request->getUrlParts();
            if (isset($params[0]) && $params[0] == "forms" && ((isset($params[1]) && $params[1] == "form") || $parts[0] == "form")) {
                $hasAction = true;
            }
        }
    }
}

gObject::extend("RequestHandler", "FormRequestExtension");
