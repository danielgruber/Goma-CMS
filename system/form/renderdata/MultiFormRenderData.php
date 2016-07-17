<?php defined("IN_GOMA") OR die();


/**
 * Render-Data for MultiFormField render data.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
 */
class MultiFormRenderData extends FormFieldRenderData {
    /**
     * sortable.
     *
     * @var bool
     */
    protected $sortable = false;

    /**
     * @var bool
     */
    protected $deletable = false;

    /**
     * @var string[]
     */
    protected $addAble = array();

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * @param boolean $sortable
     * @return $this
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * @param boolean $deletable
     * @return $this
     */
    public function setDeletable($deletable)
    {
        $this->deletable = $deletable;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAddAble()
    {
        return $this->addAble;
    }

    /**
     * @param string[] $addAble
     * @return $this
     */
    public function setAddAble($addAble)
    {
        if(!is_array($addAble)) {
            throw new InvalidArgumentException("setAddable requires first parameter to be array. " . gettype($addAble) . " given.");
        }

        $addAble = array_map("strtolower", $addAble);
        $this->addAble = ArrayLib::key_value($addAble);
        return $this;
    }

    /**
     * @param string $addable
     * @return $this
     */
    public function addAddable($addable) {
        $this->addAble[strtolower($addable)] = $addable;
        return $this;
    }

    /**
     * @param string $addable
     * @return $this
     */
    public function removeAddable($addable) {
        unset($this->addAble[strtolower($addable)]);
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

        $data["deletable"] = $this->deletable;
        $data["sortable"] = $this->sortable;

        $addable = array();
        foreach($this->addAble as $addClass) {
            $addable[] = array(
                "class" => $addClass,
                "title" => ClassInfo::getClassTitle($addClass),
                "icon"  => ClassInfo::getClassIcon($addClass)
            );
        }

        $data["addable"] = $addable;

        return $data;
    }
}
