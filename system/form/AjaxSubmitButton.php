<?php
defined("IN_GOMA") OR die();

/**
 * A simple FormAction, which submits data via Ajax and calls the
 * ajax-response-handler given.
 *
 * you should return the given AjaxResponse-Object or Plain JavaScript in
 * Ajax-Response-Handler.
 * a handler could look like this:
 * public function ajaxSave($data, $response) {
 *      $response->exec("alert('Nice!')");
 *      return $response;
 * }
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package    Goma\Form
 * @version    2.1.9
 */
class AjaxSubmitButton extends FormAction {
    /**
     * the action for ajax-submission
     */
    protected $ajaxsubmit;

    /**
     * @name __construct
     * @access public
     * @param string - name
     * @param string - title
     * @param string - ajax submission
     * @param string - optional submission
     * @param object - form
     */
    public function __construct($name = "", $value = "", $ajaxsubmit = null, $submit = null, $classes = null, &$form = null)
    {

        parent::__construct($name, $value, null, $classes);
        if ($submit === null)
            $submit = "@default";

        $this->submit = $submit;
        $this->ajaxsubmit = $ajaxsubmit;
        if ($form != null) {
            $this->parent = &$form;
            $this->setForm($form);
        }
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        $info->addJSFile("system/form/actions/AjaxSubmitButton.js");

        parent::addRenderData($info, $notifyField);
    }

    /**
     * generates the js
     *
     * @return string
     */
    public function js()
    {
        // appendix to the url
        $append = '?redirect=' . urlencode(getRedirect());
        foreach ($this->form()->getRequest()->get_params as $key => $val) {
            $append .= '&' . urlencode($key) . '=' . urlencode($val);
        }

        return 'initAjaxSubmitbutton(' . var_export($this->ID(), true) . ', ' . var_export($this->divID(), true) . ', form, field, ' . var_export($this->form()->url, true) . ', ' . var_export($append, true) . ');';
    }

    public function canSubmit($data)
    {
        return !!$data;
    }

    public function getSubmit() {
        return $this->submit;
    }

    /**
     * @return null|string
     */
    public function __getSubmit() {
        if($this->getRequest()->canReplyJavaScript()) {
            return array($this, "submit");
        }

        return $this->submit;
    }

    /**
     * submit-function
     * @param array $data
     * @param Form $form
     * @param Controller $controller
     * @return mixed
     */
    public function submit($data, $form, $controller)
    {
        $response = new FormAjaxResponse($form, $this);

        $response->exec('$("#' . $this->form()->{"secret_" . $this->form()->id()}->id() . '").val("' . convert::raw2js($this->form()->getSecretKey()) . '");');

        try {
            $response = $this->handleSubmit($data, $response, $controller);

            if (is_a($response, "AjaxFormResponse") && $response->getLeaveCheck() === null) {
                $response->setLeaveCheck(false);
            }

            return $response;
        } catch (Exception $e) {
            if (is_a($e, "FormNotValidException")) {
                /** @var FormNotValidException $e */
                $errors = $e->getErrors();
            } else {
                $errors = array($e);
            }

            /** @var Exception $error */
            foreach ($errors as $error) {
                if (is_a($error, "FormMultiFieldInvalidDataException")) {
                    /** @var FormMultiFieldInvalidDataException $error */
                    foreach ($error->getFieldsMessages() as $field => $message) {
                        if ($message) {
                            $response->addError(lang($message, $message));
                        }

                        $response->addErrorField($field);
                    }
                } else if (is_a($error, "FormInvalidDataException")) {
                    /** @var FormInvalidDataException $error */
                    if ($error->getMessage()) {
                        $response->addError(lang($error->getMessage(), $error->getMessage()));
                    }

                    $response->addErrorField($error->getField());
                } else {
                    if ($error->getMessage()) {
                        $prev = $error->getPrevious() ? " " . $error->getPrevious()->getMessage() : "";
                        $response->addError(lang($error->getMessage(), $error->getMessage()) . $prev);
                    }
                }
            }

            return $response;
        }
    }

    /**
     * @param array $result
     * @param FormAjaxResponse $response
     * @param Controller $controller
     * @return mixed
     */
    protected function handleSubmit($result, $response, $controller)
    {
        $submission = $this->ajaxsubmit;

        if (is_callable($submission) && !is_string($submission)) {
            return call_user_func_array($submission, array(
                $result,
                $response,
                $this->form(),
                $controller
            ));
        } else {
            return call_user_func_array(array(
                $controller,
                $submission
            ), array(
                $result,
                $response,
                $this->form(),
                $controller
            ));
        }
    }

    /**
     * sets the submit-method and ajax-submit-method
     *
     * @name setSubmit
     * @access public
     * @param string - submit
     * @param string - ajaxsubmit
     * @return $this
     */
    public function setSubmit($submit, $ajaxsubmit = null)
    {
        $this->submit = $submit;
        if (isset($ajaxsubmit))
            $this->ajaxsubmit = $ajaxsubmit;
        return $this;
    }
    /**
     * returns the ajax-submit-method
     */
    public function getAjaxSubmit()
    {
        return $this->ajaxsubmit;
    }
}
