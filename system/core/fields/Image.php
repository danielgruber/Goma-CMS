<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 14.04.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ImageSQLField extends DBField implements DefaultConvert {	
		
		/**
		 * field-type if you want to replace typed field type
		 * for example varchar(200)
		 *
		 *@name field_type
		 *@access public
		*/ 
		static public $field_type = "varchar(100)";
		/**
		 * gets the field-type
		 *
		 *@name getFieldType
		 *@access public
		*/
		static public function getFieldType($args = array()) {
			return "varchar(200)";
		}
		/**
		 * generates a image from the image-uri
		*/
		public function makeImage() {
			return '<img src="'.$this->value.'" alt="'.$this->value.'" />';
		}
		/**
		 * sets the width of the image
		 *
		 *@name setWidth
		 *@access public
		*/
		public function setWidth($width) {
			if(_ereg("^[0-9]+$",$width))
					return '<img src="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/'.$width.'/'.$this->value.'" alt="'.$this->value.'" />';
			else 
					return $this->makeImage();
		}
		/**
		 * sets the width of the image
		 *
		 *@name setWidth
		 *@access public
		*/
		public function setHeight($height) {	
			if(_ereg("^[0-9]+$",$height))
					return '<img src="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/x/'.$height.'/'.$this->value.'" alt="'.$this->value.'" />';
			else 
					return $this->makeImage();
		}
		/**
		 * sets the width of the image
		 *
		 *@name setWidth
		 *@access public
		*/
		public function setSize($width, $height) {
			if(_ereg("^[0-9]+$",$width) && _ereg("^[0-9]+$",$height))
					return '<img src="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/'.$width.'/'.$height.'/'.$this->value.'" alt="'.$this->value.'" />';
			else 
					return $this->makeImage();
		}
		
		/**
		 * default convert
		*/
		public function convertDefault() {
			return $this->makeImage();
		}
}