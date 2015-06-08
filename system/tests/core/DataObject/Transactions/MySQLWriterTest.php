<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for DataObject-Field-Implementation.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class MySQLWriterImplementationTest extends GomaUnitTest implements TestAble
{
    /**
     * area
     */
    static $area = "Transactions";

    /**
     * internal name.
     */
    public $name = "MySQLWriterImplementation";

    public function testDefaultFields() {
        $mockData = new WriterImplMockWriter();

        $mockData->model = new WriterImplMockModel();
        $mockData->model->id = 20;
        $mockData->model->classname = randomString(10);
        $mockData->model->baseClass = randomString(5);

        $writer = new MySQLWriterImplementation();
        $writer->setWriter($mockData);

        $reflectionMethod = new ReflectionMethod("MySQLWriterImplementation", "generateDefaultTableManipulation");
        $reflectionMethod->setAccessible(true);

        $this->assertEqual(
            $reflectionMethod->invoke($writer, " " . $mockData->model->baseClass),
            array(
                "class_name"    => $mockData->model->classname,
                "recordid"      => $mockData->model->id,
                "last_modified" => NOW
            )
        );

        $this->assertEqual(
            $reflectionMethod->invoke($writer, "_"),
            array()
        );

    }
}

class WriterImplMockWriter {

    public $model;

    public function getModel() {
        return $this->model;
    }
}

class WriterImplMockModel extends StdClass {
    public function BaseClass() {
        return $this->baseClass;
    }
}