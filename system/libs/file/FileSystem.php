<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 05.12.2011
  * $Version 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class FileSystem extends Object {
	/**
	 * creates a directory and forces chmod 0777 or given mode
	 *
	 *@name mkdir
	 *@access public
	*/
	public static function requireDir($dir, $mode = 0777) {
		if(!file_exists($dir)) {
			if(mkdir($dir, $mode, true)) {
				chmod($dir, $mode);
				return true;
			} else {
				return false;
			}
		} else {
			return chmod($dir, $mode);
		}
	}
	/**
	 * alias for requireDir
	 *
	 *@name requireFolder
	 *@access public
	*/
	public static function requireFolder($dir, $mode = 0777) {
		return self::requireDir($dir, $mode);
	}
	/**
	 * createFile
	 *
	 *@name createFile
	 *@access public
	*/
	public static function createFile($file) {
		if(!file_exists($file)) {
			if($handle = @fopen($file, "w")) {
				fclose($handle);
				chmod($file, 0777);
				return true;
			} else {
				return false;
			}
		} else {
			return -1;
		}
	}
	/**
	 * writes file contentss
	 *
	 *@name writeFileContents
	 *@access public
	*/
	public static function writeFileContents($file, $content, $modifier = null,$mode = null) {
		if(file_put_contents($file, $content, $modifier)) {
			if(!isset($mode))
				$mode = 0777;
			chmod($file, $mode);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * alias for writeFileContents
	 *
	 *@name write
	 *@access public
	*/
	public static function write($file, $content, $modifier = null, $mode = null) {
		return self::writeFileContents($file, $content, $modifier, $mode);
	}
	/**
	 * sets chmod recursivly
	 *
	 *@name chmod
	 *@access public
	 *@param string - path
	 *@param int - mode
	 *@param bool - if to break and return false on fail
	*/
	public static function chmod($file, $mode, $breakOnFail = true) {
		if(is_dir($file)) {
			if(!@chmod($file, $mode) && $breakOnFail) 
				return false;
			
			foreach(scandir($file) as $_file) {
				if($_file != "." && $_file != "..")
					if(!self::chmod($file . "/" . $_file, $mode, $breakOnFail) && $breakOnFail) {
						return false;
					}
			}
			return true;
		} else {
			return @chmod($file, $mode);
		}
	}
	/**
	 * removes recursivly
	 *
	 *@name delete
	 *@access public
	 *@param string - path
	 *@param bool - if to break and return false on fail
	*/
	public static function delete($file, $breakOnFail = true) {
		if(is_dir($file)) {
			foreach(scandir($file) as $_file) {
				if($_file != "." && $_file != "..")
					if(!self::delete($file . "/" . $_file, $breakOnFail) && $breakOnFail) {
						return false;
					}
			}
			return @rmdir($file);
		} else {
			return @unlink($file);
		}
	}
	/**
	 * copies recursivly
	 *
	 *@name copy
	 *@access public
	 *@param string - source
	 *@param string - destination
	 *@param null|int - mode, if you want to make a chmod to every destination file
	 *@param bool - if to break and return false on fail
	*/
	public static function copy($source, $destination, $mode = null, $breakOnFail = true) {
		if(is_dir($source)) {
			if(!self::mkdir($destination) && $breakOnFail){
				return false;
			}
			
			foreach(scandir($source) as $file) {
				if(!self::copy($source . "/" . $file, $destination . "/" . $file, $mode, $breakOnFail) && $breakOnFail)
					return false;
			}
			return true;
		} else {
			if(copy($source, $destination)) {
				if($mode !== null) {
					chmod($destination, $mode);
				}
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * protects file-path
	 *
	 *@name protect
	 *@access public
	*/
	public static function protect($path) {
		return str_replace("../", "", $path);
	}
	
	/**
	 * sends a file to browser in chunks, because of less RAM-Usage
	 *
	 *@name readfile_chunked
	 *@access public
	*/
	public static function readfile_chunked($filename) {
		  
	
		$range = 0; 
		$size = filesize($filename); 
	
		if(isset($_SERVER['HTTP_RANGE'])) { 
			list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']); 
			str_replace($range, "-", $range); 
			$size2 = $size - 1; 
			$new_length = $size - $range; 
			HTTPResponse::setResHeader(206);
			HTTPResponse::addHeader("content-length", $new_length);
			HTTPResponse::addHeader("content-range", "bytes " . $range . $size2 . "/" . $size);
		} else { 
			$size2 = $size-1; 
			HTTPResponse::addHeader("content-range", "bytes 0-".$size2 . "/" . $size."");
			HTTPResponse::addHeader("content-length", $size);
		} 
		HTTPResponse::addHeader("Accept-Ranges", "bytes");		
		// send headers now
		HTTPResponse::sendHeader();
	
		ini_set('max_execution_time', '0');  
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$handle = fopen($filename, 'rb');
		
		fseek($handle,$range);
		
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			print $buffer;
			ob_flush();
			flush();
		}
		return fclose($handle);
	}
}
