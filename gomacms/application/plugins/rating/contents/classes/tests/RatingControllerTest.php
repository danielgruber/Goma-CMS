<?php
defined("IN_GOMA") OR die();

/**
 * RatingControllerTest.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class RatingControllerTest extends AbstractControllerTest {

    public $name = "RatingController";

    protected function getUrlsForFirstResponder()
    {
        return array(
            "rate",
            "rate/blub/1",
            "rate/blzb/3/blah",
            "rate/lalal/abc"
        );
    }
}
