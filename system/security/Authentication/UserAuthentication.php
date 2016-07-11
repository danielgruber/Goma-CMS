<?php defined('IN_GOMA') OR die();

/**
 * authentication-model.
 *
 * @property    string token
 * @property    User user
 * @method      User user()
 *
 * @package		Goma\Security\Users
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version		1.0
 */
class UserAuthentication extends DataObject implements HistoryData {

    /**
     * versioned.
     */
    static $versions = true;

    /**
     * no default sort.
     */
    static $default_sort = false;

    /**
     * db.
     */
    static $db = array(
        "token" => "varchar(100)"
    );

    /**
     * has one user.
     */
    static $has_one = array(
        "user" => "User"
    );

    /**
     * index
     */
    static $index = array(
        "token" => true
    );

    /**
     * @var bool
     */
    static $search_fields = false;

    /**
     * returns text what to show about the event
     *
     * @name generateHistoryData
     * @access public
     * @param History $record
     * @return array|bool
     */
    public static function generateHistoryData($record) {
        /** @var UserAuthentication $version */
        switch($record->action) {
            case "remove":
            case IModelRepository::COMMAND_TYPE_DELETE:
                if($record->created - $record->oldversion()->last_modified < AuthenticationService::$expirationLimit) {
                    $lang = lang("h_user_logout");
                    if ($record->oldversion()) {
                        $version = $record->oldversion();
                    }
                    $icon = "images/icons/fatcow16/user_go.png";
                } else {
                    return false;
                }
                break;
            default:
                $lang = lang("h_user_login");
                $version = $record->newversion();
                $icon = "images/icons/fatcow16/user_go.png";
                break;
        }

        if(isset($version)) {
            $lang = str_replace('$userUrl', "member/" . $version->user->id . URLEND, $lang);
            $lang = str_replace('$euser', convert::Raw2text($version->user->title), $lang);
        } else {
            $lang = str_replace('$userUrl', "", $lang);
            $lang = str_replace('$euser', "Unbekannt", $lang);
        }

        return array(
            "icon" => $icon,
            "text" => $lang,
            "relevant" => false
        );
    }
}
