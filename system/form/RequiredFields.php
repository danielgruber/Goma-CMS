<?php defined("IN_GOMA") OR die();

/**
 * Simple class to check forms for Required Fields.
 *
 * @package		Goma\Form\Validation
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class RequiredFields extends FormValidator
{
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
     * @throws Exception
     */
    public function validate()
    {
        // get data
        $missing = $this->getMissingFields();

        // create response for it
        if (count($missing) > 0) {
            $text = lang("form_required_fields", "Please fill out the oligatory fields");
            $i = 0;
            foreach (array_keys($missing) as $field) {
                if ($i == 0) {
                    $i = 1;
                } else {
                    $text .= ", ";
                }
                $text .= ' \'' . $this->getForm()->getField($field)->getTitle() . '\'';
            }

            array_unshift($missing, $text);

            throw new FormMultiFieldInvalidDataException($missing);
        }
    }

    /**
     * returns array of missing fields.
     *
     * @return array
     */
    protected function getMissingFields() {
        $missing = array();

        foreach ($this->data as $field) {
            if ($this->form->hasField($field)) {
                $fieldObject = $this->form->getField($field);
                $fieldName = $fieldObject->dbname;
                if (!isset($this->form->result[$fieldName]) ||
                    empty($this->form->result[$fieldName]) ||
                    (is_object($this->form->result[$fieldName]) && gObject::method_exists($this->form->result[$fieldName], "bool") && !$this->form->result[$fieldName]->bool())) {
                    $missing[$field] = "";
                } else {
                    // own validation
                    try {
                        $v = $fieldObject->validate($this->form->result[$fieldName]);
                        if ($v !== true) {
                            $missing[$field] = $v;
                        }
                    } catch(Exception $e) {
                        $missing[$field] = $e->getMessage();
                    }
                }
            }
        }

        return $missing;
    }

    /**
     * javascript for client-side validation
     *
     * @name JS
     * @access public
     * @return string
     */
    public function JS()
    {
        $js = '$(function(){ ';
        $js .= 'if($("#form_' . $this->form->name() . '").length > 0)
							{
								$("#form_' . $this->form->name() . '").bind("formsubmit", function()
								{
									var require_lang = "<div class=\"err\" style=\"color: #ff0000;\">' . lang("form_required_field") . '</div>";
									
									var valid = true;
								';
        foreach ($this->data as $field) {
            if (!isset($this->form->fields[$field])) {
                continue;
            }

            /** @var FormField $formField */
            $formField = $this->form->fields[$field];
            $key = "v_" . $field;
            $js .= "var " . $key . " = function() { " . "}
						if(" . $key . "() === false) {
							valid = false;	
						}";
            $js .= 'if($("#' . $formField->ID() . '").length > 0)
								{
									if($("#' . $formField->ID() . '").length > 0)
									{
										// input
										if($("#' . $formField->ID() . '").val() == "")
										{
											$("#' . $formField->ID() . '").parent().append(require_lang);
											valid = false;
										}
									}											
									
								}
								';
        }
        // end foreach
        $js .= '
									if(valid == false)
										return false;
								
								});
							}
						});';

        return $js;
    }
}
