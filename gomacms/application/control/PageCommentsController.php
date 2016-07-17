<?php
defined("IN_GOMA") OR die();

/**
 * Controls Page-Comments-Stuff.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */

class PageCommentsController extends FrontedController {
    public $allowed_actions = array("edit", "delete");

    public $template = "comments/comments.html";

    /**
     * ajax-save
     * @param array $data
     * @param AjaxResponse $response
     * @param Form $form
     * @return AjaxResponse
     */
    public function ajaxsave($data, $response, $form)
    {
        $model = $this->save($data);
        $response->prepend(".comments", $model->renderWith("comments/onecomment.html"));
        $response->exec('$(".comments").find(".comment:first").css("display", "none").slideDown("fast");');
        $response->exec("$('#" . $form->fields["text"]->id() . "').val(''); $('#" . $form->fields["text"]->id() . "').change();");

        return $response;
    }


    /**
     * hides the deleted object
     *
     * @name hideDeletedObject
     * @access public
     * @return AjaxResponse
     */
    public function hideDeletedObject($response, $data)
    {
        $response->exec("$('#comment_" . $data["id"] . "').slideUp('fast', function() { \$('#comment_" . $data["id"] . "').remove();});");

        return $response;
    }
}

/**
 * extends the controller
 *
 * @method contentController getOwner()
 */
class PageCommentsControllerExtension extends ControllerExtension {
    /**
     * make the method work
     */
    public static $extra_methods = array(
        "pagecomments"
    );
    public $allowed_actions = array(
        "pagecomments"
    );

    public function pagecomments()
    {
        if ($this->getOwner()->modelInst()->showcomments)
            return ControllerResolver::instanceForModel($this->getOwner()->modelInst()->comments())->handleRequest($this->getOwner()->request);

        return "";
    }
}
gObject::extend("contentController", "PageCommentsControllerExtension");
