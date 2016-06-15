<?php defined("IN_GOMA") OR die();

/**
 * Base-Class for GFS Archive-Creation with Page which is reloading sometimes.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		2.7.2
 */

class GFS_Package_Creator extends GFS {
	public $status;
	public $current;
	public $progress;
	public $remaining;
	
	// packed files for evantually later reload
	static public $packed = array();
	
	/**
	 * defines if we commit changes after adding files
	 *
	 *@name autoCommit
	 *@access public
	*/
	public $autoCommit = true;
	
	/**
	 * index of files of the next operation
	 *
	 *@name fileIndex
	 *@access protected
	*/
	protected $fileIndex = array();
	
	/**
	 * construct with read-only
	 *
	 *@name __construct
	 *@access public
	*/
	public function __construct($filename) {
		parent::__construct($filename, GFS_READWRITE);
	}
	
	/**
	 * adds a folder
	 *
	 *@name add
	 *@access public
	 *@param string - directory which we add
	 *@param string - path to which we write
	 *@param array - subfolder, we want to exclude
	*/
	public function add($file, $path = "", $excludeList = array()){
	
		
		
		// create index
		
		$this->indexHelper($file, $this->fileIndex, $path, $excludeList);
		
		if($this->autoCommit) {
			$this->commit();
		}
		
		return true;
		
	}
	
	/**
	 * sets the value of auto-commit
	 *
	 *@name setAutoCommit
	 *@access public
	 *@param bool
	*/
	public function setAutoCommit($commit) {
		$this->autoCommit = $commit;
	}
	
	/**
	 * commits the changes
	 *
	 *@name commit
	 *@access public
	*/
	public function commit($inFile = null, $index = null) {
		if(isset($index)) {
			$this->fileIndex = $index;
		}
		
		// Adding files...
		$this->status = "Adding files...";
		$this->current = "";
		
		// for reloading early enough
		$start = microtime(true);
		if($start - EXEC_START_TIME > 5) {
			$start += 0.9;
		}
		
		// create index-progress-file
		if($this->exists("/gfsprogress" . count($this->fileIndex))) {
			$data = $this->getFileContents("/gfsprogress" . count($this->fileIndex));
			$data = unserialize($data);
			$i = $data["i"];
			$count = $data["count"];
		} else {
			$count = 1;
			$i = 0;
			$this->addFile("/gfsprogress" . count($this->fileIndex), serialize(array("i" => $i, "count" => $count)));
		}

		$realfiles = array_keys($this->fileIndex);
		$paths = array_values($this->fileIndex);

		// iterate through the index
		while($i < count($this->fileIndex)){
			// maximum of 2.0 seconds
			if(microtime(true) - $start < 2.0) {
				if(!$this->exists($paths[$i])) {
					$this->addFromFile($realfiles[$i], $paths[$i]);
				}
			} else {
				$count++;
				$this->write("/gfsprogress" . count($this->fileIndex), serialize(array("i" => $i, "count" => $count)));
				$this->close();
				$this->progress = ($i / count($this->fileIndex) * 100);
				$perhit = $i / $count;
				$remaining = (round((count($index) - $i) / $perhit * 3) + 3);
				$this->current = $paths[$i];
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
				
				if(!isset($inFile)) {
					// build the external file and redirect-uri
					$file = $this->buildFile($this->fileIndex);
					$uri = strpos($_SERVER["REQUEST_URI"], "?") ? $_SERVER["REQUEST_URI"] . "&pack[]=".urlencode($this->file)."" : $_SERVER["REQUEST_URI"] . "?pack[]=".urlencode($this->file)."";
					if(count(self::$packed)) {
						foreach(self::$packed as $file) {
							$uri .= "&pack[]=" . urlencode($file);
						}
					}
					$this->showUI($file . "?redirect=" . urlencode($uri));
				} else {
					// if we are in the external file
				 	$this->showUI();	
				}
			}
			$i++;
		}
		
		self::$packed[$this->file] = $this->file;
		$this->unlink("/gfsprogress" . count($this->fileIndex));
		//$this->fileIndex = array();
		
		// if we are in the external file
		if(isset($inFile)) {
			@unlink($inFile);
			if(isset($_GET["redirect"])) {
				header("Location:" . $_GET["redirect"]);
				exit;
			} else {
				header("Location:" . ROOT_PATH);
				exit;
			}
		}
	}

	/**
	 * if a specific file was packed
	 *
	 * @name wasPacked
	 * @access public
	 * @return bool
	 */
	public static function wasPacked($file = null) {
		if(isset($file)) {
			$file = str_replace('\\\\', '\\', realpath($file));
			$file = str_replace('\\', '/', realpath($file));
			$pack = isset($_GET["pack"]) ? str_replace('\\', '/', str_replace('\\\\', '\\', $_GET["pack"])) : array();
			
			if(isset($_GET["pack"])) {
				$file = realpath($file);
				return in_array($file, $pack);
			} else {
				return false;
			}
		} else {
			if(isset($_GET["pack"]))
				return true;
			else
				return false;
		}
	}
	
	/**
	 * builds the Code for the external file
	*/
	public function buildFile($index) {
		$goma = new GomaSeperatedEnvironment();
		$goma->addClasses(array("gfs", "GFS_Package_Creator"));

		$code = 'try { 
					$gfs = new GFS_Package_Creator('.var_export($this->file, true).');
					$gfs->commit(__FILE__, '.var_export($index, true).');
				} catch(Exception $e) { 
					echo "<script type=\"text/javascript\">setTimeout(location.reload, 1000);</script> An Error occurred. Please <a href=\"\">Reload</a>"; exit; 
				}';

		$file = $goma->build($code);


		return $file;

	}
	
	/**
	 * creates the index
	*/ 
	public function indexHelper($folder, &$index, $path, $excludeList = array(), $internalPath = "") {
		foreach(scandir($folder) as $file) {
			if($file != "." && $file != "..") {
				if(in_array($file, $excludeList) || in_array($internalPath . "/" . $file, $excludeList)) {
					continue;
				}
				if(is_dir($folder . "/" . $file)) {
					$this->indexHelper($folder . "/" . $file, $index, $path . "/" . $file, $excludeList, $internalPath . "/" . $file);
				} else {
					$index[$folder . "/" . $file] = $path . "/" . $file;
				}
			}
		}
	}
	/**
	 * shows the ui
	*/
	public function showUI($file = null, $reload = true) {
		if(!defined("BASE_URI")) define("BASE_URI", "./"); // most of the users use this path ;)
		
		$template = new Template();
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