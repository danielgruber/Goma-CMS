<?php
/**
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 17.01.2013
 * $Version 1.2.2
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TableView extends AdminItem
{

    /**
     * delete-session-key.
     */
    const DELETE_SESSION_KEY = "tv_deletekey";

    /**
     * entries per page
     */
    public $perPage = 20;

    /**
     * this is the template for tableview
     */
    public $template = "admin/tableview.html";

    /**
     * actions
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
     * @return string
     */
    public function index()
    {
        $globalactions = array();
        $actions = array();
        $fields = array();
        $search = false;

        if (isset($this->request->post_params["delete_many"])) {
            $this->deleteMany();
        }

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
            $arr = array("name" => $name, "title" => parse_lang($title), "sortable" => false);

            $table_name = ClassInfo::$class_info[$this->modelInst()->dataClass]["table"];
            if (isset($this->fields[$name]) && isset(ClassInfo::$database[$table_name][$name])) {
                $search = true;
                $arr["sortable"] = true;
                $arr["searchable"] = true;
                if (isset($this->request->get_params["order"]) && $this->request->get_params["order"] == $name &&
                    isset($this->request->get_params["ordertype"]) && $this->request->get_params["ordertype"] == "desc") {
                    $this->ModelInst()->sort($name, "desc");
                    $arr["order"] = true;
                    $arr["orderdesc"] = true;
                } else if (isset($this->request->get_params["order"]) && $this->request->get_params["order"] == $name) {
                    $this->ModelInst()->sort($name, "asc");
                    $arr["order"] = true;
                }

                if (isset($this->request->post_params["search_" . $name]) &&
                    $this->request->post_params["search_" . $name] != "" &&
                    !isset($this->request->post_params["search_" . $name . "_cancel"])) {
                    $this->modelInst()->addFilter(array($name => array("LIKE", "%" . $this->request->post_params["search_" . $name] . "%")));
                    $arr["searchval"] = $this->request->post_params["search_" . $name];
                }
            }

            array_push($fields, $arr);
        }

        if ($this->search === false)
            $search = $this->search;

        GlobalSessionManager::globalSession()->set(self::DELETE_SESSION_KEY . "." . $this->classname, randomString(10));

        $data = $this->modelInst()->customise(
                array_merge(
                    array(
                        "search" => $search,
                        "perPage" => $this->perPage,
                        "datafields" => $fields,
                        "action" => $actions,
                        "globalaction" => $globalactions,
                        "deletekey" => GlobalSessionManager::globalSession()->get(self::DELETE_SESSION_KEY . "." . $this->classname),
                        "deletable" => isset($this->actions["delete"])
                    ),
                    $this->tplVars
                )
            );

        return $data->renderWith($this->template);
    }

    /**
     * checks if the user is allowed to call this action
     *
     * @param string $action name of action
     * @return bool
     */
    public function checkPermission($action)
    {

        $this->actions = ArrayLib::map_key("strtolower", $this->actions);

        if (isset($this->actions[$action])) {
            return true;
        }

        return parent::checkPermission($action);
    }

    /**
     * deletes some of the data
     */
    public function deleteMany()
    {
        if (GlobalSessionManager::globalSession()->get(self::DELETE_SESSION_KEY . "." . $this->classname) == $this->request->post_params["deletekey"]) {
            $data = $this->request->post_params["data"];
            unset($data["all"]);
            foreach ($data as $key => $value) {
                if ($record = DataObject::get_one($this->model(), array("id" => $key)))
                    $record->remove();
            }
            return $this->redirectBack();
        }
    }

    /**
     * adds content-class table-view to content-div
     *
     * @return string
     */
    public function contentClass()
    {
        return parent::contentclass() . " table-view";
    }
}