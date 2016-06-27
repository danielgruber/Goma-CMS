<?php defined("IN_GOMA") OR die();

/**
 * @package goma cms
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 *
 * @property string seiteid
 */
class Boxes extends DataObject implements Notifier {

	/**
	 * title of this dataobject
	 */
	public static $cname = '{$_lang_boxes}';

	/**
	 * enable versions
	 *
	 * @name versioned
	 */
	static $versions = true;

	/**
	 * some database fields
	 */
	static $db = array(
		"title"      => "varchar(100)",
		"text"       => "HTMLtext",
		"border"     => "switch",
		"sort"       => "int(3)",
		"seiteid"    => "varchar(50)",
		"width"      => "varchar(5)",
		"fullsized"  => "switch",
		"usebgcolor" => "switch",
		"color"      => "varchar(200)",
		"cssclass"   => "varchar(200)",
		"linktype"   => "varchar(200)",
		"link"       => "varchar(300)"
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
		"view" => array("type" => "INDEX", "fields" => "seiteid,sort", "name" => "_show")
	);

	/**
	 * has-one.
	 */
	static $has_one = array(
		"linkpage" => "pages"
	);

	/**
	 * sort
	 */
	static $default_sort = "sort ASC";

	/**
	 * generates the form to add boxes
	 */
	public function getForm(&$form)
	{
		$insertAfter = (isset($_GET["insertafter"])) ? ++$_GET["insertafter"] : 1000;
		$form->add(new Hiddenfield("sort", $insertAfter));
		$form->add(new HiddenField("seiteid", $this->seiteid));
		$form->add(new Select("class_name", lang("BOXTYPE", "boxtype"), $this->getBoxTypes()));
		$form->add(new Hiddenfield("width", "auto"));
	}

	/**
	 * generates form-actions
	 */
	public function getActions(&$form)
	{
		$form->addAction(new CancelButton("cancel", lang("cancel")));

		$lang = ($this->ID == 0) ? lang("CREATE_BOX", "Create box") : lang("SAVE_BOX", "Save box");

		if (Core::is_ajax()) {
			$form->addAction(new AjaxSubmitButton("submit", $lang, "ajaxSave", "publish", array("green")));
		} else {
			$form->addAction(new FormAction("submit", $lang, "publish", array("green")));
		}
	}

	/**
	 * gets all available types
	 */
	public function getBoxTypes()
	{
		$available_types = ClassInfo::getChildren("boxes");
		$boxes = array();
		foreach ($available_types as $class) {
			$boxes[$class] = ClassInfo::getClassTitle($class);
		}

		return $boxes;
	}

	/**
	 * gets the width
	 */
	public function getWidth()
	{
		if ($this->fieldGet("width") == "auto") {
			return "100%";
		} else if (preg_match('/^[0-9\s]+$/', $this->fieldGet("width"))) {
			return $this->fieldGet("width") . "px";
		} else {
			return $this->fieldGet("width");
		}
	}

	/**
	 * gets the class box_with_border if border is set
	 */
	public function getborder_class()
	{
		$class = ($this->fieldGet("border")) ? "box_with_border " : "";
		if ($this->fullsized) {
			$class .= "fullsized";
		}

		$class .= " " . $this->cssclass;

		return $class;
	}

	/**
	 * returns color when activated
	 */
	public function getColor()
	{
		return ($this->usebgcolor) ? $this->fieldGet("color") : "";
	}

	/**
	 * returns background-image.
	 */
	public function getBG()
	{
		if ($this->background) {
			return 'url(' . $this->background()->raw() . ')';
		}

		return '';
	}

	/**
	 * returns if edit is on
	 * @param Boxes|null $row
	 * @return bool
	 */
	public function canWrite($row)
	{
		$data = DataObject::get_by_id("pages", $row->seiteid);
		if ($data && $data->can("Write")) {
			return true;
		}

		return Permission::check("PAGES_WRITE");
	}

	/**
	 * returns if deletion is allowed
	 * @param Boxes $row
	 * @return bool
	 */
	public function canDelete($row = null)
	{
		$data = DataObject::get_by_id("pages", $row->seiteid);
		if ($data && $data->can("Delete")) {
			return true;
		}

		return Permission::check("PAGES_DELETE");
	}

	/**
	 * returns if inserting is allowed
	 * @param Boxes $row
	 * @return bool
	 */
	public function canInsert($row = null)
	{
		$data = DataObject::get_by_id("pages", $row->seiteid);
		if ($data && $data->can("Insert")) {
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
	 */
	public static function NotifySettings()
	{
		return array("title" => lang("boxes"), "icon" => "images/icons/fatcow16/layout_content@2x.png");
	}

	/**
	 * returns a link or nothing when no link given.
	 */
	public function linkURL()
	{
		if ($this->linkType == "url") {
			if (!preg_match('/^http(s)?\:\/\//i', $this->fieldGet("link")) && trim($this->fieldGet("link")) != "") {
				return "http://" . $this->fieldGet("link");
			}

			return $this->fieldGet("link");
		} else if ($this->linkType == "page") {
			return BASE_SCRIPT . $this->linkpage->url;
		} else {
			return "";
		}
	}


}


class Box extends Boxes {
	/**
	 * the name of the box with language, e.g. {$_lang_textarea}
	 * @name name
	 * @var string
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
		"background" => "ImageUploads"
	);

	/**
	 * don't use from parent-class
	 * there would be much tables, which we don't need
	 */
	static $many_many = array();

	/**
	 * prefix of table
	 *
	 * @name prefix
	 */
	public $prefix = "Box_";

	/**
	 * get Edit-form
	 * @name getEditForm
	 * @param object
	 * @param string
	 */
	public function getForm(&$form)
	{
		$form->add(new HiddenField("seiteid", $this->seiteid));

		$form->add(new TabSet("tabs", array(
			new Tab("content", array(), lang("content")),
			new Tab("settings", array(), lang("settings"))
		)));
		$form->add(new TextField("title", lang("box_title")), null, "content");
		if ($this->classname == "box") {
			if ($this->fullsized) {
				$width = "";
			} else if (strpos($this->width, "%") === false) {
				$width = $this->width;
			} else {
				$width = "";
			}

			$form->add(new HTMLEditor("text", lang("content"), null, null, $width), null, "content");
			$form->add(new ImageUploadField("background", lang("bgimage")), null, "settings");
		}

		$form->add(new CheckBox("border", lang("border")), null, "settings");
		$form->add(new CheckBox("fullsized", lang("fullwidth")), null, "settings");
		$form->add(new CheckBox("usebgcolor", lang("bgcolor")), null, "settings");
		$form->add(new ColorPicker("color", lang("color")), null, "settings");
		$form->add(new TextField("cssclass", lang("cssclass")), null, "settings");
		$form->add(new TextField("width", lang("width")), null, "settings");

		$form->add(new ObjectRadioButton("linktype", lang("box_linktype"), array(
			""     => lang("no_link"),
			"url"  => array(lang("URL"), "link"),
			"page" => array(lang("page"), "linkpage")
		)), null, "settings");
		$form->add(new TextField("link", lang("url")), null, "settings");
		$form->add(new HasOneDropdown("linkpage", lang("page")), null, "settings");

		// used to have big enough boxes for editing.
		$form->add(new HTMLField("spacer", '<div style="width: 600px;">&nbsp;</div>'));
	}

	public function getContent()
	{
		return $this->text()->forTemplate();
	}

	public function isCacheable()
	{
		if ($this->classname == "box")
			return true;

		return false;
	}
}

/**
 * controller
 */
class boxController extends BoxesController {

}

class login_meinaccount extends Box {
	public static $cname = '{$_lang_login_myaccount}';

	/**
	 * renders the box
	 */
	public function getContent()
	{
		return tpl::render('boxes/login.html');
	}
}

class BoxesTplExtension extends Extension {
	/**
	 * extra methods
	 */
	public static $extra_methods = array(
		"boxes"
	);

	public function boxes($name, $count = null)
	{
		return BoxesController::renderBoxes($name, $count);
	}
}

gObject::extend("tplCaller", "BoxesTPLExtension");

/**
 * boxpage
 */
class boxpage extends Page {
	public static $cname = '{$_lang_boxes_page}';

	/**
	 * pretty nice icon for that
	 */
	public static $icon = "images/icons/fatcow-icons/16x16/layout_content.png";

	/**
	 * gets a object of this record with id and versionid set to 0
	 */
	public function duplicate()
	{
		$new = parent::duplicate();

		$new->boxes_seite_id = $this->id;

		return $new;
	}

	/**
	 * duplicates boxes when a page was duplicated.
	 *
	 * @name    onAfterWrite
	 * @access    public
	 */
	public function onAfterWrite($modelWriter)
	{
		parent::onAfterWrite($modelWriter);

		if ($this->boxes_seite_id && $this->id != $this->boxes_seite_id) {
			$data = DataObject::get("boxes", array("seiteid" => $this->boxes_seite_id));

			/** @var Box $record */
			foreach ($data as $record) {
				$new = $record->duplicate();
				$new->seiteid = $this->id;
				$new->writeToDB(true, true);
			}
		}
	}

	/**
	 * we render boxes if it is already created
	 */
	public function getForm(&$form)
	{
		parent::getForm($form);

		if ($this->path != "") {
			$boxes = boxesController::renderBoxes($this->id);
		} else {
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

class boxPageController extends PageController {
	/**
	 * template of this controller
	 * @var string
	 */
	public $template = "pages/box.html";


	/**
	 * generates a button switch-view
	 */
	public function frontedBar()
	{
		$arr = parent::frontedBar();

		if ($this->modelInst()->can("Write")) {

			if (GlobalSessionManager::globalSession()->hasKey(SystemController::ADMIN_AS_USER)) {
				$arr[] = array(
					"url"   => BASE_SCRIPT . "system/switchview" . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"]),
					"title" => lang("switch_view_edit_on", "enable edit-mode")
				);
			} else {
				$arr[] = array(
					"url"   => BASE_SCRIPT . "system/switchview" . URLEND . "?redirect=" . urlencode($_SERVER["REQUEST_URI"]),
					"title" => lang("switch_view_edit_off", "disable edit-mode")
				);
			}
		}

		return $arr;
	}
}
