<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 18.11.2012
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HistoryController extends Controller {
	/**
	 * url-handlers
	 *
	 *@name url_handlers
	 *@access public
	*/
	public $url_handlers = array(
		'$c/$i'	=> "index"
	);
	
	/**
	 * name of this controller
	 *
	 *@name PageTitle
	*/
	public function PageTitle() {
		return lang("history");
	}
	
	/**
	 * index-method
	 *
	 *@name index
	*/
	public function index() {
		$filter = array();
		$class = $this->getParam("c");
		if(isset($class))
			$filter["dbobject"] = $class;
		
		$item = $this->getParam("i");
		if(isset($item))
			$filter["recordid"] = $item;
		
		
		// render the tabset
		$tabs = new Tabs("history");
		if(isset($filter["dbobject"])) {
			$tabs->addTab(ClassInfo::getClassTitle($filter["dbobject"]), History::renderHistory($filter), $filter["dbobject"]);
		}
		$tabs->addTab(lang("h_all"), History::renderHistory(array()), "h_all");
		$output = $tabs->render();
		
		if(Core::is_ajax()) {
			HTTPResponse::setBody($output);
			HTTPResponse::output();
			exit;
		} else {
			return $output;
		}
	}
}