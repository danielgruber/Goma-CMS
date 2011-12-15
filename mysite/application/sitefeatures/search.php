<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 11.11.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang("search");

class SearchController extends FrontedController
{
        public $model = "pages";
        
        public $title = "{\$_lang_search.search}";
        
        /**
         *@name index
        */
        public function index()
        {
                $word = isset($_GET["q"]) ? $_GET["q"] : "";
                if($word == "")
                {
                        return $this->model_inst->renderWith("pages/search.html");
                } else
                {
                        $data = DataObject::_search("pages", array($word), array("search" => 1), array(), array(), array(), array(), true);
                        return $data->customise(array(), array("word" => text::protect($word)))->renderWith("pages/search.html");
                }
        }
}

class SearchPageExtension extends DataObjectExtension
{
        public function argumentSearchSQL($query, $search)
        {
                $_query = Object::instance("boxes")->buildSearchQuery(array("seiteid"));
                
                // override default fields, because goma loads as default id, class_name, autorid and last_modfied
                $_query->fields = array("seiteid" => "seiteid");               
                
                // now generate query and new addWhere
                $query->addFilter(array("OR", "pages.id IN ( ".$_query->build() ." )" ));                
                
                return $query;
        }
}

Object::extend("pages", "SearchPageExtension");