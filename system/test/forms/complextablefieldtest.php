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

class ComplexTableFieldTest extends Test
{
		public $name = "complexTableField";
		public function render()
		{
				Profiler::mark("FormTest::render");
				$model = new Pages(array("id" => 14));
				$form = new Form($model->controller(), "test2", array(
					new HasManyComplexTableField("children", "Members", array(), array("title" => "Titel", "path" => "Pfad"))
				), array(
					new FormAction("submit", "Speichern"),
					new AjaxSubmitButton("ajax", "Save via Ajax", "AjaxSubmit", "test")
				), array(
					new RequiredFields(array("email"))
				));
				$form->setSubmission("safe");
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

Object::extend("TestController", "ComplexTableFieldTest");