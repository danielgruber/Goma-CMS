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
class DataNotFoundException extends GomaException {
    /**
     * @var int
     */
    protected $standardCode = ExceptionManager::DATA_NOT_FOUND;

    /**
     * FormInvalidDataException constructor.
     *
     * @param string $message
     * @param null|int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "Data not found.", $code = null, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function http_status() {
        return 404;
    }
}
