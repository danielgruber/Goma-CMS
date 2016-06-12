<?php
defined("IN_GOMA") OR die();

/**
 * Template-Switcher.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.0.5
 */
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
	public function __construct($name = null, $title = null, $appname = null, $appversion = null, $frameworkVersion = null, $value = null, $form = null) {
		
		$this->appname = $appname;
		$this->appversion = $appversion;
		$this->frameworkVersion = $frameworkVersion;
		
		parent::__construct($name, $title, $value, $form);
	}
	
	/**
	 * creates the hidden field to store the value
	*/
	public function createNode() {
		$node = parent::createNode();
		$node->type = "hidden";
		return $node;
	}
	
	/**
	 * renders the field
	*/
	public function renderAfterSetForm() {
		$templates = new DataSet();
		foreach(TemplateInfo::get_available_templates($this->appname, $this->appversion, $this->frameworkVersion) as $template) {
			if($this->value == $template) {
				$templates->push(array_merge(TemplateInfo::get_plist_contents($template), array("name" => $template, "selected" => true)));
			} else {
				$templates->push(array_merge(TemplateInfo::get_plist_contents($template), array("name" => $template)));
			}
		}
		
		$this->content = $templates->customise(array("id" => $this->ID(), "containerid" => $this->divID()))->renderWith("form/templateSwitcher/templateSwitcher.html");
	}

	/**
	 * build the field
	 * @param FormFieldRenderData $info
	 * @return HTMLNode
	 */
	public function field($info) {
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
