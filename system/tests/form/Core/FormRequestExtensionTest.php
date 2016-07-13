<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for FormRequestExtension-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class FormRequestExtensionTests extends GomaUnitTest
{
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "FormRequestExtension";

    public function testRequests() {
        $request = new Request("get", "forms/form/blub/blah/test");
        $this->assertEqual($this->unitTestRequests(
            array('$Action/$Id', '$Action', '$Id', '$blah', '$hulapalu/$lalala/$lustigeNamen'), $request), array("blub", "blah"));

        $request = new Request("get", "forms/form/form/blah/test");
        $this->assertEqual($this->unitTestRequests(
            array('$Action/$Id', '$Action', '$Id', '$blah', '$hulapalu/$lalala/$lustigeNamen'), $request), array("form", "blah"));

        $request = new Request("get", "forms/form/blah/test");
        $this->assertEqual($this->unitTestRequests(
            array('$Action/$Id', '$Action', '$Id', '$blah', '$hulapalu/$lalala/$lustigeNamen'), $request), array("blah", "test"));

        $request = new Request("get", "form/form/blah/test");
        $this->assertEqual($this->unitTestRequests(
            array('$Action/$Id', '$Action', '$Id', '$blah', '$hulapalu/$lalala/$lustigeNamen'), $request), "");
    }

    /**
     * @param string $match
     * @param Request $request
     * @return mixed
     */
    public function unitTestRequests($match, $request) {
        $contents = array();
        foreach((array) $match as $currentMatch) {
            $currentRequest = clone $request;

            $this->assertTrue(!!$currentRequest->match($currentMatch, true));
            $controller = new RequestHandler();
            $controller->Init($currentRequest);

            $extension = new FormRequestExtension(new MockControllerForExternalForm());

            $extension->setOwner($controller);
            $extension->onBeforeHandleAction("", $content, $handleWithAction);
            $contents[] = $content;
        }

        if(count($contents) > 1) {
            for($i = 0; $i < count($contents); $i++) {
                $this->assertEqual($contents[0], $contents[$i]);
            }
        }

        return $contents[0];
    }
}

class MockControllerForExternalForm extends RequestHandler {
    /**
     * @param Request $request
     * @param bool $subController
     * @return false|null|string
     * @throws Exception
     */
    public function handleRequest($request, $subController = false)
    {
        $form = $request->getParam("form");
        $field = $request->getParam("field");
        return array($form, $field);
    }
}
