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
     * handles the request
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

        if(session_store_exists("form_" . strtolower($form))) {
            $f = session_restore("form_" . strtolower($form));

            if(isset($f->$field)) {
                $data = $f->$field->handleRequest($this->request);

                session_store("form_" . strtolower($form), $f);
                return $data;
            }
            return false;

        }
        return false;
    }

}


Core::addRules(array('system/forms/$form!/$field!' => "ExternalFormController"), 50);
