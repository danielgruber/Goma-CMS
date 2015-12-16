<?php defined("IN_GOMA") OR die();

/**
 * Extends Uploads-Object to track down dependencies between uploads and pages.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */
class UploadsPageBacktrace extends DataObjectExtension {
    /**
     * relation to pages
     *
     *@name belongs_many_many
     */
    static $belongs_many_many = array(
        "linkingPages"	=> "pages"
    );
}
gObject::extend("Uploads", "UploadsPageBacktrace");
