<?php defined("IN_GOMA") OR die();
/**
 * Integration-Tests for DataObject-ManyMany-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ManyManyIntegrationTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "ManyMany";

    /**
     * internal name.
     */
    public $name = "ManyManyIntegrationTest";

    /**
     * @var ManyManyTestObjectOne[]
     */
    protected   $ones = array();

    /**
     * @var ManyManyTestObjectTwo[]
     */
    protected   $twos = array(),
                $createdSet = 0;

    public function setUp()
    {
        foreach(DBTableManager::Tables("ManyManyTestObjectOne") as $table) {
            if(!SQL::query("TRUNCATE TABLE " . DB_PREFIX . $table)) {
                throw new MySQLException();
            }
        }

        foreach(DBTableManager::Tables("ManyManyTestObjectTwo") as $table) {
            if(!SQL::query("TRUNCATE TABLE " . DB_PREFIX . $table)) {
                throw new MySQLException();
            }
        }

        /** @var ModelManyManyRelationShipInfo $relationship */
        foreach(gObject::instance("ManyManyTestObjectOne")->ManyManyRelationships() as $relationship) {
            if(!SQL::query("TRUNCATE TABLE " . DB_PREFIX . $relationship->getTableName())) {
                throw new MySQLException();
            }
        }

        $this->ones = array();
        $this->twos = array();
        $this->createdSet = 0;

        for($i = 0; $i < 5; $i++) {
            $this->ones[$i] = $one = new ManyManyTestObjectOne(array(
                "one" => "one_" . $i
            ));
            $one->writeToDB(true, true);
            $one->random = randomString(10);
            $one->writeToDB(false, true);

            $this->twos[$i] = $two = new ManyManyTestObjectTwo(array(
                "two"   => "two_" . $i
            ));
            $two->writeToDB(true, true);
            $two->random = randomString(10);
            $two->writeToDB(false, true);

            /** @var ManyMany_DataObjectSet $onesInTwo */
            $onesInTwo = $two->ones();
            for($a = $i; $a >= 0; $a--) {
                $this->ones[$a]->extra = $i . "_" . $a;
                $onesInTwo->add($this->ones[$a]);
                $this->createdSet++;
            }

            $onesInTwo->commitStaging(false, true);
        }
    }

    public function testLoad() {
        /** @var ManyManyTestObjectOne $firstOne */
        $firstOne = DataObject::get_one("ManyManyTestObjectOne");

        $set = $firstOne->twos();
        $data = $set->getRelationshipData();
        $cloned = clone $firstOne;
        $cloned->writeToDB(false, true);
        $set->setRelationENV($firstOne->getManyManyInfo("twos"), $cloned);

        $data2 = $set->getRelationshipData();
        $this->assertEqual(count($data2), count($data));
    }

    /**
     * tests basic data loading.
     */
    public function testDataLoading() {

        $countedSet = 0;
        /** @var ManyManyTestObjectTwo $two */
        foreach(DataObject::get("ManyManyTestObjectTwo") as $two) {
            $ones = $two->ones();
            $twoInt = (int) str_replace("two_", "", $two->two);
            $this->assertEqual($ones->count(), $twoInt + 1);

            /** @var ManyManyTestObjectOne $one */
            foreach($ones as $one) {
                $oneInt = (int) str_replace("one_", "", $one->one);
                $this->assertEqual($one->extra, $twoInt . "_" . $oneInt);
                $countedSet++;
            }
        }

        $countedSetB = 0;

        /** @var ManyManyTestObjectOne $one */
        foreach(DataObject::get("ManyManyTestObjectOne") as $one) {
            $twos = $one->twos();
            $oneInt = (int) str_replace("one_", "", $one->one);
            $this->assertEqual($twos->count(), 5 - $oneInt);

            /** @var ManyManyTestObjectTwo $two */
            foreach($twos as $two) {
                $twoInt = (int) str_replace("two_", "", $two->two);
                $this->assertEqual($two->extra, $twoInt . "_" . $oneInt);
                $countedSetB++;
            }
        }

        $this->assertEqual($countedSet, $this->createdSet);
        $this->assertEqual($countedSetB, $this->createdSet);
    }

    public function testSetIds() {
        /** @var ManyManyTestObjectOne $firstOne */
        $firstOne = DataObject::get_one("ManyManyTestObjectOne");

        $this->assertEqual($firstOne->twos()->count(), count($this->twos));
        $this->assertEqual(count($firstOne->twosids), count($this->twos));

        $ids = $firstOne->twosids;
        array_splice($ids, 3);
        $firstOne->twosids = $ids;
        $this->assertEqual(count($firstOne->twosids), 3);

        /** @var ManyManyTestObjectTwo $two */
        $i = 0;
        foreach($firstOne->twos() as $two) {
            $this->assertEqual($two->versionid, $ids[$i]);
            $i++;
        }

        shuffle($ids);
        $firstOne->twosids = $ids;
        $i = 0;
        foreach($firstOne->twos() as $two) {
            $this->assertEqual($two->versionid, $ids[$i]);
            $i++;
        }
    }

    public function testRewriteRelationship() {
        /** @var ManyManyTestObjectOne $firstOne */
        $firstOne = DataObject::get_one("ManyManyTestObjectOne");

        $this->assertEqual($firstOne->twos()->count(), count($this->twos));
        $ids = $firstOne->twosids;
        array_splice($ids, 4);
        shuffle($ids);
        $firstOne->twosids = $ids;
        $firstOne->twos()->commitStaging(false, true, 3);

        $newFirstOne = DataObject::get_one("ManyManyTestObjectOne");
        $this->assertEqual($newFirstOne->twos()->count(), 4);
        /** @var ManyManyTestObjectTwo $two */
        $i = 0;
        foreach($newFirstOne->twos() as $two) {
            $this->assertEqual($two->versionid, $ids[$i]);
            $i++;
        }
    }

    public function testSort() {
        /** @var ManyManyTestObjectOne $firstOne */
        $firstOne = DataObject::get_one("ManyManyTestObjectOne");

        $this->assertEqual($firstOne->twos()->count(), count($this->twos));
        $ids = $firstOne->twosids;

        shuffle($ids);
        $twos = $firstOne->twos();
        $recordids = array();
        foreach($ids as $id) {
            $recordids[] = $twos->find("versionid", $id)->recordid;
        }
        $twos->setSortByIdArray($recordids);

        $this->assertEqual($twos->getRelationshipIDs(), $ids);

        $twos->commitStaging(false, true);
        $firstOne = DataObject::get_one("ManyManyTestObjectOne");
        $this->assertEqual($firstOne->twosids, $ids);
    }

    public function testFilter() {
        $firstOne = DataObject::get_one("ManyManyTestObjectOne");

        $this->assertEqual($firstOne->twos(array("two" => $this->twos[0]->two))->count(), 1);
        $this->assertEqual($firstOne->twos()->filter(array("two" => $this->twos[0]->two))->count(), 1);
    }
}

/**
 * Class ManyManyTestObjectOne
 *
 * @method ManyMany_DataObjectSet twos()
 * @property array twosids
 * @property string one
 * @property string extra
 * @property string random
 */
class ManyManyTestObjectOne extends DataObject {

    static $version = true;

    static $db = array(
        "one"       => "varchar(200)",
        "random"    => "varchar(200)"
    );

    static $many_many = array(
        "twos"  => "ManyManyTestObjectTwo"
    );

    static $search_fields = false;

    static $many_many_extra_fields = array(
        "twos"  => array(
            "extra" => "varchar(100)"
        )
    );
}

/**
 * Class ManyManyTestObjectTwo
 *
 * @method ManyMany_DataObjectSet ones()
 * @property array onesids
 * @property string two
 * @property string extra
 * @property string random
 */
class ManyManyTestObjectTwo extends DataObject {

    static $version;

    static $db = array(
        "two"   => "varchar(200)",
        "random"    => "varchar(200)"
    );

    static $search_fields = false;

    static $belongs_many_many = array(
        "ones"  => array(
            DataObject::RELATION_TARGET     => "ManyManyTestObjectOne",
            DataObject::RELATION_INVERSE    => "twos"
        )
    );
}
