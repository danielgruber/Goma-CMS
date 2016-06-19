<?php defined('IN_GOMA') OR die();

/**
 * @package 	goma framework
 * @link 		http://goma-cms.org
 * @license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 	1.6
 *
 * @method ImageUploads modelInst()
 *
 * last modified: 06.08.2015
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

		if(!file_exists(ROOT . self::calculatePermitFile(URL)) && !file_exists(ROOT . URL . ".permit")) {
			return false;
		}

		$this->checkForSourceResize();

		return true;
	}

	public static function calculatePermitFile($file) {
		return substr($file, 0, strrpos($file, "/") + 1) . "." . substr($file, strrpos($file, "/") + 1) . ".permit";
	}

	/**
	 * check if filename matches.
	 * @param $filename
	 * @return bool
	 */
	public function checkFilename($filename) {
		return preg_match('/\.('.implode("|", ImageUploads::$file_extensions).')$/i', $filename);
	}

	/**
	 * sends the image to the browser
	 *
	 * @name index
	 * @access public
	 * @return false
	 */
	public function index() {
		if($this->getParam("height") || $this->getParam("width")) {
			return false;
		}

		if(self::checkFilename($this->modelInst()->filename)) {
			$cacheDir = substr(ROOT . URL,0,strrpos(ROOT . URL, "/"));

			// generate
			$image = new RootImage($this->modelInst()->realfile);
			$image->filename = $this->modelInst()->filename;

			// write to cache
			$filenameTwice = strtolower($image->filename . "/" . $image->filename);
			if(strtolower(substr(URL, 0 - strlen($filenameTwice))) == $filenameTwice) {
				FileSystem::requireDir($cacheDir);
				$image->toFile(ROOT . URL);
			}

			// output
			$image->output();

			exit;
		}

		return false;
	}

	/**
	 * resizeImageAndOutput
	 *
	 * @param    int $width
	 * @param    int $height
	 * @param    int $thumbLeft
	 * @param    int $thumbTop
	 * @param    int $thumbWidth
	 * @param    int $thumbHeight
	 * @param 	bool $realized
	 * @param    boolean $output or return image
	 * @return GD
	 * @internal param $resizeImage
	 */
	public function resizeImage($width, $height, $thumbLeft = 50, $thumbTop = 50, $thumbWidth = 100, $thumbHeight = 100, $realized = false, $output = true) {
		$cacheDir = substr(ROOT . URL,0,strrpos(ROOT . URL, "/"));

		// create
		$image = new RootImage($this->modelInst()->realfile);


		if(!isset($width)) {
			$width = $height / $image->height * $image->width;
		} else if(!isset($height)) {
			$height = $width / $image->width * $image->height;
		}

		if($realized) {
			$thumbLeft = $thumbTop = 50;
			$thumbWidth = $thumbHeight = 100;
		}

		// resize
		$img = $image->resize($width, $height, true, new Position($thumbLeft, $thumbTop), new Size($thumbWidth, $thumbHeight));
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
	 * checks if image should be resized to have a different source version.
	 */
	private function checkForSourceResize() {
		$model = $this->modelInst();
		if($model->sourceImage && ($model->thumbLeft != 50 || $model->thumbTop != 50 || $model->thumbWidth != 100 || $model->thumbHeight != 100) && !$model->realizedSize && $model->id != 0) {

			$width = $model->sourceImage->width * $model->thumbWidth / 100;
			$height = $model->sourceImage->height * $model->thumbHeight / 100;

			$image = new RootImage($this->modelInst()->sourceImage->realfile);

			$img = $image->resize($width, $height, true, new Position($model->thumbLeft, $model->thumbTop), new Size($model->thumbWidth, $model->thumbHeight));

			$extension = substr($model->filename, strrpos($model->filename, "."));
			$newRealFile = $model->realfile . "_" . $model->versionid . $extension;
			$img->toFile($newRealFile);

			$model->realizedSize = true;
			$model->width = $width;
			$model->height = $height;
			$model->realfile = $newRealFile;
			$model->md5 = md5_file($newRealFile);
			$model->writeToDB(false, true);
		}
	}

	/**
	 * sets the width
	 */
	public function setWidth() {
		$width = (int) $this->getParam("width");

		$this->resizeImage($width, null, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight, $this->modelInst()->realizedSize);

		exit;
	}

	/**
	 * sets the height
	 */
	public function setHeight() {
		$height = (int) $this->getParam("height");

		$this->resizeImage(null, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight, $this->modelInst()->realizedSize);

		exit;
	}

	/**
	 * sets the size
	 */
	public function setSize() {
		$height = (int) $this->getParam("height");
		$width = (int) $this->getParam("width");

		$this->resizeImage($width, $height, $this->modelInst()->thumbLeft, $this->modelInst()->thumbTop, $this->modelInst()->thumbWidth, $this->modelInst()->thumbHeight, $this->modelInst()->realizedSize);

		exit;
	}

	/**
	 * sets the size on the original
	 */
	public function noCropSetSize() {

		$height = (int) $this->getParam("height");
		$width = (int) $this->getParam("width");

		// create image
		$image = new RootImage($this->modelInst()->realfile);
		$img = $image->resize($width, $height, false);

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
	 */
	public function noCropSetWidth() {

		$width = (int) $this->getParam("width");

		$this->resizeImage($width, null);

		exit;
	}

	/**
	 * sets the height on the original
	 */
	public function noCropSetHeight() {

		$height = (int) $this->getParam("height");
		$this->resizeImage(null, $height);

		exit;
	}
}
