<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */

interface TreeModel {
	/**
	 * generates a tree.
	 *
	 * @param 	object|null $parent parent
	 * @return 	array|object TreeNodes
	*/
	static function build_tree($parent = null, $dataParams = array());
}