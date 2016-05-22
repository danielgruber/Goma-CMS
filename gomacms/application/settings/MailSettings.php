<?php defined("IN_GOMA") OR die();
/**
 * Mail-Settings DataObject.
 *
 *	@package 	goma cms
 *	@link 		http://goma-cms.org
 *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *	@author 	Goma-Team
 * @Version 	1.0
 */
class MailSettings extends Newsettings {
	/**
	 * Database-Fields
	 *
	 *@name db
	 */
	static $db = array(
		"useSMTP"	        => "Switch",
		"smtp_host"  		=> "varchar(200)",
		"smtp_auth"			=> "Switch",
		"smtp_user"			=> "varchar(200)",
		"smtp_pwd"			=> "varchar(200)",
		"smtp_secure"		=> "varchar(200)",
		"smtp_port"			=> "int(10)",
		"smtp_from"			=> "varchar(200)"
	);

	static $default = array(
		"smtp_secure" 	=> "tls",
		"smtp_port"		=> "587"
	);

	public $tab = "{\$_lang_mail}";

	/**
	 * generates the Form for this.
	 *
	 *@name getForm
	 */
	public function getFormFromDB(&$form) {
		$form->add(new TextField("smtp_from", lang("smtp_from")));
		$form->add($radio = new ObjectRadioButton("useSMTP", lang("use_smtp"), array(
			0 => lang("no"),
			1 => array(
				lang("yes"),
				"smtpsettings"
			)
		)));

		$form->add($set = new FieldSet("smtpsettings", array(
			new TextField("smtp_host", lang("smtp_host")),
			new Checkbox("smtp_auth", lang("smtp_auth")),
			new TextField("smtp_user", lang("smtp_user")),
			new PasswordField("smtp_pwd", lang("smtp_pwd"), $this->smtp_pwd),
			new Select("smtp_secure", lang("smtp_secure"), array(
				"tls"	=> "TLS",
				"ssl"	=> "SSL"
			)),
			new NumberField("smtp_port", lang("smtp_port"))
		)));

		$v = new ViewAccessableData();
		$form->form()->add(new HTMLField("jstpl", $v->renderWith("settings/MailSettingsJavaScriptTPL.html")), 0);
		Resources::add(APPLICATION . "/application/settings/MailSettings.js");
		Resources::addData('var mailSettings_FieldSet = "'.$set->divID().'"; var mailSettings_Switch = "'.$radio->divID().'"; var mailSettings_authToken = "'.SMTPConnector::allowSMTPConnect().'";');
	}

	/**
	 * sets SMTP-Settings to mailer when creating a mail.
	 */
	public static function setSMTPSettings($mail, $mailer) {
		if(SettingsController::get("useSMTP")) {
			$mailer->isSMTP();
			$mailer->Host = SettingsController::get("smtp_host");
			$mailer->SMTPAuth = SettingsController::get("smtp_auth");
			$mailer->Username = SettingsController::get("smtp_user");
			$mailer->Password = SettingsController::get("smtp_pwd");
			$mailer->SMTPSecure = SettingsController::get("smtp_secure");
			$mailer->Port = SettingsController::get("smtp_port") ?: 587;
		}
	}

	/**
	 * sets sender to mailer when creating a mail.
	 */
	public static function setSender($mail, $mailer) {
		if(SettingsController::get("smtp_from")) {
			try {
				$parsed = Mail::parseSingleAddress(SettingsController::get("smtp_from"));
				$mailer->From = $mailer->Sender = $parsed[0];
				$mailer->FromName = $parsed[1];
			} catch(Exception $e) {

			}
		}
	}
}

Core::addToHook("mail_prepareMailer", array("MailSettings", "setSMTPSettings"));
Core::addToHook("mail_prepareSend", array("MailSettings", "setSender"));
