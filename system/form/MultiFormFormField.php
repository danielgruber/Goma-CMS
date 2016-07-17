<?php
defined("IN_GOMA") OR die();

/**
 * Combines multiple forms to a combined field.
 * It requires a Set of DataObjects which have getForm or getEditForm.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class MultiFormFormField extends ClusterFormField {

    /**
     * @param
     */
    const SESSION_PREFIX = "MultiFormField";

    /**
     * @var bool
     */
    protected $useEditFormMethod = false;

    /**
     * @var bool|string
     */
    protected $allowAddOfKind = false;

    /**
     * @var string
     */
    protected $template = "form/MultiFormFormField.html";

    /**
     * @var string
     */
    protected $modelKeyField = "__MULTI__KEY__";

    /**
     * @var string
     */
    protected $secret;

    /**
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = parent::createNode();
        $node->type = "hidden";
        $node->val(1);

        return $node;
    }

    /**
     * @return array|string|ViewAccessableData
     */
    public function getModel() {
        if (!isset($this->hasNoValue) || !$this->hasNoValue) {
            if($this->POST) {
                if(!$this->secret && $this->parent && !$this->isDisabled()) {
                    $this->secret = randomString(10);
                    $this->add($hidden = new HiddenField("secret", $this->secret));

                    if($oldSecret = $this->parent->getFieldPost($hidden->PostName())) {
                        if($model = Core::globalSession()->get(self::SESSION_PREFIX . $this->PostName() . $oldSecret)) {
                            return $this->model = $model;
                        }
                    }
                }

                if ($this->model == null) {
                    return $this->parent ? $this->parent->getFieldValue($this->dbname) : null;
                }
            }
        }

        return $this->model;
    }

    /**
     * @return FormFieldRenderData
     */
    public function createsRenderDataClass()
    {
        return MultiFormRenderData::create($this->getName(), $this->classname, $this->ID(), $this->divID());
    }

    /**
     * saves to session.
     */
    protected function saveToSession() {
        Core::globalSession()->set(self::SESSION_PREFIX . $this->PostName() . $this->secret, $this->getModel());
    }

    /**
     *
     */
    protected function defineFields()
    {
        if(!is_a($this->getModel(), "DataSet") && !is_a($this->getModel(), "DataObjectSet")) {
            throw new InvalidArgumentException("Value for MultiFormFormField must be DataSet or DataObjectSet.");
        }

        foreach($this->getAddableClasses() as $class) {
            if($this->parent->getFieldPost($this->PostName() . "_add_" . $class)) {
                $this->getModel()->add(
                    $this->getModel()->createNew(array(
                        "classname" => $class
                    ))
                );
            }
        }

        /** @var DataObject $record */
        $i = 0;
        foreach($this->getModel() as $record) {
            $name = $record->versionid != 0 ? $this->name . "_" . $record->versionid :
                $this->name . "_a" . $this->getModel()->count();
            $record->{$this->modelKeyField} = $name;
            $field = new ClusterFormField(
                $name,
                "",
                array(
                    new HiddenField("shouldDelete", 0),
                    new HiddenField("sort", $i)
                ),
                $record
            );
            $field->container->addClass($record->classname);
            $field->setTemplate("form/MultiFormComponent.html");

            if($this->useEditFormMethod) {
                $record->getEditForm($field);
            } else {
                $record->getForm($field);
            }

            $this->add($field);
            $i++;
        }

        $this->saveToSession();
    }

    public function result()
    {
        $result = $this->getModel();
        if(!$result)
            throw new LogicException();

        foreach($this->getModel() as $record) {

        }
    }

    /**
     * @param null $fieldErrors
     * @return MultiFormRenderData
     */
    public function exportBasicInfo($fieldErrors = null)
    {
        /** @var MultiFormRenderData $data */
        $data = parent::exportBasicInfo($fieldErrors);

        return $data
            ->setSortable(is_a($this->getModel(), "ISortableDataObjectSet"))
            ->setDeletable(is_a($this->getModel(), "RemoveStagingDataObjectSet"))
            ->setAddAble($this->getAddableClasses());
    }

    /**
     * @return string[]
     */
    protected function getAddableClasses() {
        return $this->getAllowAddOfKindClass() ?
            array_merge(array($this->getAllowAddOfKindClass()), ClassInfo::getChildren($this->getAllowAddOfKindClass())) :
            array();
    }

    /**
     * @return null|string
     */
    protected function getAllowAddOfKindClass() {
        return $this->allowAddOfKind ? (ClassInfo::exists($this->allowAddOfKind) ? $this->allowAddOfKind : $this->getModel()->DataClass()) : null;
    }

    /**
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        return $this->container;
    }

    /**
     * @return boolean
     */
    public function isUseEditFormMethod()
    {
        return $this->useEditFormMethod;
    }

    /**
     * @param boolean $useEditFormMethod
     * @return $this
     */
    public function setUseEditFormMethod($useEditFormMethod)
    {
        $this->useEditFormMethod = $useEditFormMethod;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getAllowAddOfKind()
    {
        return $this->allowAddOfKind;
    }

    /**
     * @param bool|string $allowAddOfKind
     * @return $this
     */
    public function setAllowAddOfKind($allowAddOfKind)
    {
        $this->allowAddOfKind = $allowAddOfKind;
        return $this;
    }

    /**
     * @return string
     */
    public function getModelKeyField()
    {
        return $this->modelKeyField;
    }

    /**
     * @param string $modelKeyField
     * @return $this
     */
    public function setModelKeyField($modelKeyField)
    {
        if($modelKeyField) {
            $this->modelKeyField = $modelKeyField;
        }
        return $this;
    }
}
