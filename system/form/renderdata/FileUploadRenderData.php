<?php
defined("IN_GOMA") OR die();

/**
 * Render-Info for Fileupload-Field.
 *
 * @package Goma\Form
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class FileUploadRenderData extends FormFieldRenderData {
    /**
     * uploads object.
     *
     * @var Uploads|null
     */
    protected $upload;

    /**
     * @return null|Uploads
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * @param null|Uploads $upload
     */
    public function setUpload($upload)
    {
        $this->upload = $upload;
    }

    /**
     * @param bool $includeRendered
     * @param bool $includeChildren
     * @return array
     */
    public function ToRestArray($includeRendered = false, $includeChildren = true)
    {
        $data = parent::ToRestArray($includeRendered, $includeChildren);

        if(isset($this->upload)) {
            $data["upload"] = array(
                "name" => $this->upload->filename,
                "realpath" => $this->upload->fieldGet("path"),
                "icon16" => $this->upload->getIcon(16),
                "icon16_2x" => $this->upload->getIcon(16, true),
                "path" => $this->upload->path,
                "id" => $this->upload->id,
                "icon128" => $this->upload->getIcon(128),
                "icon128_2x" => $this->upload->getIcon(128, true),
                "icon" => $this->upload->getIcon()
            );
        }

        return $data;
    }
}