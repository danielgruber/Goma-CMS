<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 20.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class PHPPage extends Page
{
		public $name = '{$_lang_php_script}';
		public function getForm(&$form)
		{
				parent::getForm($form, array());
				
				$form->remove("edit_type");
				$form->remove("edit_groups");
				$form->add(new Textarea('data', lang("php_script", "PHP Code"), null, "300px", "800px"), 0, "content");
		}
		/**
		  * executes the php-script
		  *
		  *@name getBoxContet
		  *@access public
		*/
		public function getContent()
		{
				// first set start position
				$hash = "<!--" . microtime(true) . '-->';
				ob_start();
				echo $hash;
				eval($this->text);
				
				$content = ob_get_contents(); // get contents
				ob_end_clean(); // clean contents
				
				// second get data, so we can insert it in site-layout
				if($contents = explode($hash, $content))
				{
						if(count($contents) > 1)
						{
								echo $contents[0];
								$data = $contents[1];
						} else
						{
								$data = str_replace($hash, '',$content);
						} 
				} else
				{
						$data = str_replace($hash, '',$content);
				}
				return $data;
		}	
		
		public function canWrite() {
			return right(10);
		}
		
		public function canInsert() {
			return right(10);
		}
}

class PHPPageController extends PageController {}