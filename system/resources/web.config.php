<?php
/**
 * Configuration File for IIS Webservers
 *@link http://goma-cms.org
 *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
 *@Copyright (C) 2009 - 2013  Goma-Team
 * last modified: 03.02.2013
 */

defined('IN_GOMA') OR die('<!-- restricted access -->');

$serverconfig = '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Rewrite to system/application.php">
                    <match url="^system/application.php" negate="true" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="system/application.php" />
                </rule>
            </rules>
        </rewrite>
        <httpErrors errorMode="Custom">
            <remove statusCode="500" subStatusCode="-1" />
            <remove statusCode="404" subStatusCode="-1" />
            <error statusCode="404" prefixLanguageFilePath="" path="/system/application.php" responseMode="ExecuteURL" />
            <error statusCode="500" prefixLanguageFilePath="" path="/system/templates/framework/500.html" responseMode="ExecuteURL" />
        </httpErrors>
        <security>
            <requestFiltering>
                <fileExtensions>
                    <add fileExtension=".plist" allowed="false" />
                </fileExtensions>
            </requestFiltering>
        </security>
    </system.webServer>
</configuration>';
