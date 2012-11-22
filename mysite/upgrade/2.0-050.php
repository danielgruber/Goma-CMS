<?php
// remove user-group-admin
@unlink(ROOT . APPLICATION . "/application/admin/usergroup.php");
@unlink(ROOT . APPLICATION . "/application/model/pages/phpPage.php");
@unlink(ROOT . APPLICATION . "/application/model/boxes/phpbox.php");