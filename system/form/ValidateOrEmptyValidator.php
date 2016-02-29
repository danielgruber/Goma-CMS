<?php defined("IN_GOMA") OR die();

/**
 * Simple class to call validate function when form-fields have value.
 *
 * @package		Goma\Form\Validation
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ValidateOrEmptyValidator extends FormValidator {
    /**
     * @param array $fields
     */
    public function __construct($fields)
    {
        parent::__construct();

        if (!is_array($fields)) {
            throw new InvalidArgumentException("RequiredFields requires an array to be given.");
        }

        $this->data = $fields;
    }

    /**
     * validates the data
     *
     * @throws FormMultiFieldInvalidDataException
     */
    public function validate() {
        // get data
        $missing = $this->getErrors();

        // create response for it
        if (count($missing) > 0) {
            throw new FormMultiFieldInvalidDataException($missing);
        }
    }

    /**
     * returns errors as string. empty string means passed.
     *
     * @return array
     */
    protected function getErrors() {
        $errors = array();

        foreach ($this->data as $field) {
            if ($this->form->hasField($field)) {
                $fieldObject = $this->form->getField($field);
                $fieldName = $fieldObject->dbname;
                if (isset($this->form->result[$fieldName]) &&
                    !empty($this->form->result[$fieldName]) &&
                    (!is_object($this->form->result[$fieldName]) || !gObject::method_exists($this->form->result[$fieldName], "bool") || $this->form->result[$fieldName]->bool())) {
                    // own validation
                    try {
                        $v = $fieldObject->validate($this->form->result[$fieldName]);
                        if ($v !== true) {
                            $errors[$fieldName] = $v;
                        }
                    } catch(Exception $e) {
                        $errors[$fieldName] = $e->getMessage();
                    }
                }
            }
        }

        return $errors;
    }
}
