<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 19.01.2012
  * $Version 1.3
*/   
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class Cacher extends Object {
	/**
	 * this var declares, whether cache is active or not
	 *@name active
	 *@access private
	 *@use whether cache active or not
	 *@var bool
	*/
	private $active = true;
	
	/**
	 * it is the data of the cachefile
	 *@name data
	 *@access private
	 *@use to save the private data
	 *@var string
	*/
	private $data;
	
	/**
	 * whether cache is valid
	 *@name valid
	 *@access private
	 *@use to save the validation result
	 *@var bool
	*/
	private $valid;
	
	/**
	 * the filename
	 *@name filename
	 *@access private
	 *@use to save the path to the file
	 *@var string
	*/
	private $filename;
	
	/**
	 * time created
	 *
	 *@name created
	*/
	public $created;
	
	/**
	 * name of this cache
	 *
	 *@name name
	 *@access public
	*/
	public $name;
	
	/**
	 * don't set from external
	 * directory
	 *
	 *@name dir
	 *@access public
	 *@use to save the directory
	 *@var sting
	*/
	static $dir = CACHE_DIRECTORY;
	
	/**
	 * current data
	 *@name _data
	 *@var array
	*/
	static $_data = array();
	
	
	/**
	 * constructs the cacher
	 *
	 *@name __cunstruct
	 *@access public
	 *@param string - name of the cache
	 *@use to init an cacher
	*/
	public function __construct($name, $important = false) {
		
		if(PROFILE) Profiler::mark("cacher");
		
		parent::__construct();
		
		/* --- */
		
		if(!$important)
			$this->filename = self::$dir . 'cache.' . urlencode($name) . ".php";
		else
			$this->filename = self::$dir . 'cache.' . urlencode($name) . ".cache";
			
		$this->name = $name;
		if(isset($_GET['flush']) && !$important) {
			$this->active = false;
		} else {
			// internal cache
			if(isset(self::$_data[$name])) {
				$this->data = self::$_data[$name];
				$this->valid = true;
				if(PROFILE) Profiler::unmark("cacher");
				return "";
			}
			
			// file-cache
			if($this->active == true)
			{
				// if cache == active
				if(file_exists($this->filename)) {
					// file exists
					require_once($this->filename);
					$this->data = (isset($data)) ? $data : "";
					$time = (isset($time)) ? $time : "";
					$this->created = $time;
					if($time > DATE) {
						self::$_data[$name] = $this->data;
						$this->valid = true;
					} else {
						$this->valid = false;
					}
				} else {
					$this->valid = false;
					$this->data = "";   // no data !!
				}
			}
		}
		
		if(PROFILE) Profiler::unmark("cacher");
	}
	
	/**
	 * checks whether cache is valid
	 *
	 *@name checkvalid
	 *@access public
	 *@use to get the validation-result
	 *@return bool - validation result
	*/
	public function checkvalid() {
		if($this->active === true) {
			return $this->valid;
		} else {
			return false;
		}
	}
	
	/**
	 * gets the data of the cache
	 *@name getdata
	 *@access public
	 *@use to get the data
	 *@return string - data
	*/
	public function getData() {
		if(PROFILE) Profiler::mark("cacher");
		$data = unserialize( $this->data );		
		if(PROFILE) Profiler::unmark("cacher");
		return $data;
	}
	
	/**
	 * deletes the cachefile
	 *@name delete
	 *@access public
	 *@use to delete the file
	*/
	public function delete() {
		unset(self::$_data[$this->name]);
		return @unlink ($this->filename);
	}
	
	/**
	 * writes data into the cachefile
	 *
	 *@name write
	 *@access public
	 *@param string - the data of the cache
	 *@param (string|numeric) - the time, how long the cache is valid in seconds
	 *@return bool - result
	*/
	public function write( $data , $time = 60 ) {
		if(PROFILE) Profiler::mark("cacher_write");
		
		$data = serialize( $data );
		if($this->active == true) {
			
			// calculate the active time
			$time = DATE + $time;
			
			// build the file
			$d = '<?php 
			defined(\'IN_GOMA\') OR die(\'<!-- restricted access -->\'); // silence is golden ;) 
			$time = '.$time.'; 
			$data = '.var_export($data, true). ';';
			
			// write the file
			if(FileSystem::write($this->filename, $d, null, 0773)) {
				if(PROFILE) Profiler::unmark("cacher_write");
				return true;
			} else {
				throwError(6,'PHP-Error','Can\'t create cache-file in folder includes/cache/. Please check write-permissions there.');
			}
			
		}
	}
}