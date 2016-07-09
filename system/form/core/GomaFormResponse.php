<?php
defined("IN_GOMA") OR die();

/**
 * A GomaResponse object especially designed for Forms.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version 1.0
 */
class GomaFormResponse extends GomaResponse {
    /**
     * @var Form
     */
    protected $form;

    /**
     * prepended string.
     */
    protected $prependString;

    /**
     * @var ViewAccessableData
     */
    protected $serveWithModel;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var string|GomaResponse
     */
    protected $renderedForm;

    /**
     * @param Form $form
     * @return static
     */
    public static function create($form = null) {
        return new static($form);
    }

    /**
     * GomaFormResponse constructor.
     * @param Form $form
     */
    public function __construct($form)
    {
        parent::__construct(null);
        if(!isset($form)) {
            throw new InvalidArgumentException("Form must be not null.");
        }

        $this->form = $form;
    }

    public function isStringResponse() {
        $this->resolveForm();

        if(!is_a($this->renderedForm, "GomaResponse")) {
            if(is_object($this->renderedForm) && !method_exists($this->renderedForm, "__toString")) {
                throw new LogicException("Forms should return GomaResponse, other type or object with __toString");
            }

            return true;
        }

        return false;
    }

    /**
     *
     */
    public function resolveForm() {
        if(!isset($this->renderedForm)) {
            $this->renderedForm = $this->form->renderData();
            if(!is_a($this->renderedForm, "GomaResponse")) {
                parent::setBody($this->renderedForm);

                parent::getBody()->setIncludeResourcesInBody(!$this->form->getRequest()->is_ajax());
            }
        }
    }

    /**
     * @return bool
     */
    public function shouldServe()
    {
        if(!$this->isStringResponse()) {
            if(!is_string($this->renderedForm)) {
                return false;
            }

            return $this->shouldServe;
        } else {
            return $this->renderedForm->shouldServe();
        }
    }

    /**
     * @param bool $shouldServe
     * @return $this
     */
    public function setShouldServe($shouldServe)
    {
        if(!$this->isStringResponse()) {
            $this->renderedForm->setShouldServe($shouldServe);
        } else {
            parent::setShouldServe($shouldServe);
        }
        return $this;
    }

    /**
     * @return GomaResponseBody
     */
    public function getBody()
    {
        return $this->isStringResponse()  ? parent::getBody() : $this->renderedForm->getBody();
    }

    /**
     * @return string
     */
    public function getResponseBodyString()
    {
        return !$this->isStringResponse() ? $this->renderedForm->getResponseBodyString() : parent::getResponseBodyString();
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBodyString($body)
    {
        if(!$this->isStringResponse()) {
            $this->renderedForm->setBodyString($body);
        } else {
            parent::setBodyString($body);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if(!$this->isStringResponse()) {
            return $this->renderedForm->getStatus();
        } else {
            return parent::getStatus();
        }
    }

    /**
     * @param mixed $status
     * @return $this|void
     */
    public function setStatus($status)
    {
        if(!$this->isStringResponse()) {
            $this->renderedForm->setStatus($status);
        } else {
            parent::setStatus($status);
        }

        return $this;
    }

    /**
     * @param GomaResponseBody|string $body
     * @return $this
     */
    public function setBody($body)
    {
        if(isset($body)) {
            $this->resolveForm();
        }

        if(is_a($this->renderedForm, "GomaResponse")) {
            $this->renderedForm->setBody($body);
        } else {
            parent::setBody($body);
        }

        return $this;
    }

    /**
     * sets a header.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setHeader($name, $value) {
        if(is_a($this->renderedForm, "GomaResponse")) {
            $this->renderedForm->setHeader($name, $value);
        } else {
            parent::setHeader($name, $value);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeHeader($name) {
        if(is_a($this->renderedForm, "GomaResponse")) {
            $this->renderedForm->removeHeader($name);
        } else {
            parent::removeHeader($name);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        if(!$this->isStringResponse()) {
            return $this->renderedForm->getHeader();
        } else {
            return parent::getHeader();
        }
    }

    /**
     * @param string $content
     * @return $this
     */
    public function prependContent($content) {
        $this->prependString = $content;
        return $this;
    }

    /**
     * @param string $model
     * @param null $view
     * @param string $formName
     * @return $this
     */
    public function renderWith($model, $view = null, $formName = "form") {
        if(is_string($model)) {
            if(!isset($view)) {
                $view = $model;
                $model = new ViewAccessableData();
            } else {
                throw new InvalidArgumentException();
            }
        }

        $this->serveWithModel = $model;
        $this->template = $view;
        $this->templateName = $formName;

        return $this;
    }

    /**
     * outputs data.
     */
    public function output()
    {
        if(!$this->isStringResponse()) {
            $this->renderedForm->output();
        } else {
            parent::output();
        }
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    public function __toString()
    {
        return $this->getResponseBodyString();
    }
}
