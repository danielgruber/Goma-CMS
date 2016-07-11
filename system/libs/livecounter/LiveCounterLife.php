<?php defined("IN_GOMA") OR die();

/**
 * model used to make real life-counter.
 * data is migrated once per hour.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		1.1.1
 */
class liveCounter_live extends DataObject {
    /**
     * disable history for this DataObject, because it would be a big lag of performance
     */
    static $history = false;

    /**
     * database-fields
     */
    static $db = array(
        'phpsessid' 	=> 'varchar(800)',
        "browser"		=> "varchar(200)",
        "ip"			=> "varchar(200)",
        "hitcount"		=> "int(10)",
    );

    /**
     * has-one-relationship to statistics
     */
    static $has_one = array(
        "longterm"	=> "livecounter"
    );

    /**
     * the name of the table isn't livecounter, it's statistics
     */
    static $table = "statistics_live";

    /**
     * indexes
     */
    static $index = array(
        "recordid" 	    => false,
        "autorid" 		=> false,
        "editorid"		=> false,
        "phpsessid"		=> "INDEX"
    );

    static $search_fields = false;
}
