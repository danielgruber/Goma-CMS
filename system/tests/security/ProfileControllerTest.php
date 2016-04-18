<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Member-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class ProfileControllerTest extends GomaUnitTest
{
    /**
     * area
     */
    static $area = "User";

    /**
     * internal name.
     */
    public $name = "ProfileController";

    public function testLogin() {
        $profileController = new ProfileController();
        $request = new Request("post", "");
        $profileController->Init($request);

        $response = $profileController->login();
        if(member::login()) {
            $this->assertIsA($response, "GomaResponse");
        } else {
            $this->assertIsA($response, "string");
        }
    }
}