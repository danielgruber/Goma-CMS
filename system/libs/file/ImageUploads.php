<?php defined('IN_GOMA') OR die();

/**
  *	@package 	goma framework
  *	@link 		http://goma-cms.org
  *	@license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.5
  *
  * last modified: 26.01.2015
*/
class ImageUploads extends Uploads {
	/**
	 * add some db-fields
	 * inherits fields from Uploads
	 *
	 *@name db
	 *@access public
	*/
	static $db = array(
		"width"				=> "int(5)",
		"height"			=> "int(5)",
		"thumbLeft"			=> "int(3)",
		"thumbTop"			=> "int(3)",
		"thumbWidth"		=> "int(3)",
		"thumbHeight"		=> "int(3)"
	);
	
	/**
	 * extensions in this files are by default handled by this class
	 *
	 *@name file_extensions
	 *@access public
	*/
	static $file_extensions = array(
		"png",
		"jpeg",
		"jpg",
		"gif",
		"bmp"
	);
	
	/**
	 * some defaults
	*/
	static $default = array(
		"thumbLeft"		=> 0,
		"thumbTop"		=> 0,
		"thumbWidth"	=> 100,
		"thumbHeight"	=> 100
	);
	
	/**
	 * returns the raw-path
	 *
	 *@name raw
	 *@access public
	*/
	public function raw() {
		return $this->path;
	}
	
	/**
	 * to string
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString() {
		if(preg_match("/\.(jpg|jpeg|png|gif|bmp)$/i", $this->filename)) {
			$file = $this->raw().'/index'.substr($this->filename, strrpos($this->filename, "."));

			
			if(substr($file, 0, strlen("index.php/")) != "index.php/") {
				if(!file_exists($file)) {
					FileSystem::requireDir(dirname($file));
					FileSystem::write($file . ".permit", 1);
				}
			} else {
				if(file_exists(substr($file, strlen("index.php/")))) {
					$file = substr($file, strlen("index.php/"));
				} else {
					FileSystem::requireDir(substr(dirname($file), strlen("index.php/")));
					FileSystem::write(substr(dirname($file), strlen("index.php/")) . ".permit", 1);
				}
			}
			
			
			
			return '<img src="'.$file.'" height="'.$this->height.'" width="'.$this->width.'" alt="'.$this->filename.'" />';
		} else
			return '<a href="'.$this->raw().'">' . $this->filename . '</a>';
	}
	
	/**
	 * returns the path to the icon of the file
	 *
	 *@name getIcon
	 *@access public
	 *@param int - size; support for 16, 32, 64 and 128
	*/
	public function getIcon($size = 128, $retina = false) {
		$ext = substr($this->filename, strrpos($this->filename, "."));
		switch($size) {
			case 16:
				if($this->width > 15) {
					if($retina && $this->width > 31) {
						$icon = $this->path . "/setWidth/32" . $ext;
					} else {
						$icon = $this->path . "/setWidth/16" . $ext;
					}
				} else {
					if($retina) {
						return "images/icons/goma16/image@2x.png";
					}
					return "images/icons/goma16/image.png";
				}
			break;
			case 32:
				if($this->width > 31) {	
					if($retina && $this->width > 63) {
						$icon = $this->path . "/setWidth/64" . $ext;
					} else {
						$icon = $this->path . "/setWidth/32" . $ext;
					}
				} else {
					if($retina) {
						return "images/icons/goma32/image@2x.png";
					}
					return "images/icons/goma32/image.png";
				}
			break;
			case 64:
				if($this->width > 63) {
					if($retina && $this->width > 127) {
						$icon = $this->path . "/setWidth/128" . $ext;
					} else {
						$icon = $this->path . "/setWidth/64" . $ext;
					}
				} else {
					if($retina) {
						return "images/icons/goma64/image@2x.png";
					}
					return "images/icons/goma64/image.png";
				}
			break;
			case 128:
				if($this->width > 127) {
					if($retina && $this->width > 255) {
						$icon = $this->path . "/setWidth/256" . $ext;
					} else {
						$icon = $this->path . "/setWidth/128" . $ext;
					}
				} else {
					return "images/icons/goma/128x128/image.png";
				}
			break;
		}
		
		if(isset($icon)) {
			$this->manageURL($icon);
			return $icon;
		}
		
		return "images/icons/goma/128x128/image.png";
	}
	
	/**
	 * authenticates a specific url and removes cache-files if necessary
	 *
	 *@name manageURL
	*/
	public function manageURL($file) {
		$file = $this->removePrefix($file, "index.php/");
		$file = $this->removePrefix($file, "./index.php/");

		FileSystem::requireDir(dirname($file));
		FileSystem::write($file . ".permit", 1);
		if(file_exists($file) && filemtime($file) < NOW - Uploads::$cache_life_time) {
			@unlink($file);
		}
		return $file;
	}

	/**
	 * remove prefixes from a path.
	*/
	protected function removePrefix($file, $prefix) {
		if(substr($file, 0, strlen($prefix)) == $prefix) {
			return substr($file, strlen($prefix));
		}

		return $file;
	}
	
	public function checkForBase($file) {
		$fileWithoutBase = substr($file, strlen("index.php/"));
		if(file_exists($fileWithoutBase)) {
			$file = $fileWithoutBase;
		}
		
		return $file;
	}
	
	/**
	 * sets the height
	 *
	 *@name setHeight
	 *@access public
	*/
	public function setHeight($height, $absolute = false, $html = "", $style = "") {
	    if(!$this->path)
	        return "";
	        
	       
		// normal URL Cache
		$file = $this->path . "/setHeight/" . $height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/setHeight/" . ($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;

			$fileRetina = BASE_URI . $fileRetina;

		}
		
		return '<img src="' . $file . '" height="'.$height.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the width
	 *
	 *@name setWidth
	 *@access public
	*/
	public function setWidth($width, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path . "/setWidth/" . $width . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/setWidth/" . ($width * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;
		}

				
		return '<img src="' . $file . '" width="'.$width.'" data-retina="' . $fileRetina . '" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the Size
	 *
	 *@name setSize
	 *@access public
	*/
	public function setSize($width, $height, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path .'/setSize/'.$width.'/'.$height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path .'/setSize/'.($width * 2).'/'.($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;
		}
		
		
		return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 *@name orgSetSize
	 *@access public
	*/
	public function orgSetSize($width, $height, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path .'/orgSetSize/'.$width.'/'.$height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);		
		
		// retina
		$fileRetina = $this->path .'/orgSetSize/'.($width * 2).'/'.($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;
		}
		
		
		
		
		return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 *@name orgSetWidth
	 *@access public
	*/
	public function orgSetWidth($width, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path . "/orgSetWidth/" . $width . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/orgSetWidth/" . ($width * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" width="'.$width.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 *@name orgSetHeight
	 *@access public
	*/
	public function orgSetHeight($height, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path . "/orgSetHeight/" . $height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/orgSetHeight/" . ($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;

		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" height="'.$height.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 *@name noCropSetSize
	 *@access public
	*/
	public function noCropSetSize($width, $height, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path .'/noCropSetSize/'.$width.'/'.$height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path .'/noCropSetSize/'.($width * 2).'/'.($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;

			
			$fileRetina = BASE_URI . $fileRetina;
		}
		
		return '<img src="' . $file .'" height="'.$height.'" width="'.$width.'" data-retina="' . $fileRetina .'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 *@name noCropSetWidth
	 *@access public
	*/
	public function noCropSetWidth($width, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path . "/noCropSetWidth/" . $width . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/noCropSetWidth/" . ($width * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" width="'.$width.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 *@name noCropSetHeight
	 *@access public
	*/
	public function noCropSetHeight($height, $absolute = false, $html = "", $style = "") {
	     if(!$this->path)
	        return "";
	        
		// normal URL Cache
		$file = $this->path . "/noCropSetHeight/" . $height . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($file);
		
		// retina
		$fileRetina = $this->path . "/noCropSetHeight/" . ($height * 2) . substr($this->filename, strrpos($this->filename, "."));
		$this->manageURL($fileRetina);
		
		$file = $this->checkForBase($file);
		$fileRetina = $this->checkForBase($fileRetina);
		
		if($absolute) {
			$file = BASE_URI . $file;
			
			$fileRetina = BASE_URI . $fileRetina;
		}
		
		return '<img src="' . $file . '" data-retina="' . $fileRetina . '" height="'.$height.'" alt="'.$this->filename.'" style="'.$style.'" '.$html.' />';
	}
	
	/**
	 * returns width
	 *
	 *@name width
	 *@access public
	*/
	public function width() {
		if(preg_match('/^[0-9]+$/', $this->fieldGET("width")) && $this->fieldGET("width") != 0) {
			return $this->fieldGet("width");
		}
		
		$image = new RootImage($this->realfile);
		$this->setField("width", $image->width);
		$this->write(false, true);
		return $image->width;
	}
	
	/**
	 * returns height
	 *
	 *@name height
	 *@access public
	*/
	public function height() {
		if(preg_match('/^[0-9]+$/', $this->fieldGET("height"))  && $this->fieldGET("height") != 0) {
			return $this->fieldGet("height");
		}
		
		$image = new RootImage($this->realfile);
		$this->setField("height", $image->height);
		$this->write(false, true);
		return $image->height;
	}

}