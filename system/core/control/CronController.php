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
        ini_set('max_execution_time', 300);

        Core::callHook("cron");
    }
}
