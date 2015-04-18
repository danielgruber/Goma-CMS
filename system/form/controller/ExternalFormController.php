<?php defined("IN_GOMA") OR die();

/**
 * handler for externel urls
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class ExternalFormController extends RequestHandler {
    /**
     * handles the request
     *
     * @name handleRequest
     * @access public
     * @param Request
     * @return string|false
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
     * returns if action exists and when it exists the data of the action.
     *
     * @name FieldExtAction
     * @access public
     * @param name - form
     * @param name - field
     * @return string|false
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