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
     * uploads files for the ckeditor
     *
     * @name ckeditor_upload
     * @access public
     * @return string
     */
    public function ckeditor_upload() {

        if(!isset($_GET["accessToken"]) || !isset($_SESSION["uploadTokens"][$_GET["accessToken"]])) {
            die(0);
        }

        try {
            $fileInfo = $this->validateUpload(self::$allowed_file_types, self::$allowed_file_size);

            // add file to upload storage
            if($response = Uploads::addFile($fileInfo[0], $fileInfo[1], "ckeditor_uploads")) {
                echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', \'./'.$response->path.'\', "");</script>';
                exit;
            } else {
                return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
            }

        } catch(LogicException $e) {
            return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.$e->getMessage().'");</script>';
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

        if(!isset($_GET["accessToken"]) || !isset($_SESSION["uploadTokens"][$_GET["accessToken"]])) {
            die(0);
        }

        try {
            $fileInfo = $this->validateUpload(self::$allowed_image_types, self::$allowed_image_size);

            // add file to upload storage
            if($response = Uploads::addFile($fileInfo[0], $fileInfo[1], "ckeditor_uploads")) {
                $info = GetImageSize($response->realfile);
                $width = $info[0];
                $height = $info[0];
                if(filesize($response->realfile) > 1024 * 1024 * 4 || $width > HTMLText::MAX_RESIZE_WIDTH || $height > HTMLText::MAX_RESIZE_HEIGHT) {
                    $add = 'alert(parent.lang("alert_big_image"));';
                } else {
                    $add = "";
                }

                echo '<script type="text/javascript">' . $add .
							'window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', \'./'.$response->path . "/index" . substr($response->filename, strrpos($response->filename, ".")).'\', "");</script>';
                exit;
            } else {
                return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.lang("files.upload_failure").'");</script>';
            }

        } catch(LogicException $e) {
            return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.addSlashes($_GET['CKEditorFuncNum']).', "", "'.$e->getMessage().'");</script>';
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

        if(isset($_FILES["upload"]) && $_FILES["upload"]["error"] == 0) {
            if(preg_match('/\.('.implode("|", $allowedTypes).')$/i',$_FILES["upload"]["name"])) {
                $filename = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $_FILES["upload"]["name"]);
                if($_FILES["upload"]["size"] <= $allowedSize) {
                    return array($filename, $_FILES["upload"]["tmp_name"]);
                } else {
                    throw new LogicException(lang("files.filesize_failure"));
                }
            } else {
                throw new LogicException(lang("files.filetype_failure"));
            }
        } else {
            throw new LogicException(lang("files.upload_failure"));
        }
    }
}
