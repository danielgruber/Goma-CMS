<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 11.03.2013
  * $Version 2.1.7
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

define("SESSION_TIMEOUT", 16*3600);

class livecounter extends DataObject
{
		/**
		 * disable history for this DataObject, because it would be a big lag of performance
		*/
		static $history = false;
		
		/**
		 * database-fields
		 *
		 *@name db
		*/
		static $db = array(
				'user' 			=> 'varchar(200)', 
				'phpsessid' 	=> 'varchar(800)', 
				"browser"		=> "varchar(200)",
				"referer"		=> "varchar(400)",
				"ip"			=> "varchar(30)",
				"isbot"			=> "int(1)"
			);
			
		/**
		 * the name of the table isn't livecounter, it's statistics
		 *
		 *@name table
		*/
		static $table = "statistics";
		
		/**
		 * indexes
		 *
		 *@name index
		*/
		static $index = array(
			"recordid" => false
		);
		
		/**
		 * allow writing
		*/
		public function canWrite($data = null) {
			return true;
		}
		/**
		 * allow insert
		*/
		public function canInsert($data = null) {
			return true;
		}
}

// load language for stats
loadlang('st');

class livecounterController extends Controller
{
		/**
		 * a regexp to use to intentify browsers, which support cookies
		 *
		 *@name cookie_support
		 *@access public
		*/
		public static $cookie_support = "(firefox|msie|AppleWebKit|opera|khtml|icab|irdier|teleca|webfront|iemobile|playstation)";
		
		/**
		 * a regexp to use to intentify browsers, which support cookies
		 *
		 *@name no_cookie_support
		 *@access public
		*/
		public static $no_cookie_support = "(hotbar)";
		
		/**
		 * bot-list
		*/
		public static $bot_list = "(googlebot|msnbot|CareerBot|MirrorDetector)";
		
		/**
		 * counts how much users are online
		*/
		static private $useronline = 0;
		
		/**
		 * just run once per request
		*/
		static public $alreadyRun = false;
		
		/**
		 * this function updates the database and user-status for us, that we count all visitors
		*/
		public static function run($forceLive = false) {
			if(self::$alreadyRun) {
				return true;
			}
			
			self::$alreadyRun = true;
			
			// first get userid
			$userid = member::$id;
			
			if(preg_match('/favicon\.ico/', $_SERVER["REQUEST_URI"])) {
				return false;
			}
			
			// user identifier
			if((!isset($_COOKIE['goma_sessid']) && (!preg_match("/" . self::$cookie_support . "/i", $_SERVER['HTTP_USER_AGENT']) || preg_match("/" . self::$no_cookie_support . "/i", $_SERVER['HTTP_USER_AGENT']))) || $_SERVER['HTTP_USER_AGENT'] == "" || $_SERVER['HTTP_USER_AGENT'] == "-") {
				$user_identifier = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER["REMOTE_ADDR"]);
			} else if(isset($_COOKIE['goma_sessid'])) {
				$user_identifier = $_COOKIE['goma_sessid'];
			} else {
				$user_identifier = session_id();
			}
			
			/**
			 * there's a mode that live-counter updates record by exact date, it's better, because the database can better use it's index
			*/
			if(isset($_SESSION["user_counted"])) {
				if($_SESSION["user_counted"] < (NOW - 20) || $forceLive) {
					$data = DataObject::get("livecounter", array("phpsessid" => $user_identifier, "last_modified" => $_SESSION["user_counted"]));
					if(count($data) == 1) {
						$data->first()->user = $userid;
						$data->first()->write();
						// we set last update to next know the last update and better use database-index
						$_SESSION["user_counted"] = TIME;
						return true;
					}
				} else {
					return true;
				}
			}
			
			$timeout = TIME - SESSION_TIMEOUT;
			
			
			// check if a cookie exists, that means that the user was here in the last 16 hours
			if(isset($_COOKIE['goma_sessid']))
			{
				$sessid = $_COOKIE['goma_sessid'];
				// if sessid is not the same the user has begun a new php-session, so we update our cookie
				if($user_identifier != $sessid)
				{
					$data = DataObject::get("livecounter", "phpsessid = '".dbescape($sessid)."' AND last_modified > ".dbescape($timeout)."");
					if(count($data) > 0) {
						$data->first()->user = $userid;
						$data->first()->phpsessid = $user_identifier;
						$data->first()->write(false, true);
					} else {
						$data = new LiveCounter();
						$data->user = $userid;
						$data->phpsessid = $user_identifier;
						$data->browser = $_SERVER["HTTP_USER_AGENT"];
						$data->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
						$data->ip = $_SERVER["REMOTE_ADDR"];
						$data->isbot = preg_match("/" . self::$bot_list . "/i", $_SERVER['HTTP_USER_AGENT']);
						$data->write(true, true);
					}
					unset($data);
					// set cookie
					setCookie('goma_sessid',$user_identifier, TIME + SESSION_TIMEOUT, '/', "." . $_SERVER["HTTP_HOST"], false, true);
					$_SESSION["user_counted"] = TIME;
					return true;
				} else {
					// just rewirte cookie
					setCookie('goma_sessid',$user_identifier, TIME + SESSION_TIMEOUT, '/', "." . $_SERVER["HTTP_HOST"], false, true);
				}
			}
			
			
			$data = DataObject::get("livecounter", array("phpsessid" => $user_identifier, "last_modified" => array(">", $timeout)));
			if(count($data) > 0) {
				$data->first()->user = $userid;
				$data->first()->write(false, true);
			} else {
				$data = new LiveCounter();
				$data->user = $userid;
				$data->phpsessid = $user_identifier;
				$data->browser = $_SERVER["HTTP_USER_AGENT"];
				$data->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
				$data->ip = $_SERVER["REMOTE_ADDR"];
				$data->isbot = preg_match("/" . self::$bot_list . "/i", $_SERVER['HTTP_USER_AGENT']);
				$data->write(true, true);
			}
			
			// just rewirte cookie
			setCookie('goma_sessid',$user_identifier, TIME + SESSION_TIMEOUT, '/', "." . $_SERVER["HTTP_HOST"], false, true);
			// we set last update to next know the last update and better use database-index
			$_SESSION["user_counted"] = TIME;
			
			return true;
		}
		/**
		 * checks if a user is online by id
		 *@name checkUserOnline
		 *@param int - userid
		 *@access public
		*/
		public function checkUserOnline($userid)
		{
				
				$last = TIME - 300;
				$c = DataObject::count("livecounter", array("last_modified" => array(">", $last), "user" => $userid));
				return ($c > 0) ? true : false;
		}
		/**
		 * counts how much users are online
		 *@name countMembersOnline
		 *@access public
		*/
		public static function countMembersOnline()
		{
				$last = TIME - 300;
				return DataObject::count("livecounter", array('last_modified > '.$last.' AND user != ""'));
		}
		/**
		 * counts how much users AND guests online
		 *@name countUsersOnline
		 *@access public
		*/
		public function countUsersOnline()
		{
				if(self::$useronline != 0)
				{
						return self::$useronline;
				}
				$last = time() - 60;
				$c = DataObject::count("livecounter",array("last_modified" => array(">", $last), "isbot" => 0));
				self::$useronline = $c;
				return $c;
		}
		/**
		 * counts how much users where online since...
		 *@name countUsersByLast
		 *@access public
		 *@param timestamp
		*/
		public function countUsersByLast($last)
		{

				return DataObject::count("livecounter", array("last_modified" => array(">", $last), "isbot" => 0));
		}
		/**
		 * counts user since and before..
		*/
		public function countUsersByLastFirst($last, $first)
		{

				return DataObject::count("livecounter", ' last_modified > "'.convert::raw2sql($last).'" AND last_modified < "'.convert::raw2sql($first).'" AND isbot = 0');
		}
		
		/**
		 * gets stats for the last x days
		 *
		 *@name statisticsByDay
		 *@access public
		 *@param numeric - number of periods
		 *@param numeric - length of a period in days
		 *@param start-period
		*/
		public static function statisticsByDay($showcount = 10, $days = 1, $page = 1) {
			if(!isset($page) || !preg_match('/^[0-9]+$/', $page) || $page < 1)
				$page = 1;
				
			$interval = $days * 24 * 60 * 60;
			$day = mktime(0, 0, 0, date("n", NOW), date("j", NOW), date("Y", NOW));
			
			$page--;
			$day = $day - ($interval * $page * $showcount);
			
			$max = 0;
			$data = array();
			for($i = 0; $i < $showcount; $i++) {
				
				// gets last day
				$last =$day + $interval;
				$data[$day] = DataObject::count("livecounter", "last_modified > ".$day." AND last_modified < ". $last . " AND isbot = 0");
				if($max < $data[$day]) {
					$max = $data[$day];
				}
				$day = $day - $interval;
			}
			ksort($data);
			$dataobject = new DataSet();
			foreach($data as $timestamp => $count) {
				$dataobject->push(array(
					"timestamp"	=> $timestamp,
					"count"		=> $count,
					"max"		=> $max,
					"percent"	=> ($max == 0) ? 0 : round($count / $max * 100),
					"day"		=> true
				));
			}
			
			return $dataobject;
		}
		
		/**
		 * gets stats for the last x month
		 *
		 *@name statisticsByMonth
		 *@access public
		 *@param numeric - number of periods
		 *@param numeric - length of a period in month
		*/
		public static function statisticsByMonth($showcount = 10, $page = 1) {
			if(!isset($page) || !preg_match('/^[0-9]+$/', $page) || $page < 1)
				$page = 1;
			
			$page--;
			
			// get last month
			$month = date("n", NOW);
			$year = date("Y", NOW);
			
			for($i = 0; $i < $page * $showcount; $i++) {
				if($month == 1) {
					$month = 12;
					$year--;
				} else {
					$month--;
				}
			}
			
			$start = mktime(0, 0, 0, $month, 1, $year); // get 1st of last month 00:00:00
			$endm = date("n", NOW);
			$endy = date("Y", NOW);
			if($endm == 12) {
				$endm = 1;
				$endy++;
			} else {
				$endm++;
			}
			
			for($i = 0; $i < $page * $showcount; $i++) {
				if($endm == 1) {
					$endm = 12;
					$endy--;
				} else {
					$endm--;
				}
			}
			
			$end = mktime(0, 0, 0, $endm, 1, $endy);
			$max = 0;
			$data = array();
			for($i = 0; $i < $showcount; $i++) {
				
				
				$data[$start] = DataObject::count("livecounter", "last_modified > ".$start." AND last_modified < ". $end . " AND isbot = 0");
				if($max < $data[$start]) {
					$max = $data[$start];
				}
				// recalculate the new start and end
				$end = $start;
				if($month == 1) {
					$month = 12;
					$year--;
				} else {
					$month--;
				}
				$start = mktime(0, 0, 0, $month, 1, $year); // get 1st of the month before month 00:00:00
			}
			unset($start, $end, $month, $year);
			ksort($data);
			$dataobject = new DataSet();
			foreach($data as $timestamp => $count) {
				$dataobject->push(array(
					"timestamp"	=> $timestamp,
					"count"		=> $count,
					"max"		=> $max,
					"percent"	=> ($max == 0) ? 0 : round($count / $max * 100),
					"month"		=> true
				));
			}
			
			return $dataobject;
		}
}