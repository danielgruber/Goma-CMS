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
	 * @var array
	 */
	public $allowed_actions = array(
		"setCropInfo"
	);

	/**
	 * upload-class
	 */
	protected $uploadClass = "ImageUploads";

	/**
	 * @var string
	 */
	protected $widgetTemplate = "form/ImageUploadWidget.html";

	/**
	 * @param FormFieldRenderData $info
	 * @param bool $notifyField
	 */
	public function addRenderData($info, $notifyField = true)
	{
		parent::addRenderData($info, $notifyField);

		$info->addJSFile("system/libs/thirdparty/jcrop/jquery.Jcrop.js");
		$info->addJSFile("system/form/imageUpload.js");
		$info->addCSSFile("system/libs/thirdparty/jcrop/jquery.Jcrop.css");

		$info->getRenderedField()->append(
			$this->templateView->renderWith($this->widgetTemplate)
		);
	}

	public function js()
	{
		return parent::js() . '
			$(function(){
				new ImageUploadController(field, '.var_export($this->externalURL() . "/setCropInfo" . URLEND, true).')
			});
		';
	}

	/**
	 * sets crop-info.
	 */
	public function setCropInfo() {
		if(!$this->request->isPOST()) {
			throw new BadRequestException("You need to use POST.");
		}

		if(!is_a($this->value, "ImageUploads")) {
			throw new InvalidArgumentException("Value is not type of ImageUpload.");
		}

		$crop = true;
		foreach(array("thumbHeight", "thumbWidth", "thumbLeft", "thumbTop") as $key) {
			if(!RegexpUtil::isDouble($this->getParam($key))) {
				$crop = false;
			}
		}

		/** @var ImageUploads $image */
		$image = $this->value;

		if($this->getParam("useSource") && $this->getParam("useSource") != "false") {
			if(!$image->sourceImage) {
				throw new InvalidArgumentException("Source Image not defined.");
			}

			$image = $image->sourceImage;
		}

		if($this->getParam("thumbWidth") == 0 || $this->getParam("thumbHeight") == 0 || !$crop) {
			$upload = $image;
		} else {
			$upload = $image->addImageVersionBySizeInPx($this->getParam("thumbLeft"), $this->getParam("thumbTop"), $this->getParam("thumbWidth"), $this->getParam("thumbHeight"));
		}

		$this->value = $upload;

		return new JSONResponseBody(array(
			"status" => 1,
			"file" => $this->getFileResponse($upload)
		));
	}

	/**
	 * @param Exception $e
	 * @return string
	 * @throws Exception
	 */
	public function handleException($e) {
		if(strtolower($this->request->getParam("action")) == "setcropinfo") {
			if(method_exists($e, "http_status")) {
				HTTPResponse::setResHeader($e->http_status());
			} else {
				HTTPResponse::setResHeader(500);
			}

			return json_encode(array(
				"class" => get_class($e),
				"errstring" => $e->getMessage(),
				"code" => $e->getCode()
			));
		}

		return parent::handleException($e);
	}

	/**
	 * @return FormFieldRenderData
	 */
	protected function createsRenderDataClass()
	{
		return ImageFileUploadRenderData::create($this->name, $this->classname, $this->ID(), $this->divID());
	}
}
