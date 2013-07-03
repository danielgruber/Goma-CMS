<?php

defined("IN_GOMA") OR die();

$apps = {apps};

/*
 * sql-driver of this application
*/
$sql_driver = {sql_driver};

/*
 * development-mode
*/
$dev = {dev};

/*
 * this will be added on each url at the end
*/
$urlend = {urlend};

/*
 * defines if the report of includes/profile.php/ is more detailed or not
*/

$profile_detail = {profile_detail};

/*
 * you should activate this method to allow the browser store the sites in the cache and if the user presses the back-button, the browser load the data from the cache
*/
$browsercache = {browsercache};

/*
 * defines log-folder
*/
$logFolder = {logFolder};

/*
 * defines private key of this application
*/
$privateKey = {privateKey};

/*
 * defines SSL-private-key of this application
*/
$SSLprivateKey = {SSLprivateKey};

/*
 * defines SSL-public-key of this application
*/
$SSLpublicKey = {SSLpublicKey};

/*
 * default lang
*/
$defaultLang = {defaultLang};

/*
 * defines from which time of ms a query is logged as slow
 * -1 disables slow-query-logger
*/
$slowQuery = {slowQuery};