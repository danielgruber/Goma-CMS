<?php defined("IN_GOMA") OR die();

/**
 * CKEditor Uploader.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class CKEditorUploadsController extends RequestHandler {

    /**
     * file contents for access file.
     */
    const FILE_CONTENT = "ckeditor_permit";

    /**
     * @var array
     */
    public $url_handlers = array(
        "ck_uploader"			=> "ckeditor_upload",
        "ck_imageuploader"		=> "ckeditor_imageupload",
    );

    public $allowed_actions = array(
        "ckeditor_upload",
        "ckeditor_imageupload"
    );

    /**
     * allowed file types for standard upload.
     */
    public static $allowed_file_types = array(
        "jpg",
        "png",
        "bmp",
        "jpeg",
        "zip",
        "rar",
        "doc",
        "txt",
        "text",
        "pdf",
        "dmg",
        "7z",
        "gif",
        "mp3",
        "xls",
        "xlsx",
        "docx",
        "pptx",
        "numbers",
        "key",
        "pages"
    );

    /**
     * allowed file size for files.
     */
    public static $allowed_file_size = 104875600;

    /**
     * allowed image filesize.
     */
    public static $allowed_image_size = 20971520;

    /**
     * allowed image types.
     */
    public static $allowed_image_types = array(
        "jpg",
        "png",
        "bmp",
        "jpeg",
        "gif"
    );

    /**
     * gets an upload-token.
     */
    public static function getUploadToken() {
        $accessToken = randomString(20);
        $file = ROOT . CACHE_DIRECTORY . "data." . $accessToken . ".goma";
        FileSystem::write($file, self::FILE_CONTENT, LOCK_EX);

        return $accessToken;
    }

    /**
     * uploads files for the ckeditor
     *
     * @name ckeditor_upload
     * @access public
     * @return string
     */
    public function ckeditor_upload() {
        try {
            $fileInfo = $this->validateUpload(self::$allowed_file_types, self::$allowed_file_size);

            // add file to upload storage
            if($response = Uploads::addFile($fileInfo[0], $fileInfo[1], "ckeditor_uploads")) {
                return $this->respondToUpload(true, $this->getFileUrl($response), $response->filename, "");
            } else {
                return $this->respondToUpload(false, "", "", lang("files.upload_failure"));
            }

        } catch(LogicException $e) {
            return $this->respondToUpload(false, "", "", $e->getMessage());
        }
    }

    /**
     * uploads files for the ckeditor
     *
     * @name ckeditor_upload
     * @access public
     * @return string
     */
    public function ckeditor_imageupload() {
        try {
            $fileInfo = $this->validateUpload(self::$allowed_image_types, self::$allowed_image_size);

            // add file to upload storage
            if($response = Uploads::addFile($fileInfo[0], $fileInfo[1], "ckeditor_uploads")) {
                $info = GetImageSize($response->realfile);
                $width = $info[0];
                $height = $info[0];
                if(filesize($response->realfile) > 1024 * 1024 * 4 || $width > HTMLText::MAX_RESIZE_WIDTH || $height > HTMLText::MAX_RESIZE_HEIGHT) {
                    $add = lang("alert_big_image");
                } else {
                    $add = "";
                }

                return $this->respondToUpload(true, $this->getFileUrl($response), $response->filename, $add);
            } else {
                return $this->respondToUpload(false, "", "", lang("files.upload_failure"));
            }

        } catch(LogicException $e) {
            return $this->respondToUpload(false, "", "", $e->getMessage());
        }
    }

    /**
     * returns file url from File-Path.
     *
     * @param Uploads $response
     * @return string
     */
    protected function getFileUrl($response) {
        return './'.$response->path . "/index" . substr($response->filename, strrpos($response->filename, "."));
    }

    /**
     * @param bool $uploaded
     * @param string $path
     * @param string $filename
     * @param string $error
     * @return string
     */
    protected function respondToUpload($uploaded, $path, $filename, $error) {
        if(!isset($_GET["CKEditorFuncNum"])) {
            HTTPResponse::setHeader("content-type", "application/json");

            $response = array(
                "uploaded" => (int) $uploaded
            );

            if($path) {
                $response["url"] = $path;
                $response["fileName"] = $filename;
            }

            if($error) {
                $response["error"] = array(
                    "message" => $error
                );
            }

            return json_encode($response);
        } else {
            return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).',
            '.var_export($path, true).', '.var_export($error, true).');</script>';
        }
    }

    /**
     * validates the upload.
     *
     * @param array $allowedTypes
     * @param int $allowedSize
     * @return array
     */
    protected static function validateUpload($allowedTypes, $allowedSize) {
        if(!isset($_GET["accessToken"]) ||
            !file_exists(self::getFileForKey($_GET["accessToken"])) ||
            file_get_contents(self::getFileForKey($_GET["accessToken"])) != self::FILE_CONTENT) {
            die(0);
        }

        if(isset($_SERVER["HTTP_X_FILE_NAME"]) && !isset($_FILES["upload"])) {
            if(Core::phpInputFile()) {
                $tmp_name = Core::phpInputFile();

                if(filesize($tmp_name) == $_SERVER["HTTP_X_FILE_SIZE"]) {
                    $_FILES["upload"] = array(
                        "name" => $_SERVER["HTTP_X_FILE_NAME"],
                        "size" => $_SERVER["HTTP_X_FILE_SIZE"],
                        "error" => 0,
                        "tmp_name" => $tmp_name
                    );
                }

            }
        }

        if(isset($_FILES["upload"])) {
            if($_FILES["upload"]["error"] == UPLOAD_ERR_OK) {
                if (preg_match('/\.(' . implode("|", $allowedTypes) . ')$/i', $_FILES["upload"]["name"])) {
                    $filename = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $_FILES["upload"]["name"]);
                    if ($_FILES["upload"]["size"] <= $allowedSize) {
                        return array($filename, $_FILES["upload"]["tmp_name"]);
                    } else {
                        throw new LogicException(lang("files.filesize_failure"));
                    }
                } else {
                    throw new LogicException(lang("files.filetype_failure"));
                }
            } else {
                if($_FILES["upload"]["error"] == UPLOAD_ERR_INI_SIZE) {
                    throw new LogicException(lang("files.filesize_failure"));
                } else {
                    throw new LogicException(lang("files.upload_failure"));
                }
            }
        } else {
            throw new LogicException(lang("files.upload_failure"));
        }
    }

    /**
     * returns file by key.
     */
    protected static function getFileForKey($key) {
        return ROOT . CACHE_DIRECTORY . "data." . $key . ".goma";
    }
}
