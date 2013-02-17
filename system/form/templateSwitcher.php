<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 -  2012 Goma-Team
  * last modified: 17.02.13
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TemplateSwitcher extends FormField {
	/**
	 * constructor
	 *
	 *@name __construct
	 *@param string - name
	 *@param string - title
	 *@param string - app-name
	 *@param string - app-version
	 *@param string - value
	 *@param object - form
	*/
	public function __construct($name, $title = null, $appname = null, $appversion = null, $frameworkVersion = null, $value = null, $form = null) {
		
		$this->appname = $appname;
		$this->appversion = $appversion;
		$this->frameworkVersion = $frameworkVersion;
		
		parent::__construct($name, $title, $value, $form);
	}
	
	/**
	 * creates the hidden field to store the value
	 *
	 *@name createNode
	*/
	public function createNode() {
		$node = parent::createNode();
		$node->type = "hidden";
		return $node;
	}
	
	/**
	 * renders the field
	 *
	 *@name renderAfterSetForm
	 *@access public
	*/
	public function renderAfterSetForm() {
		$templates = new DataSet();
		foreach(TemplateInfo::get_available_templates($this->appname, $this->appversion, $this->frameworkVersion) as $template) {
			$templates->push(array_merge(TemplateInfo::parse_plist($template), array("name" => $template)));
		}
		
		$this->content = $templates->customise(array("id" => $this->ID(), "containerid" => $this->divID()))->renderWith("form/templateSwitcher/templateSwitcher.html");
	}
	
	/**
	 * build the field
	 *
	 *@name field
	*/
	public function field() {
		if(PROFILE) Profiler::mark("FormField::field");
				
		$this->callExtending("beforeField");
		
		$this->setValue();
		
		$this->container->append(new HTMLNode(
			"label",
			array("for"	=> $this->ID()),
			$this->title
		));
		
		$this->container->append($this->input);
		
		$this->container->append($this->content);
		
		$this->callExtending("afterField");
		
		if(PROFILE) Profiler::unmark("FormField::field");
		
		return $this->container;
	}
}