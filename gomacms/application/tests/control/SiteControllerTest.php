<?php
defined("IN_GOMA") OR die();

/**
 * SiteControllerTest.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class SiteControllerTest extends AbstractControllerTest {

    public $name = "SiteController";

    protected function getUrlsForFirstResponder()
    {
        return array(
            "blub",
            "blah",
            "test/lala/123",
            "POST blah"
        );
    }
}
