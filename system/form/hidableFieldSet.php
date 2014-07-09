<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 06.04.2012
  * $Version: 1.0
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
		
		$js .= '$(function(){
			var container = $("#'.$this->divID().'");
			container.find(" > div").css("display", "none");
			container.find(" > label").disableSelection();
			container.find(" > label").click(function(){
				if(container.find(" > div:first").css("display") == "none") {
					$(this).addClass("open");
					container.addClass("opened");
					container.find(" > div").slideDown("fast");
				} else {
					container.find(" > div").slideUp("fast", function(){
						container.find(" > label").removeClass("open");
						container.removeClass("opened");
					});
				}
			});
		});';
		
		return $js;
	}
}