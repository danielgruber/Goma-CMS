<?php defined("IN_GOMA") OR die();


/**
 * This is a fieldset which is used as a tabsetb, which holds several tabs.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5
 */
class TabSet extends FieldSet
{
    /**
     * @name __construct
     * @access public
     * @param string - name
     * @param array - fields
     * @param null|object - form
     */
    public function __construct($name, $fields, &$form = null)
    {
        parent::__construct($name, $fields, null, $form);

        $this->container->setTag("div");
        $this->container->addClass("tabs");
    }

    /**
     * renders the field
     * @name field
     * @access public
     * @return HTMLNode
     */
    public function field()
    {
        if (PROFILE) Profiler::mark("FieldSet::field");

        $this->callExtending("beforeField");

        $this->container->addClass("hidden");

        $this->callExtending("afterField");

        if (PROFILE) Profiler::unmark("FieldSet::field");

        return $this->container;
    }

    /**
     * @param FormFieldResponse $info
     */
    public function addRenderData($info)
    {
        foreach($info->getChildren() as $child) {
            /** @var FormFieldResponse $child */
            if($child->getField()->hidden()) {
                $info->removeChild($child);
            }
        }

        parent::addRenderData($info);

        $this->renderTabList($info);
    }

    /**
     * render tab list.
     *
     * @param FormFieldResponse $info
     */
    protected function renderTabList($info) {
        $list = new HTMLNode("ul", array());

        $activeTabFound = false;
        $children = $info->getChildren();
        if(count($children) == 0) {
            return;
        }

        for($i = 0; $i < count($children); $i++) {
            /** @var FormFieldResponse $child */
            $child = $children[$i];

            $listItem = new HTMLNode('li', array(), new HTMLNode('input', array(
                'type' => "submit",
                'name' => "tabs_" . $child->getName(),
                "value" => $child->getTitle(),
                "class" => "tab",
                "id" => $child->getDivId() . "_tab"
            )));

            if ((isset($_POST["tabs_" . $child->getName()])) && !$activeTabFound) {
                $activeTabFound = true;
                $child->getRenderedField()->addClass("active");
                setcookie("tabs_" . $this->name, $child->getName(), 0, "/");

                $listItem->getNode(0)->addClass("active");
            }
            $list->append($listItem);
        }

        if (!$activeTabFound) {
            // check session
            if (isset($_COOKIE["tabs_" . $this->name])) {
                foreach ($list->content as $item) {
                    if ($item->getNode(0)->name == "tabs_" . $_COOKIE["tabs_" . $this->name]) {
                        $item->getNode(0)->addClass("active");
                        $children[0]->getRenderedField()->addClass("active");
                        $activeTabFound = true;
                        break;
                    }
                }
            }

            if (!$activeTabFound) {
                // make first tab active
                $list->getNode(0)->getNode(0)->addClass("active");
                $children[0]->getRenderedField()->addClass("active");
            }
        }

        $info->getRenderedField()->prepend($list);
    }

    /**
     * generates js
     * @name JS
     * @access public
     * @return string
     */
    public function JS()
    {
        Resources::add("tabs.css");
        gloader::load("gtabs");
        return '$(function(){ $("#' . $this->divID() . '").gtabs({"animation": true, "cookiename": "tabs_' . $this->name . '"}); });';
    }
}