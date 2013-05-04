<?php
@unlink(ROOT . "system/libs/gd/image.php");
file_put_contents(ROOT . "system/tests/autoloader_non_dev_exclude", "1");
file_put_contents(ROOT . "system/libs/thirdparty/simpletest/autoloader_exclude", "1");
file_put_contents(ROOT . "system/core/model/view/autoloader_exclude", "1");