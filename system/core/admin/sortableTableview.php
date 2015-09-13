<?php
/**
 * @package goma
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 11.02.2013
 * $Version 1.0
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class SortableTableView extends TableView
{
    public $pages = true;
    public $perPage = 20;

    /**
     * this is the template for tableview
     */
    public $template = "admin/sortableTableview.html";

    /**
     * actions
     *
     * @name actions
     * @access public
     */
    public $actions = array(
        "edit"   => '<img src="images/icons/fatcow-icons//16x16/edit.png" alt="{$_lang_edit}" title="{$_lang_edit}" />',
        "delete" => '<img src="images/icons/fatcow-icons/16x16/delete.png" alt="{$_lang_delete}" title="{$_lang_delete}" />',
        "add"    => array("{\$_lang_add_data}")
    );

    /**
     * fields
     */
    public $fields = array();

    /**
     * url-handlers
     *
     * @name url_handlers
     * @access public
     */
    public $url_handlers = array(
        "deletemany" => "deletemany",
        "saveSort"   => "saveSort"
    );

    /**
     * sort-field
     *
     * @name sort_field
     * @access public
     */
    public $sort_field = "";

    /**
     * allowed actions
     *
     * @name allowed_actions
     * @access public
     */
    public $allowed_actions = array(
        "deletemany",
        "saveSort"
    );

    /**
     * defines if search is enabled
     * you need at least one field with table-relation
     *
     * @name search
     * @access public
     */
    public $search = true;

    /**
     * this action will be called if no other action was found
     *
     * @name index
     * @access public
     * @return string
     */
    public function index()
    {
        $globalactions = array();
        $actions = array();
        $fields = array();

        foreach ($this->actions as $name => $data) {
            if (is_array($data)) {
                $globalactions[] = array(
                    "url"   => $this->url() . $name,
                    "title" => parse_lang($data[0])
                );
            } else {
                array_push($actions, array(
                    "url"   => $this->url() . $name,
                    "title" => parse_lang($data)
                ));
            }
        }

        foreach ($this->fields as $name => $title) {
            array_push($fields, array("name" => $name, "title" => parse_lang($title)));
        }

        $this->modelInst()->sort($this->sort_field, "ASC");

        GlobalSessionManager::globalSession()->set("deletekey." . $this->classname, randomString(10));

        Resources::addData("var adminURI = " . var_export($this->namespace, true) . ";");

        return $this->model_inst->customise(
            array_merge(
                array(
                    "datafields" => $fields,
                    "action" => $actions,
                    "globalaction" => $globalactions,
                    "deletekey" => GlobalSessionManager::globalSession()->get("deletekey." . $this->classname),
                    "deletable" => isset($this->actions["delete"])
                ),
                $this->tplVars
            ))->renderWith($this->template);
    }

    /**
     * saves the sort
     *
     * @name saveSort
     * @access public
     */
    public function saveSort()
    {
        if (isset($_POST["sort_item"])) {
            $field = $this->sort_field;
            foreach ($_POST["sort_item"] as $key => $value) {
                $key += isset($_GET["pa"]) ? ($_GET["pa"] - 1) * $this->perPage : 0;
                DataObject::update($this->models[0], array($field => $key), array("recordid" => $value));
            }
        }
        HTTPResponse::output(1);
        exit;
    }

    /**
     * adds content-class table-view to content-div
     *
     * @name contentClass
     * @access public
     * @return string
     */
    public function contentClass()
    {
        return parent::contentclass() . " table-view";
    }
}