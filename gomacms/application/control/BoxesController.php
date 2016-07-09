<?php
defined("IN_GOMA") OR die();

/**
 * Boxes-Controller.
 *
 * @package Goma CMS
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *
 * @version 1.0
 */
class BoxesController extends FrontedController {
    /**
     * some urls
     */
    public $url_handlers = array(
        "\$pid!/add"               => "add",
        "\$pid!/edit/\$id!"        => "edit",
        "\$pid!/delete/\$id"       => "delete",
        "\$pid!/saveBoxWidth/\$id" => "saveBoxWidth",
        "\$pid!/saveBoxOrder"      => "saveBoxOrder"
    );

    /**
     * rights
     *
     * @name allowed_actions
     * @access public
     */
    public $allowed_actions = array(
        "add"          => "->canEdit",
        "edit"         => "->canEdit",
        "delete"       => "->canEdit",
        "saveBoxWidth" => "->canEdit",
        "saveBoxOrder" => "->canEdit"
    );

    /**
     * returns if edit is on
     */
    public function canEdit()
    {
        $data = DataObject::get_by_id("pages", $this->getParam("pid"));
        if ($data && $data->can("Write")) {
            return true;
        }

        return Permission::check("PAGES_WRITE");
    }

    /**
     * renders boxes
     *
     * @param string $id
     * @param $count
     */
    public static function renderBoxes($id, $count = null)
    {
        $data = DataObject::get("boxes", array("seiteid" => $id));

        return gObject::instance("boxesController")->setModelInst($data)->render($id, $count);
    }

    /**
     * edit-functionallity
     */
    public function edit()
    {
        Core::setTitle(lang("edit"));

        return parent::Edit();
    }

    /**
     * add-functionality
     */
    public function add()
    {
        $boxes = new Boxes(array(
            "seiteid" => $this->getParam("pid")
        ));

        return $this->form("add", $boxes);
    }

    /**
     * saves box width
     */
    public function saveBoxWidth()
    {
        if (isset($this->request->post_params["width"])) {
            $data = DataObject::get_by_id("boxes", $this->getParam("id"));
            if ($data) {
                $data->width = $this->request->post_params["width"];
                $data->writeToDB();

                return new JSONResponseBody("ok");
            }
        }
    }

    /**
     * saves box orders
     *
     * @return JSONResponseBody
     */
    public function saveBoxOrder()
    {
        if (isset($this->request->post_params["box_new"])) {
            foreach ($this->request->post_params["box_new"] as $sort => $id) {
                if ($data = DataObject::get_by_id("boxes", $id)) {
                    $data->sort = $sort;
                    $data->writeToDB();
                }
            }

            return new JSONResponseBody("ok");
        }
    }

    /**
     * renders boxes
     * @param string $pid
     * @param int|null $count
     * @return string
     * @throws Exception
     */
    final public function render($pid = null, $count = 0)
    {
        if (isset($pid)) {
            $data = DataObject::get("boxes", array("seiteid" => $pid));
            $this->model_inst = $data;
        }

        $this->callExtending("beforeRenderBoxes", $pid);

        $canWrite = $data->first() ? $data->first()->can("write") : gObject::instance("boxes")->can("write");

        $cacher = new Cacher("boxes2_" . $pid . "_" . Core::adminAsUser() . "_" . member::$id . "_" . $this->modelInst()->maxCount("last_modified"));
        if ($cacher->checkValid()) {
            return $this->modelInst()->customise(array(
                "pageid" => $pid,
                "boxlimit" => (int)$count,
                "cache" => $cacher->getData(),
                "canWrite" => $canWrite
            ))->renderWith("boxes/boxes.html");
        } else {
            $output = $this->modelInst()->customise(array(
                "pageid" => $pid,
                "boxlimit" => (int)$count,
                "canWrite" => $canWrite
            ))->renderWith("boxes/boxes.html");

            if ($this->checkCachable()) {
                $cacher->write($output, 86400);
            }

            return $output;
        }
    }

    /**
     * checks if the current set of boxes is cachable.
     */
    public function checkCachable()
    {
        /** @var Box $record */
        foreach ($this->modelInst() as $record) {
            if (!$record->isCacheable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * hides the deleted object
     * @param AjaxResponse $response
     * @param array $data
     * @return AjaxResponse
     */
    public function hideDeletedObject($response, $data)
    {
        $response->exec('$("#box_new_' . $data["id"] . '").hide(300, function(){
			$(this).remove();
			if($("#boxes_new_' . $data["seiteid"] . '").find(" > .box_new").length == 0) {
				$("#boxes_new_' . $data["seiteid"] . '").html("' . convert::raw2js(BoxesController::RenderBoxes($data["seiteid"])) . '");
			}
		});');

        return $response;
    }

    /**
     * index
     */
    public function index()
    {
        return '<div class="error">' . lang("less_rights") . '</div>';
    }

    /**
     * saves via ajax
     *
     * @name ajaxSave
     * @access public
     */
    public function ajaxSave($data, $response)
    {
        if ($this->save($data, 2) !== false) {
            Notification::notify("boxes", lang("box_successful_saved", "The data was successfully written!"), lang("saved"));
            //$response->exec(new Dialog(lang("successful_saved", "The data was successfully written!"), lang("okay"), 3));
            $response->exec('$("#boxes_new_' . convert::raw2js($data["seiteid"]) . '").html("' . convert::raw2js(BoxesController::renderBoxes($data["seiteid"])) . '");');
            $response->exec('dropdownDialog.get(ajax_button.parents(".dropdownDialog").attr("id")).hide();');

            return $response;
        } else {

            $response->exec(new Dialog(lang("mysql_error"), lang("error"), 5));

            return $response;
        }
    }

    public function isCacheable()
    {
        return false;
    }
}