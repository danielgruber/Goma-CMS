<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 04.09.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

ClassInfo::addSaveVar("gLoader", "resources");

class gLoader extends Object
{
		/**
		 * loadable resources
		 *@name resources
		 *@access public
		*/
		public static $resources = array();
		/**
		 * preloaded resources
		 *@name preloaded
		 *@access public
		*/
		public static $preloaded = array();
		/**
		 * adds a loadable resource
		 *@name addLoadAble
		 *@access public
		 *@param string - name
		 *@param string - filename
		 *@param array - required other resources
		*/
		public function addLoadAble($name, $file, $required = array())
		{
				
				self::$resources[$name] = array(
					"file"		=> $file,
					"required"	=> $required
				);
		}
		/**
		 * this is the php-function for the js-function gloader.load, it loads it for pageload
		 *@name load
		 *@access public
		*/
		public function load($name)
		{
				if(!isset(self::$preloaded[$name]))
				{					
						if(isset(self::$resources[$name]))
						{
								foreach(self::$resources[$name]["required"] as $name)
								{
										self::load($name);
								}
								Resources::add(self::$resources[$name]["file"], "js", "preload");
						}
						self::$preloaded[$name] = true;
				}
		}
}
