<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for RequestHandler-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class RequestHandlerTest extends GomaUnitTest {

	static $area = "Controller";
	/**
	 * name
	*/
	public $name = "RequestHandler";

	public function testPermissionSystem() {
		$h = new TestableRequestHandler();
		$h->Init(new Request("get", ""));
		$this->assertTrue($h->hasAction("testAction"));
		$this->assertEqual($h->handleAction("testAction"), $h->content);

		$h->allowed_actions["testaction"] = "->canCallTestMethod";
		$this->assertFalse($h->wasCalled);
		$this->assertTrue($h->hasAction("testAction"));
		$this->assertTrue($h->wasCalled);
		$this->assertEqual($h->handleAction("testAction"), $h->content);

		$h->wasCalled = false;
		$h->shouldCall = false;
		$this->assertFalse($h->wasCalled);
		$this->assertFalse($h->hasAction("testAction"));
		$this->assertTrue($h->wasCalled);

		// you should be able to call a method also when hasAction returns false, if method exists.
		$this->assertEqual($h->handleAction("testAction"), $h->content);

		// serve should not be called when handleAction gets called or handleRequest.
		$this->assertEqual($h->serve($h->handleAction("testAction")), $h->content . 1);
	}

	public function testRequestSystem() {
		$h = new TestableRequestHandler();
		$r = new Request("GET", "testAction/1");

		$this->assertEqual($h->handleRequest($r), $h->content);
		$this->assertEqual($h->getParam("Action"), "testAction");
		$this->assertEqual($h->getParam("id"), 1);
		$this->assertEqual($h->getParam("uid"), null);
	}
}

class TestableRequestHandler extends RequestHandler {

	public $shouldCall = true;
	public $content = "test";
	public $wasCalled = false;

	public $url_handlers = array(
		'$Action/$Id' => '$Action'
	);

	public $allowed_actions = array(
		"testAction" => true
	);

	public function serve($content) {
		return $content . 1;
	}

	public function canCallTestMethod() {
		$this->wasCalled = true;
		return $this->shouldCall;
	}

	public function testAction() {
		return $this->content;
	}
}