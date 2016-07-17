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
class FormFieldRenderData extends gObject implements IRestResponse {
    /**
     * name.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string.
     */
    protected $title;

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
    protected $extra = array();

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
     * children.
     */
    protected $children = array();

    /**
     * is disabled.
     */
    protected $isDisabled = false;

    /**
     * reference to field.
     */
    protected $field;

    /**
     * @var boolean
     */
    protected $hasError;

    /**
     * resources.
     */
    protected $renderResources = array(
        "js"    => array(),
        "css"   => array()
    );

    /**
     * @var string
     */
    protected $postname;

    /**
     * constructor.
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $divId
     */
    public function __construct($name, $type, $id, $divId)
    {
        parent::__construct();

        $this->name = $name;
        $this->type = $type;
        $this->id = $id;
        $this->divId = $divId;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $divId
     * @return FormFieldRenderData
     */
    public static function create($name, $type, $id, $divId) {
        return new static($name, $type, $id, $divId);
    }

    /**
     * adds a child.
     *
     * @param FormFieldRenderData $child
     * @return $this
     */
    public function addChild($child) {
        if(!is_a($child, "FormFieldRenderData")) throw new InvalidArgumentException("Child must be FormFieldResponse.");

        $this->children[] = $child;

        return $this;
    }

    /**
     * @param $child
     * @return $this
     */
    public function removeChild($child) {
        $key = array_search($child, $this->children);
        unset($this->children[$key]);
        $this->children = array_values($this->children);

        return $this;
    }

    /**
     * returns children.
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @return FormField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param AbstractFormComponent $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return $this
     */
    public function setDivId($divId)
    {
        $this->divId = $divId;
        return $this;
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
     * @return $this
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
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
     * @return $this
     */
    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
        return $this;
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
     * @return $this
     */
    public function setRenderedField($renderedField)
    {
        $this->renderedField = $renderedField;
        return $this;
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
     * @return $this
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;
        return $this;
    }

    /**
     * @return mixed
     * @param string $key
     */
    public function getExtra($key)
    {
        return isset($this->extra[$key]) ? $this->extra[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $extra
     * @return $this
     */
    public function setExtra($key, $extra)
    {
        $this->extra[$key] = $extra;
        return $this;
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
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
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
     * @return $this
     */
    public function setJs($js)
    {
        $this->js = $js;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @param mixed $isDisabled
     * @return $this
     */
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHasError()
    {
        return $this->hasError;
    }

    /**
     * @param boolean $hasError
     * @return $this
     */
    public function setHasError($hasError)
    {
        $this->hasError = $hasError;
        return $this;
    }

    /**
     * @param string $js
     */
    public function addJSFile($js) {
        $this->renderResources["js"][$js] = $js;
    }

    /**
     * @param string $css
     */
    public function addCSSFile($css) {
        $this->renderResources["css"][$css] = $css;
    }

    /**
     * @return array
     */
    public function getRenderResources()
    {
        return $this->renderResources;
    }

    /**
     * @return string
     */
    public function getPostName()
    {
        return $this->postname;
    }

    /**
     * @param string $postname
     * @return $this
     */
    public function setPostName($postname)
    {
        $this->postname = $postname;
        return $this;
    }

    /**
     * to rest array.
     * @param bool $includeRendered
     * @param bool $includeChildren
     * @return array
     */
    public function ToRestArray($includeRendered = false, $includeChildren = true) {
        $data = array(
            "class" => $this->classname,
            "name" => $this->name,
            "title" => $this->title,
            "id" => $this->id,
            "divId" => $this->divId,
            "maxLength" => $this->maxLength,
            "regex" => $this->regexp,
            "isHidden" => $this->isHidden,
            "extra" => $this->extra,
            "hasRenderData" => $this->renderedField != null,
            "disabled"  => $this->isDisabled,
            "cssRenderResources" => $this->renderResources["css"],
            "jsRenderResources" => $this->renderResources["js"],
            "postname"  => $this->postname
        );

        if($this->js) {
            $data["js"] = $this->js;
        }

        if($this->hasError) {
            $data["hasError"] = $this->hasError;
        }

        if($includeRendered) {
            $data["field"] = $this->renderedField != null ? $this->renderedField->__toString() : "";
        }

        if(!empty($this->children) && $includeChildren) {
            $data["children"] = array();

            /** @var FormFieldRenderData $child */
            foreach($this->children as $child) {
                $data["children"][] = $child->ToRestArray();
            }
        }

        $this->callExtending("exportRESTData");

        return $data;
    }
}
