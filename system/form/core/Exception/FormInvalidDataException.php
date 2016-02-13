<?php
defined("IN_GOMA") OR die();

/**
 * Describe your class
 *
 * @package Goma
 *
 * @author D
 * @copyright 2016 D
 *
 * @version 1.0
 */
class FormInvalidDataException extends GomaException {
    /**
     * @var int
     */
    protected $standardCode = ExceptionManager::FORM_NOT_SUBMITTED;

    /**
     * @var string
     */
    protected $field;

    /**
     * FormInvalidDataException constructor.
     * @param string $field
     * @param string $message
     * @param null|int $code
     * @param Exception|null $previous
     */
    public function __construct($field, $message = "You provided not valid data", $code = null, Exception $previous = null) {
        $this->field = $field;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}