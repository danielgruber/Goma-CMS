<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 19.12.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ImageSQLField extends DBField {	
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
					return '<img src="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/'.$width.'/'.$this->value.'" data-retina="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/'.($width*2).'/'.$this->value.'" style="width:'.$width.'px;" alt="'.$this->value.'" />';
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
					return '<img src="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/x/'.$height.'/'.$this->value.'" data-retina="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/x/'.($height*2).'/'.$this->value.'"  style="height:'.$height.'px;" alt="'.$this->value.'" />';
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
					return '<img src="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/'.$width.'/'.$height.'/'.$this->value.'" data-retina="'.ROOT_PATH.BASE_SCRIPT.'images/resampled/'.($width*2).'/'.($height*2).'/'.$this->value.'" style="width: '.$width.'px; height: '.$height.'px;" alt="'.$this->value.'" />';
			else 
					return $this->makeImage();
		}
		
		/**
		 * default convert
		*/
		public function forTemplate() {
			return $this->makeImage();
		}
}