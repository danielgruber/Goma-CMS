<?php
/**
 * @package		Goma\System\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined("IN_GOMA") OR die();

/**
 * This class represents the Extension system.
 *
 * @package		Goma\System\Core
 * @version		1.0
 */
abstract class Extension extends ViewAccessAbleData implements ExtensionModel {

	/**
	 * extra_methods
	 */
	public static $extra_methods = array();
	/**
	 * the owner-class
	 *@name owner
	 */
	protected $owner;
	/**
	 * sets the owner-class
	 *@name setOwner
	 */
	public function setOwner($object) {
		if(!is_object($object)) {
			throwError(20, 'PHP-Error', '$object isn\'t a object in ' . __FILE__ . ' on line ' . __LINE__ . '');
		}
		if(class_exists($object->classname)) {
			$this->owner = $object;
		} else {
			throwError(20, 'PHP-Error', 'Class ' . $class . ' doesn\'t exist in context.');
		}

		return $this;
	}

	/**
	 * gets the owner of class
	 *@name getOwner
	 */
	public function getOwner() {
		return $this->owner;
	}

}
