<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 26.03.2014
  * $Version 1.3.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Boxes extends DataObject implements Notifier {
	
	/**
	 * title of this dataobject
	*/
	public static $cname = '{$_lang_boxes}';
	
	/**
	 * enable versions
	 *
	 *@name versioned
	*/
	static $versions = true;
	
	/**
	 * some database fields
	*/
	static $db = array(
		"title"		=> "varchar(100)",
		"text"		=> "HTMLtext",
		"border"	=> "switch",
		"sort"		=> "int(3)",
		"seiteid"	=> "varchar(50)",
		"width"		=> "varchar(5)",
		"fullsized"	=> "switch",
		"usebgcolor"=> "switch",
		"color"		=> "varchar(200)",
		"cssclass"	=> "varchar(200)"
	);
	
	/**
	 * some searchable fields
	*/
	static $search_fields = array(
		"text",
		"title"
	);
	
	/**
	 * for performance, some indexes
	*/
	static $index = array(
		"view"	=> array("type"	=> "INDEX", "fields" => "seiteid,sort", "name"	=> "_show")
	);
	
	/**
	 * sort
	*/
	static $default_sort = "sort ASC";
	
	/**
	 * generates the form to add boxes
	*/
	public function getForm(&$form) {
		$insertAfter = (isset($_GET["insertafter"])) ? ++$_GET["insertafter"] : 1000;
		$form->add(new Hiddenfield("sort", $insertAfter));
		$form->add(new HiddenField("seiteid", $this->seiteid));
		$form->add(new Select("class_name", lang("BOXTYPE", "boxtype"), $this->getBoxTypes()));
		$form->add(new Hiddenfield("width", "auto"));
	}
	
	/**
	 * generates form-actions
	*/
	public function getActions(&$form) {
		$form->addAction(new CancelButton("cancel", lang("cancel")));
		
		$lang = ($this->ID == 0) ? lang("CREATE_BOX", "Create box") : lang("SAVE_BOX", "Save box");
		
		if(Core::is_ajax()) {
			$form->addAction(new AjaxSubmitButton("submit", $lang, "ajaxSave", "publish", array("green")));
		} else {
			$form->addAction(new FormAction("submit", $lang, "publish", array("green")));
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
			$boxes[$class] = ClassInfo::getClassTitle($class);
		}
		return $boxes;
	}
	
	/**
	 * gets the width
	 *
	 *@name getWidth
	 *@access public
	*/
	public function getWidth() {
		if($this->fieldGet("width") == "auto") {
			return "100%";
		} else if(preg_match('/^[0-9\s]+$/', $this->fieldGet("width"))) {
			return $this->fieldGet("width") . "px";
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
		$class = ($this->fieldGet("border")) ? "box_with_border " : "";
		if($this->fullsized) {
			$class .= "fullsized";
		}
		
		$class .= " " . $this->cssclass;
		
		return $class;
	}
	
	/**
	 * returns color when activated
	*/
	public function getColor() {
		return ($this->usebgcolor) ? $this->fieldGet("color") : "";
	}
	
	/**
	 * returns background-image.
	*/
	public function getBG() {
		if($this->background) {
			return 'url('.$this->background()->raw().')';
		}
		
		return '';
	}
	
	/**
	 * returns if edit is on
	 *
	 *@name canWrite
	*/
	public function canWrite($row) {
		$data = DataObject::get_by_id("pages", $row->seiteid);
		if($data && $data->can("Write")) {
			return true;
		}
		
		return Permission::check("PAGES_WRITE");
	}
	
	/**
	 * returns if deletion is allowed
	 *
	 *@name canDelete
	*/
	public function canDelete($row = null)
	{
		$data = DataObject::get_by_id("pages", $row->seiteid);
		if($data && $data->can("Delete")) {
			return true;
		}
		
		return Permission::check("PAGES_DELETE");
	}
	
	/**
	 * returns if inserting is allowed
	 *
	 *@name canInsert
	*/
	public function canInsert($row = null)
	{	
		$data = DataObject::get_by_id("pages", $row->seiteid);
		if($data && $data->can("Insert")) {
			return true;
		}
		
		return Permission::check("PAGES_INSERT");
	}
	
	/**
	 * returns information about notification-settings of this class
	 * these are:
	 * - title
	 * - icon
	 * this API may extended with notification settings later
	 * 
	 *@name NotifySettings
	 *@access public
	*/
	public static function NotifySettings() {
		return array("title" => lang("boxes"), "icon" => "images/icons/fatcow16/layout_content@2x.png");
	}
		
	
}

class BoxesController extends FrontedController {
	/**
	 * some urls
	 *
	 *@name url_handlers
	 *@access public
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
	 *
	 *@name allowed_actions
	 *@access public
	*/
	public $allowed_actions = array(
		"add"			=> "->canEdit",
		"edit"			=> "->canEdit",
		"delete"		=> "->canEdit",
		"saveBoxWidth"	=> "->canEdit",
		"saveBoxOrder"	=> "->canEdit"
	);
	
	/**
	 * returns if edit is on
	 *
	 *@name canEdit
	*/
	public function canEdit() {

		$data = DataObject::get_by_id("pages", $this->getParam("pid"));
		if($data && $data->can("Write")) {
			return true;
		}
		
		return Permission::check("PAGES_WRITE");
	}
	
	/**
	 * renders boxes
	 *
	 *@name renderBoxes
	 *@access public
	 *@param string - id
	*/
	public static function renderBoxes($id, $count = null)
	{
			$data = DataObject::get("boxes", array("seiteid" => $id));
			return $data->controller()->render($id, $count);
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
		$data = DataObject::get_by_id("boxes", $this->getParam("id"));
		if($data) {
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
			if($data = DataObject::get_by_id("boxes", $id)) {
				$data->sort = $sort;
				$data->write();
			}
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
	final public function render($pid = null, $count = null) {
		if(isset($pid)) {
			$data = DataObject::get("boxes", array("seiteid" => $pid));
			$this->model_inst = $data;
		}
		
		return $this->modelInst()->customise(array("pageid" => $pid, "boxlimit" => $count))->renderWith("boxes/boxes.html");
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
		return '<div class="error">' . lang("less_rights") . '</div>';
	}
	
	/**
	 * saves via ajax
	 *
	 *@name ajaxSave
	 *@access public
	*/ 
	public function ajaxSave($data, $response) {
		if($this->save($data, 2) !== false)
		{
			Notification::notify("boxes", lang("box_successful_saved", "The data was successfully written!"), lang("saved"));
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

	public function isCacheable() {
		return false;
	}
}


class Box extends Boxes
{
		/**
		 * the name of the box with language, e.g. {$_lang_textarea}
		 *@name name
		 *@var string
		*/
		public static $cname = '{$_lang_textarea}';
		
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		static $db = array();
		
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		static $has_one = array(
			"background"	=> "ImageUploads"
		);
		
		/**
		 * don't use from parent-class
		 * there would be much tables, which we don't need
		*/
		static $many_many = array();
		
		/**
		 * prefix of table
		 *
		 *@name prefix
		*/
		public $prefix = "Box_";
		
		/**
		 * get Edit-form
		 *@name getEditForm
		 *@param object
		 *@param string
		*/
		public function getForm(&$form)
		{
			$form->add(new HiddenField("seiteid", $this->seiteid));
			
			$form->add(new TabSet("tabs", array(
				new Tab("content", array(),  lang("content")),
				new Tab("settings", array(),  lang("settings"))
			)));
			$form->add(new TextField("title", lang("box_title")), null, "content");
			if($this->RecordClass == "box") {
				if($this->fullsized) {
					$width = "";
				} else if(strpos($this->width, "%") === false) {
					$width = $this->width;
				} else {
					$width = "";
				}
				
				$form->add(new HTMLEditor("text", lang("content"), null, null, $width), null, "content");
				$form->add(new ImageUpload("background", lang("bgimage")), null, "settings");
			}
			
			$form->add(new AutoFormField("border", lang("border")), null, "settings");
			$form->add(new AutoFormField("fullsized", lang("fullwidth")), null, "settings");
			$form->add(new AutoFormField("usebgcolor", lang("color")), null, "settings");
			$form->add(new ColorPicker("color", lang("color")), null, "settings");
			$form->add(new TextField("cssclass", lang("cssclass")), null, "settings");
			$form->add(new TextField("width", lang("width")), null, "settings");
			$form->add(new HTMLField("spacer", '<div style="width: 600px;">&nbsp;</div>'));
		}
		
		public function getContent()
		{
				return $this->text()->forTemplate();
		}

		public function isCacheable() {
			if($this->class == "box") 
				return true;

			return false;
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
		public static $cname = '{$_lang_login_myaccount}';
		
		/**
		 * renders the box
		 *
		 *@name getContent
		*/
		public function getContent()
		{
				 return tpl::render('boxes/login.html',array('users_online'	=> LiveCounterController::countMembersOnline()));
		}
}

class BoxesTplExtension extends Extension {
	/**
	 * extra methods
	*/
	public static $extra_methods = array(
		"boxes"
	);
	
	public function boxes($name, $count = null) {
		return BoxesController::renderBoxes($name, $count);
	}
}

Object::extend("tplCaller", "BoxesTPLExtension");

/**
 * boxpage
*/
class boxpage extends Page
{
		public static $cname = '{$_lang_boxes_page}';
		
		/**
		 * pretty nice icon for that
		*/
		public static $icon = "images/icons/fatcow-icons/16x16/layout_content.png";
		
		/**
		 * gets a object of this record with id and versionid set to 0
		 *
		 *@name duplicate
		 *@access public
		*/
		public function duplicate() {
			$new = parent::duplicate();
			
			$new->boxes_seite_id = $this->id;
			
			return $new;
		}
		
		/**
		 * will be called after write.
		 * duplicate boxes here.
		 *
		 * @name onAfterWrite
		 * @access public
		*/
		public function onAfterWrite()
		{

			if($this->boxes_seite_id && $this->id != $this->boxes_seite_id) {
				$data = DataObject::get("boxes", array("seiteid" => $this->boxes_seite_id));
				
				foreach($data as $record) {
					$new = $record->duplicate();
					$new->seiteid = $this->id;
					$new->write(true, true);
				}
			}
		}
		/**
		 * we render boxes if it is already created
		*/
		public function getForm(&$form)
		{
				parent::getForm($form);
				
				if($this->path != "")
				{
						$boxes = boxesController::renderBoxes($this->id);
				} else
				{
						$boxes = "";
				}
				$form->add(new HTMLField("boxes", $boxes . '<div style="clear: both;"></div>'), null, "content");
		}
		
		/**
		 * renders all boxes
		*/
		public function getBoxes()
		{
				return BoxesController::renderBoxes($this->id);
		}

}
class boxPageController extends PageController
{
		/**
		 * template of this controller
		 *@var string
		*/
		public $template = "pages/box.html";
		
		
		/**
		 * generates a button switch-view
		 *
		 *@name frontedBar
		 *@access public
		*/
		public function frontedBar() {
			$arr = parent::frontedBar();
			
			if($this->modelInst()->can("Write")) {
			
				if(isset($_SESSION["adminAsUser"])) {
					$arr[] = array(
							"url" 			=> BASE_SCRIPT . "system/switchview" . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"]),
							"title"			=> lang("switch_view_edit_on", "enable edit-mode")
						);
				} else {
					$arr[] = array(
							"url" 			=> BASE_SCRIPT . "system/switchview" . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"]),
							"title"			=> lang("switch_view_edit_off", "disable edit-mode")
						);
				}
			}
			
			return $arr;
		}
}