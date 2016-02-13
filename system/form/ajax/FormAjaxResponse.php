<?php
defined("IN_GOMA") OR die();

/**
 * Special ajax-response which supports form-calls.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Form
 * @version	1.0
 */
class FormAjaxResponse extends AjaxResponse
{
    /**
     * form.
     *
     * @var Form
     */
    protected $form;

    /**
     * button.
     *
     * @var AjaxSubmitButton
     */
    protected $button;

    /**
     * errors.
     */
    protected $errors = array();

    /**
     * error-fields.
     */
    protected $errorFields = array();

    /**
     * success-message(s).
     */
    protected $success = array();

    /**
     * constructor.
     *
     * @param Form $form
     * @param AjaxSubmitButton|null $button
     */
    public function __construct($form, $button = null)
    {
        parent::__construct();

        $this->form = $form;
        $this->button = $button;

        $this->exec('$("#' . $this->form->ID() . '").find(".error").remove();');
        $this->exec('$("#' . $this->form->ID() . '").find(" > .success").remove();');
        $this->exec('$("#' . $this->form->ID() . '").find(".form-field-has-error").removeClass("form-field-has-error");');

        if($button != null) {
            $this->exec('var ajax_button = $("#' . $button->ID() . '");');
        }
    }

    /**
     * resets the form.
     */
    public function resetForm() {
        $this->exec('if($("#'.$this->form->ID().'").length == 1) $("#'.$this->form->ID().'").get(0).reset();');
    }

    /**
     * adds an success-message.
     * @param string $success
     */
    public function addSuccess($success) {
        $this->success[] = $success;
    }

    /**
     * returns array of succcesses.
     */
    public function getSuccess() {
        return $this->success;
    }

    /**
     * resets succcess.
     */
    public function resetSuccess() {
        $this->success = array();
    }

    /**
     * adds an error-field.
     * @param string $field
     */
    public function addErrorField($field) {
        $this->errorFields[$field] = $field;
    }

    /**
     * returns array of error-fields.
     */
    public function getErrorFields() {
        return $this->errorFields;
    }

    /**
     * resets error-fields.
     */
    public function resetErrorFields() {
        $this->errorFields = array();
    }

    /**
     * adds an error.
     * @param string $err
     */
    public function addError($err) {
        $this->errors[] = $err;
    }

    /**
     * returns array of errors.
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * resets errors.
     */
    public function resetErrors() {
        $this->errors = array();
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return AjaxSubmitButton
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * @override
     */
    public function render()
    {
        $this->renderItems($this->errors, "error");
        $this->renderItems($this->success, "success");

        foreach($this->errorFields as $field) {
            if($this->form->getField($field)) {
                $this->exec("$('#" . $this->form->getField($field)->divID() . "').addClass('form-field-has-error');");
            }
        }

        return parent::render();
    }

    /**
     * @param array $array
     * @param string $name
     */
    protected function renderItems($array, $name) {
        if(!empty($array)) {
            $data = new HTMLNode('div', array('class' => $name), array(new HTMLNode('ul', array())));
            foreach($array as $item) {
                $data->getNode(0)->append(new HTMLNode('li', array('class' => $name . "item"), $item));
            }

            $this->prepend("#" . $this->form->ID(), $data->render());
        }
    }
}
