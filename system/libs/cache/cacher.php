<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 28.09.2011
*/   
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class cacher extends Object
{
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
	   * don't set!
	   * directory
       *@name dir
       *@access public
       *@use to save the directory
       *@var sting
       */
     static public $dir = CACHE_DIRECTORY;
	 /**
	  * current data
	  *@name _data
	  *@var array
	 */
	 static public $_data = array();
     /**
	   * constructs the cacher
       *@name __cunstruct
       *@access public
       *@param string - name of the cache
       *@use to init an cacher
       */

		public function __construct($name)
		{		
				if(PROFILE) Profiler::mark("cacher");
				
				parent::__construct();
				
				/* --- */
				
				$this->filename = self::$dir.'cache.'.urlencode($name).".php";
				if(isset($_GET['flush']) )
				{
					$this->active = false;
				} else
				{
						if(isset(self::$_data[$name]))
						{
								$this->data = self::$_data[$name];
								$this->valid = true;
								profiler::unmark("cacher");
								return "";
						}
						if($this->active == true)
						{
								// if cache == active
								if(file_exists($this->filename))
								{
										// file exists
										require_once($this->filename);
										$this->data = (isset($data)) ? $data : "";
										$time = (isset($time)) ? $time : "";
										$this->created = $time;
										if($time > DATE)
										{
												self::$_data[$name] = $this->data;
												$this->valid = true;
										} else 
										{
												$this->valid = false;
										}
								} else 
								{
										$this->valid = false;
										$this->data = "";   // no data !!
								}
						}
				}
				
				if(PROFILE) Profiler::unmark("cacher");
		}
      /**
	   * checks whether cache is valid
       *@name checkvalid
       *@access public
       *@use to get the validation-result
	   *@return bool - validation result
       */
		public function checkvalid()
		{
				if($this->active === true)
				{
						return $this->valid;
				} else 
				{
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
		public function getdata( )
		{
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
		public function delete( )
		{
				return @unlink ( $this->filename );
		}
      /**
	   * writes data in the cachefile
       *@name write
       *@access public
       *@param string - the data of the cache
       *@param (string|numeric) - the time, how long the cache is valid in seconds
       *@use to write the data into the file
	   *@return bool - result
       */
		public function write( $data , $time = 60 )
		{
				if(PROFILE) Profiler::mark("cacher_write");
				
				$data = serialize( $data );
				if($this->active == true)
				{
 
						$time = DATE + $time;
						$d = '<?php 
defined(\'IN_GOMA\') OR die(\'<!-- restricted access -->\'); // silence is golden ;) 
$time = '.$time.'; 
$data = '.var_export($data, true). ';';
						if(FileSystem::write($this->filename, $d, null, 0773))
						{
								if(PROFILE) Profiler::unmark("cacher_write");
								return true;
						} else
						{
								throwError(20,'PHP-Error','Can\'t create cache-file in folder includes/cache/. Please check write-permissions there.');
						}

				}
		}
		/**
		 * flushes the cache
		 *@name flush
		 *@access public
		 *@param files of cache-directory
		*/
		public function flush($files)
		{
				
		}
}