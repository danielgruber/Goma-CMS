<?php defined("IN_GOMA") OR die();

/**
  * @package goma cms
  * @link http://goma-cms.org
  * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  * @author Goma-Team
  * last modified: 24.11.2012
  * $Version 1.3
*/
class welcomeController extends Controller {
	/**
	 * allowed_actions
	*/
	public $allowed_actions = array(
		"step2",
		"step3",
		"finish"
	);
	/**
	 * index
	*/ 
	public function index() {
		Resources::add("default.css");
		$_SESSION["welcome_screen"] = true;
		// make some correction to database
		if(defined("SQL_LOADUP")) {
            // remake db
            $data = "";
            foreach(classinfo::getChildren("dataobject") as $value)
            {        
                    $obj = new $value;
                    
                    $data .= nl2br($obj->buildDB(DB_PREFIX));                        
            }
        }
        
        ClassInfo::write();
	       
		return tpl::render("welcome/welcome.html");
	}
	
	/**
	 * step 2
	*/
	public function step2() {
		Resources::add("default.css");
		$form = new Form($this, "user_create", array(
			new TextField("username", lang("username")),
			new TextField("email", lang("email")),
			new PasswordField("password", lang("password")),
			new PasswordField("repeat", lang("repeat"))
		), array(
			new FormAction("save", lang("save"))
		));
		$form->setSubmission("user_create");
		$form->addValidator(new FormValidator(array($this, "validatePassword")), "password");
		return $form->renderWith("welcome/step2.html");
	}
	/**
	 * step 3
	*/
	public function step3() {
		Resources::add("default.css");
		$form = new Form($this, "settings", array(
			new TextField("pagetitle", lang("title")),
			new Select("timezone", lang("timezone"), i18n::$timezones)
		), array(
			new FormAction("save", lang("save"))
		));
		$form->setSubmission("saveSettings");
		$form->addValidator(new RequiredFields(array("pagetitle")), "pagetitle");

		return $form->renderWith("welcome/step3.html");
	}
	/**
	 * user-creation
	*/
	public function user_create($result) {
		$data = new User();
		$data->nickname = $result["username"];
		$data->name = $result["username"];
		$data->password = $result["password"]; // we don't need to hash, it's implemented in the user-model
		$data->email = $result["email"];
		$data->writeToDB(true, true);
		$data->groups()->add(DataObject::get_one("group", array("type" => 2)));
		$data->groups()->commitStaging(false, true);
		return GomaResponse::redirect(BASE_URI . BASE_SCRIPT . "step3/");
	}
	/**
	 * pwd-validation
	*/
	public function validatePassword($obj) {
		$result = $obj->getForm()->result;
		if($result["password"] != $result["repeat"] && $result["password"] != "") {
			return lang("passwords_not_match");
		} else if(empty($result["username"])) {
			return lang("form_required_fields") . ' "' .  lang("username") . '"';
		} else if(empty($result["email"])) {
			return lang("form_required_fields") . ' "' .  lang("email") . '"';
		} else {
			return true;
		}
	}
	/**
	 * saves settings
	*/ 
	public function saveSettings($result) {
		$data = DataObject::get_one("newsettings", array("id" => 1));
		$data->titel = $result["pagetitle"];
		$data->timezone = $result["timezone"];
		$data->writeToDB(false, true);
		return GomaResponse::redirect(BASE_URI . BASE_SCRIPT . "finish/");
	}
	/**
	 * finishes the process
	*/
	public function finish() {
		Resources::add("default.css");
		unset($_SESSION["welcome_screen"]);
		if(@fopen(APP_FOLDER . "application/WELCOME_RUN.php", "w")) {
			fclose(APP_FOLDER . "application/WELCOME_RUN.php");
		} else {
			throw new FileException("Write-Error: Could not write '" . CURRENT_PROJECT . "/application/WELCOME_RUN.php'. Please create this file for security reason!");
		}
		return tpl::render("welcome/finish.html");
	}
}