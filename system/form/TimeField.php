<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 22.02.2013
  * $Version
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TimeField extends FormField
{
		/**
		 *@name __construct
		 *@param string -name
		 *@param string - title
		 *@param string - value
		 *@param array - between: key 0 for start and key 1 for end
		 *@param object - form
		*/
		public function __construct($name, $title = null, $value = null, $between = null, $form = null)
		{
				$this->between = $between;
				parent::__construct($name, $title, $value, $form);
		}
		
		/**
		 * creates the field
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode() {
			$node = parent::createNode();
			$node->type = "text";
			$node->addClass("timepicker");
			return $node;
		}
		
		/**
		 * validate
		 *
		 *@name validate
		*/
		public function validate($value) {
			if (($timestamp = strtotime($value)) === false) {
			    return lang("no_valid_time", "No valid timestamp!");
			} else {
				if($this->between && is_array($this->between)) {
					$between = array_values($this->between);
					$start = strtotime($between[0]);
					$end = strtotime($between[1]);
					if($start < $timestamp && $timestamp < $end) {
						return true;
					} else {
						$err = lang("time_not_in_range", "The given time is not between the range \$start and \$end.");
						$err = str_replace('$start', date("H:i:s", $start), $err);
						$err = str_replace('$end', date("H:i:s", $end), $err);
						return $err;
					}
				}
				$this->value = date("H:i:s", $timestamp);
				return true;
			}
		}
		
		/**
		 * render JavaScript
		*/
		public function JS() {
			Resources::add("system/libs/thirdparty/ui-timepicker/jquery.ui.timepicker.js");
			Resources::add("system/libs/thirdparty/ui-timepicker/jquery.ui.timepicker.css");
			$regional = "";
			foreach(i18n::getLangCodes(Core::$lang) as $code) {
				if(file_exists("system/libs/thirdparty/ui-timepicker/i18n/jquery.ui.timepicker-".$code.".js")) {
					Resources::add("system/libs/thirdparty/ui-timepicker/i18n/jquery.ui.timepicker-".$code.".js");
					$regional = $code;
					break;
				}
			}
			return '$(function(){$("#'.$this->ID().'").timepicker({regional: '.var_export($regional, true).'});});';
		}
}