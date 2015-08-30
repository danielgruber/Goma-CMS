<?php defined("IN_GOMA") OR die();
/**
 * This class manages information about expansions.
 * it provides method like getExpansionFolder or getResourceFolder.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

class ExpansionManager {

    /**
     * returns the base-folder of a expansion or class.
     *
     * @param    string    extension or class-name
     * @param    bool    if force to use as class-name
     * @param    bool    if force to be absolute path.
     * @return null|string
     */
    public static function getExpansionFolder($name, $forceAbsolute = false) {
        $name = self::getExpansionName($name);
        if(!isset($name)) {
            return null;
        }

        $data = self::getExpansionData($name);
        $folder = $data["folder"];

        if(isset($folder)) {
            if($forceAbsolute) {
                return realpath($folder) . "/";
            } else {
                return ClassInfo::makePathRelative($folder);
            }
        } else {
            return null;
        }
    }

    /**
     * determines expansion name by classname or expansion-name.
     *
     * @param 	string name
     * @return 	string|null
     */
    public static function getExpansionName($name) {
        if(is_object($name)) {
            if(isset($name->inExpansion) && self::getExpansionData($name->inExpansion)) {
                return $name->inExpansion;
            } else {
                $name = ClassManifest::resolveClassName($name);
            }
        }

        if(isset(ClassInfo::$class_info[$name]["inExpansion"]) && self::getExpansionData(ClassInfo::$class_info[$name]["inExpansion"])) {
            return ClassInfo::$class_info[$name]["inExpansion"];
        }

        return self::getExpansionData($name) ? $name : null;
    }

    /**
     * returns data if expansion with given name exists, else null.
     *
     * @param 	string name
     * @return 	array|null
     */
    public static function getExpansionData($name) {
        return isset(ClassInfo::$appENV["expansion"][strtolower($name)]) ? ClassInfo::$appENV["expansion"][strtolower($name)] : null;
    }


    /**
     * gets the resource-folder for an Expansion
     *
     * @param $class
     * @param bool $forceAbsolute
     * @return null|string
     * @internal param $getResourceFolder
     * @access public
     */
    public static function getResourceFolder($class, $forceAbsolute = false)
    {
        if($exp = self::getExpansionName($class)) {
            $extFolder = self::getExpansionFolder($exp, $forceAbsolute);
            return isset(ClassInfo::$appENV["expansion"][$exp]["resourceFolder"]) ? $extFolder . ClassInfo::$appENV["expansion"][$exp]["resourceFolder"] : $extFolder . "resources";
        }

        return null;
    }

}
