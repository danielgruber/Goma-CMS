<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for ExternalFormController-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ExternalFormControllerTest extends GomaUnitTest
{
    /**
     * area
     */
    static $area = "Form";

    /**
     * internal name.
     */
    public $name = "ExternalFormController";

    /**
     * tests handle-request.
     */
    public function testFieldExtAction() {
        $oldRequest = new Request("GET", "test", array("test" => 3), array("blah" => 2), array("HTTP" => 1));
        $newRequest = new Request("POST", "test", array("test" => 1), array("blah" => 2), array("HTTP" => 3));

        $controller = new RequestHandler();
        $controller->setRequest($oldRequest);
        $form = new Form($controller, "testform", array(
            new ExternalFormFieldTest("testfield", "TestField")
        ));
        $form->render()->__toString();

        $externalFormController = new ExternalFormController();
        $externalFormController->setRequest($newRequest);

        $this->assertEqual($externalFormController->FieldExtAction("testform", "testfield"), "content");
        $this->assertEqual(ExternalFormFieldTest::$lastRequest, $newRequest);
        $this->assertEqual($form->getRequest(), $oldRequest);

        // check for session
        /** @var Form $formInstance */
        $formInstance = GlobalSessionManager::globalSession()->get(Form::SESSION_PREFIX . ".testform");
        $this->assertEqual($formInstance->getRequest(), $oldRequest);
        $this->assertEqual($formInstance->getController()->getRequest(), $oldRequest);
    }
}

class ExternalFormFieldTest extends FormField {

    public static $lastRequest;

    public function index() {

        self::$lastRequest = $this->request;

        return "content";
    }
}
