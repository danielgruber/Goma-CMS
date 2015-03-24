<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for GFS Archive-Unpacking with Page which is reloading sometimes.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		2.7.3
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
	 *@name unpack
	 *@access public
	 *@param string - directory to which we unpack
	*/
	public function unpack($destination, $path = "") {
		if($path != "") {	
			//! TODO: Support Subfolders!
			throwError(6, "Wrong-Argument-Error", "GFS_Package_Installer doesn't support subfolders.");
		}
		if(!$this->valid) {
			return false;
		}
		
		// first we write everything to a temporary folder
		$tempfolder = ROOT . CACHE_DIRECTORY . "/" . basename($this->file);
		
		
		/*$f = @disk_free_space("/");
		if($f !== null && $f !== "" && $f !== false) {
			// check for disk-quote
			$free = (disk_free_space("/") > disk_free_space(ROOT)) ? disk_free_space(ROOT) : disk_free_space("/");
			define("GOMA_FREE_SPACE", $free);
			if($free / 1024 / 1024 < 5) {
				// free space
				FileSystem::delete($tempfolder);
				header("HTTP/1.1 500 Server Error");
				die(file_get_contents(ROOT . "system/templates/framework/disc_quota_exceeded.html"));
			}
		}*/
		
		// write files
		$this->status = "Writing files...";
		$this->current = "";
		
		// we get time, if it is over 2, we reload ;)
		$start = microtime(true);
		$number = count($this->db);
		if(file_exists($tempfolder . "/.gfsprogess")) {
			
			$data = file_get_contents($tempfolder . "/.gfsprogess");
			if(preg_match('/^[0-9]+$/i', $data)) {
				$i = $data;
				$count = 1;
			} else {
				$data = unserialize($data);
				$i = $data["i"];
				$count = $data["count"];
			}
			
		} else {
  	 		FileSystem::requireDir($tempfolder);
			$i = 0;
			$count = 1;
		}
		
		$db = array_values($this->db);
		$paths = array_keys($this->db);
		
		// let's go
		while($i < count($db)) {
			
			$path = $paths[$i];
			$data = $db[$i];
			if($data["type"] == GFS_DIR_TYPE) {
				FileSystem::requireDir($tempfolder . "/" . $path);
			} else {
				if(!file_exists($tempfolder . "/" . $path)) {
					FileSystem::RequireDir(substr($tempfolder . "/" . $path, 0, strrpos($tempfolder . "/" . $path, "/")));
					$this->writeToFileSystem($path, $tempfolder . "/" . $path);
					@chmod($tempfolder . "/" . $path, isset($this->writeMode) ? $this->writeMode : 0777);
				}
			}
			$this->current = basename($path);
			
			// maximum 2.0 second
			if(microtime(true) - $start > 2.0) {
				$i++;
				$count++;
				file_put_contents($tempfolder . "/.gfsprogess", serialize(array("i" => $i, "count" => $count)));
				$this->progress = ($i / count($this->db) * 100) * 0.7;
				$perhit = $i / $count;
				$remaining = round((round((count($this->db) - $i) / $perhit * 3) + 3) * 1.42);
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
				if(defined("IN_GFS_EXTERNAL")) {
					$this->showUI();
				} else {
					$file = $this->buildFile($destination);
					$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&unpack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?unpack[]=".urlencode($this->file)."";
					if(count(self::$unpacked)) {
						foreach(self::$unpacked as $file) {
							$uri .= "&unpack[]=" . urlencode($file);
						}
					}
					$this->showUI($file . "?redirect=" . urlencode($uri));
				}
			}
			$i++;
			unset($data, $path);
		}
		
		// now move all files
		if(file_exists($tempfolder . "/.gfsrprogess")) {
			
			$data = file_get_contents($tempfolder . "/.gfsrprogess");
			if(preg_match('/^[0-9]+$/i', $data)) {
				$i = $data;
				$count = 1;
			} else {
				$data = unserialize($data);
				$i = $data["i"];
				$count = $data["count"];
			}
			
		} else {
			$i = 0;
			$count = 1;
		}
		
		// let's go
		while($i < count($db)) {
			$path = $paths[$i];
			$data = $db[$i];
			if($data["type"] == GFS_DIR_TYPE) {
					FileSystem::requireDir($destination . "/" . $path);
			} else {
				FileSystem::requireDir(substr($destination . "/" . $path, 0, strrpos($destination . "/" . $path, "/")));
				// helps in some cases ;)
				@unlink($destination . "/" . $path);
				if(@rename($tempfolder . "/" . $path, $destination . "/" . $path))
					chmod($destination . "/" . $path, isset($this->writeMode) ? $this->writeMode : 0777);
			
			}
			
			$this->status = "Renaming files...";
			$this->current = basename($path);
			
			// maximum of 0.5 seconds
			if(microtime(true) - $start > 2.0) {
				$i++;
				$count++;
				file_put_contents($tempfolder . "/.gfsrprogess", serialize(array("i" => $i, "count" => $count)));
				$this->progress = 70 + ($i / count($this->db) * 100) * 0.3;
				$perhit = $i / $count;
				$remaining = round((round((count($this->db) - $i) / $perhit * 3) + 3) * 0.40);
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
				if(defined("IN_GFS_EXTERNAL")) {
					$this->showUI();
				} else {
					$file = $this->buildFile($destination);
					
					$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&unpack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?unpack[]=".urlencode($this->file)."";
					if(count(self::$unpacked)) {
						foreach(self::$unpacked as $file) {
							$uri .= "&unpack[]=" . urlencode($file);
						}
					}
					$this->showUI($file . "?redirect=" . urlencode($uri));
				}
				
			}
			$i++;
			unset($data, $path);
		}
		
		self::$unpacked[] = $this->file;
		
		// clean up
		
		FileSystem::delete($tempfolder);
		
		if(defined("IN_GFS_EXTERNAL")) {
			if(isset($_GET["redirect"]))
				header("Location:" . $_GET["redirect"]);
			exit; 
		}
		return true;
		
	}
	
	/**
	 * if a specific file was unpacked
	 *
	 *@name wasUnpacked
	 *@access public
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
	 *@name buildFile
	 *@access public
	*/
	public function buildFile($destination) {

		$goma = new GomaSeperatedEnvironment();
		$goma->addClasses(array("gfs", "GFS_Package_installer"));

		$code = 'try { 
					$gfs = new GFS_Package_Installer('.var_export($this->file, true).'); 
				} catch(Exception $e) { 
					echo "<script type=\"text/javascript\">setTimeout(location.reload, 1000);</script> An Error occurred. Please <a href=\"\">Reload</a>"; exit; 
				}';
		$code .= '$gfs->unpack('.var_export($destination, true).');';

		$file = $goma->build($code);

		return $file;
 
	}
	
	/**
	 * shows the ui
	 *
	 *@name showUI
	 *@access public
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