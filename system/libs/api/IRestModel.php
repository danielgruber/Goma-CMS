<?php defined("IN_GOMA") OR die();

/**
 * Model which can be used for REST.
 *
 * @package     Goma\API
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */
interface IRestModel {

    /**
     * generates an array of REST-Object.
     *
     * @return array
     */
    public static function toRESTArray($object);

    /**
     * handles REST-Request.
     *
     * @return DataObject|DataObjectSet
     */
    public static function getRESTObject($request);


}