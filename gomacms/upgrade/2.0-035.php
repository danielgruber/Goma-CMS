<?php defined("IN_GOMA") OR die();
// remove user-group-admin
@unlink(ROOT . APPLICATION . "/application/admin/usergroupAdmin.php");
@unlink(ROOT . APPLICATION . "/application/model/pages/phpPage.php");
@unlink(ROOT . APPLICATION . "/application/model/boxes/phpbox.php");