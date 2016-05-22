<?php defined("IN_GOMA") OR die();

/**
 * This class adds a simple tab to Settings.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		1.0
 */
class BackupSettings extends NewSettings {
    /**
     * name of the tab
     *
     *@name tab
     *@access public
     */
    public $tab = "{\$_lang_backups}";

    /**
     * db-fields
     *
     *@name db_fields
     *@access public
     */
    static $db = array(
        "excludeFolders" => "text"
    );

    /**
     * generates the form
     */
    public function getFormFromDB(&$form) {
        $excludedFolders = $this->excludeFolders;
        if($excludedFolders)
            $excludedFolders = (array) unserialize($excludedFolders);
        else
            $excludedFolders = array();

        $files = ArrayLib::key_value(scandir(ROOT . APPLICATION));
        $notExcludable = array("info.plist", "application", "templates", ".htaccess");
        foreach($files as $key => $file) {
            if($file == "." || $file == ".." || in_array($file, Backup::$fileExcludeList)  || in_array("/" . $file, Backup::$fileExcludeList) || in_array($file, $notExcludable)) {
                unset($files[$key]);
            } else

                if(is_dir(ROOT . APPLICATION . "/" . $file)) {
                    $files[$key] = $file . "/";
                }
        }

        $form->add($dropdown = new MultiSelectDropDown("excludeFolders", lang("backup_exclude_files"), $files, $excludedFolders));
        $form->form()->addDataHandler(array($this, "handleData"));
    }

    /**
     * data-handler
     */
    public function handleData($data) {
        $data["excludefolders"] = serialize($data["excludefolders"]);
        return $data;
    }
}