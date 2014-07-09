<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 22.12.2012
  * $Version 1.0.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class PageLinksController extends RequestHandler {
	/**
	 * limit for the list
	*/
	public $limit = 15;
	/**
	 * urls
	*/
	public $url_handlers = array(
		"search/\$search" => "search"
	);
	/**
	 * actions
	*/
	public $allowed_actions = array(
		"search"
	);
	/**
	 * index
	*/
	public function search() {
		if($this->getParam("search")) {
			$search = $this->getParam("search");
		} else {
			$search = "";
		}
		$data = DataObject::search_object("pages", array($search), array(), $this->limit);
		$output = array("count" => $data->count, "nodes" => array());
		foreach($data as $record) {
			$output["nodes"][$record["id"]] = array(
				"id" 	=> $record["id"],
				"title"	=> convert::raw2xml($record["title"]),
				"url"	=> "./?r=" . $record->id
			);
		}
		HTTPResponse::setHeader("content-type", "text/x-json");
		HTTPResponse::output("(" . json_encode($output) . ")");
		exit;
	}
	/**
	 * index
	*/
	public function index() {
		HTTPResponse::setHeader("content-type", "text/x-json");
		HTTPResponse::setResHeader(400);
		HTTPResponse::output(array("error" => "Bad Request", "errno" => 400));
		exit;
	}
}

Core::addRules(array(
	"api/pagelinks/" => "PageLinksController"
));
