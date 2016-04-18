<?php
defined("IN_GOMA") OR die();

/**
 * a multi-page page consits of a set of pages where one page is the current "main".
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 *
 * @property bool allowUrlSwitching
 * @property Pages mainpage
 */
class MultiPagePage extends Page {

    static $cname = '{$_lang_gomacms.multipage}';

    static $icon = "images/icons/goma16/page_copy.png";

    static $db = array(
        "allowUrlSwitching" => "Switch"
    );

    static $has_one = array(
        "mainpage" => "Pages"
    );

    /**
     * @param Form $form
     */
    public function getForm(&$form)
    {
        parent::getForm($form);

        $form->add(
            InfoTextField::createFieldWithInfo(
                new CheckBox("allowUrlSwitching", lang("gomacms.allowUrlSwitching")),
                lang("gomacms.allowUrlSwitchingInfo")
            ),
            null,
            "content"
        );

        $form->add(HasOneDropdown::createWithInfoField("mainpage", lang("gomacms.urlSwitchMainPage"), "title", "path", array(
            "parentid" => $this->id
        )));
    }
}

/**
 * Class MultiPagePageController
 *
 * @method MultiPagePage modelInst()
 */
class MultiPagePageController extends PageController {
    /**
     * @param string $action
     * @return bool
     */
    public function willHandleWithSubpage($action) {
        if(isset($this->request->get_params["landing"]) && $this->modelInst()->allowUrlSwitching) {
            if($this->subPage = $this->modelInst()->children(array(
                "path" => array(
                    "LIKE", $this->request->get_params["landing"]
                )
            ))->first(false)) {
                return true;
            }
        }

        if($this->modelInst()->mainpage) {
            $this->subPage = $this->modelInst()->mainpage;

            return true;
        }

        if($this->subPage = $this->modelInst()->children()->first(false)) {
            return true;
        }

        return false;
    }

    public function index()
    {
        return null;
    }
}
