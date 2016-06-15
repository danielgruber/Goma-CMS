<?php
defined("IN_GOMA") OR die();

/**
 * Abstract class for all getters like HasOne, HasMany and ManyMany.
 *
 * @package Goma
 *
 * @author Goma-Team
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
abstract class AbstractGetterExtension extends Extension {
    /**
     * link method.
     * @param string $instanceName
     * @param string $method
     * @param string $methodName
     * @param string $localMethod
     * @param string $error
     */
    protected function linkMethodWithInstance($instanceName, $method, $methodName, $localMethod, $error) {
        gObject::LinkMethod($this->getOwner()->classname, $method, array($instanceName, function($instance) use ($method, $methodName, $localMethod, $error) {
            $args = func_get_args();
            $args[0] = $methodName;
            try {
                return call_user_func_array(array($instance, $localMethod), $args);
            } catch(InvalidArgumentException $e) {
                throw new LogicException($error, 0, $e);
            }
        }), true);
    }
}
