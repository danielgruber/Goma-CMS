<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 17.03.2013
  * $Version 1.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class ExternalForm extends FormField
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
		 * title of the external form
		 *
		 *@name extTitle
		 *@access public
		*/
		public $extTitle;
		
		/**
		 *@name __construct
		 *@param string - name
		 *@param string - title
		 *@param string - title
		 *@param form - external form
		 *@param form - form for this field
		*/
		public function __construct($name = "", $title = null, $extTitle = null, $value = null, $externalCallback = null, &$form = null, $code = "")
		{
				$this->external_form = $externalCallback;
				
				if(!isset($extTitle))
					$this->extTitle = $title;
				else
					$this->extTitle = $extTitle;
				
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
						"title" => convert::raw2text($this->extTitle)
					), ($this->title != $this->extTitle) ? $this->extTitle : lang("edit"))
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
				Core::setTitle($this->extTitle);
				// create a deep copy
				if(is_callable($this->external_form)) {
					$form = call_user_func_array($this->external_form, array());
				} else {
					throwError(6, "No callback set", "No valid callback were set to ExternalForm::__construct");
				}
				$form->add(new HTMLField("head","<h1>".convert::raw2text($this->extTitle)."</h1>"), 1);
				$form->addAction(new LinkAction("cancel", lang("cancel"), $this->form()->url));
				$fronted = new FrontedController();
				return $fronted->serve($form->render());
		}
		/**
		 * we never have a result
		*/
		public function result()
		{
				return null;
		}
}