<?php defined("IN_GOMA") OR die();

/**
 * handler for external form urls.
 * is also manages session-managment.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.2
 */
class ExternalFormController extends RequestHandler {
    /**
     * handles the requemst
     *
     * @param Request $request
     * @param bool $subController
     * @return mixed
     */
    public function handleRequest($request, $subController = false) {

        $this->request = $request;
        $this->subController = $subController;

        $this->init();

        $form = $request->getParam("form");
        $field = $request->getParam("field");
        return $this->FieldExtAction($form, $field);
    }

    /**
     * calls handle-Request on the FormField we found.
     * it also manages session-managment.
     *
     * @param Form $form
     * @param FormField $field
     * @return bool
     */
    public function FieldExtAction($form, $field) {
        $field = strtolower($field);

        /** @var Form $formInstance */
        if($formInstance = Core::globalSession()->get(Form::SESSION_PREFIX . "." . strtolower($form))) {
            if(isset($formInstance->$field)) {
                $oldRequest = $formInstance->request;
                $oldControllerRequest = $formInstance->controller->getRequest();

                $formInstance->request = $this->request;
                $formInstance->controller->setRequest($this->request);

                $data = $formInstance->$field->handleRequest($this->request);

                $formInstance->request = $oldRequest;
                $formInstance->controller->setRequest($oldControllerRequest);
                Core::globalSession()->set(Form::SESSION_PREFIX . "." . strtolower($form), $formInstance);
                return $data;
            }
            return false;

        }
        return false;
    }

}


Core::addRules(array('system/forms/$form!/$field!' => "ExternalFormController"), 50);
