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

	/**
	 * if permissions are not enough to view this page.
	*/
	const PERMISSION_ERROR = -5;

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
}