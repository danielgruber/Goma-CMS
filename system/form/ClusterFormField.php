<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 27.04.2013
  * $Version - 1.0.5
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ClusterFormField extends FormField {
	/**
	 * fields of this cluster
	 *
	 *@name fields
	 *@access public
	*/
	public $fields = array();
	
	/**
	 * items of this cluster
	 *
	 *@name items
	 *@access public
	*/
	public $items = array();
	
	/**
	 * fields already rendered
	 *
	 *@name renderedFields
	 *@access public
	*/
	public $renderedFields = array();
	
	/**
	 * sort of the items
	 *@name sort
	 *@access public
	*/
	public $sort = array();
	
	/**
	 * url of the original form
	 *
	 *@name url
	 *@access public
	*/
	public $url;
	
	/**
	 * result will be linked on value
	 *
	 *@name result
	 *@access public
	*/
	public $result;
	
	/**
	 * controller
	 *
	 *@name controller
	 *@access public
	*/
	public $controller;
	
	/**
	 * post-data
	 *
	 *@name post
	 *@access public
	*/
	public $post;
	
	/**
	 * state
	*/
	public $state;
	
	/**
	 * constructing
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($name = null, $title = null, $fields = null, $value = null, &$form = null) {
		if(!isset($value))
			$value = array();
		
		parent::__construct($name, $title, $value, $form);
		
		foreach((array)$fields as $field) {
			$field->overridePostName = $this->name . "_" . $field->name;
			$this->sort = 1 + count($this->items);
			$this->items[] = $field;
		}
		
		$this->result =& $this->value;
	}
	
	/**
	 * checks if the action is available
	 * we implement sub-namespaces for sub-items here
	 *
	 *@name hasAction
	 *@access public
	*/
	public function hasAction($action) {
		if(isset($this->fields[$action]))
			return true;
			
		if(parent::hasAction($action))
			return true;
			
		return false;
	}
	
	/**
	 * handles the action
	 * we implement sub-namespaces for sub-items here
	 *
	 *@name handleAction
	 *@access public
	*/
	public function handleAction($action) {
		if(isset($this->fields[$action]))
			return $this->fields[$action]->handleRequest($this->request);
			
		return parent::handleAction($action);
	}
	
	/**
	 * returns the node
	 *
	 *@name createNode
	 *@access public
	*/
	public function createNode() {
		return new HTMLNode("div");
	}
	
	/**
	 * renders the field
	 *
	 *@name field
	 *@access public
	*/
	public function field() {
		
		if(PROFILE) Profiler::mark("ClusterFormField::field");
		
		$this->callExtending("beforeField");
		
		$this->setValue();
		
		$this->container->append(new HTMLNode(
			"label",
			array("for"	=> $this->ID()),
			$this->title
		));
		
		// create subnode
		$subContainer = new HTMLNode("div");
		
		// get content
		usort($this->items, array($this, "sort"));
		foreach($this->items as $item) {
			if($this->isFieldToRender($item->name)) {
				$this->registerRendered($item->name);
				$subContainer->append($item->field());
			}
		}
		
		// append it to the main-tree
		$this->container->append($subContainer);
		
		$this->callExtending("afterField");
		
		if(PROFILE) Profiler::unmark("ClusterFormField::field");
		
		return $this->container;
	}
	
	/**
	 * adds an field
	 *@name add
	 *@access public
	*/
	public function add($field, $sort = 0)
	{	
		$field->overridePostName = $this->name . "_" . $field->name;
		
		if($sort == 0) {
			$sort = 1 + count($this->items);
		}
		
		$this->sort[$field->name] = $sort;
		$this->items[$field->name] = $field;
		if(isset($this->parent))
			$field->setForm($this);
	}
	
	
	/**
	 * removes a field or this field
	 *
	 *@name remove
	 *@access public
	*/
	public function remove($field = null)
	{
		if($field === null) {
			parent::remove();
		} else
		{
			if(isset($this->fields[$this->name . "_" . $field]))
			{
				unset($this->fields[$this->name . "_" . $field]);
			}
			
			if(isset($this->items[$this->name . "_" . $field]))
			{
				unset($this->items[$this->name . "_" . $field]);
			}
		}
	}
	
	/**
	 * sorts the items
	 *@name sort
	 *@access public
	*/
	public function sort($a, $b)
	{
		if($this->sort[$a->name] == $this->sort[$b->name])
		{
			return 0;
		}
			
		return ($this->sort[$a->name] > $this->sort[$b->name]) ? 1 : -1;
	}
	
	/**
	 * sets the form
	 *
	 *@name setForm
	 *@access public
	*/
	public function setForm(&$form) {
		parent::setForm($form);
		
		unset($this->fields[$this->name]);
		$this->orgForm()->registerField($this->name, $this);
		
		while(!isset($form->url) && is_object($form))
			$form = $form->form();
		
		$this->url =& $form->url;
		$this->controller =& $form->controller;
		$this->post =& $form->post;
		$this->state = $this->orgForm()->state->{$this->class . $this->name};
		
		foreach($this->items as $field)
			$field->setForm($this);
	}
	
	/**
	 * gets value if is in result or post-data
	 *
	 *@name getValue
	 *@access public
	*/
	public function getValue() {
	
		if(!$this->disabled && $this->POST && isset($this->orgForm()->post[$this->PostName()])) {
			$this->value = $this->orgForm()->post[$this->PostName()];
		} else if($this->POST && $this->value == null && isset($this->orgForm()->result[$this->name]) && is_object($this->orgForm()->result)) {
			$this->value = ($this->orgForm()->result->doObject($this->name)) ? $this->orgForm()->result->doObject($this->name)->raw() : null;
		} else if($this->POST && $this->value == null && isset($this->orgForm()->result[$this->name]))
			$this->value = $this->orgForm()->result[$this->name];
	}
	
	/**
	 * returns the form
	 *
	 *@name form
	 *@access public	
	*/
	public function &form() {
		return $this;
	}
	
	/**
	 * returns original form
	 *
	 *@name orgForm
	 *@access public
	*/
	public function orgForm() {
		return parent::form();
	}
	
	/**
	 * the url for ajax
	 *
	 *@name externalURL
	 *@access public
	*/
	public function externalURL()
	{
			return $this->orgForm()->externalURL() . "/" . $this->name;
	}
	
	/**
	 * disables this field and all sub-fields
	 *
	 *@name disable
	 *@access public
	*/
	public function disable()
	{	
		$this->disabled = true;
		foreach($this->fields as $field)
			$field->disable();
	}
	
	/**
	 * enables this field and all sub-fields
	 *
	 *@name enable
	 *@access public
	*/
	public function enable()
	{	
		$this->disabled = false;
		foreach($this->fields as $field)
			$field->enable();
	}
	
	/**
	 * generates an id for the field
	 *@name id
	 *@access public
	*/
	public function ID()
	{
		if(Core::is_ajax()) {
			return "form_field_" .  $this->class . "_" . md5($this->orgForm()->name . $this->title) . "_" . $this->name . "_ajax";
		} else {
			return "form_field_" .  $this->class . "_" . md5($this->orgForm()->name . $this->title) . "_" . $this->name;
		}
	}
	
	/**
	 * result
	 *
	 *@name result
	 *@access public
	*/
	public function result() {	
		$this->result = array();
		foreach($this->fields as $field) {

			$this->result[$field->name] = $field->result();
		}
	
		return $this->result;
	}
	
	/**
	 * generates an name for this form
	 *@name name
	 *@access public
	*/
	public function name()
	{
			return $this->name;
	}
	
	/**
	 * this function generates some JavaScript for this formfield
	 *@name js
	 *@access public
	*/
	public function js()
	{
		$js = "";
		foreach($this->fields as $field) {
			$js .= $field->JS();
		}
		
		return $js;
	}
	
	/**
	 * registers a field in this form
	 *
	 *@name registerField
	 *@access public
	 *@param string - name
	 *@param object - field
	*/
	public function registerField($name, $field) {
		if($name == $this->name) {
			return false;
		}
		$this->fields[strtolower($name)] = $field;
		$field->overridePostName = $this->name . "_" . $name;
	}
	
	/**
	 * just unregisters the field in this form
	 *
	 *@name unRegister
	 *@access public
	*/
	public function unRegister($name) {
		unset($this->fields[strtolower($name)]);
	}
	
	/**
	 * gets the field by the given name
	 *
	 *@name getField
	 *@access public
	 *@param string - name
	*/
	public function getField($offset) {
		if(isset($this->fields[strtolower($offset)])) 
			return $this->fields[strtolower($offset)];
		
		return false;
	}

	/**
	 * returns if a field exists in this form
	 *
	 *@name isField
	 *@access public
	*/
	public function isField($name)
	{
			return (isset($this->fields[strtolower($name)]));
	}
	
	/**
	 * returns if a field exists and wasn't rendered in this form
	 *
	 *@name isField
	 *@access public
	*/
	public function isFieldToRender($name)
	{
			return ((isset($this->fields[strtolower($name)])) && !isset($this->renderedFields[strtolower($name)]));
	}
	
	/**
	 * registers the field as rendered
	 *
	 *@name registerRendered
	 *@access public
	 *@param string - name
	*/
	public function registerRendered($name) {
		$this->renderedFields[strtolower($name)] = true;
	}
	
	/**
	 * removes the registration as rendered
	 *
	 *@name unregisterRendered
	 *@access public
	 *@param string - name
	*/
	public function unregisterRendered($name) {
		unset($this->renderedFields[strtolower($name)]);
	}
	
	//!Overloading
	/**
	 * Overloading
	*/
	
	/**
	 * returns a field in this form by name
	 * it's not relevant how deep the field is in this form if the field is *not* within a ClusterFormField
	 *
	 *@name __get
	 *@access public
	*/
	public function __get($offset)
	{
		return $this->getField($offset);
	}
	
	/**
	 * currently set doesn't do anything
	 *
	 *@name __set
	 *@access public
	*/
	public function __set($offset, $value)
	{
			// currently there is no option to overload a form with fields
	}
	
	/**
	 * returns if a field exists in this form
	 *
	 *@name __isset
	 *@access public
	*/
	public function __isset($offset)
	{
			return $this->isField($offset);
	}

		
}