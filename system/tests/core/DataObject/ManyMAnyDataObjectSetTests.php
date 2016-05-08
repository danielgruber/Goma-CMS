<?php defined("IN_GOMA") OR die();

/**
 * Unit-Tests for ManyManyRelationShipInfo-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ManyManyDataObjectSetTests extends GomaUnitTest
{

    static $area = "Model";
    /**
     * name
     */
    public $name = "ManyMany_DataObjectSet";

    /**
     * relationship env.
     */
    public function testSetRelationENV() {
        $dataset = new ManyMany_DataObjectSet("group");

        $relationShips = array_values(ModelManyManyRelationShipInfo::generateFromClass(" ManyManyRelationshipTest"));

        $info = $relationShips[0];
        $dataset->setRelationENV($info, new Group(array("versionid" => 2)));

        $this->assertEqual($dataset->getRelationShip(), $info);
        $this->assertEqual($dataset->getRelationOwnValue(), 2);

        try {
            $dataset->setRelationENV(null, 2);

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }
    }
}
