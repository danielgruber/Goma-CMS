<?php
defined("IN_GOMA") OR die();


/**
 * Wrapper used to render form-fields.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class FormFieldErrorRenderData extends FormFieldRenderData {
    /**
     * exception.
     *
     * @var Exception
     */
    protected $exception;

    /**
     * constructor.
     *
     * @param string $name
     * @param Exception $exception
     */
    public function __construct($name, $exception)
    {
        parent::__construct($name, null, null, null);

        $this->exception = $exception;
    }

    /**
     * to rest-array.
     * @param bool $includeRendered
     * @return array
     */
    public function ToRestArray($includeRendered = false)
    {
        $data = array(
            "name" => $this->name,
            "error" => array(
                "code" => $this->exception->getCode(),
                "message" => $this->exception->getMessage(),
                "file" => $this->exception->getFile(),
                "line" => $this->exception->getLine()
            )
        );

        if($includeRendered) {
            $data["field"] = new HTMLNode("div", array("class" => "error"), convert::raw2text($this->name) . ": " . $this->exception->getMessage() . $this->exception->getTraceAsString());
        }

        return $data;
    }
}
