<?php defined("IN_GOMA") OR die();

loadlang('backup');

/**
 * This class adds a Admin-Panel to manage backups.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		1.0
 */
class BackupAdmin extends TableView
{

    /**
     * session-var for storing db-file.
     */
    const SESSION_VAR_DB_FILE = "dbfile";

    /**
     * session-var for storing complete file.
     */
    const SESSION_VAR_COMPLETE = "completebackup";

    /**
     * url-handlers
    */
    public $url_handlers = array(
        "execRestore/\$rand!"	=> "execRestore"
    );

    /**
     * allowed actions
    */
    public $allowed_actions = array(
        "execRestore"
    );

    /**
     * title of this view in admin
    */
    public $text = '{$_lang_backups}';

    /**
     * permissions needed to view this in admin
    */
    public $rights = "ADMIN_BACKUP";

    /**
     * models, which are assigned to this admin-view
    */
    public $model = "BackupModel";

    /**
     * icon
    */
    static $icon = "system/templates/admin/images/backup.png";

    /**
     * fields we want to show in the table
    */
    public $fields = array(
        "name" 			=> '{$_lang_filename}',
        "create_date"	=> '{$_lang_backup_create_date}',
        "size"			=> '{$_lang_files.size}',
        "type"			=> '{$_lang_backup_type}'
    );


    /**
     * actions, which are shown in the table or below
    */
    public $actions = array(
        "restore"		=> '<img src="images/icons/fatcow-icons/16x16/site_backup_and_restore.png" alt="{$_lang_backup_restore}" title="{$_lang_backup_restore}" />',
        "download"		=> '<img src="images/icons/fatcow-icons/16x16/database_save.png" alt="{$_lang_download}" title="{$_lang_download}" />',
        "delete"		=> '<img src="images/icons/fatcow-icons/16x16/delete.png" alt="{$_lang_delete}" title="{$_lang_delete}" />',
        "add_complete"	=> array("{\$_lang_backup_create_complete}"),
        "add_db"		=> array("{\$_lang_backup_create_sql}"),
        "upload"		=> array('{$_lang_backup_upload}')
    );

    /**
     * set correct color-theme.
    */
    public function Init($request = null) {
        Resources::$lessVars = "tint-blue.less";
        parent::Init($request);
    }

    /**
     * sends the file to the browser
     *
     * @name download
     * @access public
     * @return bool
     */
    public function download() {
        $file = DataObject::get_by_id("BackupModel", $this->getParam("id"));
        if(!$file)
            return false;

        $file = $file->name;
        $path = BackupModel::BACKUP_PATH . "/" .  $file;
        FileSystem::sendFile($path);
        exit;
    }

    /**
     * upload a backup
     *
     * @return mixed|string
     */
    public function upload() {
        $form = new Form($this, "Backup_Upload", array(
            $file = new FileUpload("file", lang("backup_upload", "Upload a Backup..."), array("gfs", "sgfs"))
        ), array(
            new CancelButton("cancel", lang("cancel")),
            new FormAction("submit", lang("submit"), "saveupload")
        ), array(
            new RequiredFields(array("file"), "validate")
        ));


        $file->max_filesize = -1;
        return $form->render();
    }

    /**
     * saves the upload
    */
    public function saveUpload($data) {
        // delete cache
        $gfs = new GFS($data["file"]->realfile);
        $plist = new CFPropertyList();
        if($gfs->valid) {
            $plist->parse($gfs->getFileContents("info.plist"));
            $_data = $plist->toArray();

            if(!isset($_data["backuptype"])) {
                @unlink($data["file"]->realfile);
                return false;
            }
            if($_data["backuptype"] == "SQLBackup") {
                $file = "sql.upload." . date("m-d-y_H-i-s") . ".sgfs";
            } else {
                $file = "complete.upload." . date("m-d-y_H-i-s") . ".gfs";
            }
        } else {
            @unlink($data["file"]->realfile);
            return false;
        }

        if(rename($data["file"]->realfile, BackupModel::BACKUP_PATH . "/" . basename($file))) {
            return $this->redirectBack();
        } else {
            AddContent::addError(lang("backup_write_error"));
            return $this->redirectBack();
        }
    }

    /**
     * creates a db-backup
     *
     * @name add_db
     * @return GomaResponse
     */
    public function add_db() {
        Backup::generateDBBackup(BackupModel::BACKUP_PATH . "/" . $this->getFileFromSession(self::SESSION_VAR_DB_FILE, "sql", "sgfs"));

        GlobalSessionManager::globalSession()->remove(self::SESSION_VAR_DB_FILE);

        BackupModel::forceSyncFolder();
        return $this->redirectBack();
    }

    /**
     * get file and stores it in session.
     */
    protected function getFileFromSession($session, $prefix, $extension) {
        if(GlobalSessionManager::globalSession()->hasKey($session)) {
            return GlobalSessionManager::globalSession()->get($session);
        }

        return $prefix . "." . randomString(5) . "." . date("m-d-y_H-i-s", NOW) . "." . $extension;
    }

    /**
     * creates a backup
    */
    public function add_complete() {
        if(class_exists("SettingsController")) {
            $exclude = (array) unserialize(SettingsController::get("excludeFolders"));
        } else {
            $exclude = array();
        }

        foreach($exclude as $key => $val) {
            $exclude[$key] = "/" . $val;
        }

        Backup::generateBackup(BackupModel::BACKUP_PATH . "/" . $this->getFileFromSession(self::SESSION_VAR_COMPLETE, "full", "gfs"), $exclude);

        GlobalSessionManager::globalSession()->remove(self::SESSION_VAR_COMPLETE);

        BackupModel::forceSyncFolder();
        return $this->redirectBack();
    }

    /**
     * restore
    */
    public function restore() {
        $file = DataObject::get_by_id("BackupModel", $this->getParam("id"));
        if(!$file)
            return false;

        $file = $file->name;

        $file = BackupModel::BACKUP_PATH . "/" . $file;
        if(!file_exists($file))
            HTTPResponse::redirect($this->namespace);

        $gfs = new GFS($file);
        $info = $gfs->parsePlist("info.plist");

        if($info["type"] == "backup" && $info["backuptype"] == "SQLBackup") {
            $str = sprintf(lang("restore_sql_sure"), (string) goma_date(DATE_FORMAT, $info["created"]));
            if($this->confirm($str)) {
                $gfs->unpack(ROOT . APPLICATION . "/" . getPrivateKey() . "-install/", "/database");
                Dev::redirectToDev();
            }
        } else {
            $t = G_SoftwareType::getByType($info["type"], $file);

            $data = $t->getRestoreInfo();
            if(is_array($data)) {
                $rand = randomString(20);
                $data["rand"] = $rand;

                GlobalSessionManager::globalSession()->removeByPrefix("restore.");
                GlobalSessionManager::globalSession()->set("restore." . $rand, $data);

                $dataset = new ViewAccessableData($data);
                return $dataset->renderWith("admin/restoreInfo.html");
            } else {
                return $data;
            }
        }
    }

    /**
     * executes the restore
    */
    public function execRestore() {
        $rand = $this->getParam("rand");
        if(GlobalSessionManager::globalSession()->hasKey("restore." . $rand)) {
            $data = GlobalSessionManager::globalSession()->get("restore." . $rand);
            G_SoftwareType::install($data);
            HTTPResponse::redirect(BASE_URI);
        } else {
            HTTPResponse::redirect(BASE_URI);
        }
    }

    /**
     * provides permissions
    */
    public function providePerms() {
        return array(
            "ADMIN_BACKUP"	=> array(
                "title" 	=> '{$_lang_administration}: {$_lang_backups}',
                "default"	=> array(
                    "type" 			=> "admins"
                ),
                "category"	=> "ADMIN"
            )
        );
    }
}
