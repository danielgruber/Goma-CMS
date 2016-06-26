<?php defined("IN_GOMA") OR die();

/**
 * Admin-Panel for @link pages.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0.10
 */
class contentAdmin extends LeftAndMain {
    /**
     * the class from which the tree should be rendered
     */
    public $tree_class = "pages";

    /**
     * the text in the admin-panel
     */
    public $text = '{$_lang_content}';

    /**
     * permissions you need to view the adminItem
     */
    public $rights = "ADMIN_CONTENT";

    /**
     * template of the admin-panel (default)
     */
    public $template = "admin/content_index.html";

    /**
     * models this admin-panel manages
     */
    public $models = array("pages");

    static $icon = "templates/images/content.png";

    public $sort = 990;

    /**
     * colors in the tree
     */
    public $colors = array(
        "withmainbar" => array(
            "color" => "#24ACB8",
            "name"  => "{\$_lang_mainbar}"
        ),
        "nomainbar"   => array(
            "color" => "#3f3f3f",
            "name"  => "{\$_lang_nomainbar}"
        )
    );

    /**
     * extend actions
     */
    public $allowed_actions = array(
        "revert_changes", "unpublish", "preview"
    );

    /**
     * sort in the tree
     *
     * @name sort_field
     */
    protected $sort_field = "sort";

    static $less_vars = "tint-blue.less";

    /**
     * gets the title of the root node
     *
     * @return string
     */
    protected function getRootNode()
    {
        return lang("pagetree");
    }

    /**
     * returns the URL for the View Website-Button
     *
     * @return string
     */
    public function PreviewURL()
    {
        return defined("PREVIEW_URL") ? PREVIEW_URL : BASE_URI;
    }


    /**
     * history-url
     *
     * @return string
     */
    public function historyURL()
    {
        return "admin/history/pages";
    }

    /**
     * redirect back
     */
    public function redirectback($param = null, $value = null)
    {
        if ($this->getParam(0) == "del" || $this->getParam(1, false) == "add") {
            return GomaResponse::redirect(ROOT_PATH . 'admin/content' . URLEND);
        } else {
            return parent::redirectback($param, $value);
        }
    }

    /**
     * init JavaScript-Files
     *
     * @param Request $request
     */
    public function Init($request = null)
    {
        Resources::add(APPLICATION . "/application/model/pages.js", "js", "tpl");

        return parent::Init($request);
    }

    /**
     * generates the options for the create-select-field
     *
     * @name CreateOptions
     * @access public
     * @return array
     */
    public function createOptions()
    {
        $data = array("page" => ClassInfo::getClassTitle("Page"));
        foreach (ClassInfo::getChildren("page") as $page) {
            if (ClassInfo::exists($page)) {
                if (!gObject::method_exists($page, "hidden") || call_user_func_array(array($page, "hidden"), array($page)) !== true) {
                    if ($title = ClassInfo::getClassTitle($page)) {
                        $data[$page] = convert::raw2text($title);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * restores the last published version
     *
     * @return AjaxResponse
     */
    public function revert_changes()
    {
        if($model = $this->getSingleModel()) {
            return $this->confirmByForm(
                lang("revert_changes_confirm", "Do you really want to revert changes and go back to the last published version?"),
                function() {
                    if($publishedData = DataObject::get_one($this->modelInst()->classname, array("id" => $this->model_inst->id))) {
                        $publishedData->writeToDB(false, true, 2, true);
                        if ($this->request->is_ajax()) {
                            $response = new AjaxResponse();
                            Notification::notify("pages", lang("revert_changes_success", "The last version was recovered successfully."), lang("reverted"));
                            $response->exec("reloadTree(function(){ LoadTreeItem('" . $publishedData->class_name . "_" . $publishedData->versionid . "'); });");

                            return $response;
                        } else {
                            addcontent::addSuccess(lang("revert_changes_success", "The last version was recovered successfully."));
                        }
                    }
                    return $this->redirectBack();
                }
            );
        }
    }

    /**
     * unpublishes the current version
     *
     * @return AjaxResponse
     */
    public function unpublish()
    {
        if($model = $this->getSingleModel()) {
            try {
                $model->unpublish();
                if (Core::is_ajax()) {
                    $response = new AjaxResponse();
                    Notification::notify("pages", lang("unpublish_success", "The site was successfully unpublished."), lang("unpublished"));
                    $response->exec("reloadTree(); $('.form_field_unpublish').remove();");

                    return $response;
                } else {
                    AddContent::addSuccess(lang("unpublish_success", "The site was successfully unpublished."));

                    return $this->redirectBack();
                }
            } catch(Exception $e) {
                if (Core::is_ajax()) {
                    $response = new AjaxResponse();
                    $response->exec('alert(' . $e->getMessage() . ');');

                    return $response;
                } else {
                    AddContent::addError($e->getMessage());
                    return $this->redirectBack();
                }
            }
        }
    }

    /**
     * generates the context-menu.
     */
    public function generateContextMenu($child)
    {
        if (!$child->model || $child->model->can("write")) {
            return array_merge(array(array(
                                         "icon"     => "images/icons/goma16/page_new.png",
                                         "label"    => lang("SUBPAGE_CREATE"),
                                         "ajaxhref" => $this->originalNamespace . "/add" . URLEND . "?parentid=" . $child->recordid
                                     ),
                                     "hr"), parent::generateContextMenu($child));
        }

        return parent::generateContextMenu($child);

    }

    /**
     * add-form
     *
     * @name cms_add
     * @access public
     * @return mixed|string
     */
    public function cms_add()
    {
        $model = $this->getModelForAdd();
        if (is_a($model, "pages")) {
            /** @var Pages $model */
            return $this->getFormForAdd($model)->render();
        } else {
            Resources::addJS('$(function(){$(".leftbar_toggle, .leftandmaintable tr > .left").addClass("active");$(".leftbar_toggle, .leftandmaintable tr > .left").removeClass("not_active");$(".leftbar_toggle").addClass("index");});');

            return $model->renderWith("admin/leftandmain_add.html");
        }
    }

    /**
     * gets model for adding a page.
     *
     * @return ViewAccessableData
     */
    protected function getModelForAdd()
    {
        $model = $this->getModelByName($this->getParam("model"));

        // show page for selecting type
        if (!$model) {
            $model = new ViewAccessableData();

            return $model->customise(array("adminuri" => $this->adminURI(), "types" => $this->types()));
        }

        $model->queryVersion = "state";

        return $model;
    }

    /**
     * generates form for adding a page.
     *
     * @param Pages $model
     * @return Form
     */
    protected function getFormForAdd($model)
    {
        $controller = clone $this;
        $controller->selectModel($model, true);
        $form = new Form($controller, "add_page");

        if (isset($this->request->get_params["parentid"]) && $this->request->get_params["parentid"] != 0) {
            $form->setModel(new Page(array(
                "parenttype" => "subpage",
                "parentid"   => $this->request->get_params["parentid"]
            )));
        } else {
            $form->setModel(new Page(array(
                "parenttype" => "root",
                "parentid"   => 0
            )));
        }

        $form->useStateData = true;

        $headBarView = new ViewAccessableData(array(
            "classname"  => $model->classname,
            "classtitle" => ClassInfo::getClassTitle($model->classname),
            "classicon"  => ClassInfo::getClassIcon($model->classname)
        ));
        $form->add(new HTMLField('headbar', $headBarView->renderWith("admin/content-headbar.html")));

        $this->getAddFormFields($form, $model);

        return $form;
    }

    /**
     * adds fields to add-form.
     *
     * @param Form $form
     * @param Pages $model
     */
    protected function getAddFormFields(&$form, $model)
    {
        $form->add($title = new textField('title', lang("title_page", "title of the page")));
        $form->add($parenttype = new ObjectRadioButton("parenttype", lang("hierarchy", "hierarchy"), array(
            "root"    => lang("no_parentpage", "Root Page"),
            "subpage" => array(
                lang("subpage", "sub page"),
                "parent"
            )
        )));


        if (!$this->modelInst()->can("insert")) {
            $parenttype->disableOption("root");
        }

        $allowed_parents = $model->parentResolver()->getAllowedParents();
        $form->add($parentDropdown = new HasOneDropDown("parent", lang("parentpage", "Parent Page"), "title", ' `pages`.`class_name` IN ("' . implode($allowed_parents, '","') . '")'));
        $parentDropdown->info_field = "url";

        $form->add(new HiddenField("class_name", $model->classname));

        $form->addValidator(new requiredFields(array('filename', 'title', 'parenttype')), "default_required_fields"); // valiadte it!
        $form->addValidator(new FormValidator(array($model, "validatePageType")), "pagetype");
        $form->addValidator(new FormValidator(array($model, "validatePageFileName")), "filename");

        // default submission
        $form->setSubmission("submit_form_generateUniquePath");
        $form->addValidator(new DataValidator($model), "datavalidator");

        $form->addAction(new AjaxSubmitButton("save_draft", lang("next_step", "next step"), "AjaxSaveGenerate", null, array("green")));

        $model->getAddFormFields($form);
        $model->callExtending("getAddFormFields", $form);
    }

    /**
     * generates mainbar-title and path for newly generated page.
     */
    public function submit_form_generateUniquePath($data)
    {
        $data["mainbartitle"] = $data["title"];
        $value = PageUtils::cleanPath($data["title"]);

        $parentid = ($data["parenttype"] == "root") ? 0 : $data["parentid"];
        $i = 1;
        $current = $value;
        while (DataObject::count("pages", array("parentid" => $parentid, "path" => $current)) > 0) {
            $i++;
            $current = $value . "-" . $i;
        }

        $data["path"] = $current;

        return $this->submit_form($data);
    }

    /**
     * saves data for editing a site via ajax
     *
     * @param array $data
     * @param FormAjaxResponse $response
     * @return FormAjaxResponse
     */
    public function ajaxSaveGenerate($data, $response)
    {
        $data["mainbartitle"] = $data["title"];
        $value = PageUtils::cleanPath($data["title"]);

        $parentid = ($data["parenttype"] == "root") ? 0 : $data["parentid"];
        $i = 1;
        $current = $value;
        $object = DataObject::get("pages", array("parentid" => $parentid, "path" => $current));
        $object->setVersion("state");
        while ($object->count() > 0) {
            $i++;
            $current = $value . "-" . $i;
            $object->filter(array("parentid" => $parentid, "path" => $current));
        }

        $data["path"] = $current;

        return $this->ajaxSave($data, $response);
    }

    /**
     * help-texts.
     */
    public function helpData()
    {
        return array(
            "#treenode_leftandmain_treerenderer_page_0_addButton a" => array(
                "text"     => lang("HELP.ADD-NEW-PAGE"),
                "position" => "right"
            ),
            ".hitarea:first a span"                                 => array(
                "text"     => lang("HELP.HIERARCHY_OPEN"),
                "position" => "bottom"
            ),
            ".treewrapper:first"                                    => array(
                "text"     => lang("HELP.PAGES_SORT"),
                "position" => "fixed",
                "autoHide" => false,
                "bottom"   => "1em",
                "left"     => "0.5em"
            ),
            "#visit_webpage"                                        => array(
                "text" => lang("PREVIEW")
            )
        );
    }
}
