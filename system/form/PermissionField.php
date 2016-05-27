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
     * generates the field
     *
     * @param FormFieldRenderData|null $info
     * @return HTMLNode
     */
    public function field($info = null)
    {
        if (is_object($this->inheritFrom) && is_a($this->inheritFrom, "Permission") && $this->inheritFrom->id != $this->value->id) {
            $title = isset($this->inheritTitle) ? ' (' . $this->inheritTitle . ')' : null;
            $this->type->addOption("inherit", lang("inherit_from_parent") . $title, true);
            if (is_object($this->inheritFrom) && $this->inheritFrom->id == $this->value->parentid) {
                $this->type->value = "inherit";
            }
        }

        if ($this->disabled) {
            $this->fields["password"]->value = "*****";
        }

        return parent::field($info);
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
     * result
     */
    public function result()
    {
        if ($this->fields["type"]->result() == "inherit" && $this->inheritFrom && is_a($this->inheritFrom, "Permission")) {
            $val = clone $this->inheritFrom;
            $val->consolidate();
            $val->parentid = $this->inheritFrom->id;
            $val->id = $this->value->id;
            $val->versionid = $this->value->versionid;
            $val->name = $this->value->name;
            $this->value = $val;
        } else {
            $this->value->parentid = 0;
            $this->value->parent = null;
            // now remodify it by the given fields
            foreach ($this->fields as $val) {
                if ($result = $val->result()) {
                    $this->value->{$val->dbname} = $result;
                }
            }
        }

        return $this->value;
    }
}