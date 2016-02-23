<?php
defined("IN_GOMA") OR die();
/**
 * Bad-Request Exception, not only throwing, but also setting http status to 400.
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.2
 */
class BadRequestException extends GomaException
{
    protected $standardCode = ExceptionManager::BAD_REQUEST;

    public function http_status()
    {
        return 400;
    }
}
