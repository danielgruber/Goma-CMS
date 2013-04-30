<?php
/**
  * this class is for an image in uploaded-directory
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 30.04.2013
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Image extends GD
{
		/**
		 * this is an md5-hash of the picture
		 *@name md5
		*/
		public $md5;
		/**
		 * the orgignial pic
		 *@name org_pic
		 *@var string
		*/
		public $org_pic;
		/**
		 * constructs the class
		 *@name __construct
		 *@param string - filename relative to the uploaded-directory
		 *@access public
		*/
		public function __construct($image)
		{
		
				parent::__construct();
				$this->org_pic = $image;
				if(substr($image, 0, strlen("Uploads")) == "Uploads") {
					if($data = DataObject::Get_one("Uploads", array("path" => $image))) {
						$this->md5 = md5_file($data->realfile);
						parent::__construct($data->realfile);
					} else {
						return false;
					}
				} else {
					$this->md5 = md5_file(ROOT . CURRENT_PROJECT . "/uploaded/" . $image);
					parent::__construct(ROOT . CURRENT_PROJECT . "/uploaded/" . $image);
				}
				
		}
		/**
		 * this function resizes an image to another size and let the relation height-width normal
		 * this function caches the result, too
		 *@name resize
		 *@access public
		 *@param numeric - new width
		 *@param numeric - height
		*/
		public function resize($width, $height, $crop = true)
		{
				
				$file = ROOT . CACHE_DIRECTORY . '/image_cache.tn_w_'.$width.'_h_'.$height.'_'.$crop.'_'.$this->md5.'.'.$this->extension.'';
				if(file_exists($file))
				{
						return new GD($file);
				}
				$gd = parent::resize($width, $height, $crop);
				$gd->toFile($file);
				return $gd;
		}
		/**
		 * rotates an image
		 * this function implements caching
		 *@name rotate
		 *@access public
		 *@param numeric - angle
		*/
		public function rotate($angle) {
				$file = ROOT . CACHE_DIRECTORY . '/image_cache.rotate_'.$angle.'_'.$this->md5.'.'.$this->extension.'';
				if(file_exists($file))
				{
						return new GD($file);
				}
				$gd = parent::rotate($angle);
				$gd->toFile($file);
				return $gd;
		}
		/**
		 * we bring resizing to the next level
		 *
		 * V2 RESIZING
		*/
		
		/**
		 * sets the size with given thumbareas
		 *
		 *@name createThumb
		 *@access public
		*/
		public function createThumb($width = null, $height = null, $cornerLeft, $cornerTop, $thumbWidth, $thumbHeight, $forceSize = false) {
			$file = ROOT . CACHE_DIRECTORY . "/thumb.{$width}_{$height}_{$cornerLeft}_{$cornerTop}_{$thumbWidth}_{$thumbHeight}_{$forceSize}_{$this->md5}.{$this->extensions}";
			if(file_exists($file))
			{
					return new GD($file);
			}
			$gd = parent::createThumb($width, $height, $cornerLeft, $cornerTop, $thumbWidth, $thumbHeight, $forceSize);
			$gd->toFile($file);
			return $gd;
		}
}