<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for content in admin-panel.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class ContentAdminTest extends GomaUnitTest implements TestAble
{


    static $area = "cms";
    /**
     * name
     */
    public $name = "content";

    public function testAdd() {
        $content = new contentAdmin();
        $method = new ReflectionMethod("contentAdmin", "getModelForAdd");
        $method->setAccessible(true);

        $this->assertIsA($method->invoke($content), "ViewAccessableData");
        $this->assertNotA($method->invoke($content), "Pages");
        $this->assertNotEqual($content->cms_add(), "");

        $request = new Request("get", "blah");
        $request->params["model"] = "page";
        $content->setRequest($request);

        $this->assertIsA($method->invoke($content), "pages");
        $this->assertNotEqual($content->cms_add(), "");
    }
}
