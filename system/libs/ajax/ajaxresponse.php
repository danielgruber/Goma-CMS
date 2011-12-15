<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 06.04.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class AjaxResponse extends JavaScriptResponse
{
		/**
		 * this array contains each action
	     *@name actions
		 *@access protected
		 *@var array
		*/
		protected $actions = array();
		
		/**
		 * adds war js to the actions
		 *@name exec
		 *@access public
		 *@param string - js
		*/
		public function exec($js)
		{
				if(is_object($js))
				{
						$js = $js->render();
				}
				$this->actions[] = $js;
				return count($this->actions) - 1;
		}
		
		/**
		 * actions
		*/
		
		/**
		 * this function replaces html in a given node
		 *@name replace
		 *@access public
		*/
		public function replace($node, $html)
		{
				$this->exec('$("'.convert::raw2js($node).'").html("'.convert::raw2js($html).'");');
		}
		/**
		 * appends code to a node
		 *@name append
		 *@access public
		*/
		public function append($node, $html)
		{
				$this->exec('$("'.convert::raw2js($node).'").append("'.convert::raw2js($html).'");');
		}
		/**
		 * appends code to a node
		 *@name append
		 *@access public
		*/
		public function appendHighlighted($node, $html)
		{
				$this->exec('$("'.convert::raw2js($node).'").append("<div class=\"highlighter\">'.convert::raw2js($html).'</div>");$("'.convert::raw2js($node).'").find(".highlighter:last").css("display", "none").slideDown("slow");');
		}
		/**
		 * appends code to a node
		 *@name append
		 *@access public
		*/
		public function prependHighlighted($node, $html)
		{
				$this->exec('$("'.convert::raw2js($node).'").prepend("<div class=\"highlighter\">'.convert::raw2js($html).'</div>");$("'.convert::raw2js($node).'").find(".highlighter:first").css("display", "none").slideDown("slow");');
		}
		/**
		 * preprend
		 *@name prepend
		 *@access public
		*/
		public function prepend($node, $html)
		{
				$this->exec('$("'.convert::raw2js($node).'").prepend("'.convert::raw2js($html).'");');
		}
		/**
		 * renders the response
		 *@name render
		 *@access public
		*/
		public function render()
		{
				HTTPResponse::AddHeader("content-type", "text/javascript");
				return implode("\n", $this->actions);
		}
		/**
		 * removes a node
		*/
		public function removeNode($node) {
			$this->exec('$("'.convert::raw2js($node).'").remove();');
		}
		/**
		 * slides a node up (hide)
		 *
		 *@name slideUp
		 *@access public
		*/
		public function slideUp($node, $duration = "200", $exec, $exec = "") {
			
			if(is_int($exec)) {
				$exec = $this->actions[$exec];
				unset($this->actions[$exec]);
			}
			$this->exec('$("'.convert::raw2js($node).'").slideUp('.var_export($duration, true).', function(){
				'.$exec.'
			});');
		}
		/**
		 * slides a node down (show)
		 *
		 *@name slideUp
		 *@access public
		*/
		public function slideDown($node, $duration = "200", $exec = "") {
			if(is_int($exec)) {
				$exec = $this->actions[$exec];
				unset($this->actions[$exec]);
			}
			$this->exec('$("'.convert::raw2js($node).'").slideDown('.var_export($duration, true).', function(){
				'.$exec.'
			});');		}
}

class JavaScriptResponse extends Object
{
		/**
		 * renders the response
		 *@name render
		 *@access public
		*/
		public function render()
		{
				
		}
		
		public function __toString()
		{
				return $this->render();
		}
}