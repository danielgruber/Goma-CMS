<?php defined("IN_GOMA") OR die();


/**
 * This is a fieldset which is used as a tab in tabset.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.2
 */
class Tab extends FieldSet
{
    /**
     * defines whether to render tab without fields.
     *
     * @var boolean
     */
    public $renderTabWithoutContent = false;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $fields fields in this set
     * @param string $title
     * @param Form $form
     */
    public function __construct($name = null, $fields = array(), $title = null, &$form = null)
    {
        parent::__construct($name, $fields, $title, $form);

        $this->container->setTag("div");
    }

    /**
     * generates the DOM.
     *
     * @access public
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = parent::createNode();
        $node->setTag("h2");
        $node->html(strtoupper(substr($this->title, 0, 1)) . substr($this->title, 1));
        return $node;
    }

    /**
     * @return TabRenderData
     */
    protected function createsRenderDataClass() {
        return TabRenderData::create($this->name, $this->classname, $this->ID(), $this->divID());
    }

    /**
     * @param TabRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        parent::addRenderData($info, $notifyField);

        if($info->isTabActive()) {
            $info->getRenderedField()->addClass("active");
        }
    }

    /**
     * returns if this tab is hidden.
     *
     * @access public
     * @return boolean
     */
    public function hidden()
    {
        return (count($this->fieldList) == count($this->renderedFields)) || $this->renderTabWithoutContent;
    }
}
