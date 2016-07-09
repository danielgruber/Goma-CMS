<?php defined("IN_GOMA") OR die();
/**
 * Goma Cache-Manager.
 *
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Core
 * @version		1.0
 */

class CacheManager {
    /**
     * minimum cache lifetime when clear is not forced.
     */
    const MIN_CACHE_LIFETIME = 300;
    /**
     * cache-directory.
     */
    protected $cacheDirectory;

    /**
     * if cache has been deleted last time.
     */
    protected $hasBeenDeletedInSession;

    /**
     * simple list of files that should be preserved.
     */
    public static $preservedFiles = array(
        "deletecache", "autoloader_exclude"
    );

    /**
     * constructor.
     * @param string $dir
     */
    public function __construct($dir) {
        $this->cacheDirectory = $this->addSlashToEnd($dir);
        $this->hasBeenDeletedInSession = false;
        $this->init();
    }

    /**
     * inits cache.
     */
    protected function init() {
        try {
            FileSystem::requireDir($this->cacheDirectory);

            if(!file_exists($this->cacheDirectory . "autoloader_exclude")) {
                if(file_put_contents($this->cacheDirectory . "autoloader_exclude", "") === false) {
                    $this->throwInitException();
                }
            }
        } catch(Exception $e) {
            $this->throwInitException($e);
        }
    }

    /**
     * throws exception that cache directory is not creatable.
     */
    protected function throwInitException($e = null) {
        include_once("system/core/CoreLibs/ExceptionManager.php");
        throw new LogicException("Cache-Directory must exist or creatable. " . $this->cacheDirectory, ExceptionManager::ERR_CACHE_NOT_INITED, $e);
    }

    /**
     * returns when the cache has been cleared last time.
     */
    public function lastClearingTime() {
        if(!file_exists($this->cacheDirectory . "age")) {
            return time();
        }

        return file_get_contents($this->cacheDirectory . "age");
    }

    /**
     * returns if cache should be deleted by time.
     */
    public function shouldDeleteCache() {
        return ($this->lastClearingTime() < time() - self::MIN_CACHE_LIFETIME) && !$this->hasBeenDeletedInSession;
    }

    /**
     * returns whether cache was deleted in this session.
     */
    public function cacheHasBeenDeleted() {
        return $this->hasBeenDeletedInSession;
    }

    /**
     * returns cache-directory.
     */
    public function dir() {
        return $this->cacheDirectory;
    }

    /**
     * puts a file in cache with given contents.
     *
     * @param string $file
     * @param string $content
     * @param int $mode file permissions
     * @return bool
     */
    public function put($file, $content, $mode = 0600) {
        return FileSystem::writeFileContents($this->cacheDirectory . $file, $content, LOCK_EX, $mode);
    }

    /**
     * removes a file from cache.
     */
    public function rm($file) {
        if(file_exists($this->cacheDirectory . $file)) {
            return FileSystem::Delete($this->cacheDirectory . $file);
        }

        return false;
    }

    /**
     * returns if a file exists.
     *
     * @param string $file
     * @return bool
     */
    public function exists($file) {
        return file_exists($this->cacheDirectory . $file);
    }

    /**
     * returns file-contents of a file or null if not exists.
     *
     * @param string $file
     * @return null|string
     */
    public function contents($file) {
        if(file_exists($this->cacheDirectory . $file)) {
            return file_get_contents($this->cacheDirectory . $file);
        }

        return null;
    }

    /**
     * deletes the cache.
     *
     * @param 	int $minLifeTime minimum lifetime for all elements
     * @param 	boolean $forceDeleteFolders force delete folders also when they contain a .dontremove file.
     */
    public function deleteCache($minLifeTime = 0, $forceDeleteFolders = false) {
        clearstatcache();

        foreach(scandir($this->cacheDirectory) as $file) {
            if($file != "." && $file != "..") {
                // folders
                if(is_dir($this->cacheDirectory . $file) && $this->shouldDeleteCacheFolder($file, $forceDeleteFolders)) {
                    $this->rm($file);
                } else if($this->shouldDeleteCacheFile($file, $minLifeTime)) { // files
                    $this->rm($file);
                }
            }
        }

        FileSystem::Delete(ROOT . APPLICATION . "/uploads/d05257d352046561b5bfa2650322d82d");

        FileSystem::Write($this->cacheDirectory . "deletecache", time());

        clearstatcache();
    }

    /**
     * returns if the folder should be removed.
     * @param boolean $forceFolders
     * @return bool
     */
    public function shouldDeleteCacheFolder($file, $forceFolders) {
        if(file_exists($this->cacheDirectory . $file . "/.dontremove") && !$forceFolders) {
            return false;
        }

        $return = true;
        Core::callHook("shouldDeleteCacheFolder", $file, $forceFolders, $this, $return);

        return $return;
    }

    /**
     * returns if you should delete a file.
     * @param string $file filename
     * @param int $maxLifeTime maximum lifetime of files
     * @return bool
     */
    public function shouldDeleteCacheFile($file, $maxLifeTime) {

        if(in_array($file, self::$preservedFiles)) {
            return false;
        }

        // lifetime for GFS-Files is 2 hours cause it is used for upgrade.
        if(substr($file, 0, 3) == "gfs" && filemtime($this->dir() . $file) > NOW - 7200) {
            return false;
        }

        // lifetime for sessions is 1 hour.
        if(preg_match('/^data\.([a-zA-Z0-9_]{20})\.goma$/Usi', $file)) {
            if(filemtime($this->cacheDirectory . $file) > NOW - 3600) {
                return false;
            }
        }

        // lifetime for uploads is 1 hour of last touch.
        if(preg_match('/^upload\.([a-zA-Z0-9_]{20})\.goma$/Usi', $file)) {
            if(filemtime($this->cacheDirectory . $file) > NOW - 3600) {
                return false;
            }
        }

        // check for lifetime for all files.
        if($maxLifeTime != 0 && filemtime($this->cacheDirectory . $file) >= time() - $maxLifeTime) {
            return false;
        }

        $return = true;
        Core::callHook("shouldDeleteCacheFile", $file, $maxLifeTime, $this, $return);

        return $return;
    }

    /**
     * cache-directory should end with /.
     */
    protected function addSlashToEnd($dir) {
        if(substr($dir, -1) != "/") {
            return $dir . "/";
        }

        return $dir;
    }
}