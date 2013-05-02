<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 20.02.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class CancelButton extends FormAction {
		/**
		 * the javascript for this button on cancel
		 *
		 *@name js
		 *@access public
		*/
		public $js;
		/**
		 *@name __construct
		 *@access public
	  	 *@param string - name
		 *@param string - title
		 *@param string - optional submission
		 *@param object - form
		*/
		public function __construct($name, $value, $redirect = null, $js = "", &$form = null)
		{
				$this->js = $js;
				parent::__construct($name, $value);
				$this->redirect = ($redirect === null) ? getredirect() : $redirect;
		}
		/**
		 * creates the node
		 *
		 *@name createNodes
		*/
		public function createNode()
		{
				$node = parent::createNode();
				$node->onClick = $this->js;
				return $node;
		}
		/**
		 * just don't let the system submit and redirect back
		*/
		public function canSubmit($data) {
			if($this->redirect !== null)
				HTTPResponse::redirect($this->redirect);
			else if(isset($_POST["redirect"]))
				HTTPResponse::redirect($_POST["redirect"]);
			else if(isset($_GET["redirect"]))
				HTTPResponse::redirect($_GET["redirect"]);
			else 
				HTTPResponse::redirect(BASE_URI);
			exit;
		}
}