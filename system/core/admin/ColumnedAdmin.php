<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
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