<?php defined("IN_GOMA") OR die();
/**
 * Goma Exception-Manager.
 *
 * @package		Goma\Core
 * @version		1.0
 */

class ExceptionManager {
	/**
	 * used when cache could not be created cause directory does not exist,
	 * is not creatable or autoloader_exclude does not exist and can't be created.
	*/
	const ERR_CACHE_NOT_INITED = -250;

	/**
	 * db connect error.
	*/
	const DB_CONNECT_ERROR = -25;
	const SQL_EXCEPTION = -26;

	/**
	 * if permissions are not enough to view this page.
	*/
	const PERMISSION_ERROR = -5;

	/**
	 * normal exception.
	 */
	const EXCEPTION = -1;

	/**
	 * unknown PHP-Error.
	*/
	const PHP_ERROR = -6;

	/**
	 * application version missmatch.
	*/
	const APPLICATION_FRAMEWORK_VERSION_MISMATCH = -10;

	/**
	 * security-error.
	*/
	const SECURITY_ERROR = -1;

	/**
	 * error when classinfo could not be written.
	*/
	const CLASSINFO_WRITE_ERROR = -8;

    /**
     * class not found.
     */
    const CLASS_NOT_FOUND = -7;

	/**
	 * called when version.php cant be written.
	*/
	const SOFTWARE_UPGRADE_WRITE_ERROR = -12;

	/**
	 * called when expansion with name wasnt found, but data were requested.
	*/
	const EXPANSION_NOT_FOUND = -15;

	/**
 	 * email invalid.
	*/
	const EMAIL_INVALID = -20;

	/*
	 * login invalid.
	*/
	const LOGIN_INVALID = -16;

	/**
	 * user locked.
	*/
	const LOGIN_USER_LOCKED = -17;

	/**
	 * user not unlocked yet.
	*/
	const LOGIN_USER_MUST_UNLOCK = -18;

    /**
     * store connection failed.
     */
    const STORE_CONNECTION_FAIL = -31;

    /**
     * 503
     */
    const SERVICE_UNAVAILABLE = -503;

    /**
     * invalid indexes
     */
    const INDEX_INVALID = -60;

    /**
     * db field invalid.
     */
    const DB_FIELD_INVALID = -61;

    /**
     * no inverse found.
     */
    const RELATIONSHIP_INVERSE_REQUIRED = -62;

	/**
	 * data
	 */
	const BAD_REQUEST = -400;
	const DATA_NOT_FOUND = -404;

	/**
	 * gd.
	 */
	const GD_EXCEPTION = -700;
	const GD_FILE_MALFORMED = -701;
	const GD_TYPE_NOT_SUPPORTED = -701;

	/**
	 * file.
	 */
	const FILE_EXCEPTION = -800;
	const FILE_NOT_PERMITTED = -803;
	const FILE_NOT_FOUND = -804;
	const FILE_ALREADY_EXISTING = -810;
	const FILE_COPY_FAIL = -820;

	/**
	 * form
	 */
	const FORM_INVALID = -900;
	const FORM_NOT_SUBMITTED = -901;

	/**
	 * file upload
	 */
	const FILEUPLOAD_FAIL = -601;
	const FILEUPLOAD_SIZE_FAIL = -602;
	const FILEUPLOAD_TYPE_FAIL = -603;
	const FILEUPLOAD_DISK_SPACE_FAIL = -604;
	const TPL_COMPILE_ERROR = -10;

	/**
	 * lists
	 */
	const ITEM_NOT_FOUND = -944;

	/**
	 * model.
	 */
	const DATAOBJECTSET_COMMIT = -1105;

	/**
	 * gfs
	 */
	const GFSException = 4000;
	const GFSVersionException = 4001;
	const GFSFileException = 4002;
	const GFSDBException = 4003;
	const GFSReadOnlyException = 4004;
	const GFSFileNotFoundException = 4005;
	const GFSFileNotValidException = 4006;
	const GFSFileExistsException = 4007;
	const GFSRealFileNotExistsException = 4008;
	const GFSRealFilePermissionException = 4009;



}
