<?php
/**
  *@todo comments
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 17.12.2012
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
        		Core::setTitle(lang("search.search"));
        		Core::addBreadCrumb(lang("search.search"), URL . URLEND);
                $word = isset($_GET["q"]) ? $_GET["q"] : "";
                if($word == "")
                {
                        return $this->modelInst()->renderWith("pages/search.html");
                } else
                {
                		Core::setTitle(convert::raw2text($word) . " - " . lang("search.search"));
                        $data = DataObject::search_object("pages", array($word), array("search" => 1));
                        $data->activatePagination(isset($_GET["pa"]) ? $_GET["pa"] : null);
                        return $data->customise(array(), array("word" => convert::raw2text($word)))->renderWith("pages/search.html");
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