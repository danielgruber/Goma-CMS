<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 31.10.2011
  * $Version 002
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

define("BACKUPS_PATH", CURRENT_PROJECT . "/backup/");

class Backups extends DataObject
{
		/**
		 * backupdir
		 *@name backupdir
		 *@access public
		*/
		public static $backup_dir = BACKUPS_PATH;
		/**
		 * lists the backups
		 *@name getWholeData
		 *@access public
		*/
		public function getWholeData($where = array())
		{
				Profiler::mark("backups::getWholeData");
				
				if(!file_exists(self::$backup_dir)) {
					mkdir(self::$backup_dir, 0777, true);
				}
				
				$orderby = ($this->orderby === false) ? "" : "".$this->orderby['field']." ".$this->orderby['type'];
				if(empty($this->limits) && $this->pages)
				{
						if(!is_bool($this->pages) && _ereg('^[0-9]+$', $this->pages)) {
							$start = $this->perPage * $this->pages - $this->perPage;
						} else {
							$start = $this->perPage * self::$p - $this->perPage;
						}
						$limits = array($start, $this->perPage);
						unset($start);
				} else
				{
						$limits = $this->limits;
				}
				
				if($limits && count($limits) > 0) {
					if(count($limits) == 1) {
						$max = $limits[0];
						$start = 0;
					} else {
						$max = $limits[1];
						$start = $limits[0];
					}
				}
				
				$where = (empty($where)) ? $this->where : $where;
				
				if(isset($where["id"])) {
					// just for security reason
					$where["id"] = basename($where["id"]);
					
					if(is_file(self::$backup_dir . "/" . $where["id"])) {
						
						$created = filectime(self::$backup_dir . $where["id"]);
						$data = array(array("id" => $where["id"], "name" => $where["id"], "created" => $created, "sort" => $created, "justsql" => _ereg('\.sgfs$', $where["id"])));
						
					}
				} else {
					$cacher = new Cacher("backups");
					if($cacher->checkvalid() && $cacher->created >= filemtime(self::$backup_dir)) {
						$data = (array)$cacher->getData();
					} else {
						$files = scandir(self::$backup_dir);
				
						$data = array();
						foreach($files as $file)
						{
								if(_ereg('\.sgfs$', $file))
								{
										$created = filectime(self::$backup_dir . $file);
										$data[] = array("id" => $file,"name" => $file, "created" => $created, "sort" => $created,"justsql" => true);
								} else if(_ereg("\.gfs$", $file)) {
										$created = filectime(self::$backup_dir . $file);
										$data[] = array("id" => $file,"name" => $file, "created" => $created, "sort" => $created,"justsql" => false);
								}
						}
						$cacher->write($data, 3600);
					}
				}
				
				
				usort($data, array($this, 'sort'));
				
				// pagination
				if(isset($start)) {
					$i = 0;
					$arr = array();
					foreach($data as $key => $value) {
						if(isset($start)) {
							if($i < $start) {
								$i++;
								continue;
							}
						} 
						if(isset($max)) {
							$end = $start + $max;
							if($i >= $end) {
								break;
							}
						}
						$arr[] = $value;	
						$i++;
					}
					$data = $arr;
					//unset($arr, $i, $key, $value, $end);
				}
				
				$this->wholedata = $data;
				$this->data = isset($data[$this->position]) ? $data[$this->position] : null;
				
				Profiler::unmark("backups::getWholeData");
				return $this;
		}
		/**
		 * counts all backups
		*/
		public function _count() {
			$cacher = new Cacher("backups");
			if($cacher->checkvalid() && $cacher->created >= filemtime(self::$backup_dir)) {
				return count($cacher->getData());
			} else {
				if(count($this->wholedata) <= $this->perPage)
					return count(scandir(self::$backup_dir));
				else
					return count($this->wholedata);
			}
		}
		
		/**
		 * deletes the current record(s)
		 *
		 *@name delete
		 *@access public
		*/
		public function _delete() {
			if($this->wholedata === null) {
				$this->getWholeData();
			}
			foreach($this->wholedata as $data) {
				@unlink(self::$backup_dir . "/" . $data["name"]);
			}
			$cacher = new Cacher("backups");
			$cacher->delete();
			return true;
		}
		/**
		 * sort-function
		 *@name sort
		 *@access public
		*/
		public function sort($a, $b)
		{		
				if(count($this->orderby) == 2) {
					$field = $this->orderby["field"];
					$type = $this->orderby["type"];
				} else {
					$field = "sort";
					$type = "desc";
				}
				if($field == "date")
					$field = "created";
				if(strtolower($type) == "desc") {
					if ($a[$field] == $b[$field]) 
					{
						return 0;
					}
					return ($a[$field] < $b[$field]) ? 1 : -1;
				} else {
					if ($a[$field] == $b[$field]) 
					{
						return 0;
					}
					return ($a[$field] > $b[$field]) ? 1 : -1;
				}
				
		}
		/**
		 * gets created
		*/
		public function getDate() {
			return $this->doObject("created")->date();
		}
}
class BackupsController extends Controller {}