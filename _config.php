<?php

$apps = array(
	  array (
	    'directory' => 'mysite',
	  )
);

/**
 * this option allows you to speed up your site
 * It saves all generated content 60 second as a static html-page in the cache
 * Your Site should be much faster, BUT some data could be old and if you use data which mustn't be old on each page you shouldn't activate this feature
*/
$speedcache = false;

$sql_driver = "mysqli";

$dev = true;
/**
 * you should activate this method to allow the browser store the sites in the cache and if the user presses the back-button, the browser load the data from the cache
*/
$browsercache = true;

/**
 * this will be added on each url at the end
*/
$urlend = "/";

/**
 * defines if the report of ?profile=1 is more detailed or not
*/

$profile_detail = false;