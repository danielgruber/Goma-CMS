<?php  defined("IN_GOMA") OR die();

/**
 * An email text field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1
 */
class Email extends TextField {
	/**
	 * @var string
	 */
	protected $regexp = RegexpUtil::EMAIL_REGEXP;

	/**
	 * @var string
	 */
	protected $regexpError = "form_email_not_valid";
}
