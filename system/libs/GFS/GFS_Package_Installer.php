<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for GFS Archive-Unpacking with Page which is reloading sometimes.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		2.7.4
 */

class GFS_Package_installer extends GFS {
	public $status;
	public $current;
	public $progress;
	public $remaining;

	/**
	 * already unpacked files
	 *
	 *@name unpacked
	 */
	public static $unpacked = array();

	/**
	 * local cache.
	 */
	private $paths;
	private $dbvalues;
	private $destination;

	/**
	 * construct with read-only
	 *
	 *@name __construct
	 *@access public
	 */
	public function __construct($filename) {
		parent::__construct($filename, GFS_READONLY);
	}

	/**
	 * unpack
	 *
	 * @name unpack
	 * @access public
	 * @param string $destination directory to which we unpack
	 * @param string $path not supported yet
	 * @return bool|int
	 */
	public function unpack($destination, $path = "") {
		if($path != "") {
			//! TODO: Support Subfolders!
			throw new InvalidArgumentException("GFS_Package_Installer doesn't support subfolders.");
		}
		if(!$this->valid) {
			return false;
		}

		// write files
		$this->status = "Writing files...";
		$this->current = "";

		// we get time, if it is over 2, we reload ;)
		$start = microtime(true);
		$info = $this->getProgressInfo(".gfsprogress");
		$i = $info[0];
		$count = $info[1];

		$this->dbvalues = array_values($this->db);
		$this->paths = array_keys($this->db);
		$this->destination = $destination;

		// let's go
		while($i < count($this->dbvalues)) {

			$this->unpackToFileSystem($i);

			// maximum 2.0 second
			$this->checkForTime($start, $i, $count, 0.7, 0);
			$i++;
			unset($data, $path);
		}

		// now move all files
		$rinfo = $this->getProgressInfo(".gfsrprogress");
		$i = $rinfo[0];
		$count = $rinfo[1];

		// let's go
		while($i < count($this->dbvalues)) {

			$this->renameUnpacked($i);

			// maximum of 0.5 seconds
			$this->checkForTime($start, $i, $count, 0.3, 0.7, ".gfsrprogress");
			$i++;
			unset($data, $path);
		}

		self::$unpacked[] = $this->file;

		// clean up

		FileSystem::delete($this->tempFolder());

		if(defined("IN_GFS_EXTERNAL")) {
			if(isset($_GET["redirect"]))
				header("Location:" . $_GET["redirect"]);
			exit;
		}
		return true;

	}

	/**
	 * gets progress info for given file.
	 *
	 * @param $file
	 * @return array
	 */
	protected function getProgressInfo($file) {
		if(file_exists($this->tempFolder() . "/" . $file)) {
			$data = file_get_contents($this->tempFolder() . "/" . $file);
			if(preg_match('/^[0-9]+$/i', $data)) {
				return array($data, 1);
				$i = $data;
				$count = 1;
			} else {
				$data = unserialize($data);
				return array($data["i"], $data["count"]);
			}
		} else {
			return array(0, 0);
		}
	}

	/**
	 * writes given index to filesystem.
	 *
	 * @param int $i
	 * @return void
	 */
	protected function unpackToFileSystem($i) {
		$path = $this->paths[$i];
		$data = $this->dbvalues[$i];
		if($data["type"] == GFS_DIR_TYPE) {
			FileSystem::requireDir($this->tempFolder() . "/" . $path);
		} else {
			if(!file_exists($this->tempFolder() . "/" . $path)) {
				FileSystem::RequireDir(substr($this->tempFolder() . "/" . $path, 0, strrpos($this->tempFolder() . "/" . $path, "/")));
				$this->writeToFileSystem($path, $this->tempFolder() . "/" . $path);
				@chmod($this->tempFolder() . "/" . $path, isset($this->writeMode) ? $this->writeMode : 0777);
			}
		}
		$this->current = basename($path);
	}

	/**
	 * renames file at index.
	 *
	 * @param $i
	 */
	protected function renameUnpacked($i) {
		$path = $this->paths[$i];
		$data = $this->dbvalues[$i];
		if($data["type"] == GFS_DIR_TYPE) {
			FileSystem::requireDir($this->destination . "/" . $path);
		} else {
			FileSystem::requireDir(substr($this->destination . "/" . $path, 0, strrpos($this->destination . "/" . $path, "/")));
			// helps in some cases ;)
			@unlink($this->destination . "/" . $path);
			if(@rename($this->tempFolder() . "/" . $path, $this->destination . "/" . $path))
				chmod($this->destination . "/" . $path, isset($this->writeMode) ? $this->writeMode : 0777);

		}

		$this->status = "Renaming files...";
		$this->current = basename($path);

	}

	/**
	 * checks for the time.
	 *
	 * @param int $start
	 * @param $i
	 * @param $count
	 * @param $operationMultiplier how much of 100% the operation should take
	 * @param $operationDone how much of 100% the previous has been taken
	 */
	protected function checkForTime($start, $i, $count, $operationMultiplier, $operationDone, $filename = ".gfsprogress") {
		// maximum 2.0 second
		if(microtime(true) - $start > 2.0) {
			$i++;
			$count++;
			file_put_contents($this->tempFolder() . "/" . $filename, serialize(array("i" => $i, "count" => $count)));

			$this->calculateRemaining($i, $count, $start, $operationMultiplier, $operationDone);

			if(defined("IN_GFS_EXTERNAL")) {
				$this->showUI();
			} else {
				$file = $this->buildFile($this->destination);
				$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&unpack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?unpack[]=".urlencode($this->file)."";
				if(count(self::$unpacked)) {
					foreach(self::$unpacked as $file) {
						$uri .= "&unpack[]=" . urlencode($file);
					}
				}

				$this->showUI($file . "?redirect=" . urlencode($uri));
			}
		}
	}

	/**
	 * calculates remaining.
	 *
	 * @param $i number of elements done
	 * @param $count number of requests done
	 * @param $startTime
	 * @param $operationMultiplier how much of 100% the operation should take
	 * @param $operationDone how much of 100% the previous has been taken
	 */
	protected function calculateRemaining($i, $count, $startTime, $operationMultiplier, $operationDone) {
		$this->progress = ($i / count($this->db) * 100) * $operationMultiplier + $operationDone;

		$perHitTime = (microtime(true) - $startTime) / $count;

		$remaining = round((100 - $this->progress) * $perHitTime / ($this->progress / $count) / 1000);
		if($remaining > 60) {
			$remaining = round($remaining / 60);
			if($remaining > 60) {
				$remaining = round($remaining / 60);
				$this->remaining = "More than ".$remaining." hours remaining";
			} else {
				$this->remaining = "More than ".$remaining." minutes remaining";
			}
		} else {
			$this->remaining = "More than ".$remaining." seconds remaining";
		}
	}

	/**
	 * returns temp-folder.
	 *
	 * @return string
	 */
	protected function tempFolder() {
		return ROOT . CACHE_DIRECTORY . "/" . basename($this->file);
	}

	/**
	 * if a specific file was unpacked
	 *
	 * @return bool
	 */
	public static function wasUnpacked($file = null) {
		if(isset($file)) {
			$file = str_replace('\\\\', '\\', realpath($file));
			$file = str_replace('\\', '/', realpath($file));
			$unpack = isset($_GET["unpack"]) ? str_replace('\\', '/', str_replace('\\\\', '\\', $_GET["unpack"])) : array();

			return in_array($file, $unpack);
		} else {
			if(isset($_GET["unpack"]))
				return true;
			else
				return false;
		}
	}

	/**
	 * builds the Code for the external file
	 *
	 * @return string
	 */
	public function buildFile($destination) {
		$goma = new GomaSeperatedEnvironment();
		$goma->addClasses(array("gfs", "GFS_Package_installer"));

		$code = 'try { 
					$gfs = new GFS_Package_Installer('.var_export($this->file, true).');
					$gfs->unpack('.var_export($destination, true).');
				} catch(Exception $e) { 
					echo "<script type=\"text/javascript\">setTimeout(location.reload, 1000);</script> An Error occurred. Please <a href=\"\">Reload</a>"; exit; 
				}';

		$file = $goma->build($code);

		return $file;

	}

	/**
	 * shows the ui
	 */
	public function showUI($file = "",$reload = true) {
		if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)

		$template = new Template;
		$template->assign("destination", $file);
		$template->assign("reload", $reload);
		$template->assign("archive", basename($this->file));
		$template->assign("progress", $this->progress);
		$template->assign("status", $this->status);
		$template->assign("current", $this->current);
		$template->assign("remaining", $this->remaining);
		echo $template->display("/system/templates/GFSUnpacker.html");
		exit;
	}
}
