<?php defined('IN_GOMA') OR die();

/**
  *	@package 	goma framework
  *	@link 		http://goma-cms.org
  *	@license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.6
  *
  * last modified: 26.01.2015
*/
class ImageUploadsController extends UploadsController {
	/**
	 * handlers
	 *
	 *@name handlers
	 *@access public
	*/
	public $url_handlers = array(
		"setWidth/\$width" 					=> "setWidth",
		"setHeight/\$height"				=> "setHeight",
		"setSize/\$width/\$height"			=> "setSize",
		"orgSetWidth/\$width" 				=> "orgSetWidth",
		"orgSetHeight/\$height"				=> "orgSetHeight",
		"orgSetSize/\$width/\$height"		=> "orgSetSize",
		"noCropSetWidth/\$width" 			=> "noCropSetWidth",
		"noCropSetHeight/\$height"			=> "noCropSetHeight",
		"noCropSetSize/\$width/\$height"	=> "noCropSetSize"
	);
	
	/**
	 * allowed actions
	*/
	
	public $allowed_actions = array(
		"setWidth" 			=> "->checkImagePerms",
		"setHeight"			=> "->checkImagePerms",
		"setSize"			=> "->checkImagePerms",
		"orgSetSize"		=> "->checkImagePerms",
		"orgSetWidth"		=> "->checkImagePerms",
		"orgSetHeight"		=> "->checkImagePerms",
		"noCropSetSize"		=> "->checkImagePerms",
		"noCropSetWidth"	=> "->checkImagePerms",
		"nocropSetHeight"	=> "->checkImagePerms",
	);

	/**
	 * checks if filename ends with correct extension and if there is a permit-file.
	*/
	public function checkImagePerms() {
		if(!self::checkFilename($this->modelInst()->filename)) {
			return false;
		}

		if(!file_exists(ROOT . URL . ".permit")) {
			return false;
		}

		return true;
	}

	/**
	 * check if filename matches.
	*/
	public function checkFilename($filename) {
		return preg_match('/\.('.implode("|", ImageUploads::$file_extensions).')$/i', $filename);
	}

	/**
	 * sends the image to the browser
	 *
	 *@name index
	 *@access public
	*/
	public function index() {
		if(self::checkFilename($this->modelInst()->filename)) {
			$cacheDir = substr(ROOT . URL,0,strrpos(ROOT . URL, "/"));
			
			// generate
			$image = new RootImage($this->modelInst()->realfile);
			
			// write to cache
			if(preg_match('/index\.(jpg|jpeg|png|bmp|gif)$/', URL)) {
				FileSystem::requireDir($cacheDir);
				$image->toFile(ROOT . URL);
			}
			
			// output
			$image->output();
		}
		
		exit;
	}

	/**
	 * resizeImageAndOutput
	 *
	 * @name 	resizeImage
	 * @param 	width
	 * @param 	height
	 * @param 	thumbLeft
	 * @param 	thumbTop
	 * @param 	thumbWidth
	 * @param 	thumbHeight
	 * @param 	boolean output or return image
	 * @return 	GD
	*/
	public function resizeImage($width, $height, $thumbLeft, $thumbTop, $thumbWidth, $thumbHeight, $output = true) {
		$cacheDir = substr(ROOT . URL,0,strrpos(ROOT . URL, "/"));
		
		// create
		$image = new RootImage($this->modelInst()->realfile);
		
		// resize
		$img = $image->createThumb($width, $height, $thumbLeft, $thumbTop, $thumbWidth, $thumbHeight);
		try {
			// write to cache
			FileSystem::requireDir($cacheDir);
			$img->toFile(ROOT . URL);
		} catch(Exception $e) {
			log_exception($e);
		}
		
		// output
		if($output) {
			$img->Output();
		}

		return $img;
	}
	
	/**
	 * sets the width
	 *
	 *@name setWidth
	 *@access public
	*/
	public function setWidth() {

		$width = (int) $this->getParam("width");
		
		$this->resizeImage($width, null, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight);
	
		exit;
	}
	
	/**
	 * sets the height
	 *
	 *@name setHeight
	 *@access public
	*/
	public function setHeight() {

		$height = (int) $this->getParam("height");

		$this->resizeImage(null, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight);
		
		exit;
	}
	
	/**
	 * sets the size
	 *
	 *@name setSize
	 *@access public
	*/
	public function setSize() {

		$height = (int) $this->getParam("height");
		$width = (int) $this->getParam("width");
		
		$this->resizeImage($width, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight);
		
		exit;
	}
	
	/**
	 * sets the size on the original 
	 *
	 *@name orgSetSize
	 *@åccess public
	*/
	public function orgSetSize() {

		$height = (int) $this->getParam("height");
		$width = (int) $this->getParam("width");
		
		$this->resizeImage($width, $height, 0, 0, 100, 100);
		
		exit;
	}
	
	/**
	 * sets the width on the original 
	 *
	 *@name orgSetWidth
	 *@åccess public
	*/
	public function orgSetWidth() {

		$width = (int) $this->getParam("width");
		
		$this->resizeImage($width, null, 0, 0, 100, 100);
		
		exit;
	}
	
	/**
	 * sets the height on the original
	 *
	 *@name orgSetHeight
	 *@access public
	*/
	public function orgSetHeight() {	
		$height = (int) $this->getParam("height");
		
		$this->resizeImage(null, $height, 0, 0, 100, 100);
		
		exit;
	}
	
	/**
	 * sets the size on the original 
	 *
	 *@name noCropSetSize
	 *@åccess public
	*/
	public function noCropSetSize() {

		$height = (int) $this->getParam("height");
		$width = (int) $this->getParam("width");
		
		// create image
		$image = new RootImage($this->modelInst()->realfile);
		$img = $image->resize($width, $height, true);
		
		// write to cache
		try {
			FileSystem::requireDir(substr(ROOT . URL,0,strrpos(ROOT . URL, "/")));
			$img->toFile(ROOT . URL);
		} catch(Exception $e) {
			log_exception($e);
		}
		
		// output
		$img->Output();
		
		exit;
	}
	
	/**
	 * sets the width on the original 
	 *
	 *@name orgSetWidth
	 *@åccess public
	*/
	public function noCropSetWidth() {

		$width = (int) $this->getParam("width");
		
		$this->resizeImage($width, null, 0, 0, 100, 100);
		
		exit;
	}
	
	/**
	 * sets the height on the original
	 *
	 *@name noCropSetHeight
	 *@access public
	*/
	public function noCropSetHeight() {	

		$height = (int) $this->getParam("height");
		$this->resizeImage(null, $height, 0, 0, 100, 100);
		
		exit;
	}
}
