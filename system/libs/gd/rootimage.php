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

class ROOTImage extends GD
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
		public function __construct($image) {
				$this->org_pic = $image;
				if(substr($image, 0, 7) == "Uploads") {
					if($data = DataObject::Get_one("Uploads", array("path" => $image))) {
						$this->md5 = md5_file($data->realfile);
						parent::__construct($data->realfile);
					} else {
						return false;
					}
				} else 
				if(file_exists(ROOT . $image))
				{				
						$this->md5 = md5_file(ROOT . $image);
						parent::__construct(ROOT . $image);
				} else {
					parent::__construct();
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
		public function rotate($angle)
		{
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
			$file = ROOT . CACHE_DIRECTORY . "/thumb.{$width}_{$height}_{$cornerLeft}_{$cornerTop}_{$thumbWidth}_{$thumbHeight}_{$forceSize}_{$this->md5}.{$this->extension}";
			if(file_exists($file))
			{
					return new GD($file);
			}
			$gd = parent::createThumb($width, $height, $cornerLeft, $cornerTop, $thumbWidth, $thumbHeight, $forceSize);
			$gd->toFile($file);
			return $gd;
		}
		
		/**
		 * generates a url
		 *@name generate_url
		 *@access public
		 *@param numeric - width - optional
		 *@param numeric - height - optional
		 *@return string
		*/
		public function generate_url($width = false, $height = false)
		{
				if($width === false)
				{
						$relation = $this->width / $this->height;
						$height = $height;
						$width = round($height * $relation);
						
				} else if($height === false)
				{
						$relation = $this->height / $this->width;
						$width = $width;
						$height = round($width * $relation);
				}
				$file = CACHE_DIRECTORY . '/image_cache.tn_w_'.$width.'_h_'.$height.'_'.$this->md5.'.'.$this->extension.'';
				if(file_exists(ROOT . $file))
				{
						return ROOT_PATH . $file;
				} else
				{
						return BASE_SCRIPT . "images/resampled/".round($width)."/".round($height)."/".$this->org_pic."";
				}				
		}
}