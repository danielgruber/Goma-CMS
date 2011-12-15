<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 06.01.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FormTest extends Test
{
		public $name = "form_test";
		public function render()
		{
				Profiler::mark("FormTest::render");
				$form = new Form($this, "test2", array(
					new CheckBox("test", "test"),
					new SingleSelectDropDown("sex", "Sex", array("male", "female"), "male"),
					new UploadFrame("file", "File"),
					new EMail("email", "E-Mail"),
					new MultiSelectDropDown("pages", "Pages", array("1" => "Home", "2" => "Contact Us", "3" => "About Us"), array(1))
				), array(
					new FormAction("submit", "Speichern"),
					new AjaxSubmitButton("ajax", "Save via Ajax", "AjaxSubmit", "test")
				), array(
					new RequiredFields(array("email"))
				));
				$form->setSubmission("test");
				$data = $form->render();
				Profiler::unmark("FormTest::render");
				return $data;
		}
		public function test($result)
		{
				return print_r($result, true);
		}
		public function ajaxsubmit($result, $response)
		{
				$response->exec(new Dialog(print_r($result, true), "result"));
				return $response->render();
		}
}

Object::extend("TestController", "FormTest");