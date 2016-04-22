<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  *@todo
  * - implement POST, PUT, DELETE
  * - implement Relations with check whether relation is available in API
  * last modified: 02.11.2010
  * $Version 2.0.0 - 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)


StaticsManager::AddSaveVar("RestfulServer", "api_accesses");

class RestfulServer extends RequestHandler
{
		public $url_handlers = array(
			'$ClassName!/$ID/$Relation'	=> "handleWithDataType"
		);
		
		public $allowed_actions = array(
			"handleWithDataType"
		);
		
		/**
		 * default response format, e.g. json or xml
		 *@name default_response
		 *@access public
		*/
		public static $default_response = "json";
		
		public static $available_response_types = array(
			"json"	=> array(array("RestfulServer", "json_encode"), "text/plain")
		);
		
		/**
		 * if you want to add api-access to a class external this var will save it
		 *@name api_accesses
		 *@access protected
		*/
		public static $api_accesses = array();
		
		/**
		 * this var contains the current status of api_access-var
		 * if this is an array, api-access is limited
		 * if this is true, api-access is not limited
		 *@name access
		 *@var array|bool
		*/
		protected $access;
		/**
		 * the class_name
		 *@name ClassName
		 *@access public
		*/
		public $ClassName;
		
		/**
		 * adds an own access
		 *@name add
		 *@param string - ClassName
		 *@param bool|array - params
		*/
		public static function add($class, $params)
		{
				self::$api_accesses[$class] = $params;
		}
		
		public function handleWithDataType()
		{
				foreach(array(
					"Relation", "ID", "ClassName"
				) as $field)
				{
						if($this->getParam($field))
						{
								foreach(self::$available_response_types as $extension => $data)
								{
										if(preg_match('/^(.*)\.'.$extension.'$/i', $this->getParam($field), $match))
										{
												$this->request->params[$field] = $match[1];
												HTTPResponse::addHeader("content-type", $data[1]);
												return call_user_func_array($data[0], array($this->handle()));
										}
								}
						}
				}
				
				// default
				HTTPResponse::addHeader("content-type", self::$available_response_types[self::$default_response][1]);
				return call_user_func_array(self::$available_response_types[self::$default_response][0], array($this->handle()));
		}
		
		public function handle()
		{
				$ClassName = $this->getParam("ClassName");
				if((!StaticsManager::hasStatic($ClassName, "api_access") && !isset(self::$api_accesses[$ClassName])) || !is_subclass_of($ClassName, "DataObject"))
				{
						HTTPResponse::setResHeader(404);
						HTTPResponse::sendHeader();
						exit;			
				}
				
				$this->ClassName = $ClassName;
				
				$api_access = (StaticsManager::hasStatic($ClassName, "api_access")) ? StaticsManager::getStatic($ClassName, "api_access") : self::$api_accesses[$ClassName];
				if((!is_bool($api_access) || $api_access !== true) && !is_array($api_access))
				{
						HTTPResponse::setResHeader(403);
						HTTPResponse::sendHeader();
						exit;
				}
				
				$this->access = $api_access;
				
				// check method
				if($this->request->isHEAD() || $this->request->isGET())
						return $this->handleGET();
				
				/*if($this->request->isPOST())
						return $this->handlePOST();
				
				if($this->request->isPUT())
						return $this->handlePUT();
				
				if($this->request->isDELETE())
						return $this->handleDelete();*/
				
				HTTPResponse::setResHeader(400);
				HTTPResponse::sendHeader();
				exit;
		}
		
		/**
		 * get-requests reads data
		 *@name handleGET
		 *@access public
		*/
		public function handleGET()
		{
				$start = microtime(true);
				// fields
				if($this->getParam("fields"))
				{
						$fieldsString = $this->getParam("fields");
						$fieldsArray = explode(",", $fieldsString);
						if(is_array($this->access) && isset($this->access["view"]) && !empty($this->access["view"]))
						{
								$fields = array();
								foreach($fieldsArray as $field)
								{
										if(in_array($field, $this->access["view"]))
												$fields[] = $field;
								}
								
								if(empty($fields))
								{
										HTTPResponse::setResHeader(201);
										HTTPResponse::sendHeader();
										exit;
								}
						} else
						{
								$fields = $fieldsArray;
						}
				} else
				{
						if(is_array($this->access) && isset($this->access["view"]))
						{
								if($this->getParam("Relation"))
								{
										$fields = array();
								} else							
										$fields = $this->access["view"];
						} else
						{
								$fields = array(); // all fields
						}
				}
				
				// additional fields
				if($this->getParam("add_fields"))
				{
						$add_fieldsString = $this->getParam("add_fields");
						$add_fieldsArray = explode(",", $add_fieldsString);
				} else
				{
						$add_fieldsArray = array();
				}
				
				// sort
				if($this->getParam("sort"))
				{
						$sort = $this->getParam("sort");
						$dir = $this->getParam("dir");
						switch(strtolower($dir))
						{
								case "asc":
									$dir = "ASC";
								break;
								default:
									$dir = "DESC";
								break;
						}
						$orderby = array($sort, $dir);
				} else
				{
						$orderby = array();
				}
				
				// limit
				if($this->getParam("limit"))
				{
						$limit = $this->getParam("limit");
						if(strpos($limit, ","))
						{
								$limits = explode(",", $limit);
								
						} else
						{
								$limits = array($limit);
						}
				}
				
				// where
				if($this->getParam("ID"))
						$where = array("id" => $this->getParam("ID"));
				else
						$where = array();
				
				foreach($_GET as $key => $value)
				{
						if(!in_array($key, array(
							"limit", "sort", "dir", "fields", "search", "add_fields", "callback"
						)))
						{
								if(isset(classinfo::$class_info[$this->ClassName]["db_fields"][$key]))
										$where[$key] = $value;
						}
				}
				
				
				
				if($this->getParam("Relation"))
				{
						$relation = $this->getParam("Relation");
						$myinfo = classinfo::$class_info[$this->ClassName];
						if(
							isset($myinfo["has_one"][$relation]) || 
							isset($myinfo["has_many"][$relation]) || 
							isset($myinfo["many_many"][$relation]) || 
							isset($myinfo["belongs_many_many"][$relation])
						)
						{
								if($this->getParam("search"))
								{
										$data = DataObject::_search($this->ClassName,array($this->getParam("search")), $where);
								} else
								{
										$data = DataObject::get($this->ClassName, $where);
								}
								
								$output = $data->$relation(array(), $fields);
								
								$i = 0;
								$arr = array("class_name" => $this->ClassName, "pages" => $output->pages, "whole"	=> $output->_count(), "data" => array());
								foreach($output as $record)
								{
										$arr["data"][$i] = $record->to_array();
										foreach($add_fieldsArray as $field)
										{
												$arr["data"][$i][$field] = $record[$field];
										}
										$i++;
								}
								
								$end = microtime(true);
								$time = $end - $start;
								$arr["time"] = $time;
								
								return $arr;
						} else
						{
								HTTPResponse::setResHeader(404);
								HTTPResponse::sendHeader();
								exit;
						}					
				} else { 
						if($this->getParam("search"))
						{
								$data = DataObject::_search($this->ClassName,array($this->getParam("search")), $where, $fields);
						} else
						{
								$data = DataObject::get($this->ClassName, $where);
						}
						
						$i = 0;
						$arr = array("class_name" => $this->ClassName, "pages" => $data->pages, "whole"	=> $data->count(), "data" => array());
						foreach($data as $record)
						{
								$arr["data"][$i] = $record->to_array();
								foreach($add_fieldsArray as $field)
								{
										$arr["data"][$i][$field] = $record[$field];
								}
								$i++;
						}
						
						$end = microtime(true);
						$time = $end - $start;
						$arr["time"] = $time;
						
						return $arr;
				}
		}
		
		public static function  json_encode($data)
		{
				if(isset($_GET["callback"]))
				{
						$callback = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET["callback"]);
						return $callback . '('.json_encode($data).')';
				} else
				{
						return '('.json_encode($data).')';
				}
		}
}