<?php
defined("IN_GOMA") OR die();

/**
 * Loads Settings.
 *
 * @package Goma CMS
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 */


if(ClassInfo::ClassInfoHasBeenRegenerated()) {
    Resources::$gzip = settingsController::get("gzip");
    RegisterExtension::$enabled = settingsController::get("register_enabled");
    RegisterExtension::$validateMail = settingsController::get("register_email");
    RegisterExtension::$registerCode = settingsController::get("register");
}

Core::setCMSVar("ptitle", settingsController::get("titel"));
Core::setCMSVar("title", settingsController::get("titel"));
Core::setCMSVar("description", settingsController::get("meta_description"));
Core::setHeader("description", settingsController::Get("meta_description"));
Core::setHeader("robots", "index,follow");

$tpl = isset($_GET["settpl"]) ? $_GET["settpl"] : settingsController::Get("stpl");
Core::setTheme($tpl);
i18n::loadTPLLang(Core::getTheme());

if(settingsController::get("favicon")) {
    Core::$favicon = "./favicon.ico";
}

// check for permission for actions run at the top
if(isset($_GET["settpl"]) && !Permission::check("SETTINGS_ADMIN")) {
    throw new PermissionException("You are not allowed to change the template at runtime.");
}

if(settingsController::get("p_app_id") && settingsController::get("p_app_key") && settingsController::get("p_app_secret")) {
    try {
        PushController::initPush(settingsController::get("p_app_key"), settingsController::get("p_app_secret"), settingsController::get("p_app_id"));
    } catch(Exception $e) {}
}

if(settingsController::get("google_site_verification")) {
    $code = settingsController::get("google_site_verification");
    if(preg_match('/\<meta[^\>]+content\=\"([a-zA-Z0-9_\-]+)\"\s+\/\>/', $code, $matches)) {
        $code = $matches[1];
    }
    Core::setHeader("google-site-verification", convert::raw2xml($code));
}

date_default_timezone_set(Core::GetCMSVar("TIMEZONE"));
