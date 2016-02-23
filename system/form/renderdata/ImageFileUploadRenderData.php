<?php
defined("IN_GOMA") OR die();

/**
 * Render-Info for ImageUpload-Field.
 *
 * @package Goma\Form
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 *
 * @property ImageUploads $upload
 */
class ImageFileUploadRenderData extends FileUploadRenderData {
    /**
     * @param null|ImageUploads $upload
     * @return $this
     */
    public function setUpload($upload)
    {
        if(is_a($upload, "ImageUploads") || $upload == null) {
            return parent::setUpload($upload);
        } else {
            throw new InvalidArgumentException("\$upload must be typeof ImageUploads.");
        }
    }

    /**
     * @param bool $includeRendered
     * @param bool $includeChildren
     * @return array
     */
    public function ToRestArray($includeRendered = false, $includeChildren = true) {
        $data = parent::ToRestArray($includeRendered, $includeChildren);

        if($this->upload) {
            $data["upload"]["thumbLeft"] = $this->upload->thumbLeft;
            $data["upload"]["thumbTop"] = $this->upload->thumbTop;
            $data["upload"]["thumbWidth"] = $this->upload->thumbWidth;
            $data["upload"]["thumbHeight"] = $this->upload->thumbHeight;

            if ($this->upload->sourceImage) {
                $data["upload"]["sourceImage"] = $this->upload->sourceImage->path;
                $data["upload"]["sourceImageRP"] = $this->upload->sourceImage->fieldGet("path");
            }
        }

        return $data;
    }
}
