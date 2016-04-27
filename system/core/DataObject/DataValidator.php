<?php defined("IN_GOMA") OR die();

/**
 * Validator-Object for DataObject-Form-Instances.
 *
 * @package		Goma\Form\Validation
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class DataValidator extends FormValidator
{
	/**
	 * @param ViewAccessableData $data
	 */
	public function __construct($data)
	{

		if (!is_subclass_of($data, "ViewAccessableData")) {
			throw new InvalidArgumentException('$data is not child of DataObject.');
		}
		$this->data = $data;
	}

	/**
	 * validates the data
	 */
	public function validate()
	{
		$valid = true;
		$errors = array();
		if (is_object($this->getForm()->result)) {
			$result = $this->getForm()->result->ToArray();
		} else {
			$result = $this->getForm()->result;
		}

		if (is_array($this->data->ToArray())) {
			$resultSet = array_merge($this->data->ToArray(), $result);
		} else {
			$resultSet = $result;
		}

		foreach ($result as $field => $data) {
			if (gObject::method_exists($this->data->classname, "validate" . $field)) {
				$method = "validate" . $field;
				$str = $this->data->$method($resultSet);
				if ($str !== true) {
					$valid = false;
					$errors[] = $str;
				}
			}
		}

		if (!$valid) {
			throw new Exception(implode(",", $errors));
		}
	}
}