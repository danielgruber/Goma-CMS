<?php
defined("IN_GOMA") OR die();

/**
 * Exception of Template-Parser.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 */
class TemplateParserException extends GomaException {
    protected $standardCode = ExceptionManager::TPL_COMPILE_ERROR;

    protected $template;

    /**
     * TemplateParserException constructor.
     * @param string $message
     * @param int|null $template
     * @param Exception|null $code
     * @param $previous
     */
    public function __construct($message, $template, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->template = $template;
    }

    /**
     * @return int|null
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
