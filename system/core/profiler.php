<?php
/**
 * @package		Goma\System\Core
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */

defined("IN_GOMA") OR die();

/**
 * This class provides profiling functionality.
 *
 * @package		Goma\System\Core
 * @version		1.0
 */
class Profiler {
	public $startTime;
	public $endTime;

	/**
	 * this var contains a log of the profiler
	 *@name log
	 */
	public $log;
	/**
	 * this contains an instance of this class
	 */
	protected static $inst;
	/**
	 * this var conatains the marks
	 */
	public $marks = array();
	/**
	 * this var conatains the time of operations
	 */
	public $times = array();
	/**
	 * shows the steps until the end
	 */
	public $steps = array();
	/**
	 * calls
	 */
	public $calls = array();
	/**
	 * memory-usage
	 */
	public $memories = array();
	/**
	 * own profile-time
	 */
	public $profile_time = 0;

	/**
	 * this starts the profiler
	 */
	public function __construct() {
		$this->startTime = microtime(true);
		$this->log .= "Starting Profiler\n";

		register_shutdown_function(array("profiler", "end"));
	}

	/**
	 *inits the profiler
	 *@name init
	 */
	public static function init() {
		return self::inst();
	}

	/**
	 * this function marks a point in the code and gives information about memory and
	 * other things
	 *@param name
	 */
	public static function mark($name) {

		if(!PROFILE) {
			return false;
		}

		$start = microtime(true);

		$times = microtime(true) - self::inst()->startTime;
		self::inst()->log .= "Mark: " . $name . "; Time since Start: " . $times * 1000 . " ms\n";
		if(!isset(self::inst()->times[$name])) {
			self::inst()->times[$name] = 0;
		}

		if(!isset(self::inst()->memories[$name])) {
			self::inst()->memories[$name] = 0;
		}

		if(isset(self::inst()->marks[$name]["start"])) {
			self::unmark($name);
			$remark = true;
		}

		$data = array("time" => microtime(true), "memory" => memory_get_usage(), "type" => "mark", "name" => $name);

		self::inst()->marks[$name]["start"] = $data;
		if(isset($remark)) {
			if(isset(self::inst()->marks[$name]["remark"])) {
				self::inst()->marks[$name]["remark"]++;
			} else {
				self::inst()->marks[$name]["remark"] = 1;
			}
		}

		$t = microtime(true) - $start;
		self::inst()->profile_time += $t;
	}

	/**
	 * ends a operation marked
	 *@param name
	 */
	public static function unmark($name, $newname = null) {

		if(!PROFILE) {
			return false;
		}

		$start = microtime(true);

		if(!isset($newname))
			$newname = $name;

		$times = microtime(true) - self::inst()->startTime;
		self::inst()->log .= "Unmark: " . $name . " with name $newname; Time since Start: " . $times * 1000 . " ms\n";

		if(!isset(self::inst()->times[$newname])) {
			self::inst()->times[$newname] = 0;
		}

		if(!isset(self::inst()->memories[$newname])) {
			self::inst()->memories[$newname] = 0;
		}

		if(isset(self::inst()->marks[$name]["start"])) {
			$time = microtime(true) - self::inst()->marks[$name]["start"]["time"];
			self::inst()->times[$newname] += $time;
			$memory = memory_get_usage() - self::inst()->marks[$name]["start"]["memory"];
			self::inst()->memories[$newname] += $memory;
		}

		if(isset(self::inst()->calls[$newname])) {
			self::inst()->calls[$newname]++;
		} else {
			self::inst()->calls[$newname] = 1;
		}

		/*$data = array
		 (
		 "time"		=> microtime(true),
		 "memory"	=> memory_get_usage(),
		 "type"		=> "unmark",
		 "name" 		=> $name
		 );

		 self::inst()->marks[$name][] = $data;

		 self::inst()->steps[] = $data;*/
		if(!isset(self::inst()->marks[$name]["remark"]) || self::inst()->marks[$name]["remark"] == 0)
			unset(self::inst()->marks[$name]["start"]);
		else if(isset(self::inst()->marks[$name]["remark"])) {
			self::inst()->marks[$name]["remark"]--;
		}

		$t = microtime(true) - $start;
		self::inst()->profile_time += $t;
	}

	/**
	 * end of the profiling
	 *@param bool - output information
	 */
	public static function end($output = false) {
		if(!PROFILE)
			return false;

		if(!defined("CURRENT_PROJECT") || !defined("LOG_FOLDER"))
			return false;

		$times = microtime(true) - self::inst()->startTime - self::inst()->profile_time;

		$currmemory = memory_get_usage();

		$profile_time = self::inst()->profile_time * 1000;

		$endWaitTime = microtime(true);
		defined("END_WAIT_TIME") OR define("END_WAIT_TIME", $endWaitTime);
		$waitTime = END_WAIT_TIME - EXEC_START_TIME - self::inst()->profile_time;

		self::inst()->log .= "End of Profiling\n";
		if(!file_exists(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/profile"))
			mkdir(ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/profile", 0777, true);
		$logfile = ROOT . CURRENT_PROJECT . "/" . LOG_FOLDER . "/profile/profile_" . date("d.m.Y_H.i.s", TIME) . ".log";
		$content = "Profile v. 1.1 Log\n";
		$content .= "Time: " . date(DATE_FORMAT, TIME) . "\n";
		$content .= "System: Goma v." . GOMA_VERSION . " - " . BUILD_VERSION . " PHP " . PHP_VERSION . "\n";
		$content .= "Profile-Time: " . $profile_time . "ms\n";
		$content .= "URL: " . $_SERVER["REQUEST_URI"] . "\n";
		$content .= "Memory-Peak-Usage: " . round(memory_get_peak_usage() / 1024) . "K\n";
		$content .= "Wait-Time: " . round($waitTime * 1000, 2) . "ms\n";
		$content .= "Times: \n\n";
		$content .= "calls   execution time     Memory             name\n";
		$content .= "---------------------------------------------------------------------\n\n";

		foreach(self::inst()->times as $key => $time) {
			$calls = isset(self::inst()->calls[$key]) ? self::inst()->calls[$key] . str_repeat(" ", 5 - strlen((string)self::inst()->calls[$key])) : null;
			$percent = round($time / $times * 100, 2);
			$time = round($time * 1000, 4);
			$timeout = "" . $time . "ms (" . $percent . "%)";
			$mempercent = isset(self::inst()->memories[$key]) ? round(self::inst()->memories[$key] / $currmemory * 100, 4) : null;
			$memory = isset(self::inst()->memories[$key]) ? round(self::inst()->memories[$key] / 1024) . "K (" . $mempercent . "%)" : null;
			$memoryout = $memory . str_repeat(" ", 20 - strlen($memory));
			$content .= "" . $calls . "   " . $timeout . "" . str_repeat(" ", 20 - strlen($timeout)) . "" . $memoryout . "" . $key . "\n";
		}

		// whole
		$content .= "======================================================================\n";
		$timeout = "" . round($times * 1000, 4) . "ms (100%)";
		$memory = round($currmemory / 1024) . "K";
		$memoryout = $memory . str_repeat(" ", 20 - strlen($memory));
		$content .= "        " . $timeout . "" . str_repeat(" ", 20 - strlen($timeout)) . "" . $memoryout . "Whole Execution";

		if($output || (PROFILE && Permission::check(7) && !Core::is_ajax())) {
			echo '<div style="background: #ffffff; width: 550px; height: 80%; padding: 5px; font-size: 12px;position: absolute; top: 50px; left: 20px; z-index: 9999;color: #000000;" id="profiler_windows">
									[ <a href="javascript: void(0);" onclick="document.getElementById(\'profiler_windows\').style.display = \'none\';">Close windows</a> ]
									<div style="overflow: auto;width: 550px; height: 98%;">
										<pre>' . $content . '</pre>
									</div>
								</div>';
		}

		if($file = fopen($logfile, "w")) {
			fwrite($file, $content);
			fclose($file);
		}
		return self::inst();
	}

	/**
	 * this function provides the instance
	 */
	public static function inst() {
		if(isset(self::$inst)) {
			return self::$inst;
		} else {
			self::$inst = new Profiler();
			return self::$inst;
		}

	}

}
