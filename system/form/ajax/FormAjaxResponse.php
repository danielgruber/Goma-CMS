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
        if($this->errors) {
            $errors = new HTMLNode('div', array('class' => "error"), array(new HTMLNode('ul', array())));
            foreach($this->errors as $error) {
                $errors->getNode(0)->append(new HTMLNode('li', array('class' => 'erroritem'), $error));
            }

            $this->prepend("#" . $this->form->ID(), $errors->render());
        }

        if($this->success) {
            $succceses = new HTMLNode('div', array('class' => "success"), array(new HTMLNode('ul', array())));
            foreach($this->success as $success) {
                $succceses->getNode(0)->append(new HTMLNode('li', array('class' => 'successitem'), $success));
            }

            $this->prepend("#" . $this->form->ID(), $succceses->render());
        }

        return parent::render();
    }
}
