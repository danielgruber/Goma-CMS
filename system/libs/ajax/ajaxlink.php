<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 08.07.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ajaxlink extends RequestHandler
{
		public $url_handlers = array(
			"link/\$id"		=> "action",
			"popup/\$id"	=> "popup"
		);
		
		public $allowed_actions = array(
			"action",
			"popup"
		);
		
		/**
		 * for PHP
		*/
		public $code;
		/**
		 *@name __construct
		 *@param callback - function
		 *@access public
		*/
		public function __construct($callback = "", $code = "")
		{
				parent::__construct();
				
				if($callback == "")
				{
						return false;
				}
				
				
				$code = ($code == "") ? strtolower(randomString(20)) : strtolower($code);
				session_store("ajaxpopups_" . $code, $callback);
				$this->code = $code;
		}
		/**
		 * generates a link
		 *@name link
		 *@access public
		 *@param string - title
		*/
		public function link($title, $classes = array())
		{
				return '<a href="system/ajax/popup/'.$this->code.'" class="'.implode(' ',$classes).'" rel="bluebox" title="'.$title.'">'.$title.'</a>';
		}
		/**
		 * shows the popup
		 *@name popup
		 *@access public
		*/
		public function popup()
		{
				$code = $this->getParam("id");
				if(session_store_exists("ajaxpopups_" . $code))
				{
						$callback = session_restore("ajaxpopups_" . $code);
						return callback($callback);
				} else
				{
						return false;
				}
		}
		
		/* --- */
		
		/**
		 * for TEMPLATE
		*/		
		/**
		 * control
		 *@name action
		 *@access public
		*/
		public function action()
		{
				$code = $this->getParam("id");
				$code = trim($code);
				if(isset($_SESSION['ajaxlinks'][$code]))
				{					
						return tpl::init($_SESSION['ajaxlinks'][$code]);
				} else
				{
						
						return $code . " wasn't found." . var_export($_SESSION['ajaxlinks'], true);
				}
		}
}
