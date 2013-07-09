<?php defined("IN_GOMA") OR die();

/**
 * Admin-Panel for @link pages.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0.8
 */

class contentAdmin extends LeftAndMain
{
	/**
	 * the class from which the tree should be rendered
	 *
	 *@name tree_class
	*/
	public $tree_class = "pages";
	
	/**
	 * the text in the admin-panel
	 *
	 *@name text
	*/
	public $text = '{$_lang_content}';
	
	/**
	 * permissions you need to view the adminItem
	 *
	 *@name rights
	*/
	public $rights = "ADMIN_CONTENT";
	
	/**
	 * template of the admin-panel (default)
	 *
	 *@name template
	 *@access public
	*/
	public $template = "admin/content_index.html";
	
	/**
	 * models this admin-panel manages
	 *
	 *@name models
	*/
	public $models = array("pages");		
	
	public $sort = 990;
	
	
	/**
	 * title of the first node of the tree
	 *
	 *@name root_node
	*/
	public $root_node = "{\$_lang_pagetree}";
	
	/**
	 * colors in the tree
	 *
	 *@name colors
	*/
	public $colors = array(
		"withmainbar"	=> array(
			"color"	=> "#24ACB8",
			"name"	=> "{\$_lang_mainbar}" 
		),
		"nomainbar" 	=> array(
			"color"	=> "#3f3f3f",
			"name"	=> "{\$_lang_nomainbar}"
		)
	);
	
	/**
	 * extend actions
	 *
	 *@name allowed_actions
	*/
	public $allowed_actions = array(
		"revert_changes", "unpublish", "preview"
	);
	
	/**
	 * sort in the tree
	 *
	 *@name sort_field
	*/
	protected $sort_field = "sort";
	
	/**
	 * returns the URL for the View Website-Button
	 *
	 *@name PreviewURL
	 *@access public
	*/
	public function PreviewURL() {
		return defined("PREVIEW_URL") ? PREVIEW_URL : BASE_URI;
	}
	
	
	/**
	 * history-url
	 *
	 *@name historyURL
	 *@access public
	*/
	public function historyURL() {
		return "admin/history/pages";
	}
	
	/**
	 * redirect back
	*/
	public function redirectback($param = null, $value = null)
	{
			if($this->getParam(0) == "del" || $this->request->getParam(1) == "add")
			{
					HTTPresponse::redirect(ROOT_PATH . 'admin/content' . URLEND);
			} else
			{
					parent::redirectback($param, $value);
			}
	}
	
	/**
	 * init JavaScript-Files
	*/
	public function Init($request = null) {
		Resources::add(APPLICATION . "/application/model/pages.js", "js", "tpl");
		return parent::Init($request);
	}
	
	/**
	 * generates the options for the create-select-field
	 *
	 *@name CreateOptions
	 *@access public
	*/
	public function createOptions() {
		$data = array("page" => ClassInfo::getClassTitle("Page"));
		foreach(ClassInfo::getChildren("page") as $page) {
			if(ClassInfo::exists($page)) {
				if(!Object::method_exists($page, "hidden") || call_user_func_array(array($page, "hidden"), array($page)) !== true)
					$data[$page] = convert::raw2text(ClassInfo::getClassTitle($page));
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
		if((is_a($this->modelInst(), "DataObject") || $this->modelInst()->Count() == 1)) {
			if($this->confirm(lang("revert_changes_confirm", "Do you really want to revert changes and go back to the last published version?"))) {
				$data = DataObject::get_one($this->modelInst()->classname, array("id" => $this->model_inst->id));
				if($data) {
					$data->write(false, false, 2, true);
					if(Core::is_ajax()) {
						$response = new AjaxResponse();
						Notification::notify("pages", lang("revert_changes_success", "The last version was recovered successfully."), lang("reverted"));
						$response->exec("reloadTree(function(){ LoadTreeItem('".$data["class_name"] . "_" . $data["id"]."'); });");
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
	}
	
	/**
	 * unpublishes the current version
	 *
	 *@name unpublish
	 *@access public
	*/
	public function unpublish() {
		if((is_a($this->modelInst(), "DataObject") || $this->modelInst()->Count() == 1) && $this->modelInst()->unpublish()) {
			if(Core::is_ajax()) {
				$response = new AjaxResponse();
				Notification::notify("pages", lang("unpublish_success", "The site was successfully unpublished."), lang("unpublished"));
				$response->exec("reloadTree(function(){ LoadTreeItem('" . $this->modelInst()->class_name . "_" .$this->modelInst()->id."'); });");
				$this->removeResume();
				HTTPResponse::setBody($response->render());
				HTTPResponse::output();
				exit;
			} else {
				AddContent::addSuccess(lang("unpublish_success", "The site was successfully unpublished."));
				$this->removeResume();
				$this->redirectBack();
				exit;
			}
		}
		if(Core::is_ajax()) {
			$response = new AjaxResponse();
			$dialog = new Dialog(lang("less_rights"), lang("error", "error"));
			$dialog->close(3);
			$response->exec($dialog);
			$this->removeResume();
			HTTPResponse::setBody($response->render());
			HTTPResponse::output();
			exit;
		} else {
			AddContent::addError(lang("less_rights"));
			$this->removeResume();
			$this->redirectBack();
			exit;
		}
	}
}