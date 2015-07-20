<?php defined("IN_GOMA") OR die();

/**
 * Simple class that is used for caching data for a several time.
 * it will support memory-caches in future.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.3.3
 *
 * last modified: 20.07.2015
*/

class Cacher {
	/**
	 * this var declares, whether cache is using cache-file or not
	 *
	 * @name active
	 * @access private
	 * @use whether cache active or not
	 * @var bool
	*/
	private $active = true;
	
	/**
	 * it is the data of the cachefile
	 *
	 * @name data
	 * @access private
	 * @use to save the private data
	 * @var string
	*/
	private $data;

	/**
	 * short-time data cache that is used when isValid get called, so that no other thread can
	 * remove data from the isValid call until the getData-Call.
	*/
	private $privateData;

	/**
	 * the filename
	 *
	 * @name filename
	 * @access private
	 * @use to save the path to the file
	 * @var string
	*/
	private $filename;
	
	/**
	 * time created
	 *
	 * @name created
	*/
	public $created;
	
	/**
	 * name of this cache
	 *
	 * @name name
	 * @access public
	*/
	public $name;
	
	/**
	 * default CacheManager which is used for this Cacher.
	 *
	 * @name dir
	 * @access public
	 * @use to save the directory
	 * @var sting
	*/
	public static $manager;
	
	/**
	 * current data
	 *
	 * @name _data
	 * @var array
	*/
	static $_data = array();
	
	/**
	 * clears cache of current PHP-Instance.
	*/
	public static function clearInstanceCache($name = null) {
		if($name === null) {
			self::$_data = array();
		} else {
			if(isset(self::$_data[$name])) {
				unset(self::$_data[$name]);
			}
		}
	}
	
	/**
	 * returns true if data exists in PHP-Instance-Cache.
	*/
	public static function dataInPHPInstance($name) {
		if(isset(self::$_data[$name]) && self::$_data[$name]["expires"] > time()) {
			return true;
		} else {
			self::clearInstanceCache($name);
			return false;
		}
	}

	/**
	 * returns data from current PHP-Instance-Cache.
	*/
	public static function getDataFromInstance($name) {
		return self::dataInPHPInstance($name) ? self::$_data[$name]["data"] : null;
	}

	/**
	 * gets created info from instance.
	*/
	public static function getCreatedFromInstance($name) {
		return self::dataInPHPInstance($name) ? self::$_data[$name]["created"] : null;
	}

	/**
	 * adds data to instance-cache.
	 *
	 * @param 	string 	name
	 * @param 	mixed 	data
	 * @param 	int 	lifetime in seconds
	*/
	public static function addDataToInstance($name, $data, $lifetime) {
		self::$_data[$name] = array(
			"data" 		=> $data,
			"expires"	=> time() + $lifetime,
			"created"	=> time()
		);
	}


	/**
	 * constructs the cacher
	 *
	 * @name __cunstruct
	 * @access public
	 * @param string - name of the cache
	 * @use to init an cacher
	*/
	public function __construct($name, $important = false) {

		if(PROFILE) Profiler::mark("cacher");

		if(!$important) {
			$this->filename = 'cache.' . urlencode($name) . ".php";
		} else {
			$this->filename = 'cache.' . urlencode($name) . ".cache";
		}

		$this->name = $name;

		if(self::dataInPHPInstance($name)) {
			$this->created = self::getCreatedFromInstance($name);
		} else { // file-cache is triggered when not in internal cache.

			if(self::$manager->exists($this->filename)) {

				// file exists
				include(self::$manager->dir() . $this->filename);

				
				$time = isset($time) ? $time : 0;

				if($time >= time()) {
					$this->data = (isset($data)) ? $data : null;
					$this->created = isset($data) ? $time : null;
				}
			}
		}
		
		
		if(PROFILE) Profiler::unmark("cacher");
	}
	
	/**
	 * checks whether cache is valid
	 *
	 * @name checkvalid
	 * @access public
	 * @use to get the validation-result
	 * @return bool - validation result
	*/
	public function checkValid() {
		if($this->active === true) {

			// thread-safe storage.
			if(isset($this->privateData)) {
				return true;
			}

			if(self::dataInPHPInstance($this->name) || isset($this->data)) {
				// store data in private data for thread-safety.
				$this->privateData = self::dataInPHPInstance($this->name) ? self::getDataFromInstance($this->name) : unserialize($this->data);
				return true;
			}

		}
		
		return false;
	}

	/**
	 * isValid.
	*/
	public function isValid() {
		return $this->checkValid();
	}

	/**
	 * gets the data of the cache
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getData() {
		if(PROFILE) Profiler::mark("cacher");

		if(isset($this->privateData)) {
			$data = $this->privateData;
		} else if(self::dataInPHPInstance($this->name)) {
			$data = self::getDataFromInstance($this->name);
		} else if(isset($this->data)) {
			$data = unserialize( $this->data );
		} else {
			throw new Exception("Data not exists anymore for this cacher.");
		}

		if(PROFILE) Profiler::unmark("cacher");
		return $data;
	}
	
	/**
	 * deletes the cachefile
	 *
	 * @name delete
	 * @access public
	 * @use to delete the file
	*/
	public function delete() {
		self::clearInstanceCache($this->name);
		$this->data = null;
		$this->privateData = null;
		return self::$manager->rm ($this->filename);
	}
	
	/**
	 * writes data into the cachefile
	 *
	 * @name write
	 * @access public
	 * @param string - the data of the cache
	 * @param (string|numeric) - the time, how long the cache is valid in seconds
	 * @return bool - result
	*/
	public function write( $data , $time = 60 ) {
		if(PROFILE) Profiler::mark("cacher_write");

		if($time < 0) {
			$time = 0;
		}

		self::addDataToInstance($this->name, $data, $time);

		$data = serialize( $data );

		$this->data = $data;

		if($this->active == true) {
			
			// calculate the active time
			$time = time() + $time;
			
			// build the file
			$d = '<?php 
			defined(\'IN_GOMA\') OR die(" ");
			$time = '.$time.'; 
			$data = '.var_export($data, true). ';';
			
			// write the file
			if(self::$manager->put($this->filename, $d)) {
				if(PROFILE) Profiler::unmark("cacher_write");
				return true;
			} else {
				if(PROFILE) Profiler::unmark("cacher_write");
				return false;
			}
			
		}
	}
}

Cacher::$manager = Core::$cacheManagerApplication;