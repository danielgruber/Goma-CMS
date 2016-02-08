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
     * manual set name of active tab.
     *
     * @var string|null
     */
    public $activeTab = null;

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
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        foreach($info->getChildren() as $child) {
            /** @var FormFieldResponse $child */
            if($child->getField()->hidden()) {
                $info->removeChild($child);
            }
        }

        parent::addRenderData($info, false);

        $this->renderTabList($info);

        if($notifyField) {
            $this->callExtending("afterRenderFormResponse", $info);
        }
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

            if ((isset($this->form()->post["tabs_" . $child->getName()])) && !$activeTabFound) {
                $activeTabFound = true;
                $child->getRenderedField()->addClass("active");
                setcookie("tabs_" . $this->name, $child->getName(), 0, "/");

                $children[$i]->getRenderedField()->addClass("active");
                $listItem->getNode(0)->addClass("active");
            }
            $list->append($listItem);
        }

        if (!$activeTabFound) {
            // check session
            $active = isset($this->activeTab) ? $this->activeTab : (isset($_COOKIE["tabs_" . $this->name]) ? $_COOKIE["tabs_" . $this->name] : null);
            if (isset($active)) {
                $i = 0;
                foreach ($list->content as $item) {
                    if ($item->getNode(0)->name == "tabs_" . $active) {
                        $item->getNode(0)->addClass("active");
                        $children[$i]->getRenderedField()->addClass("active");
                        $activeTabFound = true;
                        break;
                    }

                    $i++;
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
