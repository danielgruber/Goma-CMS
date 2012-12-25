<?php
$sql = "TRUNCATE TABLE ".DB_PREFIX."permission";
SQL::Query($sql);
$sql = "TRUNCATE TABLE ".DB_PREFIX."permission_state";
SQL::Query($sql);
$sql = "TRUNCATE TABLE ".DB_PREFIX."many_many_permission_groups_group";
SQL::Query($sql);
if(ClassInfo::$appENV["app"]["name"] == "gomacms") {
	$sql = "UPDATE " . DB_PREFIX . "pages SET publish_permissionid = 0, edit_permissionid = 0, read_permissionid = 0";
	SQL::Query($sql);
}