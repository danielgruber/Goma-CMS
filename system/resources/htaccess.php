<?php
/**
 * Configuration File for Apache Webservers
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 03.02.2013
 */

defined('IN_GOMA') OR die('<!-- restricted access -->');

$serverconfig = 'RewriteEngine on

RewriteBase ' . ROOT_PATH . '
	
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_URI} !^/system/application.php
RewriteRule (.*) system/application.php [QSA]
	
<IfModule mod_expires.c>
	ExpiresActive On
	ExpiresByType image/jpg "access 1 year"
	ExpiresByType image/jpeg "access 1 year"
	ExpiresByType image/gif "access 1 year"
	ExpiresByType image/png "access 1 year"
	ExpiresByType text/css "access 1 month"
	ExpiresByType application/pdf "access 1 month"
	ExpiresByType text/x-javascript "access 1 month"
	ExpiresByType application/x-shockwave-flash "access 1 month"
	ExpiresByType image/x-icon "access 1 year"
	ExpiresDefault "access 2 days"
</IfModule>
	
<IfModule mod_headers.c>
	<FilesMatch ".(jpg|jpeg|png|gif)$">
		Header set Cache-Control "max-age=31536000, public"
	</FilesMatch>
	<FilesMatch ".(js|css|swf|pdf|ico)$">
		Header set Cache-Control "max-age=2678400, public"
	</FilesMatch>
	
	Header set Connection keep-alive
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
