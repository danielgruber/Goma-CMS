<?php
/**
  * shows a dropdown-select, where the user can choose a language from the available languages
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 10.09.2010
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class langSelect extends Select
{
		/**
		 * construct
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param string - select
		 *@param object - form
		*/
		public function __construct($name, $title = null, $selected = null, $form = null)
		{
				
				parent::__construct($name, $title, $this->options(), $selected, $form);
		}
		/**
		 * gets all options
		 *@name options
		 *@access public
		*/
		public function options()
		{
				$options = array();
				$files = scandir(ROOT . LANGUAGE_DIRECTORY);
				foreach($files as  $file)
				{
						if(filetype(ROOT . LANGUAGE_DIRECTORY . $file) == "dir" && $file != "." && $file != "..")
						{
								if(file_exists(ROOT . LANGUAGE_DIRECTORY . $file . '/description.php'))
								{
										include(ROOT . LANGUAGE_DIRECTORY . $file . '/description.php');
										$description = "(".$name.")";
								} else
								{
										$description = "";
								}
								$options[$file] = $file . " " .  $description; 
						}
				}
				return $options;
		}
}