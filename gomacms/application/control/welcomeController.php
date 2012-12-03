<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 24.11.2012
  * $Version 1.3
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

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
		$data = new ViewAccessableData();
		return $data->customise(array("form" => $form->render()))->renderWith("welcome/step2.html");
	}
	/**
	 * step 3
	*/
	public function step3() {
		$form = new Form($this, "settings", array(
			new TextField("pagetitle", lang("title")),
			new Select("timezone", lang("timezone"), i18n::$timezones)
		), array(
			new FormAction("save", lang("save"))
		));
		$form->setSubmission("saveSettings");
		$form->addValidator(new RequiredFields(array("pagetitle")), "pagetitle");
		
		$data = new ViewAccessableData();
		return $data->customise(array("form" => $form->render()))->renderWith("welcome/step3.html");
	}
	/**
	 * user-creation
	*/
	public function user_create($result) {
		$data = new User();
		$data->nickname = $result["username"];
		$data->password = $result["password"]; // we don't need to hash, it's implemented in the user-model
		$data->email = $result["email"];
		$data->write(true, true);
		$data->groups()->add(DataObject::get_one("group", array("type" => 2)));
		$data->groups()->write(false, true);
		HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "step3/");
	}
	/**
	 * pwd-validation
	*/
	public function validatePassword($obj) {
		$result = $obj->form->result;
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
		$data = DataObject::get("newsettings", array("id" => 1));
		$data->titel = $result["pagetitle"];
		$data->timezone = $result["timezone"];
		$data->write(false, true);
		HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "finish/");
	}
	/**
	 * finishes the process
	 *
	 *@name finish
	 *@access public
	*/
	public function finish() {
		unset($_SESSION["welcome_screen"]);
		if(@fopen(APP_FOLDER . "application/WELCOME_RUN.php", "w")) {
			fclose(APP_FOLDER . "application/WELCOME_RUN.php");
		} else {
			throwError(6, "PHP-Error", "Write-Error: Could not write '" . CURRENT_PROJECT . "/application/WELCOME_RUN.php'. Please create this file for security reason!");
		}
		return tpl::render("welcome/finish.html");
	}
}