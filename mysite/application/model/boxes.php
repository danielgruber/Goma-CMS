<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 24.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Boxes extends DataObject {
	/**
	 * controller
	*/
	public $controller = "boxesController";
	/**
	 * some database fields
	*/
	public $db_fields = array(
		"title"		=> "varchar(100)",
		"text"		=> "text",
		"border"	=> "switch",
		"sort"		=> "int(3)",
		"seiteid"	=> "varchar(50)",
		"width"		=> "varchar(5)"
	);
	/**
	 * some searchable fields
	*/
	public $searchable_fields = array(
		"text",
		"title"
	);
	/**
	 * for performance, some indexes
	*/
	public $indexes = array(
		"view"	=> array("type"	=> "INDEX", "fields" => "seiteid,sort", "name"	=> "_show")
	);
	/**
	 * orderby
	*/
	public $orderby = array(
		"field"	=> "sort",
		"type"	=> "ASC"
	);
	/**
	 * generates the form to add boxes
	*/
	public function getForm(&$form) {
		$insertAfter = (isset($_GET["insertafter"])) ? ++$_GET["insertafter"] : 1000;
		$form->add(new Hiddenfield("sort", $insertAfter));
		$form->add(new HiddenField("seiteid", $this->seiteid));
		$form->add(new Select("class_name", lang("boxtype", "boxtype"),$this->getBoxTypes()));
		$form->add(new Hiddenfield("width", "auto"));
	}
	/**
	 * generates form-actions
	*/
	public function getActions(&$form) {
		$form->addAction(new FormAction("cancel", lang("cancel")));
		if(Core::is_ajax()) {
			$form->addAction(new AjaxSubmitButton("submit", lang("save"), "ajaxSave"));
		} else {
			$form->addAction(new FormAction("submit", lang("save")));
		}
	}
	/**
	 * gets all available types
	 *
	 *@name getBoxTypes
	 *@access public
	*/
	public function getBoxTypes() {
		$available_types = ClassInfo::getChildren("boxes");
		$boxes = array();
		foreach($available_types as $class) {
			$c = new $class;
			$boxes[$class] = parse_lang($c->name);
		}
		return $boxes;
	}
	/**
	 * permissions
	*/
	public function providePermissions()
	{
			return array(
				"BOXES_ALL"	=> array(
					"title"		=> '{$_lang_admin_boxes}',
					"default"	=> 7
				)
			);
	}
	/**
	 * gets the width
	 *
	 *@name getWidth
	 *@access public
	*/
	public function getWidth() {
		if($this->fieldGet("width") == "auto") {
			return "100";
		} else {
			return $this->fieldGet("width");
		}
	}
	/**
	 * gets the class box_with_border if border is set
	 *
	 *@name getborder_class
	 *@access public
	*/
	public function getborder_class() {
		return ($this->fieldGet("border")) ? "box_with_border" : "";
	}
	
	
}

class BoxesController extends FrontedController {
	/**
	 * some actions
	*/
	public $url_handlers = array(
		"\$pid!/add"				=> "add",
		"\$pid!/edit/\$id!"			=> "edit",
		"\$pid!/delete/\$id"		=> "delete",
		"\$pid!/saveBoxWidth/\$id"	=> "saveBoxWidth",
		"\$pid!/saveBoxOrder"		=> "saveBoxOrder"
	);
	/**
	 * rights
	*/
	public $allowed_actions = array(
		"add"			=> "->canEdit",
		"edit"			=> "->canEdit",
		"delete"		=> "->canEdit",
		"saveBoxWidth"	=> "->canEdit",
		"saveBoxOrder"	=> "->canEdit"
	);
	/**
	 * renders boxes
	 *@name renderBoxes
	 *@access public
	 *@param string - id
	*/
	public static function renderBoxes($id)
	{
			$data = DataObject::get("boxes", array("seiteid" => $id));
			return $data->controller()->render();
	}
	/**
	 * returns if edit is on
	 *
	 *@name canEdit
	*/
	public function canEdit() {
		if(!Permission::check("BOXES_ALL"))
			return false;
		
		if(_ereg("^[0-9]+$", $this->getParam("pid"))) {
			$data = DataObject::get("pages", array("id" => $this->getParam("pid")));
			if(!$data->canWrite())
				return false;
			
		}
		
		return true;
	}
	
	/**
	 * edit-functionallity
	 *
	 *@name edit
	 *@access public
	*/
	public function edit() {
		Core::setTitle(lang("edit"));
		return parent::Edit();
	}
	
	/**
	 * add-functionality
	 *
	 *@name add
	 *@access public
	*/
	public function add() {
		$boxes = new Boxes(array(
			"seiteid" => $this->getParam("pid")
		));
		return $this->form("add", $boxes);
	}
	/**
	 * saves box width
	 *
	 *@name saveBoxWidth
	 *@access public
	*/
	public function saveBoxWidth() {
		$data = DataObject::get("boxes", array("id" => $this->getParam("id")));
		if($data->count > 0) {
			$data->width = $_POST["width"];
			if($data->write()) {
				return true;
			}
		}
		HTTPResponse::sendHeader();
		exit;
	}
	/**
	 * saves box orders
	 *
	 *@name saveBoxOrder
	 *@access public
	*/
	public function saveBoxOrder() {
		foreach($_POST["box_new"] as $sort => $id) {
			$data = DataObject::get("boxes", array("id" => $id));
			$data->sort = $sort;
			$data->write();
		}
		HTTPResponse::sendHeader();
		exit;
	}
	/**
	 * renders boxes
	 *
	 *@name render
	 *@access public
	*/
	final public function render($pid = null) {
		if(isset($pid)) {
			$this->modelInst(DataObject::get("boxes", array("seiteid" => $pid)));
		}
		return $this->modelInst()->customise(array("id" => $this->model_inst->seiteid))->renderWith("boxes/boxes.html");
	}
	/**
	 * hides the deleted object
	 *
	 *@name hideDeletedObject
	 *@access public
	*/
	public function hideDeletedObject($response, $data) {
		 
		$response->exec('$("#box_new_'.$data["id"].'").hide(300, function(){
			$(this).remove();
			if($("#boxes_new_'.$data["seiteid"].'").find(" > .box_new").length == 0) {
				$("#boxes_new_'.$data["seiteid"].'").html("'.convert::raw2js(BoxesController::RenderBoxes($data["seiteid"])).'");
			}
		});');
		return $response;
	}
	/**
	 * index
	*/
	public function index() {
		$this->redirectBack();
	}
	/**
	 * saves via ajax
	 *
	 *@name ajaxSave
	 *@access public
	*/ 
	public function ajaxSave($data, $response) {
		if($this->save($data) !== false)
		{
			//$response->exec(new Dialog(lang("successful_saved", "The data was successfully written!"), lang("okay"), 3));
			$response->exec('$("#boxes_new_'.convert::raw2js($data["seiteid"]).'").html("'.convert::raw2js(BoxesController::renderBoxes($data["seiteid"])).'");');
			$response->exec('dropdownDialog.get(ajax_button.parents(".dropdownDialog").attr("id")).hide();');
			return $response->render();
		} else
		{
			
			$response->exec(new Dialog(lang("mysql_error"), lang("error"), 5));
			return $response->render();
		}
	}
}


class Box extends Boxes
{
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		public $db_fields = array();
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		public $has_one = array();
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		public $many_many = array();
		public $prefix = "Box_";
		/**
		 * the name of the box with language, e.g. {$_lang_textarea}
		 *@name name
		 *@var string
		*/
		public $name = '{$_lang_textarea}';
		/**
		 * get Edit-form
		 *@name getEditForm
		 *@param object
		 *@param string
		*/
		public function getForm(&$form)
		{
			$form->add(new HiddenField("seiteid", $this->seiteid));
			$form->add(new TextField("title", lang("box_title")));
			if($this->RecordClass == "box")
				$form->add(new HTMLEditor("text", lang("content")));
			$form->add(new AutoFormField("border", lang("border")));
			$form->add(new HTMLField("spacer", '<div style="width: 600px;">&nbsp;</div>'));
		}
		
		public function getText()
		{
				return $this->data['text'];
		}
		public function getContent()
		{
				return $this->data['text'];
		}
}
/**
 * controller
*/
class boxController extends BoxesController
{

}

class login_meinaccount extends Box
{
		public $name = '{$_lang_login_myaccount}';
		public function getContent()
		{
				 return tpl::render('boxes/login.html',array('new_messages' => PMController::countNew(), 'users_online'	=> LiveCounterController::countMembersOnline()));
		}
}

class BoxesTplExtension extends Extension {
	/**
	 * extra methods
	*/
	public static $extra_methods = array(
		"boxes"
	);
	
	public function boxes($name) {
		return BoxesController::renderBoxes($name);
	}
}

Object::extend("tplCaller", "BoxesTPLExtension");

/**
 * boxpage
*/
class boxpage extends Page
{
		public $name = '{$_lang_boxes_page}';
		public function getForm(&$form)
		{
				parent::getForm($form);
				
				if($this->path != "")
				{
						$boxes = boxesController::renderBoxes($this->id, false, true);
				} else
				{
						$boxes = "";
				}
				$form->add(new HTMLField("boxes", $boxes . '<div style="clear: both;"></div>'),0, "content");
		}
		public function getBoxes()
		{
				return BoxesController::renderBoxes($this->fieldGet("id"));
		}
}
class boxPageController extends PageController
{
		/**
		 * template of this controller
		 *@var string
		*/
		public $template = "pages/box.html";
}


Autoloader::$loaded["box"] = true;
Autoloader::$loaded["boxcontroller"] = true;
Autoloader::$loaded["login_meinaccount"] = true;
Autoloader::$loaded["boxes"] = true;
Autoloader::$loaded["boxescontroller"] = true;
Autoloader::$loaded["boxpage"] = true;
Autoloader::$loaded["boxpagecontroller"] = true;