<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for JSONResponseBody-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class JSONResponseBodyTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Response";

    /**
     * internal name.
     */
    public $name = "JSONResponseBody";

    public function testArraySupport() {
        $response = new GomaResponse();

        $json = new JSONResponseBody($array = array(
            "test" => 123
        ));

        $this->assertEqual($json->toServableBody($response), json_encode($array));
    }

    public function testStringSupport() {
        $response = new GomaResponse();

        $json = new JSONResponseBody($string = "Hallo Welt");

        $this->assertEqual($json->toServableBody($response), json_encode($string));
    }

    public function testObjectSupport() {
        $response = new GomaResponse();

        $json = new JSONResponseBody($obj = new MockObjectToString("Hallo Welt"));

        $this->assertEqual($json->toServableBody($response), json_encode($obj));
    }

    public function testIRestResponseSupport() {
        $response = new GomaResponse();

        $json = new JSONResponseBody($obj = new MockObjectIRestResponse($array = array(
            "test" => "blub"
        )));

        $this->assertEqual($json->toServableBody($response), json_encode($array));
    }
}

class MockObjectToString {
    public $string;

    /**
     * MockObjectToString constructor.
     * @param string $str
     */
    public function __construct($str)
    {
        $this->string = $str;
    }

    public function __toString()
    {
        return $this->string ? $this->string : "";
    }
}

class MockObjectIRestResponse implements IRestResponse {
    public $response;

    /**
     * MockObjectToString constructor.
     * @param array $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * returns array of this object.
     *
     * @return array
     */
    public function ToRestArray()
    {
        return $this->response;
    }
}
