<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 30.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class AjaxExternalForm extends FormField
{

		public $allowed_actions = array(
			"render"
		);
		/**
		 * external-form
		 *@name external-form
		 *@access public
		*/
		public $external_form;
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param string - title
		 *@param form - external form
		 *@param form - form for this field
		*/
		public function __construct($name = "", $title = null, $value = null, $external = "", $form = null, $code = "")
		{
				if(is_object($external))
					$this->external_form = $external;
				
				
				parent::__construct($name, $title, $value, $form);
		}
		/**
		 *@name createNode
		 *@access public
		*/
		public function createNode()
		{
				$node = new HTMLNode("div", array(
					"class"	=> "value"
				));
				
				return $node;
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				$this->callExtending("beforeField");
				
				$this->container->append(new HTMLNode("label", array(
				
				), $this->title));
				
				$this->container->append($this->input);
				$this->input->append(array(
					new HTMLNode("div", array(
						"style" => array("float" => "right"),
						"class"	=> "edit"
					), '<a href="'.$this->externalURL().'/render/?redirect='.urlencode(getredirect()).'" title="'.text::protect($this->title).'" rel="bluebox">'.lang("edit").'</a>&nbsp;<img src="images/icons/fatcow-icons/16x16/edit.png" alt="{$_lang_edit}" style="height: 13px; width: 13px;" />'),
					$this->value,
					new HTMLNode("div", array(
						"class"	=> "clear"
					))
				));
				
				$this->callExtending("afterField");
				
				return $this->container;
		}
		/**
		 * renders the bluebox
		 *@name render
		 *@access public
		*/
		public function render()
		{
				// create a deep copy
				$form = unserialize(serialize($this->external_form)); 
				if(Core::is_ajax()) {
					$form->addAction(new Button("cancel", lang("cancel", "Cancel"), "var id = $(this).parents('.bluebox').attr('id').replace('bluebox_','');getblueboxbyid(id).close();"));
					return $form->render();
				} else {
					$form->add(new HTMLField("head","<h1>".text::protect($this->title)."</h1>"), 1);
					$form->addAction(new LinkAction("cancel", lang("cancel"), $this->form()->url));
					return showSite($form->render(), $this->title);
				}
		}
		/**
		 * we never have a result
		*/
		public function result()
		{
				return null;
		}
}