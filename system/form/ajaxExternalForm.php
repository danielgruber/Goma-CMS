<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 08.05.2012
  * $Version 1.1.1
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
		public function __construct($name = "", $title = null, $value = null, $external = "", &$form = null, $code = "")
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
				$node = new HTMLNode("span", array(
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
					$this->value,
					"&nbsp;&nbsp;&nbsp;&nbsp;",
					new HTMLNode("a", array(
						"href" 	=> $this->externalURL() . "/render/?redirect=" . urlencode(getredirect()),
						"title" => convert::raw2text($this->title),
						"rel"	=> "dropdownDialog",
						"class" => "edit hideClose noAutoHide"
					), new HTMLNode("img", array("alt" => lang("edit"), "src" => "images/icons/fatcow16/edit.png", "data-retina" => "images/icons/fatcow16/edit@2x.png")))
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
					$form->add(new HTMLField("head","<h1>".convert::raw2text($this->title)."</h1>"), 1);
					$form->addAction(new LinkAction("cancel", lang("cancel"), $this->form()->url));
					$fronted = new FrontedController();
					return $fronted->serve($form->render());
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