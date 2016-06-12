<?php
defined("IN_GOMA") OR die();

/**
 * Permission-Field.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0.5
 */
class PermissionField extends ClusterFormField {

    /**
     * defines if it is from a relation
     *
     * @var bool
     */
    protected $byRelation;

    /**
     * inherit-from
     * @var Permission
     */
    public $inheritFrom;

    /**
     * title of inherit
     */
    public $inheritTitle;

    /**
     * construction
     * @param string|null $name
     * @param string|null $title
     * @param string|null $value
     * @param bool $password
     * @param array $disabled
     * @param string|null $inheritFrom
     * @param string|null $inheritTitle
     * @param Form|null $parent
     */
    public function __construct($name = null, $title = null, $value = null, $password = false, $disabled = array(), $inheritFrom = null, $inheritTitle = null, $parent = null)
    {
        parent::__construct($name, null, array(), $value, $parent);

        if (is_string($inheritFrom)) {
            Permission::check($inheritFrom);
            $inheritFrom = DataObject::get_one("Permission", array("name" => $inheritFrom));
        }
        $this->inheritFrom = $inheritFrom;
        $this->inheritTitle = $inheritTitle;

        $this->add($radio = new ObjectRadioButton("type", $title, array(
            "all"      => lang("everybody", "everybody"),
            "users"    => lang("login_groups", "Everybody, who can login"),
            "admins"   => lang("admins", "admins"),
            "password" => array(
                lang("password", "password"),
                "password"
            ),
            "groups"   => array(
                lang("following_groups", "Following Groups"),
                "_groups"
            )
        )));
        $this->add(new TextField("password", lang("password")));
        if (!$password)
            $disabled[] = "password";

        $this->add(new FieldSet("_groups", array(
            new Checkbox("invert_groups", lang("invert_groups", "invert groups")),
            new ManyManyDropdown("groups", lang("groups"), "name")
        )));

        foreach ($disabled as $disabled_node) {
            $radio->disableOption($disabled_node);
        }
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        if (is_object($this->inheritFrom) && is_a($this->inheritFrom, "Permission") && $this->inheritFrom->id != $this->getModel()->id) {
            $title = isset($this->inheritTitle) ? ' (' . $this->inheritTitle . ')' : null;
            $this->type->addOption("inherit", lang("inherit_from_parent") . $title, true);
            if (is_object($this->inheritFrom) && $this->inheritFrom->id == $this->getModel()->parentid) {
                $this->getField("type")->setModel("inherit");
            }
        }

        parent::addRenderData($info, $notifyField);
    }

    /**
     * sets inherit
     * @param string $from
     * @param string|null $title
     */
    public function setInherit($from, $title = null)
    {
        $this->inheritFrom = $from;
        $this->inheritTitle = $title;
    }

    /**
     * @return Permission
     */
    public function getModel()
    {
        $model = parent::getModel();

        if(!isset($model)) {
            $model = new Permission();
        }

        if(!is_a($model, "Permission")) {
            throw new InvalidArgumentException("Model for Permissionfield must be Permission or null.");
        }

        return $model;
    }

    /**
     * @param Permission $model
     * @return mixed
     */
    protected function setInheritOnModel($model) {
        if($model->type == "inherit" && $this->inheritFrom) {
            if($this->inheritFrom->id != $model->parentid) {
                /** @var Permission $val */
                $val = clone $this->inheritFrom;
                $val->consolidate();
                $val->parentid = $this->inheritFrom->id;
                $val->id = $model->id;
                $val->versionid = $model->versionid;
                $val->name = $model->name;
                return $val;
            } else {
                $model->type = $this->inheritFrom->type;
            }
        } else {
            $model->parentid = 0;
            $model->parent = null;
        }

        return $model;
    }

    /**
     * result
     */
    public function result()
    {
        $model = parent::result();

        return $this->setInheritOnModel($model);
    }
}
