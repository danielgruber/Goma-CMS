<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for GomaResponseBody-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class GomaResponseBodyTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Response";

    /**
     * internal name.
     */
    public $name = "JSONResponseBody";

    public function testArraySupport() {
        $this->assertThrows(function() {
            new GomaResponseBody(array(
                "test" => 123
            ));
        }, "InvalidArgumentException");
    }

    public function testStringSupport() {
        $html = new htmlparser();
        $response = new GomaResponse();

        $body = new GomaResponseBody($string = "Hallo Welt");

        $this->assertEqual($body->toServableBody($response), $html->parseHTML($string, true, true));

        $body->setIncludeResourcesInBody(false);
        $this->assertEqual($body->toServableBody($response), $html->parseHTML($string, true, false));

        $body->setParseHTML(false);
        $this->assertEqual($body->toServableBody($response), $string);

        $body->setIncludeResourcesInBody(true);
        $this->assertEqual($body->toServableBody($response), $string);
    }

    public function testObjectSupport() {
        $html = new htmlparser();
        $response = new GomaResponse();

        $body = new GomaResponseBody($obj = new MockObjectToString("Hallo Welt"));

        $this->assertEqual($body->toServableBody($response), $html->parseHTML($obj->string, true, true));

        $body->setIncludeResourcesInBody(false);
        $this->assertEqual($body->toServableBody($response), $html->parseHTML($obj->string, true, false));

        $body->setParseHTML(false);
        $this->assertEqual($body->toServableBody($response), $obj->string);

        $body->setIncludeResourcesInBody(true);
        $this->assertEqual($body->toServableBody($response), $obj->string);
    }
}
