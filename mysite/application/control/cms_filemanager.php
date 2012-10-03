<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 29.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class cms_filemanager extends DataObject {}


class cms_filemanagerController extends Controller
{
		public $template = "../../adm/tpl/{admintpl}/filemanager/page.html";
		/*
		 * the content
		*/
		public static $content;
		public function handleRequest($request)
		{
				self::$content = parent::handleRequest($request);
				
				$GLOBALS["cms_atpl"] = settingsController::Get("admintpl");
				
				$this->template = str_replace("{admintpl}", settingsController::Get("admintpl"), $this->template);
				
				return tpl::init($this->template, true, array("content" => self::$content));
		}
		/**
		 * show-function
		 *@name show
		*/
		public function index()
		{		
				if(!right(7))
				{
						throwErrorById(5);
				}
				$options = array();
				if(isset($_GET["type"]) && $_GET["type"] == "image")
				{
						$options["allowed_extensions"] = "(jpg|png|jpeg|gif|bmp)";
				}
				return new filemanager(CURRENT_PROJECT . '/uploaded/', $options);
		}
		/**
		 * content
		*/
		public function content()
		{
				return self::$content;
		}
		/**
		 * headers
		 *@name header
		 *@access public
		*/
		public function header()
		{
				return header::get();
		}
}
