<?php
defined("IN_GOMA") OR die();

/**
 * Render-Info for FileuploadSet-Field.
 *
 * @package Goma\Form
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class FileUploadSetRenderData extends FormFieldRenderData {
    /**
     * uploads object.
     *
     * @var DataObjectSet
     */
    protected $uploads = array();

    /**
     * default icon.
     *
     * @var string
     */
    protected $defaultIcon;

    /**
     * include link or not.
     * @var bool
     */
    protected $includeLink = true;

    /**
     * @return Uploads[]
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    /**
     * @param Uploads[] $uploads
     * @return $this
     */
    public function setUploads($uploads)
    {
        if(!is_a($uploads, DataObjectSet::ID)) {
            throw new InvalidArgumentException("Uploads must be an DataObjectSet of uploads.");
        }

        $this->uploads = $uploads;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultIcon()
    {
        return $this->defaultIcon;
    }

    /**
     * @param string $defaultIcon
     * @return $this
     */
    public function setDefaultIcon($defaultIcon)
    {
        $this->defaultIcon = $defaultIcon;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIncludeLink()
    {
        return $this->includeLink;
    }

    /**
     * @param bool $includeLink
     * @return $this
     */
    public function setIncludeLink($includeLink)
    {
        $this->includeLink = $includeLink;
        return $this;
    }

    /**
     * @param bool $includeRendered
     * @param bool $includeChildren
     * @return array
     */
    public function ToRestArray($includeRendered = false, $includeChildren = true)
    {
        $data = parent::ToRestArray($includeRendered, $includeChildren);

        $data["uploads"] = array();
        if(isset($this->uploads)) {
            foreach($this->uploads as $upload) {
                $data["uploads"][] = array(
                    "name"       => $upload->filename,
                    "realpath"   => $this->includeLink ? $upload->fieldGet("path") : null,
                    "icon16"     => $upload->getIcon(16),
                    "icon16_2x"  => $upload->getIcon(16, true),
                    "path"       => $this->includeLink ? $upload->path : null,
                    "id"         => $upload->id,
                    "icon128"    => $upload->getIcon(128),
                    "icon128_2x" => $upload->getIcon(128, true),
                    "icon"       => $upload->getIcon(),
                    "canDelete"  => is_a($this->uploads, RemoveStagingDataObjectSet::ID) || $this->uploads->getStaging()->itemExists($upload)
                );
            }
        }

        $data["defaultIcon"] = $this->defaultIcon;

        return $data;
    }
}
