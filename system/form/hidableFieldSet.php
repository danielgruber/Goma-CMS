<?php defined("IN_GOMA") OR die();

/**
 * Fieldset which can be hidden.
 *
 * @package        Goma\libs\WYSIWYG
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version        1.3.2
 */
class HidableFieldSet extends FieldSet {
	/**
	 * adds the javascript and other resources to this field
	 * @param FormFieldRenderData $info
	 * @return HTMLNode
	 */
	public function field($info) {
		$this->container->addClass("hidableFieldSet");
		$this->input->setTag("label");
		
		$open = ($this->POST && isset($this->form()->post[$this->PostName()]) && $this->form()->post[$this->PostName()] == 1);
		$this->container->append(new HTMLNode("input", array("type" => "hidden", "id" => $this->ID() . "_open", "name" => $this->PostName(), "value" => $open ? 1 : 0)));

		Resources::add("hidableFieldSet.css", "css", "tpl");
		
		return parent::field($info);
	}
	
	/**
	 * javascript
	*/
	public function JS() {
		$js = parent::JS();
		
		$open = ($this->POST && isset($this->form()->getRequest()->post_params[$this->PostName()]) && $this->form()->getRequest()->post_params[$this->PostName()] == 1);


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