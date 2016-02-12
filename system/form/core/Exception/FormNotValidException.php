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
class FormNotValidException extends Exception
{
    /**
     * @var int
     */
    protected $standardCode = ExceptionManager::FORM_INVALID;

    /**
     * @var array
     */
    protected $errors;

    /**
     * FormInvalidDataException constructor.
     * @param array $errors
     * @param null|int $code
     * @param Exception|null $previous
     */
    public function __construct($errors, $code = null, Exception $previous = null)
    {
        if (!isset($code)) {
            $code = $this->standardCode;
        }

        $this->errors = $errors;

        foreach($errors as $error) {
            if(!is_a($error, "Exception")) {
                throw new InvalidArgumentException("All elements of the array must be type of Exception.");
            }
        }

        parent::__construct(count($errors) . " Errors in Form.", $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}