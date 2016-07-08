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
class FormMultiFieldInvalidDataException extends GomaException
{
    /**
     * @var int
     */
    protected $standardCode = ExceptionManager::FORM_INVALID;

    /**
     * @var array
     */
    protected $fieldsMessages;

    /**
     * FormInvalidDataException constructor.
     * @param string[] $fieldsMessages
     * @param null|int $code
     * @param Exception|null $previous
     */
    public function __construct($fieldsMessages, $code = null, Exception $previous = null)
    {
        $this->fieldsMessages = $fieldsMessages;

        parent::__construct($this->getFieldsMessage(), $code, $previous);
    }

    /**
     * @return array
     */
    public function getFieldsMessages()
    {
        return $this->fieldsMessages;
    }

    /**
     * @return string
     */
    public function getFieldsMessage()
    {
        return implode(", ", $this->fieldsMessages);
    }
}
