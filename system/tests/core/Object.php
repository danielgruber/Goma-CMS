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
	public $name = gObject::ID;

	/**
	 * @var gObject
	 */
	protected $o;

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
		$this->assertTrue(gObject::method_exists($this->o, "extra_method"));
        $this->assertTrue(gObject::method_exists($this->o, " exTra_mEthod "));

		$this->assertEqual($this->o->extra_method(), "it works");
        $this->assertEqual($this->o->EXTRA_METHOD(), "it works");
        $this->assertEqual($this->o->ExTrA_mEtHoD(), "it works");
        $this->assertEqual($this->o->__call(" ExTrA_mEtHoD ", array()), "it works");

		$this->o->workWithExtensionInstance("TestObjectExtension", function($instance1) {
			$this->o->workWithExtensionInstance("TestObjectExtension", function($instance2) use($instance1) {
				$this->assertFalse($instance1 === $instance2);
			});
		});
	}

	/**
	 * tests linkMethod
	*/
	public function testLinkMethod() {
		gObject::linkMethod("TestObject", "testlink", "testObjectExtFunction", true);
		$this->assertEqual($this->o->testlink(), "test");
        $this->assertEqual($this->o->TESTLINK(), "test");
        $this->assertEqual($this->o->TeStLiNK(), "test");
        $this->assertEqual($this->o->__call(" tEstlink ", array()), "test");
		
		gObject::linkMethod("TestObject", "testlink_absolute", "testObjectExtFunction", false);
		$this->assertEqual($this->o->testlink_absolute(), "test");
        $this->assertEqual($this->o->TESTLINK_ABSOLUTE(), "test");
        $this->assertEqual($this->o->TeStLiNK_aBsOlUtE(), "test");
        $this->assertEqual($this->o->__call(" TeStLiNK_aBsOlUtE ", array()), "test");
	}
	
	/**
	 * tests createMethod
	*/
	public function testCreateMethod() {
		gObject::createMethod("TestObject", "testcreate", "return 'blah';", true);
		$this->assertEqual($this->o->testcreate(), "blah");
        $this->assertEqual($this->o->TESTCREATE(), "blah");
        $this->assertEqual($this->o->TestCreate(), "blah");
        $this->assertEqual($this->o->__call(" TestCreate ", array()), "blah");
		
		gObject::createMethod("TestObject", "testcreate_absolute", "return 'blub';", false);
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

		$this->assertFalse(gObject::method_exists($this->dummyMethod->classname, "myDynamicMethod"));
	}

    public function testEmptyMethod() {
        $this->assertThrows(function() { gObject::method_exists("", ""); }, "InvalidArgumentException");
        $this->assertThrows(function() { gObject::method_exists("test", ""); }, "InvalidArgumentException");
        $this->assertThrows(function() { gObject::method_exists("", "test"); }, "InvalidArgumentException");
    }


    public function testInstance() {
        $this->assertIsA(gObject::instance("DummyMethodTest"), "DummyMethodTest");
        $this->assertClone(gObject::instance("DummyMethodTest"), gObject::instance("DummyMethodTest"));

        // check for cloning
        $o = gObject::instance("DummyMethodTest");
        $o->b = 1;
        $this->assertEqual(gObject::instance($o)->b, 1);
        $this->assertNotEqual(gObject::instance("DummyMethodTest")->b, 1);

        // check if these are clones
        $second = gObject::instance("DummyMethodTest");
        $second->b = 2;
        $this->assertEqual(gObject::instance($o)->b, 1);
        $this->assertNotEqual(gObject::instance("DummyMethodTest")->b, 1);
        $this->assertEqual(gObject::instance($second)->b, 2);
        $this->assertNotEqual(gObject::instance("DummyMethodTest")->b, 2);
    }

    public function testExtensionArguments() {
        $this->unitExtensionArguments("TestObjectExtension('12aB', 34, true)", "testobjectextension", "'12aB', 34, true");
        $this->unitExtensionArguments("TestObjectExtension", "testobjectextension", array());
        $this->unitExtensionArguments("TestObjectExtension(array(123,456))", "testobjectextension", "array(123,456)");
    }

    public function unitExtensionArguments($exp, $name, $args) {
        $info = gObject::getArgumentsFromExtend($exp);

        $this->assertEqual($info[0], $name, "Name $name expected %s");
        $this->assertEqual($info[1], $args, "Arguments ".var_export($args, true) . " expected %s");
    }

    public function testExtensionWithArgs() {
        $o = new TestObject();

        $args = $o->getInstance("TestExtensionWithArgs")->args;
        $this->assertEqual($args, array('a', 12, array(23)));
    }

	public function testexpansionInstanceSerialize() {
		$o = new TestObject();
		$o->workWithExtensionInstance("TestObjectExtension", function($instance) use($o) {
			$this->assertIsA($instance, "TestObjectExtension");
			$this->assertNotEqual($instance, $o);
		});

		$d = clone $o;
		$d->test = 1;

		$this->assertNotEqual($d->getInstance("TestObjectExtension")->getOwner(), $o);

		$d->getInstance("TestObjectExtension")->setOwner(new StdClass());
		/** @var gObject $data */
		$data = unserialize(serialize($d));

		$data->workWithExtensionInstance("TestObjectExtension", function($instance) use($data) {
			$this->assertIsA($instance, "TestObjectExtension");
			$this->assertEqual($instance->getOwner(), $data);
		});
	}

	public function testCheckWakeup() {

		$wakeUpCacheProp = new ReflectionProperty("gObject", "wakeUpCache");
		$wakeUpCacheProp->setAccessible(true);
		$wakeUpCacheProp->setValue(array());

		$object = new TestCheckWakeup();
		$this->assertEqual($object->wokeup, false);
		$this->assertEqual($object->checked, true);

		$wakeUpCacheProp->setValue(array());

		$new = unserialize(serialize($object));

		$this->assertEqual($new->wokeup, true);
		$this->assertEqual($new->checked, true);

		$new->wokeup = $new->checked = false;

		$this->assertEqual($new->wokeup, false);
		$this->assertEqual($new->checked, false);

		$wakeUpCacheProp->setValue(array());

		$newer = unserialize(serialize($object));

		$this->assertEqual($newer->wokeup, true);
		$this->assertEqual($newer->checked, true);

	}
}

class DummyMethodTest extends gObject {
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

class TestObject extends gObject {
	
	
	
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
gObject::extend("testObject", "TestObjectExtension");
gObject::extend("testObject", "TestExtensionWithArgs('a', 12, array(23))");

function testObjectExtFunction() {
	return "test";
}

class TestCheckWakeup extends gObject {

	public $checked = false;
	public $wokeup = false;

	public function checkDefineStatics()
	{
		$this->checked = true;

		parent::checkDefineStatics();
	}

	public function __wakeup()
	{
		$this->wokeup = true;
		$this->checked = false;

		parent::__wakeup();
	}
}
