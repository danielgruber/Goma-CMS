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
    </system.webServer>
</configuration>';
