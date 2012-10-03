<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 25.06.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ColumnedAdmin extends AdminItem
{
		public $baseTemplate = "admin/columnedadmin.html";
		/**
		 * serve
		*/
		public function serve($content) {
			$data = $this->model_inst;
				
			return parent::serve($data->customise(array(), array("CONTENT"	=> $content))->renderWith($this->baseTemplate));
		}
		/**
		 * generates template-file
		 * e.g. it replaces {atpl} with the current template
		*/
		public function generateTemplateFile($template)
		{				
				$this->baseTemplate = $this->baseTemplate;;
				return $template;
		}
}