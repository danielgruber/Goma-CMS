<?php defined("IN_GOMA") OR die();

/**
 * Rest-Interface of Goma.
 *
 * @package     Goma
 *
 * @copyright   2016 Goma Team
 * @author      Goma Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version     1.0
 */
interface IRestResponse {
    /**
     * returns array of this object.
     *
     * @return array
     */
    public function ToRestArray();
}
