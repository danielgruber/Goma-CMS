<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ClassManifest.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ClassManifestTest extends GomaUnitTest
{
    static $area = "framework";
    /**
     * name
     */
    public $name = "ClassManifest";


    public function testParseInterface()
    {
        $classes = $class_info = array();

        $method = new ReflectionMethod("ClassManifest", "parsePHPFile");
        $method->setAccessible(true);
        $method->invokeArgs(null, array(
            "system/tests/core/ClassManifestTestPath/interfaceOnly.php", &$classes, &$class_info
        ));

        $this->assertEqual($classes, array(
            "myinterface" => "system/tests/core/ClassManifestTestPath/interfaceOnly.php"
        ));

        $this->assertEqual($class_info, array(
            "myinterface" => array(
                "abstract" => true,
                "interface" => true
            )
        ));
    }

    public function testParseInterfaces()
    {
        $classes = $class_info = array();

        $method = new ReflectionMethod("ClassManifest", "parsePHPFile");
        $method->setAccessible(true);
        $method->invokeArgs(null, array(
            "system/tests/core/ClassManifestTestPath/interfacesOnly.php", &$classes, &$class_info
        ));

        $this->assertEqual($classes, array(
            "myinterface1" => "system/tests/core/ClassManifestTestPath/interfacesOnly.php",
            "myinterface2" => "system/tests/core/ClassManifestTestPath/interfacesOnly.php",
            "myinterface3" => "system/tests/core/ClassManifestTestPath/interfacesOnly.php",
            "myinterface4" => "system/tests/core/ClassManifestTestPath/interfacesOnly.php",
            "myinterface5" => "system/tests/core/ClassManifestTestPath/interfacesOnly.php"
        ));

        $this->assertEqual($class_info, array(
            "myinterface1" => array(
                "abstract" => true,
                "interface" => true
            ),
            "myinterface2" => array(
                "abstract" => true,
                "interface" => true
            ),
            "myinterface3" => array(
                "abstract" => true,
                "interface" => true
            ),
            "myinterface4" => array(
                "abstract" => true,
                "interface" => true,
                "parent" => "myinterface3"
            ),
            "myinterface5" => array(
                "abstract" => true,
                "interface" => true,
                "parent" => "myinterface1"
            )
        ));
    }

    public function testParseClassAndInterface()
    {
        $classes = $class_info = array();

        $method = new ReflectionMethod("ClassManifest", "parsePHPFile");
        $method->setAccessible(true);
        $method->invokeArgs(null, array(
            "system/tests/core/ClassManifestTestPath/classAndInterface.php", &$classes, &$class_info
        ));

        $this->assertEqual($class_info, array (
            'test' =>
                array (
                ),
            'test3' =>
                array (
                ),
            'myclass' =>
                array (
                    'parent' => 'test',
                    'interfaces' =>
                        array (
                            0 => 'i1',
                        ),
                ),
            'myclass2' =>
                array (
                    'parent' => 'test',
                    'interfaces' =>
                        array (
                            0 => 'i1',
                            1 => 'i3',
                            2 => 'i4',
                        ),
                ),
            'i1' =>
                array (
                    'abstract' => true,
                    'interface' => true,
                ),
            'i3' =>
                array (
                    'abstract' => true,
                    'interface' => true,
                ),
            'i4' =>
                array (
                    'abstract' => true,
                    'interface' => true,
                ),
        ));

        $this->assertEqual($classes, array (
            'test' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
            'test3' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
            'myclass' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
            'myclass2' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
            'i1' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
            'i3' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
            'i4' => 'system/tests/core/ClassManifestTestPath/classAndInterface.php',
        ));
    }
}
