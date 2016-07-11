<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for all Model-Classes.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class AllModelTests extends GomaUnitTest
{
    static $area = "NModel";
    /**
     * name
     */
    public $name = "AllModel";

    public function testSearch() {
        foreach(ClassInfo::getChildren("DataObject") as $class) {
            if(StaticsManager::getStatic($class, "db")) {
                $fields = StaticsManager::getStatic($class, "search_fields");
                if($fields !== false) {
                    $this->assertNotEqual($fields, null, "Class $class does not define search-fields. %s");
                }
            }
        }
    }
}
