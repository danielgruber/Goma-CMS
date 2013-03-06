<?php
/**
 * Configuration File for Apache Webservers
 *@link http://goma-cms.org
 *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
 *@Copyright (C) 2009 - 2013  Goma-Team
 * last modified: 03.02.2013
 */

defined('IN_GOMA') OR die('<!-- restricted access -->');

$serverconfig = 'RewriteEngine on

RewriteBase ' . ROOT_PATH . '
	
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_URI} !^/system/application.php
RewriteRule (.*) system/application.php [QSA]
	
	
<IfModule mod_headers.c>
	<FilesMatch ".(jpg|jpeg|png|gif|swf|js|css)$">
		Header set Cache-Control "max-age=86400, public"
	</FilesMatch>
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\.(gdf|ggz)\.(css|js)$">
    Header append Vary Accept-Encoding
  </FilesMatch>
</IfModule>

AddEncoding x-gzip .ggz
AddEncoding deflate .gdf

<files *.plist>
	order allow,deny
	deny from all
</files>
	
ErrorDocument 404 '.ROOT_PATH .'system/application.php
ErrorDocument 500 '.ROOT_PATH .'system/templates/framework/500.html';
