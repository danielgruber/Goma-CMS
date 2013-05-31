<?php defined("IN_GOMA") OR die();


/**
 * This is a fieldset which is used as a tabsetb, which holds several tabs.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.5
 */
class TabSet extends FieldSet
{
		/**
		 *@name __construct
		 *@access public
		 *@param string - name
		 *@param array - fields
		 *@param null|object - form
		*/
		public function __construct($name, $fields, &$form = null)
		{
				parent::__construct($name, $fields, null, $form);
							
				$this->container->setTag("div");
				$this->container->addClass("tabs");
		}
		/**
		 * renders the field
		 *@name field
		 *@access public
		*/
		public function field()
		{
				if(PROFILE) Profiler::mark("FieldSet::field");
				
				$this->callExtending("beforeField");
				
				// get content
				usort($this->items, array($this, "sort"));
				
				$list = new HTMLNode("ul", array(
				
				));
				$this->container->append($list);
				$i = 0;
				foreach($this->items as $key => $item)
				{
						// if a FieldSet is disabled all subfields should disabled, too
						if($this->disabled) {
							$item->disable();
						}
						
						$name = strtolower($item->name);
						// if a field is deleted the field does not exist in that array
						if($this->form()->isFieldToRender($name) && !$item->hidden())
						{
							$this->form()->registerRendered($name);
							if((isset($_POST["tabs_" . $item->name])) && $i == 0 /*make sure just one tab is active*/) {
								$i++;
								setcookie("tabs_" . $this->name, $item->name, 0, "/");
								$item->container->addClass("active");
								$this->container->append($item->field());
								$list->append(new HTMLNode('li', array(
									
								),	 new HTMLNode('input', array(
										'type'	=> "submit",
										'name'	=> "tabs_" . $item->name,
										"value"	=> $item->title,
										"class"	=> "tab active",
										"id"	=> $item->divid() . "_tab",
									))
								)); // add an li with an a inside
							} else {
								$this->container->append($item->field());
								$list->append(new HTMLNode('li', array(
									
								),	 new HTMLNode('input', array(
										'type'	=> "submit",
										'name'	=> "tabs_" . $item->name,
										"value"	=> $item->title,
										"class"	=> "tab",
										"id"	=> $item->divid() . "_tab"
									))
								)); // add an li with an a inside
							}
						}
				}
				if($i == 0) {
					// check session
					if(isset($_COOKIE["tabs_" . $this->name])) {
						foreach($list->content as $item) {
							if($item->getNode(0)->name == "tabs_" . $_COOKIE["tabs_" . $this->name]) {
								$item->getNode(0)->addClass("active");
								foreach($this->items as $_item) {
									if($_item->name == substr($item->getNode(0)->name, 5)) {
										$_item->container->addClass("active");
									}
								}
								unset($item);
								$i++;
							}
						}
					}
					if($i == 0) {
						// make first tab active
						
						$this->container->getNode(1)->addClass("active");
						$list->getNode(0)->getNode(0)->addClass("active");
					}
				}
				
				$this->callExtending("afterField");
				$this->container->addClass("hidden");
				
				if(PROFILE) Profiler::unmark("FieldSet::field");
				
				return $this->container;
		}
		/**
		 * generates js
		 *@name JS
		 *@access public
		*/
		public function JS()
		{
				Resources::add("tabs.css");
				gloader::load("gtabs");
				return '$(function(){ $("#'.$this->divID().'").gtabs({"animation": true, "cookiename": "tabs_'.$this->name.'"}); });';
		}
}