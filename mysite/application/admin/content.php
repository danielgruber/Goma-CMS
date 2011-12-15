<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 10.07.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class contentAdmin extends LeftAndMain
{
		public $tree_class = "pages";
		// config
		public $text = '{$_lang_content}';
		
		public $rights = "PAGES_WRITE";
		
		public $template = "admin/content_index.html";
		
		public $models = array("pages");		
		
		public $sort = 990;
		
		public $root_node = "{\$_lang_pagetree}";
		
		public $colors = array(
			"withmainbar"	=> array(
				"color"	=> "#036",
				"name"	=> "{\$_lang_mainbar}" 
			),
			"nomainbar" 	=> array(
				"color"	=> "#3f3f3f",
				"name"	=> "{\$_lang_nomainbar}"
			)
		);
		
		public $allowed_actions = array(
			"revert_changes", "unpublish"
		);
		
		/**
		 * redirect back
		*/
		public function redirectback()
		{
				if($this->getParam(0) == "del" || $this->request->getParam(1) == "add")
				{
						HTTPresponse::redirect(ROOT_PATH . 'admin/content' . URLEND);
				} else
				{
						parent::redirectback();
				}
		}
		
		/**
		 * generates the options for the create-select-field
		 *
		 *@name CreateOptions
		 *@access public
		*/
		public function createOptions() {
			$data = array();
			foreach(classinfo::getChildren("pages") as $page) {
				if(classinfo::exists($page)) {
					$c = new $page;
					if(!Object::method_exists($c, "hidden") || $c->hidden() !== true)
						$data[$page] = parse_lang($c->name);
				}
			}
			
			return $data;
		}
		
	/**
	 * restores the last published version
	 *
	 *@name revert_changes
	 *@access public
	*/
	public function revert_changes() {

		if($this->confirm(lang("revert_changes_confirm", "Do you really want to revert changes and go back to the last published version?"))) {
			$data = DataObject::getObject($this->model_inst->class, array("id" => $this->model_inst->id));
			if($data->_count() > 0) {
				$data->write(false, false, 2);
				if(Core::is_ajax()) {
					$response = new AjaxResponse();
					$dialog = new Dialog(lang("revert_changes_success", "The last version was recovered successfully."), lang("okay", "Okay"));
					$dialog->close(3);
					$response->exec($dialog);
					$response->exec("reloadTree(function(){ LoadTreeItem(".$data["id"]."); });");
					HTTPResponse::setBody($response->render());
					HTTPResponse::output();
					exit;
				} else {
					addcontent::addSuccess(lang("revert_changes_success", "The last version was recovered successfully."));
					$this->redirectBack();
				}
				
			}
			
		}
	}
	
	/**
	 * unpublishes the current version
	 *
	 *@name unpublish
	 *@access public
	*/
	public function unpublish() {
		if($this->model_inst->unpublish()) {
			if(Core::is_ajax()) {
				$response = new AjaxResponse();
				$dialog = new Dialog(lang("unpublish_success", "The site was successfully unpublished."), lang("okay", "Okay"));
				$dialog->close(3);
				$response->exec($dialog);
				$response->exec("reloadTree(function(){ LoadTreeItem(".$this->model_inst->id."); });");
				HTTPResponse::setBody($response->render());
				HTTPResponse::output();
				exit;
			} else {
				AddContent::addSuccess(lang("unpublish_success", "The site was successfully unpublished."));
				$this->redirectBack();
				exit;
			}
		}
		if(Core::is_ajax()) {
				$response = new AjaxResponse();
				$dialog = new Dialog(lang("less_rights"), lang("error", "error"));
				$dialog->close(3);
				$response->exec($dialog);
				HTTPResponse::setBody($response->render());
				HTTPResponse::output();
				exit;
			} else {
				AddContent::addError(lang("less_rights"));
				$this->redirectBack();
				exit;
			}
	}
}