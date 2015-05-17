<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Object.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ObjectTest extends GomaUnitTest implements TestAble {
	
	static $area = "framework";
	/**
	 * name
	*/
	public $name = "Object";

	/**
	 * setup test
	*/
	public function setUp() {
		$this->o = new TestObject();
		$this->dummyMethod = new DummyMethodTest();
	}

	public function tearDown() {
		unset($this->o);
	}
	
	/**
	 * tests basic functionallity
	*/	
	public function testBasic() {
		$this->assertEqual($this->o->action1(), 1);
	}
	
	/**
	 * tests basic functionallity
	*/	
	public function testExtensionMethod() {

        $this->assertTrue(Object::method_exists($this->o, "extra_method"));
        $this->assertTrue(Object::method_exists($this->o, " exTra_mEthod "));

		$this->assertEqual($this->o->extra_method(), "it works");
        $this->assertEqual($this->o->EXTRA_METHOD(), "it works");
        $this->assertEqual($this->o->ExTrA_mEtHoD(), "it works");
        $this->assertEqual($this->o->__call(" ExTrA_mEtHoD ", array()), "it works");
	}

	/**
	 * tests linkMethod
	*/
	public function testLinkMethod() {
		Object::linkMethod("TestObject", "testlink", "testObjectExtFunction", true);
		$this->assertEqual($this->o->testlink(), "test");
        $this->assertEqual($this->o->TESTLINK(), "test");
        $this->assertEqual($this->o->TeStLiNK(), "test");
        $this->assertEqual($this->o->__call(" tEstlink ", array()), "test");
		
		Object::linkMethod("TestObject", "testlink_absolute", "testObjectExtFunction", false);
		$this->assertEqual($this->o->testlink_absolute(), "test");
        $this->assertEqual($this->o->TESTLINK_ABSOLUTE(), "test");
        $this->assertEqual($this->o->TeStLiNK_aBsOlUtE(), "test");
        $this->assertEqual($this->o->__call(" TeStLiNK_aBsOlUtE ", array()), "test");
	}
	
	/**
	 * tests createMethod
	*/
	public function testCreateMethod() {
		Object::createMethod("TestObject", "testcreate", "return 'blah';", true);
		$this->assertEqual($this->o->testcreate(), "blah");
        $this->assertEqual($this->o->TESTCREATE(), "blah");
        $this->assertEqual($this->o->TestCreate(), "blah");
        $this->assertEqual($this->o->__call(" TestCreate ", array()), "blah");
		
		Object::createMethod("TestObject", "testcreate_absolute", "return 'blub';", false);
		$this->assertEqual($this->o->testcreate_absolute(), "blub");
        $this->assertEqual($this->o->TESTCREATE_ABSOLUTE(), "blub");
        $this->assertEqual($this->o->TeStCrEATe_AbSolUtE(), "blub");
        $this->assertEqual($this->o->__call(" TeStCrEATe_AbSolUtE ", array()), "blub");
	}
	
	/**
	 * extensions
	*/
	public function testGetExtensions() {
		$this->assertEqual($this->o->getExtensions(), array("testobjectextension", "testextensionwithargs"));
	}
	
	/**
	 * extension-instance
	*/
	public function testGetInstance() {
		$this->assertIsA($this->o->getInstance("testobjectextension"), "testobjectextension");
	}
	
	/**
	 * extending calling
	*/
	public function testcallExtending() {
		$this->assertEqual($this->o->callExtending("callExtends"), array("works"));
	}

	public function testDummyMethodTest() {
		$this->assertEqual($this->dummyMethod->ownMethod(), "blah");
		$this->assertEqual($this->dummyMethod->__call("ownMethod", array()), "blah");
		$this->assertEqual($this->dummyMethod->__call("OWNMETHOD", array()), "blah");
		$this->assertEqual($this->dummyMethod->__call("myDynamicMethod", array()), "It works");
		$this->assertEqual($this->dummyMethod->myDynamicMethod(), "It works");

		$this->assertFalse(Object::method_exists($this->dummyMethod->classname, "myDynamicMethod"));
	}

    public function testEmptyMethod() {
        $this->assertFalse(Object::method_exists("", ""));
        $this->assertFalse(Object::method_exists("test", ""));
        $this->assertFalse(Object::method_exists("", "test"));
    }


    public function testInstance() {
        $this->assertIsA(Object::instance("DummyMethodTest"), "DummyMethodTest");
        $this->assertClone(Object::instance("DummyMethodTest"), Object::instance("DummyMethodTest"));

        // check for cloning
        $o = Object::instance("DummyMethodTest");
        $o->b = 1;
        $this->assertEqual(Object::instance($o)->b, 1);
        $this->assertNotEqual(Object::instance("DummyMethodTest")->b, 1);

        // check if these are clones
        $second = Object::instance("DummyMethodTest");
        $second->b = 2;
        $this->assertEqual(Object::instance($o)->b, 1);
        $this->assertNotEqual(Object::instance("DummyMethodTest")->b, 1);
        $this->assertEqual(Object::instance($second)->b, 2);
        $this->assertNotEqual(Object::instance("DummyMethodTest")->b, 2);
    }

    public function testExtensionArguments() {
        $this->unitExtensionArguments("TestObjectExtension('12aB', 34, true)", "testobjectextension", "'12aB', 34, true");
        $this->unitExtensionArguments("TestObjectExtension", "testobjectextension", "");
        $this->unitExtensionArguments("TestObjectExtension(array(123,456))", "testobjectextension", "array(123,456)");
    }

    public function unitExtensionArguments($exp, $name, $args) {
        $info = Object::getArgumentsFromExtend($exp);

        $this->assertEqual($info[0], $name, "Name $name expected %s");
        $this->assertEqual($info[1], $args, "Arguments $args expected %s");
    }

    public function testExtensionWithArgs() {
        $o = new TestObject();

        $args = $o->getInstance("TestExtensionWithArgs")->args;
        $this->assertEqual($args, array('a', 12, array(23)));
    }
}

class DummyMethodTest extends Object {
    public $b;

	public function ownMethod() {
		return "blah";
	}

	public function __cancall($method) {
		if($method == "myDynamicMethod") {
			return true;
		}

		return false;
	}

	public function __call($method, $args) {
		if($method == "myDynamicMethod") {
			return "It works";
		}

		return parent::__call($method, $args);
	}

}

class TestExtensionWithArgs extends Extension {
    public $args;

    public function __construct() {
        parent::__construct();

        $this->args = func_get_args();
    }
}

class TestObject extends Object {
	
	
	
	public function action1() {
		return 1;
	}
}

class TestObjectExtension extends Extension {
	static $extra_methods = array(
		"extra_method"
	);

	public static function extra_method() {
		return "it works";
	}

	public function callExtends() {
		return "works";
	}
}
Object::extend("testObject", "TestObjectExtension");
Object::extend("testObject", "TestExtensionWithArgs('a', 12, array(23))");

function testObjectExtFunction() {
	return "test";
}