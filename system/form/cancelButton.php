<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 18.05.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class CancelButton extends FormAction {
		public function createNode()
		{
				$node = parent::createNode();
				$node->onClick = $this->js;
				return $node;
		}
		/**
		 *@name __construct
		 *@access public
	  	 *@param string - name
		 *@param string - title
		 *@param string - optional submission
		 *@param object - form
		*/
		public function __construct($name, $value, $redirect = null, $js = "", $form = null)
		{
				$this->js = $js;
				parent::__construct($name, $value, null, null);
				$this->redirect = ($redirect === null) ? getredirect() : $redirect;
		}
		/**
		 * just don't let the system submit and redirect back
		*/
		public function canSubmit() {
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