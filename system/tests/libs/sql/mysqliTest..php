<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for MySQLi-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class MysqliTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "DataBase";

    /**
     * internal name.
     */
    public $name = "MySQLi";

    /**
     * returns field from insert manipulation.
     */
    public function testFieldsFromInsert() {
        $this->assertEqual($this->unitTestFieldsFromInsert(array(
            "fields" => array(
                "test" => 1,
                "blah" => 2,
                "blub" => 3
            )
        )), array("test", "blah", "blub"));

        $this->assertEqual($this->unitTestFieldsFromInsert(array(
            "fields" => array(array(
                "test" => 1,
                "blah" => 2,
                "blub" => 3
            ))
        )), array("test", "blah", "blub"));

        $this->assertEqual($this->unitTestFieldsFromInsert(array(
            "fields" => array( )
        )), array());

        try {
            $this->unitTestFieldsFromInsert(array(
                "fields" => null
            ));

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }
    }

    protected function unitTestFieldsFromInsert($data) {
        $mysql = new MySqliDriver(false);

        $reflectionMethod = new ReflectionMethod("MySQLiDriver", "getFieldsFromInsertManipulation");
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invoke($mysql, $data);
    }

    /**
     * tests for get values to throw correct exceptions.
     */
    public function testgetValuesSQL() {
        try {
            $this->unittestgetValuesSQL(array(
                "fields" => null
            ), array());

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }

        try {
            $this->unittestgetValuesSQL(array(
                "fields" => array(
                    "t" => 1
                )
            ), array());

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }

        try {
            $this->unittestgetValuesSQL(array(
                "fields" => array(
                    array(
                        "t" => 1
                    ),
                    array(
                        "a" => 2
                    )
                )
            ), array());

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }

        try {
            $this->unittestgetValuesSQL(array(
                "fields" => array(
                    array(
                        "t" => 1
                    )
                )
            ), array("t", "a"));

            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertIsA($e, "InvalidArgumentException");
        }

        $this->assertEqual(trim($this->unittestgetValuesSQL(array(
            "fields" => array(
                array(
                    "t" => 1
                ),
                array(
                    "t" => 2
                )
            )
        ), array("t"))), "VALUES ( '1' ) , ( '2' )");

        $this->assertEqual(trim($this->unittestgetValuesSQL(array(
            "fields" => array(
                array(
                    "t" => "'abc'"
                ),
                array(
                    "t" => 2
                )
            )
        ), array("t"))), "VALUES ( '\\'abc\\'' ) , ( '2' )");

        $this->assertEqual(trim($this->unittestgetValuesSQL(array(
            "fields" => array(
                array(
                    "t" => "'abc'",
                    "a" => 5
                ),
                array(
                    "a" => 3,
                    "t" => 2
                )
            )
        ), array("t", "a"))), "VALUES ( '\\'abc\\'', '5' ) , ( '2', '3' )");
    }

    protected function unittestgetValuesSQL($data, $fields) {
        $mysql = new MySqliDriver(false);

        $reflectionMethod = new ReflectionMethod("MySQLiDriver", "getValuesSQL");
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invoke($mysql, $data, $fields);
    }
}