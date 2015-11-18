<?php defined("IN_GOMA") OR die();
/**
 * @package 	goma framework
 * @link 		http://goma-cms.org
 * @license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 *	@Version 	2.3
 *
 * last modified: 30.13.2015
 */

class UpdateController extends adminController {
    /**
     * allowed actions
     *
     *@name allowed_actions
     *@access public
     */
    public $allowed_actions = array(
        "installUpdate",
        "showInfo",
        "upload",
        "showPackageInfo"
    );

    /**
     * title in view of this controller
     *
     * @return string
     */
    public function title() {
        return lang("update");
    }

    /**
     * default index action. Shows Update-Page.
     *
     * @return bool|string
     */
    public function index() {
        $view = new ViewAccessableData();

        $updates = isset($_GET["noJS"]) ? new DataSet($this->generateUpdatePackages()) : new DataSet(array());
        $updatables = G_SoftwareType::listUpdatablePackages();

        $view->customise(array( "updates" => $updates,
                                "BASEURI" => BASE_URI,
                                "storeAvailable" => G_SoftwareType::isStoreAvailable(),
                                "updatables" => new DataSet($updatables),
                                "updatables_json" => json_encode($updatables)));

        return $view->renderWith("admin/update.html");
    }

    /**
     * generates update-packages.
     *
     * @return DataSet
     */
    public function generateUpdatePackages() {
        G_SoftwareType::forceLiveDB();
        $updates = G_SoftwareType::listUpdatePackages();
        foreach($updates as $name => $data) {
            $data["secret"] = randomString(20);
            if(!isset($data["AppStore"])) {
                $_SESSION["updates"][$data["file"]] = $data["secret"];
            } else {
                $_SESSION["AppStore_updates"][$data["AppStore"]] = $data["secret"];
            }
            $updates[$name] = $data;
        }
        return $updates;
    }


    /**
     * shows the information of the update-file with the given id. Mostly this is used when uploading files.
     *
     *@name showInfo
     *@access public
     */
    public function showInfo() {
        if($id = $this->getParam("id")) {
            if(!($fileObj = DataObject::get_one("Uploads", array("id" => $id)))) {
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
                exit;
            }

            $file = $fileObj->realfile;

            return $this->showFileInfoForFile($file);

        } else {
            HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
            exit;
        }
    }


    /**
     * shows info about a given package. Packages are either files which can be downloaded
     * or are in the folder defined in G_SoftwareTyp::$package_folder.
     *
     * @name showPackageInfo
     * @access public
     * @return mixed
     */
    public function showPackageInfo() {
        if($this->getParam("id")) {
            try {
                $file = $this->getFilePackage($this->getParam("id"));
            } catch(Exception $e) {
                return $e->getMessage();
            }


            return $this->showFileInfoForFile($file);
        } else {
            HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
            exit;
        }
    }

    /**
     * returns update-info page or redirects for given Update-File.
     *
     * @param string $file
     * @return string
     */
    protected function showFileInfoForFile($file) {
        if($file === null || !preg_match('/\.gfs$/i', $file)) {
            HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
            exit;
        }

        $data = G_SoftwareType::getInstallInfos($this, $file);

        if(is_string($data)) {
            return $data;
        } else if(is_array($data)) {
            $inst = new ViewAccessableData($data);
            $inst->filename = basename($file);
            $inst->fileid = convert::raw2text($this->getParam("id"));

            GlobalSessionManager::globalSession()->set("update." . $inst->fileid, $inst);

            return $inst->renderWith("admin/updateInfo.html");
        }

        AddContent::addError(lang("install_invalid_file", "The file you uploaded isn't a valid installer-package."));

        HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
        exit;
    }

    /**
     * gets filename of package by given id.
     *
     * @param $id
     * @throws StoreConnectionError
     * @return string
     */
    protected function getFilePackage($id) {
        $_SESSION["AppStore_updates"] = isset($_SESSION["AppStore_updates"]) ? $_SESSION["AppStore_updates"] : array();
        $_SESSION["updates"] = isset($_SESSION["updates"]) ? $_SESSION["updates"] : array();

        // check for files, which can be downloaded from the internet.
        if(in_array($id, $_SESSION["AppStore_updates"])) {

            // search for download-url.
            $url = array_search($id, $_SESSION["AppStore_updates"]);

            if(G_SoftwareType::isStoreAvailable()) {
                if($handle = @fopen($url, "r")) {

                    $file = G_SoftwareType::$package_folder . "/" . basename($url);

                    file_put_contents($file, $handle, LOCK_EX);

                    $_SESSION["updates"][$file] = $id;

                    return $file;
                } else {
                    throw new StoreConnectionError("Could not open socket.");
                }
            } else {
                throw new StoreConnectionError("Store is not available. Maybe you disabled remote connections?");
            }

            // check for local updates.
        } else if(in_array($id, $_SESSION["updates"])) {
            return array_search($id, $_SESSION["updates"]);
        } else {
            return null; // no update found.
        }
    }

    /**
     * shows a form for uploading a file.
     *
     * @name index
     * @access public
     * @return mixed|string
     */
    public function upload() {

        // show upload-button and a download-button. User can down and reupload file now.
        $form = new Form($this, "update", array(
            $file = new FileUpload("file", lang("update_file_upload"), array("gfs"), null, "updates")
        ), array(
            new FormAction("submit", lang("submit"), "checkUpdate")
        ));

        // check if we have a possible download.
        if(isset($_GET["download"]) && preg_match('/^http(s)?\:\/\/(www\.)?goma\-cms\.org/i', $_GET["download"])) {

            if($model = $this->tryToDownload($_GET["download"])) {
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/showInfo/" . $model->id);
                exit;
            }

            $form->add(new HTMLField("download", '<a href="'.addslashes($_GET["download"]).'" class="button">'.lang("update_file_download").'</a>'), 0);
        }

        $file->max_filesize = -1;
        $form->addValidator(new RequiredFields(array("file")), "valid");
        return $form->render();
    }

    /**
     * tries to download a file from goma-server and returns model if succeeded.
     *
     * @param   string url
     * @return  Uploads|null
     */
    protected function tryToDownload($url) {
        // try to download the file, else just show Download-Button and user must zpload.
        $filename = ROOT . CACHE_DIRECTORY . md5(basename($url)) . ".gfs";
        if(file_put_contents(ROOT . CACHE_DIRECTORY . md5(basename($url)) . ".gfs", @file_get_contents($url))) {
            if($model = Uploads::addFile(basename($url), $filename, "updates")) {
                return $model;
            }
        }

        return null;
    }

    /**
     * validates the update
     *
     *@name checkUpdate
     *@access public
     */
    public function checkUpdate($data) {
        $file = $data["file"]->realfile;
        if(!file_exists($file)) {
            HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
            exit;
        }


        HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/showInfo/" . $data["file"]->id . URLEND . "?redirect=" . urlencode(BASE_URI . BASE_SCRIPT . "admin" . URLEND));
        exit;
    }

    /**
     * installs the update
     *
     * @return bool
     */
    public function installUpdate() {
        if(preg_match('/^[0-9]+$/', $this->getParam("update"))) {
            if(!($fileid = $this->getParam("update"))) {
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
                exit;
            }

            if(!($file = DataObject::get_one("Uploads", array("id" => $fileid)))) {
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
                exit;
            }

            clearstatcache();
            if(!file_exists($file->realfile)) {
                $file->remove(true);
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
                exit;
            }

            if(!GlobalSessionManager::globalSession()->hasKey("update." . $file->id)) {
                AddContent::addError(lang("less_rights", "You are not permitted to do this."));

                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/upload/");
                exit;
            }

            $data = GlobalSessionManager::globalSession()->get("update." . $file->id);
        } else {

            if(!in_array($this->getParam("update"), $_SESSION["updates"])) {
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
                exit;
            }

            $file = array_search($this->getParam("update"), $_SESSION["updates"]);

            if(!preg_match('/\.gfs$/i', $file)) {
                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
                exit;
            }

            if(!GlobalSessionManager::globalSession()->hasKey("update." . $this->getParam("update"))) {
                AddContent::addError(lang("less_rights", "You are not permitted to do this."));

                HTTPResponse::redirect(BASE_URI . BASE_SCRIPT . "admin/update/");
                exit;
            }

            $data = GlobalSessionManager::globalSession()->get("update." . $this->getParam("update"));
        }

        return G_SoftwareType::install($data);
    }
}
