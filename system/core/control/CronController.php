<?php defined("IN_GOMA") OR die();

/**
 * Basic Class some system behaviour.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class CronController extends RequestHandler {

    /**
     * cron.
     */
    public function handleRequest() {
        Core::callHook("cron");
    }
}