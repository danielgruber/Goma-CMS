<?php
defined("IN_GOMA") OR die();


/**
 * Wrapper used to render form-fields.
 *
 * @package Goma\Form
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version 1.0
 */
class FormFieldResponse {
    /**
     * string.
     *
     * @var string
     */
    protected $name;

    /**
     * id.
     *
     * @var string
     */
    protected $id;

    /**
     * div-id.
     *
     * @var string
     */
    protected $divId;

    /**
     * max-length.
     *
     * @var int
     */
    protected $maxLength;

    /**
     * regexp.
     *
     * @var string
     */
    protected $regexp;

    /**
     * rendered-field.
     *
     * @var HTMLNode
     */
    protected $renderedField;

    /**
     * is hidden.
     *
     * @var bool
     */
    protected $isHidden;

    /**
     * extra-data.
     * @var array
     */
    protected $extra;

    /**
     * @var string
     */
    protected $type;

    /**
     * js
     *
     * @var string
     */
    protected $js;

    /**
     * constructor.
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $divId
     * @param int $maxLength
     * @param string $regex
     * @param null|string $js
     * @param null|HTMLNode $renderedField
     * @param bool $isHidden
     * @param array $extra
     */
    public function __construct($name, $type, $id, $divId, $maxLength, $regex, $js = null, $renderedField = null, $isHidden = false, $extra = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->id = $id;
        $this->divId = $divId;
        $this->maxLength = $maxLength;
        $this->regexp = $regex;
        $this->renderedField = $renderedField;
        $this->isHidden = $isHidden;
        $this->extra = $extra;
        $this->js = $js;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDivId()
    {
        return $this->divId;
    }

    /**
     * @param string $divId
     */
    public function setDivId($divId)
    {
        $this->divId = $divId;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @return string
     */
    public function getRegexp()
    {
        return $this->regexp;
    }

    /**
     * @param string $regexp
     */
    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
    }

    /**
     * @return HTMLNode
     */
    public function getRenderedField()
    {
        return $this->renderedField;
    }

    /**
     * @param HTMLNode $renderedField
     */
    public function setRenderedField($renderedField)
    {
        $this->renderedField = $renderedField;
    }

    /**
     * @return boolean
     */
    public function isIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * @param boolean $isHidden
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @param string $js
     */
    public function setJs($js)
    {
        $this->js = $js;
    }

    /**
     * to rest array.
     * @param bool $includeRendered
     * @return array
     */
    public function ToRestArray($includeRendered = false) {
        $data = array(
            "name" => $this->name,
            "id" => $this->id,
            "divId" => $this->divId,
            "maxLength" => $this->maxLength,
            "regex" => $this->regexp,
            "isHidden" => $this->isHidden,
            "extra" => $this->extra,
            "hasRenderData" => $this->renderedField != null,
            "js" => $this->js
        );

        if($includeRendered) {
            $data["field"] = $this->renderedField != null ? $this->renderedField->__toString() : "";
        }

        return $data;
    }
}
