<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 21.07.2014
  * $Version: 1.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HidableFieldSet extends FieldSet {
	/**
	 * adds the javascript and other resources to this field
	 *
	 *@name field
	 *@access public
	*/
	public function field() {
		$this->container->addClass("hidableFieldSet");
		$this->input->setTag("label");
		
		$open = ($this->POST && isset($this->form()->post[$this->PostName()]) && $this->form()->post[$this->PostName()] == 1);
		$this->container->append(new HTMLNode("input", array("type" => "hidden", "id" => $this->ID() . "_open", "name" => $this->PostName(), "value" => $open ? 1 : 0)));

		Resources::add("hidableFieldSet.css", "css", "tpl");
		
		return parent::field();
	}
	
	/**
	 * javascript
	 *
	 *@name JS
	 *@access public
	*/
	public function JS() {
		$js = parent::JS();
		
		$open = ($this->POST && isset($this->form()->post[$this->PostName()]) && $this->form()->post[$this->PostName()] == 1);


		$js .= '$(function(){
			var container = $("#'.$this->divID().'");
			var open = '.var_export($open, true).';
			var id = '.var_export($this->ID() . "_open", true).';
			if(!open) {
				container.find(" > div").css("display", "none");
			} else {
				container.find(" > label").addClass("open");
				container.addClass("opened");
			}
			container.find(" > label").disableSelection();
			container.find(" > label").click(function(){
				if(container.find(" > div:first").css("display") == "none") {
					$(this).addClass("open");
					container.addClass("opened");
					container.find(" > div").slideDown("fast");
					$("#" + id).val(1);
				} else {
					container.find(" > div").slideUp("fast", function(){
						container.find(" > label").removeClass("open");
						container.removeClass("opened");
					});
					$("#" + id).val(0);
				}
			});
		});';
		
		return $js;
	}
}