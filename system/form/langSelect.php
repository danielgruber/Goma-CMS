<?php
/**
  * shows a dropdown-select, where the user can choose a language from the available languages
  *
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 20.03.2012
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
				parent::__construct($name, $title, null, $selected, $form);
		}
		
		/**
		 * gets all options
		 *@name options
		 *@access public
		*/
		public function options()
		{
				$options = array();
				$data = i18n::listLangs();
				foreach($data as $lang => $contents) {
					$options[$lang] = $contents["title"];
				}
				return $options;
		}
}