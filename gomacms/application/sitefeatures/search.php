<?php
/**
  *@todo comments
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 15.01.2015
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang("search");

class SearchController extends FrontedController
{
        public $model = "pages";
        
        public $title = "{\$_lang_search.search}";

        /**
         * @name index
         * @return bool|string
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
                        $data = DataObject::search_object("pages", array($word), array("include_in_search" => 1));
                        $data->activatePagination(isset($_GET["pa"]) ? $_GET["pa"] : null);
                        return $data->customise(array("word" => convert::raw2text($word)))->renderWith("pages/search.html");
                }
        }
}

class SearchPageExtension extends DataObjectExtension implements argumentsSearchQuery
{
        public function argumentSearchSQL($query, $search, $version, $filter, $sort, $limit, $join, $forceClasses)
        {
                $_query = gObject::instance("boxes")->buildSearchQuery($search);
                
                // now generate query and new addWhere
                $query->addFilter(array("OR", "pages_state.id IN ( ".$_query->build("seiteid") ." )" ));                
                
                return $query;
        }
}

gObject::extend("pages", "SearchPageExtension");