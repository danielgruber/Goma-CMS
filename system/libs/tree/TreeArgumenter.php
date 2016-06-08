<?php defined("IN_GOMA") OR die();

/**
 * @package		Goma\Tree-Lib
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */

interface TreeArgumenter {
	
	/**
	 * can change the options for the tree and must return them.
	 *
	 * @param Controller
	 * @param array - options
	*/
	public function argumentTree($controller, $array);
}
