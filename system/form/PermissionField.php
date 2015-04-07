<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 09.12.2012
  * $Version - 1.1.3
 */
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class PermissionField extends ClusterFormField {
	
	/**
	 * defines if it is from a relation
	 *
	 *@name byRelation
	 *@access protected
	*/
	protected $byRelation;
	
	/**
	 * inherit-from
	 *
	 *@name inheritFrom
	 *@access public
	*/
	public $inheritFrom;
	
	/**
	 * title of inherit
	 *
	 *@name inheritTitle
	 *@access public
	*/
	public $inheritTitle;
	
	/**
	 * disabled 
	*/
	
	/**
	 * construction
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($name = null, $title = null, $value = null, $password = false, $disabled = array(), $inheritFrom = null, $inheritTitle = null, $parent = null) 	{
		parent::__construct($name, null, array(), $value, $parent);
		
		if(is_string($inheritFrom)) {
			Permission::check($inheritFrom);
			$inheritFrom = DataObject::get_one("Permission", array("name" => $inheritFrom));
		}
		$this->inheritFrom = $inheritFrom;
		$this->inheritTitle = $inheritTitle;
		
		$this->add($radio = new ObjectRadioButton("type", $title, array(
			"all" 	  	=> lang("everybody", "everybody"),
			"users" 	=> lang("login_groups", "Everybody, who can login"),
			"admins"	=> lang("admins", "admins"),
			"password"	=> array(
				lang("password", "password"),
				"password"
			),
			"groups"	=> array(
				lang("following_groups", "Following Groups"),
				"_groups"
			)
		)));
		$this->add(new TextField("password", lang("password")));
		if(!$password)
			$disabled[] = "password";
		
		$this->add(new FieldSet("_groups", array(
			new Checkbox("invert_groups", lang("invert_groups", "invert groups")),
			new ManyManyDropdown("groups", lang("groups"), "name")
		)));
		
		foreach($disabled as $disabled_node) {
			$radio->disableOption($disabled_node);
		}
	}
	
	/**
	 * generates the field
	 *
	 *@name field
	 *@access public
	*/
	public function field() {
		if(is_object($this->inheritFrom) && is_a($this->inheritFrom, "Permission") && $this->inheritFrom->id != $this->value->id) {
			$title = isset($this->inheritTitle) ? ' (' . $this->inheritTitle . ')' : null;
			$this->type->addOption("inherit", lang("inherit_from_parent") . $title, true);
			if(is_object($this->inheritFrom) && $this->inheritFrom->id == $this->value->parentid) {
				$this->type->value = "inherit";
			}
		}
		
		if($this->disabled) {
			$this->fields["password"]->value = "*****";
		}
		
		return parent::field();
	}
	
	/**
	 * sets inherit
	 *
	 *@name setInherit
	 *@access public
	*/
	public function setInherit($from, $title = null) {
		$this->inheritFrom = $from;
		$this->inheritTitle = $title;
	}
	
	/**
	 * generates the value of this field
	 *
	 *@name getValue
	 *@access public
	*/
	public function getValue() {

		if(is_object($this->orgForm()->result)) {
			$has_one = $this->orgForm()->result->hasOne();

			if(isset($has_one[$this->name])) {

				if(strtolower($has_one[$this->name]) == "permission" || is_subclass_of($has_one[$this->name], "permission")) {
					$this->byRelation = true;

					if(isset($this->orgForm()->result[$this->name]) && $this->orgForm()->result[$this->name]) {
						$this->value = $this->orgForm()->result->doObject($this->name);

                        return;
					}
				} else {
					$this->byRelation = false;
				}
			} else {
				$this->byRelation = false;
			}
		}
		
		if($this->POST && $this->value == null && isset($this->orgForm()->result[$this->name]) && is_object($this->orgForm()->result)) {
			$this->value = ($this->orgForm()->result->doObject($this->name)) ? $this->orgForm()->result->doObject($this->name) : null;
		} else if($this->POST && $this->value == null && isset($this->orgForm()->result[$this->name]))
			$this->value = $this->orgForm()->result[$this->name];
		
		if(!isset($this->value)) {
			if(!$this->byRelation && $perm = DataObject::get_one("Permission", array("name" => $this->name))) {
				$this->value = $perm;
			} else if($this->byRelation) {
				$this->value = new Permission();
			} else {
				$this->value = new Permission(array("name" => $this->name));
			}
		}
		
		
	}
	
	/**
	 * result
	 *
	 *@name result
	 *@access public
	*/
	public function result() {
		if($this->fields["type"]->result() == "inherit" && $this->inheritFrom && is_a($this->inheritFrom, "Permission")) {
			$val = clone $this->inheritFrom;
			$val->consolidate();
			$val->parentid = $this->inheritFrom->id;
			$val->id = $this->value->id;
			$val->name = $this->value->name;
			//$val->versionid = $this->value->versionid;
			$this->value = $val;
		} else {
			$this->value->parentid = 0;
			$this->value->parent = null;
			// now remodify it by the given fields
			foreach($this->fields as $val) {
				if($result = $val->result()) {
					$this->value->{$val->dbname} = $result;
				}
			}
		}
		
		return $this->value;
	}
}