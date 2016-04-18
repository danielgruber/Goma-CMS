<?php defined("IN_GOMA") OR die();
/**
 * Abstract Base-Class for Controller-tests.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

abstract class AbstractControllerTest extends GomaUnitTest {

    static $area = "Controller";

    public $name = "AbstractController";

    /**
     * @return array
     */
    abstract protected function getUrlsForFirstResponder();

    public function testForFirstResponder() {
        $urls = $this->getUrlsForFirstResponder();
        foreach($urls as $url) {
            $requiresFirstInMatching = false;
            $method = "get";
            if(substr($url, -1) == "!") {
                $requiresFirstInMatching = true;
                $url = substr($url, 0, -1);
            }

            if (preg_match("/^(POST|PUT|DELETE|HEAD|GET)\s+(.*)$/Usi", $url, $matches)) {
                $method = $matches[1];
                $url = $matches[2];
            }

            $request = new Request($method, $url);
            $ruleMatcher = RuleMatcher::initWithRulesAndRequest(Director::getSortedRules(), $request);
            if(!$requiresFirstInMatching) {
                $found = false;
                while ($nextController = $ruleMatcher->matchNext()) {
                    if(strtolower($nextController) == strtolower($this->name)) {
                        $found = true;
                        break;
                    }
                }

                $this->assertTrue($found, "check for $url.");
            } else {
                $this->assertEqual(
                    strtolower($ruleMatcher->matchNext()),
                    strtolower($this->name),
                    "check for $url and it is required as absolute first responder."
                );
            }
        }
    }
}
