<?php
defined("IN_GOMA") OR die();

/**
 * Render-Info for one Tab.
 *
 * @package Goma\Form
 *
 * @author 	Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class TabRenderData extends FormFieldRenderData
{
    /**
     * @var bool
     */
    protected $tabActive = false;

    /**
     * @var string submitName
     */
    protected $submitName;

    /**
     * @return boolean
     */
    public function isTabActive()
    {
        return $this->tabActive;
    }

    /**
     * @param boolean $tabActive
     * @return $this
     */
    public function setTabActive($tabActive)
    {
        $this->tabActive = $tabActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubmitName()
    {
        return $this->submitName;
    }

    /**
     * @param string $submitName
     */
    public function setSubmitName($submitName)
    {
        $this->submitName = $submitName;
    }

    /**
     * @param bool $includeRendered
     * @param bool $includeChildren
     * @return array
     */
    public function ToRestArray($includeRendered = false, $includeChildren = true)
    {
        $data = parent::ToRestArray($includeRendered, $includeChildren);

        $data["tabActive"] = $this->tabActive;
        $data["submitName"] = $this->submitName;

        return $data;
    }
}
