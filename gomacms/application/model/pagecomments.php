<?php defined("IN_GOMA") OR die();
/**
 * @package goma cms
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 17.01.2013
 * $Version 1.1.6
 */

loadlang('comments');

class PageComments extends DataObject {

    static $db = array('name' => 'varchar(200)',
                       'text' => 'text');

    /**
     * has-one-relation to page
     */
    static $has_one = array('page' => 'pages'); // has one page

    /**
     * sort
     */
    static $default_sort = "created DESC";

    /**
     * indexes for faster look-ups
     */
    static $index = array("name" => true);

    /**
     * rights
     */
    public $writeField = "autorid";

    static $search_fields = array(
        "name", "text"
    );

    /**
     * insert is always okay
     */
    public function canInsert($row = null)
    {
        return true;
    }

    /**
     * generates the form
     *
     * @name getForm
     * @access public
     */
    public function getForm(&$form)
    {
        if (member::$nickname) {
            $form->add(new HiddenField("name", member::$nickname));
        } else {
            $form->add(new TextField("name", lang("name", "Name")));
        }

        $form->add(new BBCodeEditor("text", lang("text", "text"), null, null, null, array("showAlign" => false)));
        if (!member::login())
            $form->add(new Captcha("captcha"));
        $form->addValidator(new RequiredFields(array("text", "name", "captcha")), "fields");
        $form->addAction(new AjaxSubmitButton("save", lang("co_add_comment", "add comment"), "ajaxsave", "safe", array("green")));
    }

    /**
     * edit-form
     *
     * @name getEditForm
     * @access public
     */
    public function getEditForm(&$form)
    {
        $form->add(new HTMLField("heading", "<h3>" . lang("co_edit", "edit comments") . "</h3>"));
        $form->add(new BBCodeEditor("text", lang("text", "text")));

        $form->addAction(new CancelButton("cancel", lang("cancel", "cancel")));
        $form->addAction(new FormAction("save", lang("save", "save"), null, array("green")));
    }

    public function timestamp()
    {
        return $this->created();
    }


    /**
     * returns the representation of this record
     *
     * @param bool $link
     * @return string
     */
    public function generateRepresentation($link = false)
    {
        return lang("CO_COMMENT") . " " . lang("CO_OF") . ' ' . convert::raw2text($this->name) . ' ' . lang("CO_ON") . ' ' . $this->created()->date() . '';
    }
}


/**
 * extends the page
 */
class PageCommentsDataObjectExtension extends DataObjectExtension {
    /**
     * make relation
     */
    static $has_many = array(
        "comments" => "pagecomments"
    );
    /**
     * make field for enable/disable
     */
    static $db = array(
        "showcomments" => "int(1)"
    );

    static $default = array(
        "showcomments" => 0
    );

    /**
     * make extra fields to form
     */
    public function getForm(&$form)
    {
        $form->meta->add(new Checkbox("showcomments", lang("co_comments")));
    }

    /**
     * append content to sites if needed
     * @param HTMLNode $object
     */
    public function appendContent(&$object)
    {
        if ($this->getOwner()->showcomments) {
            /** @var HasMany_DataObjectSet $comments */
            $comments = $this->getOwner()->comments();

            /** @var GomaFormResponse $form */
            $form = gObject::instance("PageCommentsController")->setModelInst($comments)->form("add");
            if(!$form->isStringResponse()) {
                Director::serve($form);
                exit;
            }

            $object->append($comments->customise(array(
                "page"  => $this->getOwner(),
                "form" => $form
            ))->renderWith("comments/comments.html"));
        }
    }
}

gObject::extend("pages", "PageCommentsDataObjectExtension");
