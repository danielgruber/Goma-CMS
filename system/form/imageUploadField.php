<?php defined("IN_GOMA") OR die();

/**
 * a simple Upload form-field which supports Images with Ajax-Upload + cropping.
 * it will give back an ImageUploads-Class with parameters correctly filled out.
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.2
 */
class ImageUploadField extends FileUpload
{
	/**
	 * all allowed file-extensions
	 *@name allowed_file_types
	 *@access public
	*/
	public $allowed_file_types = array(
		"jpg",
		"png",
		"bmp",
		"gif",
		"jpeg"
	);
	/**
	 * upload-class
	*/
	protected $uploadClass = "ImageUploads";

	/**
	 * @param Uploads $response
	 * @return Uploads
	 */
	protected function getFileResponse($response)
	{
		$data = parent::getFileResponse($response);

		/** @var ImageUploads $response */
		if(is_a($response, "ImageUploads")) {
			$data["thumbLeft"] = $response->thumbLeft;
			$data["thumbTop"] = $response->thumbTop;
			$data["thumbWidth"] = $response->thumbWidth;
			$data["thumbHeight"] = $response->thumbHeight;
		}

		return $response;
	}
}
