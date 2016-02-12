<?php
defined("IN_GOMA") OR die();

/**
 * Thrown when a Validator throws an exception.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 */
class FormNotSubmittedException extends Exception {

    /**
     * @var int
     */
    protected $standardCode = ExceptionManager::FORM_INVALID;

    /**
     * FormInvalidDataException constructor.
     * @param string $message
     * @param null|int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "The Form was not submitted.", $code = null, Exception $previous = null) {
        if(!isset($code)) {
            $code =  $this->standardCode;
        }

        parent::__construct($message, $code, $previous);
    }
}
