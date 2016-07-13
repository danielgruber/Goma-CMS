<?php defined("IN_GOMA") OR die();
/**
 * Mock for Unit-Testing.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class MyTestModelForDataObjectFieldWrite extends DataObject {

    static $db = array(
        "blub" => "int(10)"
    );

    static $default = array(
        "blub" => 2
    );

    static $search_fields = false;
}
