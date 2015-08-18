<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for 503-Handling.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class SelectQueryTest extends GomaUnitTest implements TestAble {


    static $area = "framework";
    /**
     * name
     */
    public $name = "SelectQuery";


	/**
     * test availability functions.
     */
    public function testColiding() {
        /*$query = new SelectQuery("MyTestModelForDataObjectFieldWrite", array("myfield" => 0));
        $query->db_fields["myfield"] = "MyTestModelForDataObjectFieldWrite.myfield";

        echo $query->build();*/
    }
}