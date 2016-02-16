<?php defined("IN_GOMA") OR die();

/**
 * Adds a info-text to field.
 *
 * @package        Goma\Form-Framework
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version    2.0
 */
class InfoTextField extends Extension {
	/**
	 * created field with info-field.
	 * @param FormField $field
	 * @param string $info
	 * @return FormField
	 */
	public static function createFieldWithInfo($field, $info) {
		$field->info = $info;
		return $field;
	}

	/**
	 * adds the info to the field.
	 *
	 * @param FormFieldRenderData $info
	 */
	public function afterRenderFormResponse($info) {
		if(isset($this->owner->info) && $this->owner->info)
			$info->getRenderedField()->append(new HTMLNode("div", array("class" => "info_field"), $this->owner->info));
	}
}

gObject::extend("FormField", "InfoTextField");
