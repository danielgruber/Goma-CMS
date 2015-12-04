<?php
defined("IN_GOMA") OR die();

/**
 * An phone-number text field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0
 */
class PhoneNumberField extends TextField {
    /**
     * @var string
     */
    protected $regexp = '/^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/';

    /**
     * @var string
     */
    protected $regexpError = 'form_phone_not_valid';
}
