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
class FileUploadException extends GomaException {
    /**
     * @var int
     */
    protected $standardCode = ExceptionManager::FILEUPLOAD_FAIL;
}
