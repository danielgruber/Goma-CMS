<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 28.03.2013
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
		public function makeImage($absolute = false, $html = "", $style = "") {
			$url = $this->value;
			if($absolute)
				$url = BASE_URI . BASE_SCRIPT . $url;
			
			return '<img src="'.$url.'" alt="'.$this->value.'" style="'.$style.'" '.$html.' />';
		}
		
		/**
		 * sets the width of the image
		 *
		 *@name setWidth
		 *@access public
		*/
		public function setWidth($width, $absolute = false, $html = "", $style = "") {
			if(_ereg("^[0-9]+$",$width)) {
				$url = 'images/resampled/'.$width.'/'.$this->value;
				if($absolute) {
					$url = BASE_URI . BASE_SCRIPT . $url;
				}
				
				$retinaUrl = 'images/resampled/'.($width*2).'/'.$this->value;
				if($absolute) {
					$retinaUrl = BASE_URI . BASE_SCRIPT . $retinaUrl;
				}
				
				return '<img src="'.$url.'" data-retina="'.$retinaUrl.'" style="width:'.$width.'px;'.$style.'" alt="'.$this->value.'" '.$html.' />';
			} else 
				return $this->makeImage($absolute, $html, $style);
		}
		
		/**
		 * sets the width of the image
		 *
		 *@name setWidth
		 *@access public
		*/
		public function setHeight($height, $absolute = false, $html = "", $style = "") {	
			if(_ereg("^[0-9]+$",$height)) {
				$url = 'images/resampled/x/'.$height.'/'.$this->value;
				if($absolute) {
					$url = BASE_URI . BASE_SCRIPT . $url;
				}
				
				$retinaUrl = 'images/resampled/x/'.($height*2).'/'.$this->value;
				if($absolute) {
					$retinaUrl = BASE_URI . BASE_SCRIPT . $retinaUrl;
				}
				
				return '<img src="'.$url.'" data-retina="'.$retinaUrl.'"  style="height:'.$height.'px;'.$style.'" alt="'.$this->value.'" '.$html.' />';
			} else 
				return $this->makeImage($absolute, $html, $style);
		}
		
		/**
		 * sets the width of the image
		 *
		 *@name setWidth
		 *@access public
		*/
		public function setSize($width, $height, $absolute = false, $html = "", $style = "") {
			if(_ereg("^[0-9]+$",$width) && _ereg("^[0-9]+$",$height))
				$url = 'images/resampled/'.$width.'/'.$height.'/'.$this->value;
				if($absolute)
					$url = BASE_URI . BASE_SCRIPT . $url;
				
				$retinaUrl = 'images/resampled/'.($width*2).'/'.($height*2).'/'.$this->value;
				if($absolute) {
					$retinaUrl = BASE_URI . BASE_SCRIPT . $retinaUrl;
				}
				
				return '<img src="'.$url.'" data-retina="'.$retinaUrl.'" style="width: '.$width.'px; height: '.$height.'px;'.$style.'" alt="'.$this->value.'" '.$html.' />';
			else 
					return $this->makeImage($absolute, $html, $style);
		}
		
		/**
		 * default convert
		*/
		public function forTemplate() {
			return $this->makeImage();
		}
}