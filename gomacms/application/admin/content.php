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
	 * add-form
	 *
	 *@name cms_add
	 *@access public
	*/
	public function cms_add() {	
		
		define("LAM_CMS_ADD", 1);
		
		if($this->getParam("model")) {
			if(count($this->models) > 1) {
				foreach($this->models as $_model) {
					$_model = trim(strtolower($_model));
					if(is_subclass_of($this->getParam("model"), $_model) || $_model == $this->getParam("model")) {
						$type = $this->getParam("model");
						$model = new $type;
						break;
					}
				}
			} else {
				$models = array_values($this->models);
				$_model = trim(strtolower($models[0]));
				if(is_subclass_of($this->getParam("model"), $_model) || $_model == $this->getParam("model")) {
					$type = $this->getParam("model");
					$model = new $type;
				}
			}
		} else {
			Resources::addJS('$(function(){$(".leftbar_toggle, .leftandmaintable tr > .left").addClass("active");$(".leftbar_toggle, .leftandmaintable tr > .left").removeClass("not_active");$(".leftbar_toggle").addClass("index");});');
		
			$model = new ViewAccessableData();
			return $model->customise(array("adminuri" => $this->adminURI(), "types" => $this->types()))->renderWith("admin/leftandmain_add.html");
		}
		
		if(DataObject::Versioned($model->dataClass) && $model->canWrite($model)) {
			$model->queryVersion = "state";
		}
		
		$allowed_parents = $model->allowed_parents();
		
		$this->selectModel($model, true);
		
		// render head-bar
		$html = '<div class="headBar"><a href="#" class="leftbar_toggle" title="{$_lang_toggle_sidebar}"><img src="system/templates/images/appbar.list.png" alt="{$_lang_show_sidebar}" /></a><span class="'.$model->classname.' pageType"><img src="'.ClassInfo::getClassIcon($model->classname).'" alt="" /><span>';

		$html .= convert::raw2text(ClassInfo::getClassTitle($model->classname));
		
		// end of title in head-bar
		$html .= ' </span></span></div>';
		
		$form = new Form($this, "add_page");
		
		if(isset($_GET["parentid"]) && $_GET["parentid"] != 0) {
			$form->setResult(array(
				"parenttype"	=> "subpage",
				"parentid"		=> $_GET["parentid"]
			));
		} else {
			$form->setResult(array(
				"parenttype"	=> "root",
				"parentid"		=> 0
			));

		}
		
		$form->add(new HTMLField('headbar', $html));
		$form->add($title = new textField('title', lang("title_page", "title of the page")));
		$form->add($parenttype = new ObjectRadioButton("parenttype", lang("hierarchy", "hierarchy"), array(
				"root" => lang("no_parentpage", "Root Page"),
				"subpage" => array(
					lang("subpage","sub page"),
					"parent"
				)
			)));
		$form->add($parentDropdown = new HasOneDropDown("parent", lang("parentpage", "Parent Page"), "title", ' `pages`.`class_name` IN ("'.implode($allowed_parents, '","').'")'));
		$form->add(new HiddenField("class_name", $model->classname));
		
		$form->addValidator(new requiredFields(array('filename','title', 'parenttype')), "default_required_fields"); // valiadte it!
		$form->addValidator(new FormValidator(array($model, "validatePageType")), "pagetype");
		$form->addValidator(new FormValidator(array($model, "validatePageFileName")), "filename");
		
		// default submission
		$form->setSubmission("submit_form_generate");	
			
		$form->addValidator(new DataValidator($model), "datavalidator");
		
		if($model->can("Write"))
			$form->addAction(new AjaxSubmitButton("save_draft",lang("next_step", "next step"),"AjaxSaveGenerate"));
		
		return $form->render();
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
	
	/**
	 * generates mainbar-title and path for page.
	*/
	public function submit_form_generate($data) {
		$data["mainbartitle"] = $data["title"];
		$value = $data["title"];
		$value = trim($value);
		$value = strtolower($value);
		
		// special chars
		$value = str_replace("ä", "ae", $value);
		$value = str_replace("ö", "oe", $value);
		$value = str_replace("ü", "ue", $value);
		$value = str_replace("ß", "ss", $value);
		$value = str_replace("ù", "u", $value);
		$value = str_replace("û", "u", $value);
		$value = str_replace("ú", "u", $value);
		
		$value = str_replace(" ",  "-", $value);
		// normal chars
		$value = preg_replace('/[^a-zA-Z0-9\-\._]/', '-', $value);
		$value = str_replace('--', '-', $value);
		
		$parentid = ($data["parenttype"] == "root") ? 0 : $data["parentid"];
		$i = 1;
		$current = $value;
		while(DataObject::count("pages", array("parentid" => $parentid, "path" => $current)) > 0) {
			$i++;
			$current = $value . "-" . $i;
		}
		
		$data["path"] = $current;
		return $this->submit_form($data);
	}
	
	/**
	 * saves data for editing a site via ajax
	 *
	 *@name ajaxSave
	 *@access public
	 *@param array - data
	 *@param object - response
	*/
	public function ajaxSaveGenerate($data, $response) {
		$data["mainbartitle"] = $data["title"];
		$value = $data["title"];
		$value = trim($value);
		$value = strtolower($value);
		
		// special chars
		$value = str_replace("ä", "ae", $value);
		$value = str_replace("ö", "oe", $value);
		$value = str_replace("ü", "ue", $value);
		$value = str_replace("ß", "ss", $value);
		$value = str_replace("ù", "u", $value);
		$value = str_replace("û", "u", $value);
		$value = str_replace("ú", "u", $value);
		
		$value = str_replace(" ",  "-", $value);
		// normal chars
		$value = preg_replace('/[^a-zA-Z0-9\-\._]/', '-', $value);
		$value = str_replace('--', '-', $value);
		
		$parentid = ($data["parenttype"] == "root") ? 0 : $data["parentid"];
		$i = 1;
		$current = $value;
		$object = DataObject::get("pages", array("parentid" => $parentid, "path" => $current));
		$object->setVersion("state");
		while($object->count() > 0) {
			$i++;
			$current = $value . "-" . $i;
			$object->filter(array("parentid" => $parentid, "path" => $current));
		}
		
		$data["path"] = $current;
		return $this->ajaxSave($data, $response);
	}
}